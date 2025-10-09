import { Types } from 'mongoose';
import { 
  PromptTemplate, 
  PromptVersion, 
  PromptSet, 
  IPromptTemplate, 
  IPromptVersion, 
  IPromptSet 
} from '../models/prompt.models.js';

export interface PromptWithVersion {
  _id: Types.ObjectId;
  name: string;
  description: string;
  category: IPromptTemplate['category'];
  wordCountVariant: IPromptTemplate['wordCountVariant'];
  sectionType?: IPromptTemplate['sectionType'];
  variables: string[];
  createdAt: Date;
  updatedAt: Date;
  activeVersion: IPromptVersion;
}

export interface CompletePromptSet {
  _id: Types.ObjectId;
  name: string;
  description: string;
  isActive: boolean;
  createdBy: string;
  createdAt: Date;
  updatedAt: Date;
  prompts: PromptWithVersion[];
}

export class PromptManagementService {
  
  /**
   * Get the currently active prompt set with all its prompts and active versions
   */
  async getActivePromptSet(): Promise<CompletePromptSet> {
    const activeSet = await PromptSet.findOne({ isActive: true });
    if (!activeSet) {
      throw new Error('No active prompt set found');
    }

    const promptsWithVersions = await Promise.all(
      activeSet.prompts.map(async ({ templateName, versionId }) => {
        const template = await PromptTemplate.findOne({ name: templateName });
        if (!template) {
          throw new Error(`Template ${templateName} not found`);
        }
        
        const version = await PromptVersion.findById(versionId);
        if (!version) {
          throw new Error(`Version ${versionId} not found for template ${templateName}`);
        }

        return {
          _id: template._id,
          name: template.name,
          description: template.description,
          category: template.category,
          wordCountVariant: template.wordCountVariant,
          sectionType: template.sectionType,
          variables: template.variables,
          createdAt: template.createdAt,
          updatedAt: template.updatedAt,
          activeVersion: version
        } as PromptWithVersion;
      })
    );

    return {
      _id: activeSet._id,
      name: activeSet.name,
      description: activeSet.description,
      isActive: activeSet.isActive,
      createdBy: activeSet.createdBy,
      createdAt: activeSet.createdAt,
      updatedAt: activeSet.updatedAt,
      prompts: promptsWithVersions
    } as CompletePromptSet;
  }

  /**
   * Get a specific prompt by template name and word count variant
   */
  async getPrompt(templateName: string, wordCountVariant: string): Promise<string> {
    const template = await PromptTemplate.findOne({ 
      name: templateName,
      $or: [
        { wordCountVariant },
        { wordCountVariant: 'all' }
      ]
    });
    
    if (!template) {
      throw new Error(`Template '${templateName}' not found for word count '${wordCountVariant}'`);
    }

    // First try to get from active prompt set
    const activeSet = await PromptSet.findOne({ isActive: true });
    if (activeSet) {
      const promptMapping = activeSet.prompts.find(p => p.templateName === templateName);
      if (promptMapping) {
        const version = await PromptVersion.findById(promptMapping.versionId);
        if (version) {
          return version.prompt;
        }
      }
    }

    // Fallback to active version for the template
    const activeVersion = await PromptVersion.findOne({
      templateId: template._id,
      isActive: true
    });

    if (!activeVersion) {
      throw new Error(`No active version found for template '${templateName}'`);
    }

    return activeVersion.prompt;
  }

  /**
   * Create a new version of a prompt template
   */
  async createPromptVersion(
    templateId: string, 
    prompt: string, 
    adminId: string,
    metadata?: IPromptVersion['metadata']
  ): Promise<IPromptVersion> {
    const template = await PromptTemplate.findById(templateId);
    if (!template) {
      throw new Error(`Template ${templateId} not found`);
    }

    // Get the next version number
    const lastVersion = await PromptVersion
      .findOne({ templateId })
      .sort({ version: -1 });

    const newVersion = new PromptVersion({
      templateId,
      version: (lastVersion?.version || 0) + 1,
      prompt,
      isActive: false, // New versions start inactive
      createdBy: adminId,
      createdAt: new Date(),
      metadata
    });

    return await newVersion.save();
  }

  /**
   * Activate a specific prompt version (deactivates others for the same template)
   */
  async activatePromptVersion(templateId: string, versionId: string): Promise<void> {
    // Deactivate all versions for this template
    await PromptVersion.updateMany(
      { templateId, isActive: true },
      { isActive: false }
    );

    // Activate the specified version
    const updatedVersion = await PromptVersion.findByIdAndUpdate(
      versionId, 
      { isActive: true },
      { new: true }
    );

    if (!updatedVersion) {
      throw new Error(`Version ${versionId} not found`);
    }
  }

  /**
   * Create a new prompt set
   */
  async createPromptSet(
    name: string, 
    description: string, 
    promptMappings: { templateName: string; versionId: string }[],
    adminId: string
  ): Promise<IPromptSet> {
    // Validate that all referenced templates and versions exist
    for (const mapping of promptMappings) {
      const template = await PromptTemplate.findOne({ name: mapping.templateName });
      if (!template) {
        throw new Error(`Template '${mapping.templateName}' not found`);
      }

      const version = await PromptVersion.findById(mapping.versionId);
      if (!version) {
        throw new Error(`Version '${mapping.versionId}' not found`);
      }

      if (!version.templateId.equals(template._id)) {
        throw new Error(`Version '${mapping.versionId}' does not belong to template '${mapping.templateName}'`);
      }
    }

    const newSet = new PromptSet({
      name,
      description,
      isActive: false, // New sets start inactive
      prompts: promptMappings.map(m => ({
        templateName: m.templateName,
        versionId: new Types.ObjectId(m.versionId)
      })),
      createdBy: adminId,
      createdAt: new Date(),
      updatedAt: new Date()
    });

    return await newSet.save();
  }

