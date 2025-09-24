import axios from 'axios';

export type ChatMessage = { role: 'system' | 'user' | 'assistant'; content: string };

const OPENAI_API_KEY = process.env.OPENAI_API_KEY || '';
const OPENAI_BASE_URL = 'https://api.openai.com/v1/chat/completions';
const MODEL = process.env.OPENAI_MODEL || 'gpt-4o';

export async function chat(messages: ChatMessage[]) {
  if (!OPENAI_API_KEY) {
    throw new Error('OPENAI_API_KEY is not set');
  }

  const { data } = await axios.post(
    OPENAI_BASE_URL,
    { model: MODEL, messages },
    {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${OPENAI_API_KEY}`,
      },
      timeout: 60_000,
    }
  );

  const content: string = data?.choices?.[0]?.message?.content ?? '';
  return content;
}
