# Mentorship Quest Mobile Client

This Flutter app is a simple mobile participant client for the mentorship quest platform.

## Features
- Load quest stages from the backend
- Open stage and view individual tasks
- Submit open-answer and choice-based responses

## Run
1. Install Flutter SDK
2. Navigate to `mobile`
3. Run `flutter pub get`
4. Start on device or emulator:
   - `flutter run`

## API Configuration
The default API URL is configured in `lib/services/api_service.dart` as `http://10.0.2.2:8000`.
Use an emulator or adjust the base URL to your backend host if needed.