  /**
   * Activate a prompt set (deactivates all other sets)
   */
  async activatePromptSet(setId: string): Promise<void> {
    // Deactivate all prompt sets
    await PromptSet.updateMany({}, { isActive: false });

    // Activate the specified set
    const updatedSet = await PromptSet.findByIdAndUpdate(
      setId,
      { isActive: true, updatedAt: new Date() },
      { new: true }
    );

    if (!updatedSet) {
      throw new Error(`Prompt set ${setId} not found`);
    }
  }

  /**
   * Get all templates
   */
  async getAllTemplates(): Promise<IPromptTemplate[]> {
    return await PromptTemplate.find().sort({ name: 1, wordCountVariant: 1 });
  }

  /**
   * Get all versions for a template
   */
  async getTemplateVersions(templateId: string): Promise<IPromptVersion[]> {
    return await PromptVersion.find({ templateId }).sort({ version: -1 });
  }

  /**
   * Get all prompt sets
   */
  async getAllPromptSets(): Promise<IPromptSet[]> {
    return await PromptSet.find().sort({ createdAt: -1 });
  }

  /**
   * Process prompt template variables (replace {{variable}} with actual values)
   */
  processPromptVariables(prompt: string, variables: Record<string, string>): string {
    let processedPrompt = prompt;
    
    Object.entries(variables).forEach(([key, value]) => {
      const placeholder = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
      processedPrompt = processedPrompt.replace(placeholder, value || '');
    });

    return processedPrompt;
  }

  /**
   * Extract variables from a prompt template
   */
  extractVariables(prompt: string): string[] {
    const variableRegex = /\{\{([^}]+)\}\}/g;
    const variables: string[] = [];
    let match;

    while ((match = variableRegex.exec(prompt)) !== null) {
      if (!variables.includes(match[1])) {
        variables.push(match[1]);
      }
    }

    return variables;
  }

  /**
   * Create a new prompt template
   */
  async createPromptTemplate(
    name: string,
    description: string,
    category: IPromptTemplate['category'],
    wordCountVariant: IPromptTemplate['wordCountVariant'],
    sectionType?: IPromptTemplate['sectionType'],
    variables: string[] = []
  ): Promise<IPromptTemplate> {
    const template = new PromptTemplate({
      name,
      description,
      category,
      wordCountVariant,
      sectionType,
      variables,
      createdAt: new Date(),
      updatedAt: new Date()
    });

    return await template.save();
  }

  /**
   * Update prompt template metadata
   */
  async updatePromptTemplate(
    templateId: string,
    updates: Partial<Pick<IPromptTemplate, 'description' | 'variables'>>
  ): Promise<IPromptTemplate | null> {
    return await PromptTemplate.findByIdAndUpdate(
      templateId,
      { ...updates, updatedAt: new Date() },
      { new: true }
    );
  }

  // ===== VERSION MANAGEMENT METHODS =====

  /**
   * Create a new version for a template
   */
  async createTemplateVersion(
    templateId: string, 
    versionData: {
      prompt: string;
      createdBy: string;
      metadata?: {
        notes?: string;
        expectedOutput?: string;
      };
    }
  ): Promise<IPromptVersion> {
    // Get the highest version number for this template
    const latestVersion = await PromptVersion.findOne({ templateId })
      .sort({ version: -1 })
      .exec();
    
    const nextVersion = latestVersion ? latestVersion.version + 1 : 1;

    const newVersion = new PromptVersion({
      templateId: new Types.ObjectId(templateId),
      version: nextVersion,
      prompt: versionData.prompt,
      isActive: false, // New versions start as inactive
      createdBy: versionData.createdBy,
      metadata: versionData.metadata
    });

    return await newVersion.save();
  }

  /**
   * Activate a specific version (deactivates all other versions for this template)
   */
  async activateTemplateVersion(templateId: string, versionId: string): Promise<IPromptVersion> {
    // Deactivate all versions for this template
    await PromptVersion.updateMany(
      { templateId },
      { isActive: false }
    );

    // Activate the specified version
    const activatedVersion = await PromptVersion.findByIdAndUpdate(
      versionId,
      { isActive: true },
      { new: true }
    );

    if (!activatedVersion) {
      throw new Error('Version not found');
    }

    return activatedVersion;
  }

  /**
   * Get a specific version by ID
   */
  async getVersionById(versionId: string): Promise<IPromptVersion | null> {
    return await PromptVersion.findById(versionId).exec();
  }

  /**
   * Delete a version (cannot delete active version)
   */
  async deleteTemplateVersion(versionId: string): Promise<void> {
    const version = await PromptVersion.findById(versionId);
    
    if (!version) {
      throw new Error('Version not found');
    }

    if (version.isActive) {
      throw new Error('Cannot delete active version. Please activate another version first.');
    }

    await PromptVersion.findByIdAndDelete(versionId);
  }

  /**
   * Get active version for a template
   */
  async getActiveVersion(templateId: string): Promise<IPromptVersion | null> {
    return await PromptVersion.findOne({ 
      templateId, 
      isActive: true 
    }).exec();
  }
}