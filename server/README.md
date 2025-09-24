# improveseo-ai-server

Express.js server for AI content generation used by the ImproveSEO plugin.

## Setup

1. Copy `.env.sample` to `.env` and set values:

```
PORT=8080
OPENAI_API_KEY=YOUR_OPENAI_KEY
```

2. Install dependencies:

- Preferred: pnpm
- Fallback: npm

3. Run in dev mode:

```
pnpm install
pnpm dev
# or with npm
npm install
npm run dev
```

## API

POST `/v1/generate`

Payload JSON:

```
{
  "seed_keyword": "<string>",
  "keyword_selection": "<string>",
  "seed_options": "<string>",
  "nos_of_words": "600 to 1200 words" | "1200 to 2400 words",
  "content_lang": "<string>",
  "voice_tone": "<string>",
  "point_of_view": "<string>",
  "title": "<string>",
  "call_to_action": "<string>",
  "details_to_include": "<string>",
  "AudienceData": "<string>",
  "for_testing_only": "<string>"
}
```

Response JSON:

```
{
  "ai_title": "...",
  "ai_content": "<html>...</html>",
  "lsi_keywords": ["..."],
  "facts": ["..."],
  "prompt_collection": { "lsiPrompt": "...", "factsPrompt": "...", "introTocPrompt": "..." },
  "meta": { "title": "...", "description": "..." }
}
```

## Example

```
curl -X POST http://localhost:8080/v1/generate \
  -H "Content-Type: application/json" \
  -d '{
    "seed_keyword":"best hiking boots",
    "nos_of_words":"600 to 1200 words",
    "content_lang":"English",
    "voice_tone":"friendly",
    "AudienceData":"{\"age\":\"25-34\"}"
  }'
```
