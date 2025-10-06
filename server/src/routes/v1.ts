import { Router, type Request, type Response, type NextFunction } from 'express';
import { z } from 'zod';
import { generatePost } from '../services/generator.js';
import { generateAiHtmlPost } from '../services/ai_content_genrator.js';
import { EnhancedAIContentGenerator } from '../services/enhanced-ai-generator.service.js';
import { PromptManagementService } from '../services/prompt-management.service.js';

export const router = Router();

// Initialize services
const promptService = new PromptManagementService();
const enhancedAIGenerator = new EnhancedAIContentGenerator(promptService);

const generateSchema = z.object({
  seed_keyword: z.string().min(1),
  keyword_selection: z.string().optional().default(''),
  seed_options: z.string().optional().default(''),
  nos_of_words: z.enum(['600 to 1200 words', '1200 to 2400 words']).default('600 to 1200 words'),
  content_lang: z.string().min(1),
  voice_tone: z.string().optional().default(''),
  point_of_view: z.string().optional().default(''),
  title: z.string().optional().default(''),
  call_to_action: z.string().optional().default(''),
  details_to_include: z.string().optional().default(''),
  AudienceData: z.string().optional().default(''),
  for_testing_only: z.string().optional().default(''),
});
router.post('/generate-ai-post', async (req, res) => {
  console.log("Received:", req.body);
  try {
    // Check if enhanced mode is enabled via environment variable or request parameter
    const useEnhanced = process.env.USE_ENHANCED_AI === 'true' || req.body.use_enhanced === true;
    
    let result;
    if (useEnhanced) {
      console.log("🚀 Using enhanced AI generator with MongoDB prompts");
      result = await enhancedAIGenerator.generateAiHtmlPost({
        apiKey: process.env.OPENAI_API_KEY,
        seed_keyword: req.body.seed_keyword,
        keyword_selection: req.body.keyword_selection,
        seed_options: req.body.seed_options,
        nos_of_words: req.body.nos_of_words,
        content_lang: req.body.content_lang,
        voice_tone: req.body.voice_tone,
        point_of_view: req.body.point_of_view,
        title: req.body.title,
        call_to_action: req.body.call_to_action,
        details_to_include: req.body.details_to_include,
        for_testing_only: Number(req.body.for_testing_only) || 0,
        audienceData: req.body.audienceData
      });
    } else {
      console.log("🔄 Using legacy AI generator");
      result = await generateAiHtmlPost({
        apiKey: process.env.OPENAI_API_KEY,
        seed_keyword: req.body.seed_keyword,
        keyword_selection: req.body.keyword_selection,
        seed_options: req.body.seed_options,
        nos_of_words: req.body.nos_of_words,
        content_lang: req.body.content_lang,
        voice_tone: req.body.voice_tone,
        point_of_view: req.body.point_of_view,
        title: req.body.title,
        call_to_action: req.body.call_to_action,
        details_to_include: req.body.details_to_include,
        for_testing_only: Number(req.body.for_testing_only) || 0,
        audienceData: req.body.audienceData
      });
    }
    
    console.log(result.content);
    res.json({ 
      success: true, 
      data: result,
      generator: useEnhanced ? 'enhanced' : 'legacy'
    });
  } catch (e) {
    res.status(500).json({ success: false, error: `${e}.message` });
  }
});

// NEW: Enhanced AI post generation with MongoDB prompt management
router.post('/generate-ai-post-enhanced', async (req, res) => {
  console.log("Enhanced AI generation received:", req.body);
  try {
    const result = await enhancedAIGenerator.generateAiHtmlPost({
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
    console.log("Enhanced AI generation result:", result.content);
    res.json({ 
      success: true, 
      data: result,
      message: "Content generated using MongoDB-managed prompts"
    });
  } catch (e) {
    console.error("Enhanced AI generation error:", e);
    res.status(500).json({ 
      success: false, 
      error: `Enhanced AI generation failed: ${e instanceof Error ? e.message : String(e)}` 
    });
  }
});

router.post('/generate', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const input = generateSchema.parse(req.body);

    const result = await generatePost(input);

    res.json(result);
  } catch (err) {
    next(err);
  }
});
