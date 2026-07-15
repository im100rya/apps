from datetime import datetime, timedelta, timezone
from enum import Enum
from typing import Dict, List, Optional
from uuid import uuid4

from fastapi import Depends, FastAPI, Header, HTTPException, WebSocket, WebSocketDisconnect
from pydantic import BaseModel
from sqlmodel import Field, Session, SQLModel, create_engine, select

app = FastAPI(title="ConsultPro SaaS API", version="1.0.0")
engine = create_engine("sqlite:///consultpro.db", connect_args={"check_same_thread": False})


class Role(str, Enum):
    USER = "USER"
    PROFESSIONAL = "PROFESSIONAL"


class Category(str, Enum):
    LAWYER = "LAWYER"
    CA_AUDITOR = "CA_AUDITOR"
    DOCTOR = "DOCTOR"
    ASTROLOGER = "ASTROLOGER"
    EDUCATIONAL_CAREER_CONSULTANT = "EDUCATIONAL_CAREER_CONSULTANT"


class Tenant(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    name: str
    slug: str = Field(index=True, unique=True)


class User(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    tenant_id: int = Field(index=True)
    phone: str = Field(index=True)
    role: Role = Field(default=Role.USER)
    display_name: str
    category: Optional[Category] = Field(default=None)


class OtpCode(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    tenant_id: int = Field(index=True)
    phone: str
    code: str
    expires_at: datetime


class Conversation(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    tenant_id: int = Field(index=True)
    user_id: int
    professional_id: int


class Message(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    tenant_id: int = Field(index=True)
    conversation_id: int = Field(index=True)
    sender_id: int
    content: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))


class OtpRequest(BaseModel):
    phone: str


class OtpVerify(BaseModel):
    phone: str
    code: str
    display_name: str


class ProfessionalOut(BaseModel):
    id: int
    display_name: str
    category: Category


class ChatIn(BaseModel):
    sender_id: int
    content: str


class WebRTCSignal(BaseModel):
    conversation_id: int
    sender_id: int
    target_id: int
    type: str
    payload: dict


rtc_clients: Dict[int, WebSocket] = {}


def get_tenant_slug(x_tenant: str = Header(...)) -> str:
    return x_tenant


def get_or_create_tenant(tenant_slug: str, db: Session) -> Tenant:
    tenant = db.exec(select(Tenant).where(Tenant.slug == tenant_slug)).first()
    if not tenant:
        tenant = Tenant(name=tenant_slug.title(), slug=tenant_slug)
        db.add(tenant)
        db.commit()
        db.refresh(tenant)
    return tenant


@app.on_event("startup")
def on_startup() -> None:
    SQLModel.metadata.create_all(engine)


@app.post("/auth/request-otp")
def request_otp(payload: OtpRequest, tenant_slug: str = Depends(get_tenant_slug)):
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        otp = f"{str(uuid4().int)[0:6]}"
        db.add(
            OtpCode(
                tenant_id=tenant.id,
                phone=payload.phone,
                code=otp,
                expires_at=datetime.now(timezone.utc) + timedelta(minutes=5),
            )
        )
        db.commit()
    return {"message": "OTP generated", "debug_otp": otp}


@app.post("/auth/verify-otp")
def verify_otp(payload: OtpVerify, tenant_slug: str = Depends(get_tenant_slug)):
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        otp = db.exec(
            select(OtpCode).where(
                OtpCode.tenant_id == tenant.id,
                OtpCode.phone == payload.phone,
                OtpCode.code == payload.code,
            )
        ).first()
        if not otp or otp.expires_at < datetime.now(timezone.utc):
            raise HTTPException(status_code=400, detail="Invalid or expired OTP")

        user = db.exec(
            select(User).where(User.tenant_id == tenant.id, User.phone == payload.phone)
        ).first()
        if not user:
            user = User(
                tenant_id=tenant.id,
                phone=payload.phone,
                display_name=payload.display_name,
                role=Role.USER,
            )
            db.add(user)
            db.commit()
            db.refresh(user)

    return {"user_id": user.id, "tenant": tenant_slug}


@app.get("/professionals", response_model=List[ProfessionalOut])
def professionals(category: Optional[Category] = None, tenant_slug: str = Depends(get_tenant_slug)):
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        query = select(User).where(User.tenant_id == tenant.id, User.role == Role.PROFESSIONAL)
        if category:
            query = query.where(User.category == category)
        pros = db.exec(query).all()
        return [
            ProfessionalOut(id=p.id, display_name=p.display_name, category=p.category)
            for p in pros
            if p.category
        ]


@app.post("/admin/bootstrap-professionals")
def bootstrap_professionals(tenant_slug: str = Depends(get_tenant_slug)):
    seed = [
        ("Adv. Priya Sharma", Category.LAWYER),
        ("CA Rahul Verma", Category.CA_AUDITOR),
        ("Dr. Neha Kapoor", Category.DOCTOR),
        ("Astrologer Dev", Category.ASTROLOGER),
        ("Mentor Anjali Iyer", Category.EDUCATIONAL_CAREER_CONSULTANT),
    ]
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        for name, cat in seed:
            exists = db.exec(
                select(User).where(
                    User.tenant_id == tenant.id,
                    User.display_name == name,
                    User.role == Role.PROFESSIONAL,
                )
            ).first()
            if not exists:
                db.add(
                    User(
                        tenant_id=tenant.id,
                        phone=f"+1000000{uuid4().int % 9999}",
                        role=Role.PROFESSIONAL,
                        display_name=name,
                        category=cat,
                    )
                )
        db.commit()
    return {"message": "Professionals bootstrapped"}


@app.post("/conversations")
def create_conversation(user_id: int, professional_id: int, tenant_slug: str = Depends(get_tenant_slug)):
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        conversation = Conversation(
            tenant_id=tenant.id, user_id=user_id, professional_id=professional_id
        )
        db.add(conversation)
        db.commit()
        db.refresh(conversation)
        return {"conversation_id": conversation.id}


@app.get("/conversations/{conversation_id}/messages")
def list_messages(conversation_id: int):
    with Session(engine) as db:
        msgs = db.exec(select(Message).where(Message.conversation_id == conversation_id)).all()
        return msgs


@app.post("/conversations/{conversation_id}/messages")
def send_message(conversation_id: int, payload: ChatIn, tenant_slug: str = Depends(get_tenant_slug)):
    with Session(engine) as db:
        tenant = get_or_create_tenant(tenant_slug, db)
        msg = Message(
            tenant_id=tenant.id,
            conversation_id=conversation_id,
            sender_id=payload.sender_id,
            content=payload.content,
        )
        db.add(msg)
        db.commit()
        db.refresh(msg)
        return msg


@app.websocket("/ws/rtc/{user_id}")
async def rtc_signaling(websocket: WebSocket, user_id: int):
    await websocket.accept()
    rtc_clients[user_id] = websocket
    try:
        while True:
            payload = WebRTCSignal(**await websocket.receive_json())
            target = rtc_clients.get(payload.target_id)
            if target:
                await target.send_json(payload.model_dump())
    except WebSocketDisconnect:
        if user_id in rtc_clients:
            del rtc_clients[user_id]
