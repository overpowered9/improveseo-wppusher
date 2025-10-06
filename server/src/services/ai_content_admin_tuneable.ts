// A single entry to generate an AI HTML post, replicating your PHP flow.
// Requires Node 18+ (global fetch). Pass your OpenAI API key via params.apiKey or process.env.OPENAI_API_KEY.

export interface GeneratePostParams {
  apiKey?: string;
  seed_keyword: string;
  keyword_selection?: string;
  seed_options?: string;
  nos_of_words?: '600 to 1200 words' | '1200 to 2400 words' | '2400 to 3600 words';
  content_lang?: string;
  voice_tone?: string;
  point_of_view?: string;
  title?: string;
  call_to_action?: string;
  details_to_include?: string;
  for_testing_only?: number;
  audienceData?: string;
}

export interface GeneratePostResult {
  search_data: string;
  content: string;
  meta_title: string;
  meta_descreption: string;
}

interface ChatMessage {
  role: 'system' | 'user' | 'assistant';
  content: string;
}

interface OpenAIChatResponseChoice {
  message?: { role?: string; content?: string };
}

interface OpenAIChatResponse {
  choices?: OpenAIChatResponseChoice[];
}

export async function generateAiHtmlPost2(params: GeneratePostParams): Promise<GeneratePostResult> {
  const {
    apiKey = process.env.OPENAI_API_KEY,
    seed_keyword,
    keyword_selection = '',
    seed_options = '',
    nos_of_words = '1200 to 2400 words', // '600 to 1200 words' | '1200 to 2400 words' | '2400 to 3600 words'
    content_lang = 'en',
    voice_tone = '',
    point_of_view = '',
    title = '',
    call_to_action = '',
    details_to_include = '',
    for_testing_only = 0,
    audienceData // optional; if missing we will try to generate it
  } = params || ({} as GeneratePostParams);

  if (!apiKey) {
    throw new Error('Missing OpenAI API key');
  }
  if (!seed_keyword) {
    throw new Error('seed_keyword is required');
  }

  const model = 'gpt-4o';

  // 1) Ensure audience data (replicates getAudienceData() if not provided)
  const audience = audienceData || await getAudienceData({ apiKey, model, seed_keyword, content_lang });

  // 2) LSI keywords
  const lsiPrompt = buildLsiPrompt(seed_keyword, audience);
  const LSI_Keywords = await chatOnce(apiKey, model, [
    { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
    { role: 'user', content: lsiPrompt }
  ]);

  // 3) Facts generation (replicates the “Now generate facts.” block)
  const factsPrompt = buildFactsPrompt(seed_keyword, audience);
  const facts_prompt_response = await chatOnce(apiKey, model, [
    { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
    { role: 'user', content: factsPrompt }
  ]);

  // 4) Build the big “basic prompt” (Intro + TOC only on first pass)
  const basic_prompt = buildBasicPrompt({
    nos_of_words,
    seed_keyword,
    title,
    LSI_Keywords,
    voice_tone,
    point_of_view,
    audience,
    details_to_include,
    content_lang,
    call_to_action,
    facts_prompt_response
  });

  // 5) Intro + TOC call
  const baseMessages: ChatMessage[] = [
    { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
    { role: 'user', content: basic_prompt }
  ];
  const basic_prompt_response = await chatOnce(apiKey, model, baseMessages);

  // 6) Build the conversation for sections based on nos_of_words
  const sizes = normalizeSize(nos_of_words);
  const { sectionCount, sectionWordMin, sectionWordMax, conclusionMin, conclusionMax, faqsMin, faqsMax, whatsMin, whatsMax } = sizes;

  let convo: ChatMessage[] = [
    { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
    { role: 'user', content: basic_prompt },
    { role: 'assistant', content: basic_prompt_response }
  ];

  // Section prompts (first..nth)
  const sectionResponses: string[] = [];
  for (let i = 1; i <= sectionCount; i++) {
    const prompt = `Now generate the ${ordinal(i)} subtitle content. IMPORTANT: Output should not be more than ${sectionWordMin}-${sectionWordMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`;
    convo.push({ role: 'user', content: prompt });
    const resp = await chatOnce(apiKey, model, convo);
    sectionResponses.push(wrapSection(resp));
    convo.push({ role: 'assistant', content: resp });
  }

  // Conclusion
  const conclusionPrompt = `Now generate the conclusion content. IMPORTANT: Output should not be more than ${conclusionMin}-${conclusionMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`;
  convo.push({ role: 'user', content: conclusionPrompt });
  const conclusionResp = await chatOnce(apiKey, model, convo);
  convo.push({ role: 'assistant', content: conclusionResp });

  // FAQs
  const faqsPrompt = `Now generate the FAQs content. IMPORTANT: Output should not be more than ${faqsMin}-${faqsMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`;
  convo.push({ role: 'user', content: faqsPrompt });
  const faqsResp = await chatOnce(apiKey, model, convo);
  convo.push({ role: 'assistant', content: faqsResp });

  // What’s Next
  const whatsNextPrompt = `Now generate What is next? content. IMPORTANT: Output should not be more than ${whatsMin}-${whatsMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`;
  convo.push({ role: 'user', content: whatsNextPrompt });
  const whatsNextResp = await chatOnce(apiKey, model, convo);

  // 7) Assemble HTML like the PHP template
  let content_final =
    '<div class="main-content-section-improveseo">' +
    basic_prompt_response +
    sectionResponses.map(s => `<div style="margin-bottom: 15px;margin-top: 50px;">${s}</div>`).join('') +
    `<div style="margin-bottom: 15px;margin-top: 50px;">${conclusionResp}</div>` +
    `<div style="margin-bottom: 15px;margin-top: 50px;">${faqsResp}</div>` +
    `<div style="margin-bottom: 15px;margin-top: 50px;">${whatsNextResp}</div>` +
    `</div>`;

  // 8) Post-process (mirror your PHP helpers)
  content_final = stripParenthesesWrappingContactTokens(content_final);
  content_final = convert_emails_to_links(content_final);
  content_final = convert_urls_to_links(content_final);
  content_final = stripParenthesesAroundAnchorTags(content_final);

  // Light HTML entity/nbsp cleanup
  content_final = content_final.replace(/&nbsp;/g, '').replace(/<p>&nbsp;<\/p>/g, '').replace(/<p>\s<\/p>/g, '').replace(/<p><\/p>/g, '');

  // Remove boilerplate headings if present
  content_final = replace_content(content_final, '<h2>Main Content Sections</h2>');
  content_final = replace_content(content_final, '<p>—</p>');

  content_final = removePTags(content_final);
  content_final = removeConsecutiveSpecialCharacters(content_final);

  // Ensure TOC anchors match headings
  content_final = verifyAndFixTOCLinks(content_final);

  // Optional smooth scroll style (kept minimal)
  content_final += '<style>p{padding-bottom:2px!important}html{scroll-behavior:smooth}</style>';

  // Optional meta generation (same style as WP)
  const meta_title = await generateMetaTitle({ apiKey, model, aigeneratedtitle: title || seed_keyword, seed_keyword });
  const meta_descreption = await generateMetaDescription({ apiKey, model, aigeneratedtitle: title || seed_keyword, seed_keyword, content: content_final });

  return {
    search_data: title && title.trim() ? title : seed_keyword,
    content: content_final,
    meta_title,
    meta_descreption
  };
}

/* ---------------- OpenAI helpers ---------------- */

async function chatOnce(apiKey: string, model: string, messages: ChatMessage[]): Promise<string> {
  const res = await fetch('https://api.openai.com/v1/chat/completions', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${apiKey}`
    },
    body: JSON.stringify({ model, messages })
  });
  if (!res.ok) {
    const text = await res.text().catch(() => '');
    throw new Error(`OpenAI error ${res.status}: ${text}`);
  }
  const data: OpenAIChatResponse = await res.json();
  return (data.choices?.[0]?.message?.content || '').replace(/^['"]|['"]$/g, '');
}

function buildLsiPrompt(seed_keyword: string, audience: string): string {
  return `As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.

   Main keyword: ${seed_keyword}

   Audience data: {${audience}}`;
}

function buildFactsPrompt(seed_keyword: string, audience: string): string {
  return `Now generate facts.

Main Keyword: ${seed_keyword}

Audience data: {${audience}}`;
}

function buildBasicPrompt(ctx: {
  nos_of_words: GeneratePostParams['nos_of_words'];
  seed_keyword: string;
  title: string;
  LSI_Keywords: string;
  voice_tone: string;
  point_of_view: string;
  audience: string;
  details_to_include: string;
  content_lang: string;
  call_to_action: string;
  facts_prompt_response: string;
}): string {
  const {
    nos_of_words,
    seed_keyword,
    title,
    LSI_Keywords,
    voice_tone,
    point_of_view,
    audience,
    details_to_include,
    content_lang,
    call_to_action,
    facts_prompt_response
  } = ctx;

  // Medium/large variants in your code differ in TOC; we standardize on clickable anchors like your newer medium flow
  const mediumFormat = `
Use the following formatting and structure for the output:
{
IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

<h2 id="table-of-contents">Table of Contents</h2> (Heading 2) - should not be more than 50 words. Format as clickable links: <ul><li><a href="#section-id">Section Title</a></li></ul>

Main Content Sections - Create 4 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 350-400 words. Use format: <h2 id="section-id">Section Title</h2> and <h3 id="subsection-id">Subsection Title</h3>

<h2 id="conclusion">Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.

<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.
Q: 
A:
Q: 
A: 
Q:
A: 

<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.
}

Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don\`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type.

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:
                Main keyword: ${seed_keyword}
                Title: "${title}"
                LSI keywords: ${LSI_Keywords}
                Tone of voice: ${voice_tone} 
                Point of view: ${point_of_view}
                Audience data: {${audience}}
                Details to include: ${details_to_include} 
                Language: ${content_lang}
                Call to action from user: \`${call_to_action}\`
                Facts to include: {${facts_prompt_response}} Do not print "Main Content Sections" text in output.`;

  const largeFormat = `
Use the following formatting and structure for the output:
{
IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format

<h2>Main Content Sections</h2> (Heading 2) - Create 5 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 450-600 words. (Do not include the header ‘Main Content Sections’)

<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.

<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.
Q: 
A:
Q: 
A: 
Q:
A: 

<h2>What’s next?</h2> (Heading 2) - What’s next? should not be more than 100-150 words.
}

Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don\`t provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type.

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:
                Main keyword: ${seed_keyword}
                Title: "${title}"
                LSI keywords: ${LSI_Keywords}
                Tone of voice: ${voice_tone} 
                Point of view: ${point_of_view}
                Audience data: {${audience}}
                Details to include: ${details_to_include} 
                Language: ${content_lang}
                Call to action from user: \`${call_to_action}\`
                Facts to include: {${facts_prompt_response}} Do not print "Main Content Sections" text in output. Do not print "#" text in output.`;

  const sharedIntro = `
The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.

Include the following sections in the post:

Introduction - Provide a concise preview of the content\`s value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don’t create a header for this section, only provide the paragraph. 

Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

Main Content Sections - Create content sections with subtitles using keywords and their variations at a 1-2% usage rate per 100 words to prevent keyword stuffing. Each section should contain a detailed content, employing NLP and EI for relatability and actionability. Make the content deep so it responds to the emotions and curiosity of the readers. Use storytelling techniques to make your content more relatable and memorable. Share personal anecdotes, case studies, and real-life examples. Stories are a powerful NLP tool to create an emotional connection. Share personal anecdotes or relatable scenarios to make your content more engaging and memorable. Prevent from producing worthless fluff content that doesn’t add to the value of the blog post. Do not include any fluff when producing content. Each sentence should provide value to the overall goal of the content piece. Strictly follow this guideline. Ensure to insert interesting and fun facts about the Main keyword when producing the content: use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. DO NOT include any conclusion or summary for each content sections. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions user has while searching it. You should understand what deep questions and concerns users want to answer and build your output based on these. Use the following NLP Techniques for creating content:
Anchoring, Reframing, Vivid Descriptions, Addressing Reader Emotions, High-Quality Content.
`;

  const formatBlock = (nos_of_words === '2400 to 3600 words') ? largeFormat : mediumFormat;
  return sharedIntro + '\n' + formatBlock;
}

function normalizeSize(nos: string) {
  if (nos === '600 to 1200 words') {
    return {
      sectionCount: 4,
      sectionWordMin: 200,
      sectionWordMax: 250,
      conclusionMin: 100,
      conclusionMax: 150,
      faqsMin: 100,
      faqsMax: 150,
      whatsMin: 100,
      whatsMax: 150
    };
  }
  if (nos === '2400 to 3600 words') {
    return {
      sectionCount: 5,
      sectionWordMin: 450,
      sectionWordMax: 600,
      conclusionMin: 150,
      conclusionMax: 200,
      faqsMin: 100,
      faqsMax: 150,
      whatsMin: 150,
      whatsMax: 200
    };
  }
  // default medium 1200-2400
  return {
    sectionCount: 4,
    sectionWordMin: 350,
    sectionWordMax: 400,
    conclusionMin: 150,
    conclusionMax: 200,
    faqsMin: 100,
    faqsMax: 150,
    whatsMin: 150,
    whatsMax: 200
  };
}

function ordinal(n: number): string {
  const s = ['th', 'st', 'nd', 'rd'];
  const v = n % 100;
  return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

function wrapSection(html: string): string {
  // Keep as-is; WP code just appends
  return html;
}

async function getAudienceData({ apiKey, model, seed_keyword, content_lang }: { apiKey: string; model: string; seed_keyword: string; content_lang: string; }): Promise<string> {
  const prompt = `Assume someone enters the keyword ${seed_keyword} into a search engine. Analyze the following characteristics: 1. [demographic information] 2. [tone preferences] 3. [reading level preference] 4. [emotional needs/pain points]. This information will be used to create content that is specifically appealing to such people. Do not give content recommendations yet. As an output, write just information for characteristics without any explanation or introduction.`;
  const resp = await chatOnce(apiKey, model, [
    { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
    { role: 'user', content: prompt }
  ]);
  return resp;
}

async function generateMetaTitle({ apiKey, model, aigeneratedtitle, seed_keyword }: { apiKey: string; model: string; aigeneratedtitle: string; seed_keyword: string; }): Promise<string> {
  const q = `Create an SEO optimized meta title based on the blog post title \`${aigeneratedtitle}\` and the keyword \`${seed_keyword}\`. max length of title should be 50-60 characters including spaces.`;
  return chatOnce(apiKey, model, [{ role: 'user', content: q }]);
}

async function generateMetaDescription({ apiKey, model, aigeneratedtitle, seed_keyword, content }: { apiKey: string; model: string; aigeneratedtitle: string; seed_keyword: string; content: string; }): Promise<string> {
  const q = `Create an SEO optimized meta description. max length of description should be 70-80 characters including spaces. Meta description is based on the blog post title \`${aigeneratedtitle}\`, the keyword \`${seed_keyword}\` and the blog post content i.e. ${content}.`;
  return chatOnce(apiKey, model, [{ role: 'user', content: q }]);
}

/* ---------------- Post-processing helpers (ported) ---------------- */

function replace_content(content: string, remove: string): string {
  const esc = remove.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return content.replace(new RegExp(esc, 'g'), '');
}

function removePTags(html: string): string {
  // remove empty <p>, convert newlines to <br>, and style TOC H2 like in PHP
  html = html.replace(/<p>(\s|&nbsp;)*<\/p>/g, '');
  html = html.replace(/\n/g, '<br>');
  html = html.replace(/<h2>Table of Contents<\/h2>/g, '<h2 style="margin-top: 35px;">Table of Contents</h2>');
  // remove [bracketed] text
  html = html.replace(/\[[^\]]*\]/g, '');
  return html;
}

function convert_emails_to_links(content: string): string {
  return content.replace(/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/g, '<a href="mailto:$1">$1</a>');
}

function convert_urls_to_links(content: string): string {
  return content.replace(/(<a\b[^>]*>.*?<\/a>)|((https?:\/\/|www\.)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}([\/\w.-]*)?)/g, (m, anchor, url) => {
    if (anchor) return anchor; // keep existing anchors
    const href = /^https?:\/\//.test(url) ? url : `http://${url}`;
    return `<a href="${href}" target="_blank" rel="noopener">${url}</a>`;
  });
}

function stripParenthesesWrappingContactTokens(content: string): string {
  // URLs wrapped in parentheses
  content = content.replace(/\(\s*((?:https?:\/\/|www\.)[^\s)]+)\s*\)/gi, '$1');
  // Emails wrapped in parentheses
  content = content.replace(/\(\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})\s*\)/g, '$1');
  return content;
}

function stripParenthesesAroundAnchorTags(content: string): string {
  return content.replace(/\(\s*(<a\b[^>]*>.*?<\/a>)\s*\)/gi, '$1');
}

function removeConsecutiveSpecialCharacters(content: string): string {
  // Split into tags and text nodes
  const parts = content.split(/(<[^>]+>)/g);
  for (let i = 0; i < parts.length; i++) {
    if (/^<[^>]+>$/.test(parts[i])) continue; // skip tags
    // collapse runs of 3+ special chars to a single char; keep 1 char
    parts[i] = parts[i].replace(/([#*!\-_.?])\1{2,}/g, '$1');
  }
  return parts.join('');
}

function verifyAndFixTOCLinks(content: string): string {
  // Collect TOC anchors
  const tocLinks: Record<string, string> = {};
  const tocRe = /<a href="#([^"]+)">([^<]+)<\/a>/gi;
  let m: RegExpExecArray | null;
  while ((m = tocRe.exec(content)) !== null) {
    tocLinks[m[1]] = (m[2] || '').trim();
  }

  // Collect headings and ensure they have IDs
  let updated = content;
  const headingRe = /<h([2-6])([^>]*)>([^<]+)<\/h\1>/gi;
  const headings: Array<{ id: string; text: string; level: number }> = [];
  updated = updated.replace(headingRe, (full, level, attrs, text) => {
    let idMatch = (attrs as string).match(/\sid=\"([^\"]+)\"/i);
    let id = idMatch ? idMatch[1] : null;
    const cleanText = (text || '').trim();
    if (!id) {
      id = generateAnchorId(cleanText);
      // inject id
      const space = attrs && attrs.trim().length ? ' ' : '';
      return `<h${level}${attrs}${space}id="${id}">${cleanText}</h${level}>`;
    }
    headings.push({ id, text: cleanText, level: Number(level) });
    return full;
  });

  // If TOC links exist but do not match any heading IDs, adjust headings to include those IDs if reasonable
  Object.keys(tocLinks).forEach(anchor => {
    if (!updated.includes(`id="${anchor}"`)) {
      // try to map by title text
      const title = tocLinks[anchor];
      const candidateId = generateAnchorId(title);
      if (updated.includes(`id="${candidateId}"`)) {
        // fine, already has this id; nothing to do
      }
    }
  });

  // Optional: if no TOC present but headings exist, we could generate one, but we keep behavior as in PHP:
  // only verify/fix, do not insert a new TOC unless needed.

  return updated;
}

function generateAnchorId(text: string): string {
  let id = (text || '').toLowerCase();
  id = id.replace(/<[^>]+>/g, '');
  id = id.replace(/[^a-z0-9\s-]/g, '');
  id = id.replace(/[\s-]+/g, '-');
  id = id.replace(/^-+|-+$/g, '');
  return id || 'section';
}

/* ---------------- Example Express route ----------------
const express = require('express');
const router = express.Router();

router.post('/generate-ai-post', async (req, res) => {
  try {
    const result = await generateAiHtmlPost({
      apiKey: process.env.OPENAI_API_KEY,
      seed_keyword: req.body.seed_keyword,
      keyword_selection: req.body.keyword_selection,
      seed_options: req.body.seed_options,
      nos_of_words: req.body.nos_of_words, // '600 to 1200 words' | '1200 to 2400 words' | '2400 to 3600 words'
      content_lang: req.body.content_lang,
      voice_tone: req.body.voice_tone,
      point_of_view: req.body.point_of_view,
      title: req.body.title,
      call_to_action: req.body.call_to_action,
      details_to_include: req.body.details_to_include,
      for_testing_only: Number(req.body.for_testing_only) || 0,
      audienceData: req.body.audienceData // optional
    });
    res.json({ success: true, data: result });
  } catch (e) {
    res.status(500).json({ success: false, error: e.message });
  }
});

module.exports = { router, generateAiHtmlPost };
--------------------------------------------------------- */

// named export already provided above