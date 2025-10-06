import { Router } from 'express';
import { PromptManagementService } from '../services/prompt-management.service.js';

const router = Router();

// Middleware for admin authentication (placeholder - implement based on your auth system)
const requireAdmin = (req: any, res: any, next: any) => {
  // TODO: Implement actual admin authentication
  req.adminId = 'admin-user-id'; // Mock admin ID
  next();
};

// Initialize service (assumes MongoDB connection is already established)
const promptService = new PromptManagementService();

// ===== PROMPT SETS MANAGEMENT =====

/**
 * GET /admin/prompt-sets
 * Get all prompt sets
 */
router.get('/prompt-sets', requireAdmin, async (req, res) => {
  try {
    const promptSets = await promptService.getAllPromptSets();
    res.json({ 
      success: true, 
      data: promptSets,
      message: `Found ${promptSets.length} prompt sets`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Unknown error' 
    });
  }
});

/**
 * GET /admin/prompt-sets/active
 * Get the currently active prompt set with all prompts
 */
router.get('/prompt-sets/active', requireAdmin, async (req, res) => {
  try {
    const activeSet = await promptService.getActivePromptSet();
    res.json({ 
      success: true, 
      data: activeSet,
      message: `Active prompt set: ${activeSet.name}`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'No active prompt set found' 
    });
  }
});

/**
 * POST /admin/prompt-sets
 * Create a new prompt set
 */
router.post('/prompt-sets', requireAdmin, async (req, res) => {
  try {
    const { name, description, promptMappings } = req.body;

    if (!name || !description || !promptMappings || !Array.isArray(promptMappings)) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: name, description, promptMappings'
      });
    }

    const newSet = await promptService.createPromptSet(
      name,
      description,
      promptMappings,
      req.adminId
    );

    res.status(201).json({ 
      success: true, 
      data: newSet,
      message: `Prompt set '${name}' created successfully`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to create prompt set' 
    });
  }
});

/**
 * PUT /admin/prompt-sets/:setId/activate
 * Activate a specific prompt set
 */
router.put('/prompt-sets/:setId/activate', requireAdmin, async (req, res) => {
  try {
    const { setId } = req.params;
    await promptService.activatePromptSet(setId);
    
    res.json({ 
      success: true, 
      message: `Prompt set ${setId} activated successfully`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to activate prompt set' 
    });
  }
});

// ===== PROMPT TEMPLATES MANAGEMENT =====

/**
 * GET /admin/prompt-templates
 * Get all prompt templates
 */
router.get('/prompt-templates', requireAdmin, async (req, res) => {
  try {
    const templates = await promptService.getAllTemplates();
    res.json({ 
      success: true, 
      data: templates,
      message: `Found ${templates.length} prompt templates`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to fetch templates' 
    });
  }
});

/**
 * POST /admin/prompt-templates
 * Create a new prompt template
 */
router.post('/prompt-templates', requireAdmin, async (req, res) => {
  try {
    const { name, description, category, wordCountVariant, sectionType, variables } = req.body;

    if (!name || !description || !category || !wordCountVariant) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: name, description, category, wordCountVariant'
      });
    }

    const newTemplate = await promptService.createPromptTemplate(
      name,
      description,
      category,
      wordCountVariant,
      sectionType,
      variables || []
    );

    res.status(201).json({ 
      success: true, 
      data: newTemplate,
      message: `Template '${name}' created successfully`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to create template' 
    });
  }
});

/**
 * PUT /admin/prompt-templates/:templateId
 * Update a prompt template
 */
router.put('/prompt-templates/:templateId', requireAdmin, async (req, res) => {
  try {
    const { templateId } = req.params;
    const { description, variables } = req.body;

    const updatedTemplate = await promptService.updatePromptTemplate(templateId, {
      description,
      variables
    });

    if (!updatedTemplate) {
      return res.status(404).json({
        success: false,
        error: 'Template not found'
      });
    }

    res.json({ 
      success: true, 
      data: updatedTemplate,
      message: `Template ${templateId} updated successfully`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to update template' 
    });
  }
});

