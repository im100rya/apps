# ConsultPro SaaS Backend (Linux-hostable)

This backend is a multi-tenant FastAPI service for online professional consultation.

## Features
- Tenant isolation via `X-Tenant` header.
- OTP login flow (`request-otp`, `verify-otp`).
- Professional catalog for:
  - Lawyer
  - CA/Auditor
  - Doctor
  - Astrologer
  - Educational/Career Consultant
- Conversation + chat message APIs.
- WebRTC signaling WebSocket endpoint for in-app audio calls.

## Quick Start
```bash
cd saas-backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

## API Flow
1. `POST /admin/bootstrap-professionals` with `X-Tenant: <tenant-slug>`.
2. `POST /auth/request-otp` then `POST /auth/verify-otp`.
3. `GET /professionals` (optional `?category=LAWYER`).
4. `POST /conversations?user_id=<id>&professional_id=<id>`.
5. Chat over REST (`/messages`) and audio signaling over `/ws/rtc/{user_id}`.

## Linux Deployment
Use Docker:
```bash
cd saas-backend
docker compose up --build -d
```

Put Nginx in front for TLS termination and route traffic to `localhost:8000`.
