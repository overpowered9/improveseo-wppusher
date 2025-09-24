import express from 'express';
import dotenv from 'dotenv';
import { router as v1 } from './routes/v1.js';
import { errorHandler } from './utils/error-handler.js';

dotenv.config();

const app = express();
app.use(express.json({ limit: '1mb' }));

app.use('/v1', v1);

app.use(errorHandler);

const port = Number(process.env.PORT || 8080);
app.listen(port, () => {
  console.log(`[improveseo-ai-server] listening on http://localhost:${port}`);
});