// ===== PROMPT VERSIONS MANAGEMENT =====

/**
 * GET /admin/prompt-templates/:templateId/versions
 * Get all versions for a template
 */
router.get('/prompt-templates/:templateId/versions', requireAdmin, async (req, res) => {
  try {
    const { templateId } = req.params;
    const versions = await promptService.getTemplateVersions(templateId);
    
    res.json({ 
      success: true, 
      data: versions,
      message: `Found ${versions.length} versions for template ${templateId}`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to fetch versions' 
    });
  }
});

/**
 * POST /admin/prompt-templates/:templateId/versions
 * Create a new version of a prompt template
 */
router.post('/prompt-templates/:templateId/versions', requireAdmin, async (req, res) => {
  try {
    const { templateId } = req.params;
    const { prompt, metadata } = req.body;

    if (!prompt) {
      return res.status(400).json({
        success: false,
        error: 'Missing required field: prompt'
      });
    }

    const newVersion = await promptService.createPromptVersion(
      templateId,
      prompt,
      req.adminId,
      metadata
    );

    res.status(201).json({ 
      success: true, 
      data: newVersion,
      message: `Version ${newVersion.version} created for template ${templateId}`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to create version' 
    });
  }
});

/**
 * PUT /admin/prompt-versions/:versionId/activate
 * Activate a specific prompt version
 */
router.put('/prompt-versions/:versionId/activate', requireAdmin, async (req, res) => {
  try {
    const { versionId } = req.params;
    const { templateId } = req.body;

    if (!templateId) {
      return res.status(400).json({
        success: false,
        error: 'Missing required field: templateId'
      });
    }

    await promptService.activatePromptVersion(templateId, versionId);
    
    res.json({ 
      success: true, 
      message: `Version ${versionId} activated for template ${templateId}`
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Failed to activate version' 
    });
  }
});

// ===== UTILITY ENDPOINTS =====

/**
 * GET /admin/prompts/:templateName/:wordCount
 * Get a specific prompt by name and word count (for testing)
 */
router.get('/prompts/:templateName/:wordCount', requireAdmin, async (req, res) => {
  try {
    const { templateName, wordCount } = req.params;
    const prompt = await promptService.getPrompt(templateName, wordCount);
    
    res.json({ 
      success: true, 
      data: { 
        templateName, 
        wordCount, 
        prompt 
      },
      message: `Retrieved prompt for ${templateName} (${wordCount})`
    });
  } catch (error) {
    res.status(404).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Prompt not found' 
    });
  }
});

/**
 * POST /admin/prompts/test-variables
 * Test prompt variable processing
 */
router.post('/prompts/test-variables', requireAdmin, async (req, res) => {
  try {
    const { prompt, variables } = req.body;

    if (!prompt || !variables) {
      return res.status(400).json({
        success: false,
        error: 'Missing required fields: prompt, variables'
      });
    }

    const processedPrompt = promptService.processPromptVariables(prompt, variables);
    const extractedVariables = promptService.extractVariables(prompt);

    res.json({ 
      success: true, 
      data: {
        originalPrompt: prompt,
        processedPrompt,
        variables: extractedVariables,
        providedVariables: variables
      },
      message: 'Variable processing test completed'
    });
  } catch (error) {
    res.status(500).json({ 
      success: false, 
      error: error instanceof Error ? error.message : 'Variable processing failed' 
    });
  }
});

/**
 * GET /admin/prompts/health
 * Health check for prompt management system
 */
router.get('/prompts/health', requireAdmin, async (req, res) => {
  try {
    const templates = await promptService.getAllTemplates();
    const promptSets = await promptService.getAllPromptSets();
    const activeSet = await promptService.getActivePromptSet().catch(() => null);

    res.json({
      success: true,
      data: {
        totalTemplates: templates.length,
        totalPromptSets: promptSets.length,
        hasActiveSet: !!activeSet,
        activeSetName: activeSet?.name || null
      },
      message: 'Prompt management system is healthy'
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error instanceof Error ? error.message : 'Health check failed'
    });
  }
});

export default router;