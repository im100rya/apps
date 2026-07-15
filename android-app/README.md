# ConsultPro Android App Scaffold

Jetpack Compose Android client for online consultations.

## User Journey
1. Login with OTP.
2. Select one of the professional categories:
   - Lawyer
   - CA/Auditor
   - Doctor
   - Astrologer
   - Educational/Career Consultant
3. Enter consultation room:
   - Text chat
   - Audio call button (WebRTC integration point)

## Tech Design
- UI: Jetpack Compose
- API: Retrofit (backend REST)
- Realtime signaling: WebSocket
- Audio: WebRTC (`org.webrtc:google-webrtc`)

## Next Steps
- Replace placeholder OTP verification with backend integration.
- Add repository + ViewModel layers.
- Add proper WebRTC peer connection setup (`PeerConnectionFactory`, `AudioTrack`, SDP exchange).
