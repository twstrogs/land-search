@echo off

cd /d C:\laragon\www\land-search-v2

C:\laragon\bin\php\php-8.3.30-nts-Win32-vs16-x64\php.exe artisan serve --host=0.0.0.0 --port=8000

pause

export OLLAMA_API_KEY="34b7cb4cc415406d93730bedab3e8992.6yiypcXb-153_JWWVAgTq_5U"
curl https://ollama.com/api/chat \
  -H "Authorization: Bearer $OLLAMA_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "gpt-oss:120b",
    "messages": [
      { "role": "user", "content": "Say hello in Vietnamese" }
    ],
    "stream": false
  }'