# ConsultPro End-to-End Architecture

## Modules
- **Android App** (`android-app/`): OTP auth, professional discovery, chat, audio calls.
- **SaaS Backend** (`saas-backend/`): Tenant-aware APIs, chat persistence, signaling server.
- **Deployment**: Linux host with Docker + Nginx + TLS.

## Multi-tenant SaaS Model
- Tenant identified by `X-Tenant` header.
- All key records carry `tenant_id`.
- Suitable for white-label clients or multiple business units.

## Realtime Communication
- **Chat**: persisted through REST API.
- **Audio Call**:
  - WebRTC on Android clients for media transport.
  - Backend WebSocket only for signaling (offer/answer/ICE candidates).

## Recommended Production Add-ons
- PostgreSQL + Redis.
- SMS gateway (Twilio/MSG91) for real OTP.
- JWT-based session auth.
- STUN/TURN (Coturn) for robust call connectivity.
- Observability: Prometheus + Grafana + centralized logs.
