import express from 'express';
import dotenv from 'dotenv';
import { router as v1 } from './routes/v1.js';
import { errorHandler } from './utils/error-handler.js';
import { connectToDatabase } from './config/database.js';
import adminPromptRoutes from './routes/admin/prompts.routes.js';

dotenv.config();

const app = express();
app.use(express.json({ limit: '1mb' }));

// API routes
app.use('/v1', v1);
app.use('/admin', adminPromptRoutes);

app.use(errorHandler);

const port = Number(process.env.PORT || 8080);

// Initialize database and start server
async function startServer() {
  try {
    // Connect to MongoDB
    await connectToDatabase();
    
    // Start Express server
    app.listen(port, () => {
      console.log(`[improveseo-ai-server] listening on http://localhost:${port}`);
      console.log(`🚀 Server ready with MongoDB prompt management system`);
    });
  } catch (error) {
    console.error('❌ Failed to start server:', error);
    process.exit(1);
  }
}

startServer();
