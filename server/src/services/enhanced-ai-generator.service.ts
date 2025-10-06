import { PromptManagementService } from './prompt-management.service';
import { GeneratePostParams, GeneratePostResult, ChatMessage } from './ai_content_admin_tuneable';

export class EnhancedAIContentGenerator {
  constructor(private promptService: PromptManagementService) {}

  async generateAiHtmlPost(params: GeneratePostParams): Promise<GeneratePostResult> {
    const {
      apiKey = process.env.OPENAI_API_KEY,
      seed_keyword,
      keyword_selection = '',
      seed_options = '',
      nos_of_words = '1200 to 2400 words',
      content_lang = 'en',
      voice_tone = '',
      point_of_view = '',
      title = '',
      call_to_action = '',
      details_to_include = '',
      for_testing_only = 0,
      audienceData
    } = params || ({} as GeneratePostParams);

    if (!apiKey) {
      throw new Error('Missing OpenAI API key');
    }
    if (!seed_keyword) {
      throw new Error('seed_keyword is required');
    }

    const model = 'gpt-4o';
    const wordCountVariant = this.getWordCountVariant(nos_of_words);

    // 1) Ensure audience data (replicates getAudienceData() if not provided)
    const audience = audienceData || await this.generateAudienceData(apiKey, model, seed_keyword, content_lang);

    // 2) LSI keywords using dynamic prompt
    const lsiPrompt = await this.promptService.getPrompt('lsi_keywords', wordCountVariant);
    const lsiPromptProcessed = this.promptService.processPromptVariables(lsiPrompt, {
      seed_keyword,
      audience_data: audience
    });
    const lsiKeywords = await this.chatOnce(apiKey, model, [
      { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
      { role: 'user', content: lsiPromptProcessed }
    ]);

    // 3) Facts generation using dynamic prompt
    const factsPrompt = await this.promptService.getPrompt('facts_generation', wordCountVariant);
    const factsPromptProcessed = this.promptService.processPromptVariables(factsPrompt, {
      seed_keyword,
      audience_data: audience
    });
    const facts = await this.chatOnce(apiKey, model, [
      { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
      { role: 'user', content: factsPromptProcessed }
    ]);

    // 4) Introduction and TOC using dynamic prompt
    const introPrompt = await this.promptService.getPrompt('introduction_toc', wordCountVariant);
    const introPromptProcessed = this.promptService.processPromptVariables(introPrompt, {
      nos_of_words: nos_of_words || '',
      seed_keyword,
      title: title || '',
      lsi_keywords: lsiKeywords,
      voice_tone: voice_tone || '',
      point_of_view: point_of_view || '',
      audience_data: audience,
      details_to_include: details_to_include || '',
      content_lang: content_lang || '',
      call_to_action: call_to_action || '',
      facts_to_include: facts
    });

    let conversation: ChatMessage[] = [
      { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
      { role: 'user', content: introPromptProcessed }
    ];

    const introResponse = await this.chatOnce(apiKey, model, conversation);
    conversation.push({ role: 'assistant', content: introResponse });

    // 5) Generate content sections dynamically
    const sectionConfig = this.getSectionConfig(wordCountVariant);
    const sectionResponses: string[] = [];

    for (let i = 1; i <= sectionConfig.sectionCount; i++) {
      try {
        // Try to get specific section prompt first
        const sectionPrompt = await this.promptService.getPrompt(`section_${i}`, wordCountVariant);
        const sectionPromptProcessed = this.promptService.processPromptVariables(sectionPrompt, {
          section_number: i.toString(),
          min_words: sectionConfig.sectionWordMin.toString(),
          max_words: sectionConfig.sectionWordMax.toString(),
          ordinal: this.ordinal(i)
        });
        conversation.push({ role: 'user', content: sectionPromptProcessed });
      } catch (error) {
        // Fallback to generic section prompt
        const genericPrompt = `Now generate the ${this.ordinal(i)} subtitle content. IMPORTANT: Output should not be more than ${sectionConfig.sectionWordMin}-${sectionConfig.sectionWordMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`;
        conversation.push({ role: 'user', content: genericPrompt });
      }

      const sectionResponse = await this.chatOnce(apiKey, model, conversation);
      sectionResponses.push(this.wrapSection(sectionResponse));
      conversation.push({ role: 'assistant', content: sectionResponse });
    }

    // 6) Generate conclusion using dynamic prompt
    const conclusionPrompt = await this.getPromptOrFallback(
      'conclusion',
      wordCountVariant,
      `Now generate the conclusion content. IMPORTANT: Output should not be more than ${sectionConfig.conclusionMin}-${sectionConfig.conclusionMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
    );
    conversation.push({ role: 'user', content: conclusionPrompt });
    const conclusionResp = await this.chatOnce(apiKey, model, conversation);
    conversation.push({ role: 'assistant', content: conclusionResp });

    // 7) Generate FAQs using dynamic prompt
    const faqsPrompt = await this.getPromptOrFallback(
      'faqs',
      wordCountVariant,
      `Now generate the FAQs content. IMPORTANT: Output should not be more than ${sectionConfig.faqsMin}-${sectionConfig.faqsMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
    );
    conversation.push({ role: 'user', content: faqsPrompt });
    const faqsResp = await this.chatOnce(apiKey, model, conversation);
    conversation.push({ role: 'assistant', content: faqsResp });

    // 8) Generate What's Next using dynamic prompt
    const whatsNextPrompt = await this.getPromptOrFallback(
      'whats_next',
      wordCountVariant,
      `Now generate What is next? content. IMPORTANT: Output should not be more than ${sectionConfig.whatsMin}-${sectionConfig.whatsMax} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
    );
    conversation.push({ role: 'user', content: whatsNextPrompt });
    const whatsNextResp = await this.chatOnce(apiKey, model, conversation);

    // 9) Assemble HTML content
    let contentFinal = this.assembleContent({
      introResponse,
      sectionResponses,
      conclusionResp,
      faqsResp,
      whatsNextResp
    });

    // 10) Post-process content
    contentFinal = this.postProcessContent(contentFinal);

    // 11) Generate meta data using dynamic prompts
    const metaTitle = await this.generateMetaTitle(apiKey, model, title || seed_keyword, seed_keyword);
    const metaDescription = await this.generateMetaDescription(apiKey, model, title || seed_keyword, seed_keyword, contentFinal);

    return {
      search_data: title && title.trim() ? title : seed_keyword,
      content: contentFinal,
      meta_title: metaTitle,
      meta_descreption: metaDescription
    };
  }

  private async getPromptOrFallback(templateName: string, wordCountVariant: string, fallback: string): Promise<string> {
    try {
      return await this.promptService.getPrompt(templateName, wordCountVariant);
    } catch (error) {
      console.warn(`Prompt '${templateName}' not found, using fallback`);
      return fallback;
    }
  }

  private getWordCountVariant(nos_of_words: string): string {
    if (nos_of_words === '600 to 1200 words') return 'small';
    if (nos_of_words === '2400 to 3600 words') return 'large';
    return 'medium';
  }

  private getSectionConfig(variant: string) {
    const configs = {
      small: { 
        sectionCount: 4, 
        sectionWordMin: 200, 
        sectionWordMax: 250,
        conclusionMin: 100,
        conclusionMax: 150,
        faqsMin: 100,
        faqsMax: 150,
        whatsMin: 100,
        whatsMax: 150
      },
      medium: { 
        sectionCount: 4, 
        sectionWordMin: 350, 
        sectionWordMax: 400,
        conclusionMin: 150,
        conclusionMax: 200,
        faqsMin: 100,
        faqsMax: 150,
        whatsMin: 150,
        whatsMax: 200
      },
      large: { 
        sectionCount: 5, 
        sectionWordMin: 450, 
        sectionWordMax: 600,
        conclusionMin: 150,
        conclusionMax: 200,
        faqsMin: 100,
        faqsMax: 150,
        whatsMin: 150,
        whatsMax: 200
      }
    };
    return configs[variant as keyof typeof configs] || configs.medium;
  }

  private ordinal(n: number): string {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  }

  private wrapSection(html: string): string {
    return html;
  }

  private assembleContent(sections: {
    introResponse: string;
    sectionResponses: string[];
    conclusionResp: string;
    faqsResp: string;
    whatsNextResp: string;
  }): string {
    const { introResponse, sectionResponses, conclusionResp, faqsResp, whatsNextResp } = sections;
    
    return '<div class="main-content-section-improveseo">' +
      introResponse +
      sectionResponses.map(s => `<div style="margin-bottom: 15px;margin-top: 50px;">${s}</div>`).join('') +
      `<div style="margin-bottom: 15px;margin-top: 50px;">${conclusionResp}</div>` +
      `<div style="margin-bottom: 15px;margin-top: 50px;">${faqsResp}</div>` +
      `<div style="margin-bottom: 15px;margin-top: 50px;">${whatsNextResp}</div>` +
      '</div>';
  }

  private postProcessContent(content: string): string {
    // Apply all the post-processing from the original function
    content = this.stripParenthesesWrappingContactTokens(content);
    content = this.convert_emails_to_links(content);
    content = this.convert_urls_to_links(content);
    content = this.stripParenthesesAroundAnchorTags(content);

    // Light HTML entity/nbsp cleanup
    content = content.replace(/&nbsp;/g, '').replace(/<p>&nbsp;<\/p>/g, '').replace(/<p>\s<\/p>/g, '').replace(/<p><\/p>/g, '');

    // Remove boilerplate headings if present
    content = this.replace_content(content, '<h2>Main Content Sections</h2>');
    content = this.replace_content(content, '<p>—</p>');

    content = this.removePTags(content);
    content = this.removeConsecutiveSpecialCharacters(content);

    // Ensure TOC anchors match headings
    content = this.verifyAndFixTOCLinks(content);

    // Optional smooth scroll style (kept minimal)
    content += '<style>p{padding-bottom:2px!important}html{scroll-behavior:smooth}</style>';

    return content;
  }

  private async generateAudienceData(apiKey: string, model: string, seed_keyword: string, content_lang: string): Promise<string> {
    const prompt = `Assume someone enters the keyword ${seed_keyword} into a search engine. Analyze the following characteristics: 1. [demographic information] 2. [tone preferences] 3. [reading level preference] 4. [emotional needs/pain points]. This information will be used to create content that is specifically appealing to such people. Do not give content recommendations yet. As an output, write just information for characteristics without any explanation or introduction.`;
    return await this.chatOnce(apiKey, model, [
      { role: 'system', content: 'You are a helpful assistant. Please respond in ' + content_lang },
      { role: 'user', content: prompt }
    ]);
  }

  private async generateMetaTitle(apiKey: string, model: string, aigeneratedtitle: string, seed_keyword: string): Promise<string> {
    try {
      const metaTitlePrompt = await this.promptService.getPrompt('meta_title', 'all');
      const processedPrompt = this.promptService.processPromptVariables(metaTitlePrompt, {
        title: aigeneratedtitle,
        seed_keyword
      });
      return await this.chatOnce(apiKey, model, [{ role: 'user', content: processedPrompt }]);
    } catch (error) {
      // Fallback to original meta title generation
      const q = `Create an SEO optimized meta title based on the blog post title \`${aigeneratedtitle}\` and the keyword \`${seed_keyword}\`. max length of title should be 50-60 characters including spaces.`;
      return await this.chatOnce(apiKey, model, [{ role: 'user', content: q }]);
    }
  }

  private async generateMetaDescription(apiKey: string, model: string, aigeneratedtitle: string, seed_keyword: string, content: string): Promise<string> {
    try {
      const metaDescPrompt = await this.promptService.getPrompt('meta_description', 'all');
      const processedPrompt = this.promptService.processPromptVariables(metaDescPrompt, {
        title: aigeneratedtitle,
        seed_keyword,
        content
      });
      return await this.chatOnce(apiKey, model, [{ role: 'user', content: processedPrompt }]);
    } catch (error) {
      // Fallback to original meta description generation
      const q = `Create an SEO optimized meta description. max length of description should be 70-80 characters including spaces. Meta description is based on the blog post title \`${aigeneratedtitle}\`, the keyword \`${seed_keyword}\` and the blog post content i.e. ${content}.`;
      return await this.chatOnce(apiKey, model, [{ role: 'user', content: q }]);
    }
  }

  private async chatOnce(apiKey: string, model: string, messages: ChatMessage[]): Promise<string> {
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
    
    const data = await res.json();
    return (data.choices?.[0]?.message?.content || '').replace(/^['"]|['"]$/g, '');
  }

  // Helper methods (copied from original implementation)
  private replace_content(content: string, remove: string): string {
    const esc = remove.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return content.replace(new RegExp(esc, 'g'), '');
  }

  private removePTags(html: string): string {
    html = html.replace(/<p>(\s|&nbsp;)*<\/p>/g, '');
    html = html.replace(/\n/g, '<br>');
    html = html.replace(/<h2>Table of Contents<\/h2>/g, '<h2 style="margin-top: 35px;">Table of Contents</h2>');
    html = html.replace(/\[[^\]]*\]/g, '');
    return html;
  }

  private convert_emails_to_links(content: string): string {
    return content.replace(/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/g, '<a href="mailto:$1">$1</a>');
  }

  private convert_urls_to_links(content: string): string {
    return content.replace(/(<a\b[^>]*>.*?<\/a>)|((https?:\/\/|www\.)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}([\/\w.-]*)?)/g, (m, anchor, url) => {
      if (anchor) return anchor;
      const href = /^https?:\/\//.test(url) ? url : `http://${url}`;
      return `<a href="${href}" target="_blank" rel="noopener">${url}</a>`;
    });
  }

  private stripParenthesesWrappingContactTokens(content: string): string {
    content = content.replace(/\(\s*((?:https?:\/\/|www\.)[^\s)]+)\s*\)/gi, '$1');
    content = content.replace(/\(\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,})\s*\)/g, '$1');
    return content;
  }

  private stripParenthesesAroundAnchorTags(content: string): string {
    return content.replace(/\(\s*(<a\b[^>]*>.*?<\/a>)\s*\)/gi, '$1');
  }

  private removeConsecutiveSpecialCharacters(content: string): string {
    const parts = content.split(/(<[^>]+>)/g);
    for (let i = 0; i < parts.length; i++) {
      if (/^<[^>]+>$/.test(parts[i])) continue;
      parts[i] = parts[i].replace(/([#*!\-_.?])\1{2,}/g, '$1');
    }
    return parts.join('');
  }

  private verifyAndFixTOCLinks(content: string): string {
    const tocLinks: Record<string, string> = {};
    const tocRe = /<a href="#([^"]+)">([^<]+)<\/a>/gi;
    let m: RegExpExecArray | null;
    
    while ((m = tocRe.exec(content)) !== null) {
      tocLinks[m[1]] = (m[2] || '').trim();
    }

    let updated = content;
    const headingRe = /<h([2-6])([^>]*)>([^<]+)<\/h\1>/gi;
    
    updated = updated.replace(headingRe, (full, level, attrs, text) => {
      let idMatch = (attrs as string).match(/\sid=\"([^\"]+)\"/i);
      let id = idMatch ? idMatch[1] : null;
      const cleanText = (text || '').trim();
      
      if (!id) {
        id = this.generateAnchorId(cleanText);
        const space = attrs && attrs.trim().length ? ' ' : '';
        return `<h${level}${attrs}${space}id="${id}">${cleanText}</h${level}>`;
      }
      
      return full;
    });

    return updated;
  }

  private generateAnchorId(text: string): string {
    let id = (text || '').toLowerCase();
    id = id.replace(/<[^>]+>/g, '');
    id = id.replace(/[^a-z0-9\s-]/g, '');
    id = id.replace(/[\s-]+/g, '-');
    id = id.replace(/^-+|-+$/g, '');
    return id || 'section';
  }
}