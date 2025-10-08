import axios, { type AxiosInstance } from 'axios';

// Types for our API responses
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface PromptTemplate {
  _id: string;
  name: string;
  description: string;
  category: 'content_generation' | 'meta_generation' | 'format_template';
  wordCountVariant: 'small' | 'medium' | 'large' | 'all';
  sectionType?: 'introduction' | 'section' | 'conclusion' | 'faqs' | 'whats_next' | 'meta_title' | 'meta_description';
  variables: string[];
  createdAt: string;
  updatedAt: string;
}

export interface PromptVersion {
  _id: string;
  templateId: string;
  version: number;
  prompt: string;
  isActive: boolean;
  createdBy: string;
  createdAt: string;
  metadata?: {
    expectedOutput?: string;
    notes?: string;
    testResults?: {
      qualityScore?: number;
      performanceMetrics?: any;
    };
  };
}

export interface PromptSet {
  _id: string;
  name: string;
  description: string;
  isActive: boolean;
  prompts: {
    templateName: string;
    versionId: string;
  }[];
  createdBy: string;
  createdAt: string;
  updatedAt: string;
}

export interface PromptWithVersion extends PromptTemplate {
  activeVersion: PromptVersion;
}

export interface CompletePromptSet {
  _id: string;
  name: string;
  description: string;
  isActive: boolean;
  createdBy: string;
  createdAt: string;
  updatedAt: string;
  prompts: PromptWithVersion[];
}

export interface GenerationRequest {
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
  use_enhanced?: boolean;
}

export interface GenerationResult {
  search_data: string;
  content: string;
  meta_title: string;
  meta_descreption: string;
}

class ApiClient {
  private client: AxiosInstance;

  constructor(baseURL: string = 'http://localhost:8080') {
    this.client = axios.create({
      baseURL,
      timeout: 30000, // 30 seconds timeout for AI generation
      headers: {
        'Content-Type': 'application/json',
      },
    });

    // Request interceptor for auth
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('admin_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          localStorage.removeItem('admin_token');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // Auth methods
  async login(credentials: { username: string; password: string }): Promise<ApiResponse<{ token: string }>> {
    const response = await this.client.post('/auth/login', credentials);
    return response.data;
  }

  async logout(): Promise<void> {
    localStorage.removeItem('admin_token');
  }

  // Prompt Templates
  async getPromptTemplates(): Promise<ApiResponse<PromptTemplate[]>> {
    const response = await this.client.get('/admin/prompt-templates');
    return response.data;
  }

  async createPromptTemplate(template: {
    name: string;
    description: string;
    category: PromptTemplate['category'];
    wordCountVariant: PromptTemplate['wordCountVariant'];
    sectionType?: PromptTemplate['sectionType'];
    variables?: string[];
  }): Promise<ApiResponse<PromptTemplate>> {
    const response = await this.client.post('/admin/prompt-templates', template);
    return response.data;
  }

  async updatePromptTemplate(
    templateId: string,
    updates: { description?: string; variables?: string[] }
  ): Promise<ApiResponse<PromptTemplate>> {
    const response = await this.client.put(`/admin/prompt-templates/${templateId}`, updates);
    return response.data;
  }

  // Prompt Versions
  async getPromptVersions(templateId: string): Promise<ApiResponse<PromptVersion[]>> {
    const response = await this.client.get(`/admin/prompt-templates/${templateId}/versions`);
    return response.data;
  }

  async createPromptVersion(
    templateId: string,
    version: { prompt: string; metadata?: PromptVersion['metadata'] }
  ): Promise<ApiResponse<PromptVersion>> {
    const response = await this.client.post(`/admin/prompt-templates/${templateId}/versions`, version);
    return response.data;
  }

  async activatePromptVersion(
    versionId: string,
    templateId: string
  ): Promise<ApiResponse<void>> {
    const response = await this.client.put(`/admin/prompt-versions/${versionId}/activate`, { templateId });
    return response.data;
  }

  // Prompt Sets
  async getPromptSets(): Promise<ApiResponse<PromptSet[]>> {
    const response = await this.client.get('/admin/prompt-sets');
    return response.data;
  }

  async getActivePromptSet(): Promise<ApiResponse<CompletePromptSet>> {
    const response = await this.client.get('/admin/prompt-sets/active');
    return response.data;
  }

  async createPromptSet(promptSet: {
    name: string;
    description: string;
    promptMappings: { templateName: string; versionId: string }[];
  }): Promise<ApiResponse<PromptSet>> {
    const response = await this.client.post('/admin/prompt-sets', promptSet);
    return response.data;
  }

  async activatePromptSet(setId: string): Promise<ApiResponse<void>> {
    const response = await this.client.put(`/admin/prompt-sets/${setId}/activate`);
    return response.data;
  }

  // AI Content Generation
  async generateContent(request: GenerationRequest): Promise<ApiResponse<GenerationResult>> {
    const response = await this.client.post('/v1/generate-ai-post-enhanced', request);
    return response.data;
  }

  async testPrompt(
    templateName: string,
    wordCount: string
  ): Promise<ApiResponse<{ templateName: string; wordCount: string; prompt: string }>> {
    const response = await this.client.get(`/admin/prompts/${templateName}/${wordCount}`);
    return response.data;
  }

  // System Health
  async getSystemHealth(): Promise<ApiResponse<{
    totalTemplates: number;
    totalPromptSets: number;
    hasActiveSet: boolean;
    activeSetName: string | null;
  }>> {
    const response = await this.client.get('/admin/prompts/health');
    return response.data;
  }

  // Variable Testing
  async testVariables(test: {
    prompt: string;
    variables: Record<string, string>;
  }): Promise<ApiResponse<{
    originalPrompt: string;
    processedPrompt: string;
    variables: string[];
    providedVariables: Record<string, string>;
  }>> {
    const response = await this.client.post('/admin/prompts/test-variables', test);
    return response.data;
  }
}

// Create singleton instance
const apiClient = new ApiClient(import.meta.env.VITE_API_URL || 'http://localhost:8080');

export default apiClient;