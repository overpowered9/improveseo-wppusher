import { Schema, model, Document, Types } from 'mongoose';

// Interface definitions
export interface IPromptTemplate extends Document {
  _id: Types.ObjectId;
  name: string; // e.g., "lsi_keywords", "facts_generation", "introduction_toc", "section_content"
  description: string;
  category: 'content_generation' | 'meta_generation' | 'format_template';
  wordCountVariant: 'small' | 'medium' | 'large' | 'all';
  sectionType?: 'introduction' | 'section' | 'conclusion' | 'faqs' | 'whats_next' | 'meta_title' | 'meta_description';
  variables: string[]; // Template variables this prompt uses: ['seed_keyword', 'audience_data', etc.]
  createdAt: Date;
  updatedAt: Date;
}

export interface IPromptVersion extends Document {
  _id: Types.ObjectId;
  templateId: Types.ObjectId; // Reference to PromptTemplate
  version: number;
  prompt: string; // The actual prompt text with {{variable}} placeholders
  isActive: boolean;
  createdBy: string; // Admin user ID
  createdAt: Date;
  metadata?: {
    expectedOutput?: string;
    notes?: string;
    testResults?: {
      qualityScore?: number;
      performanceMetrics?: any;
    };
  };
}

export interface IPromptSet extends Document {
  _id: Types.ObjectId;
  name: string; // e.g., "Default Content Generation v2.1", "SEO Optimized Set"
  description: string;
  isActive: boolean;
  prompts: {
    templateName: string; // Reference by name for easier management
    versionId: Types.ObjectId;
  }[];
  createdBy: string;
  createdAt: Date;
  updatedAt: Date;
}

// MongoDB Schemas
const promptTemplateSchema = new Schema<IPromptTemplate>({
  name: { type: String, required: true, unique: true },
  description: { type: String, required: true },
  category: { 
    type: String, 
    required: true, 
    enum: ['content_generation', 'meta_generation', 'format_template'] 
  },
  wordCountVariant: { 
    type: String, 
    required: true, 
    enum: ['small', 'medium', 'large', 'all'] 
  },
  sectionType: { 
    type: String, 
    enum: ['introduction', 'section', 'conclusion', 'faqs', 'whats_next', 'meta_title', 'meta_description'],
    required: false 
  },
  variables: [{ type: String }],
  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
});

const promptVersionSchema = new Schema<IPromptVersion>({
  templateId: { type: Schema.Types.ObjectId, ref: 'PromptTemplate', required: true },
  version: { type: Number, required: true },
  prompt: { type: String, required: true },
  isActive: { type: Boolean, default: false },
  createdBy: { type: String, required: true },
  createdAt: { type: Date, default: Date.now },
  metadata: {
    expectedOutput: String,
    notes: String,
    testResults: {
      qualityScore: Number,
      performanceMetrics: Schema.Types.Mixed
    }
  }
});

const promptSetSchema = new Schema<IPromptSet>({
  name: { type: String, required: true, unique: true },
  description: { type: String, required: true },
  isActive: { type: Boolean, default: false },
  prompts: [{
    templateName: { type: String, required: true },
    versionId: { type: Schema.Types.ObjectId, ref: 'PromptVersion', required: true }
  }],
  createdBy: { type: String, required: true },
  createdAt: { type: Date, default: Date.now },
  updatedAt: { type: Date, default: Date.now }
});

// Indexes for performance
promptTemplateSchema.index({ name: 1, wordCountVariant: 1 });
promptVersionSchema.index({ templateId: 1, version: -1 });
promptVersionSchema.index({ templateId: 1, isActive: 1 });
promptSetSchema.index({ isActive: 1 });

// Models
export const PromptTemplate = model<IPromptTemplate>('PromptTemplate', promptTemplateSchema);
export const PromptVersion = model<IPromptVersion>('PromptVersion', promptVersionSchema);
export const PromptSet = model<IPromptSet>('PromptSet', promptSetSchema);