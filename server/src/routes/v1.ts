import { Router, type Request, type Response, type NextFunction } from 'express';
import { z } from 'zod';
import { generatePost } from '../services/generator.js';

export const router = Router();

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

router.post('/generate', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const input = generateSchema.parse(req.body);

    const result = await generatePost(input);

    res.json(result);
  } catch (err) {
    next(err);
  }
});
