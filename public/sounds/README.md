# Quiz Sound Effects

This directory stores custom audio files for quiz answer feedback.

## File Naming Convention

- `correct.mp3` / `correct.wav` / `correct.ogg` - Sound for correct answers
- `incorrect.mp3` / `incorrect.wav` / `incorrect.ogg` - Sound for wrong answers

## Upload Instructions

1. Go to Admin Dashboard (http://127.0.0.1:8000/admin/dashboard)
2. Scroll to the "Quiz Sound Effects" section
3. Upload your custom sound files (MP3, WAV, or OGG format)
4. Maximum file size: 2MB per file

## Recommendations

- Keep sounds short (0.5-2 seconds) for quick feedback
- Use clear, distinct sounds for correct vs incorrect
- MP3 format is recommended for best browser compatibility
- Test sounds after uploading

## Fallback Behavior

If no custom sounds are uploaded, the system will automatically use generated sounds using the Web Audio API.
