import { chat, type ChatMessage } from './openai.js';
import { cleanupHtml, enhanceWithTOC, fixAnchors } from '../utils/enhancers.js';

export type GenerateInput = {
  seed_keyword: string;
  keyword_selection?: string;
  seed_options?: string;
  nos_of_words: '600 to 1200 words' | '1200 to 2400 words';
  content_lang: string;
  voice_tone?: string;
  point_of_view?: string;
  title?: string;
  call_to_action?: string;
  details_to_include?: string;
  AudienceData?: string;
  for_testing_only?: string;
};

export async function generatePost(input: GenerateInput) {
  const {
    seed_keyword,
    keyword_selection = '',
    seed_options = '',
    nos_of_words,
    content_lang,
    voice_tone = '',
    point_of_view = '',
    title = '',
    call_to_action = '',
    details_to_include = '',
    AudienceData = '',
    for_testing_only = '',
  } = input;

  const sizeHint = nos_of_words === '1200 to 2400 words' ? '1200-2400' : '600-1200';

  const lsiPrompt = `You are an SEO assistant. Generate a JSON array of 50 LSI keywords related to the main keyword. Do not include explanations.
Main keyword: ${seed_keyword}
Audience data: ${AudienceData}`;

  const factsPrompt = `List 5 concise facts (JSON array of strings) that MUST be incorporated in the article for the main keyword below. No commentary.
Main keyword: ${seed_keyword}
Audience data: ${AudienceData}`;

  const [lsiRaw, factsRaw] = await Promise.all([
    chat([
      { role: 'system', content: 'Return only valid JSON array of strings.' },
      { role: 'user', content: lsiPrompt },
    ] as ChatMessage[]),
    chat([
      { role: 'system', content: 'Return only valid JSON array of strings.' },
      { role: 'user', content: factsPrompt },
    ] as ChatMessage[]),
  ]);

  let lsi_keywords: string[] = [];
  let facts: string[] = [];
  try {
    lsi_keywords = JSON.parse(lsiRaw);
    if (!Array.isArray(lsi_keywords)) throw new Error('LSI not array');
  } catch {
    lsi_keywords = lsiRaw
      .split(/[,\n]/)
      .map((s) => s.trim())
      .filter(Boolean);
  }
  try {
    facts = JSON.parse(factsRaw);
    if (!Array.isArray(facts)) throw new Error('Facts not array');
  } catch {
    facts = factsRaw
      .split(/[,\n]/)
      .map((s) => s.trim())
      .filter(Boolean)
      .slice(0, 5);
  }

  const introTocPrompt = `Write ONLY the Introduction and a hyperlinked Table of Contents for an article in ${content_lang}.
Constraints:
- Word target: ${sizeHint} words for the full article; produce a ~2-3 paragraph introduction now.
- Voice & POV: ${voice_tone || 'neutral'}; ${point_of_view || ''}.
- Audience: ${AudienceData}.
- Include meaningful H2/H3 sections in the TOC; use anchor-friendly IDs.
- Main keyword: ${seed_keyword}.
- Seed options/selection: ${seed_options} ${keyword_selection}.
- Facts to include later: ${facts.join('; ')}.
- CTA (to weave into conclusion later): ${call_to_action}.
- Required details: ${details_to_include}.

Return HTML only.`;

  const titleText = title || `Draft: ${seed_keyword}`;

  const draftHtml = await chat([
    { role: 'system', content: 'You are an expert SEO article writer.' },
    { role: 'user', content: `Title: ${titleText}\n\n${introTocPrompt}` },
  ]);

  // Enhancements similar to PHP helpers
  const cleaned = cleanupHtml(draftHtml);
  const withToc = enhanceWithTOC(cleaned);
  const finalHtml = fixAnchors(withToc);

  // Meta title/description
  const metaTitle = await chat([
    { role: 'system', content: 'Return a single line title for SEO. No quotes.' },
    { role: 'user', content: `Create an SEO title for: ${titleText} | Keyword: ${seed_keyword}` },
  ]);
  const metaDescription = await chat([
    { role: 'system', content: 'Return a single sentence meta description under 155 chars.' },
    {
      role: 'user',
      content: `Create a meta description for: ${titleText} | Keyword: ${seed_keyword}`,
    },
  ]);

  return {
    ai_title: titleText,
    ai_content: finalHtml,
    lsi_keywords,
    facts,
    prompt_collection: {
      lsiPrompt,
      factsPrompt,
      introTocPrompt,
    },
    meta: {
      title: metaTitle.trim(),
      description: metaDescription.trim(),
    },
  };
}
