import { PromptManagementService } from '../services/prompt-management.service.js';
import { connectToDatabase } from '../config/database.js';

export async function seedDefaultPrompts() {
  const promptService = new PromptManagementService();

  console.log('🌱 Starting prompt seeding process...');

  try {
    // Define default prompts based on your current implementation
    const defaultPrompts = [
      {
        template: {
          name: 'lsi_keywords',
          description: 'Generate LSI keywords for SEO optimization',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          variables: ['seed_keyword', 'audience_data']
        },
        prompt: `As an expert SEO manager, you are tasked with generating 50 Latent Semantic Indexing (LSI) keywords. You are provided a word or phrase that is searched by the reader, and the audience data of the reader, including demographic information, tone preferences, reading level preference and emotional needs/pain points. Using this information you should come up with the LSI keywords that will be engaging and interesting for the reader who is described in the audience data and search provided word or phrase. These keywords should be closely related to the provided main keyword, enhancing content relevance and SEO effectiveness. Please compile the keywords in a comma separated text format without any additional explanations or introductions.

Main keyword: {{seed_keyword}}

Audience data: {{audience_data}}`
      },
      {
        template: {
          name: 'facts_generation',
          description: 'Generate facts for content enrichment',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          variables: ['seed_keyword', 'audience_data']
        },
        prompt: `As an expert content researcher with extensive knowledge in various fields, you are tasked with generating 50 unique, fascinating, and highly relevant facts about a specific topic. These facts should be engaging, informative, and suitable for inclusion in high-quality content. Focus on lesser-known details, recent developments, surprising statistics, and interesting connections that would captivate readers and enhance the value of the content.

Topic: {{seed_keyword}}

Audience: {{audience_data}}

Provide the facts in a numbered list format (1-50). Each fact should be concise yet informative, approximately 1-2 sentences long. Ensure the facts are accurate, current, and would appeal to the target audience described above.`
      },
      {
        template: {
          name: 'introduction_toc',
          description: 'Generate introduction with table of contents - Small articles',
          category: 'content_generation' as const,
          wordCountVariant: 'small' as const,
          variables: ['nos_of_words', 'seed_keyword', 'title', 'lsi_keywords', 'voice_tone', 'point_of_view', 'audience_data', 'details_to_include', 'content_lang', 'call_to_action', 'facts_to_include']
        },
        prompt: `Write a comprehensive article about "{{seed_keyword}}" targeting {{nos_of_words}}. The article should be written in {{content_lang}} with a {{voice_tone}} tone and from a {{point_of_view}} perspective.

ARTICLE REQUIREMENTS:
- Target Length: {{nos_of_words}}
- Language: {{content_lang}}
- Tone: {{voice_tone}}
- Perspective: {{point_of_view}}
- Title: {{title}}

AUDIENCE INFORMATION:
{{audience_data}}

ADDITIONAL DETAILS TO INCLUDE:
{{details_to_include}}

LSI KEYWORDS TO INCORPORATE:
{{lsi_keywords}}

RELEVANT FACTS TO INCLUDE:
{{facts_to_include}}

CALL TO ACTION:
{{call_to_action}}

Please start by writing:
1. An engaging introduction (200-300 words)
2. A table of contents with 4 main sections

Format the table of contents as:
<h2>Table of Contents</h2>
<ol>
<li><a href="#section1">Section Title 1</a></li>
<li><a href="#section2">Section Title 2</a></li>
<li><a href="#section3">Section Title 3</a></li>
<li><a href="#section4">Section Title 4</a></li>
</ol>

Start with the introduction now. Do not include the word count in your output.`
      }
    ];

    // Create templates and versions
    console.log('📝 Creating prompt templates...');
    
    const createdPrompts = [];
    for (const promptData of defaultPrompts) {
      try {
        // Create template
        const template = await promptService.createPromptTemplate(
          promptData.template.name,
          promptData.template.description,
          promptData.template.category,
          promptData.template.wordCountVariant,
          undefined, // sectionType - not needed for these templates
          promptData.template.variables
        );
        
        console.log(`✅ Created template: ${template.name}`);
        
        // Create initial version
        const version = await promptService.createPromptVersion(
          template._id.toString(),
          promptData.prompt,
          'system-seed',
          {
            notes: 'Initial version seeded from WordPress implementation',
            expectedOutput: `${promptData.template.category} content for ${promptData.template.wordCountVariant} articles`
          }
        );
        
        // Activate this version
        await promptService.activatePromptVersion(
          template._id.toString(),
          version._id.toString()
        );
        
        console.log(`✅ Created and activated version ${version.version} for ${template.name}`);
        
        createdPrompts.push({
          templateName: template.name,
          versionId: version._id.toString()
        });
        
      } catch (error) {
        console.error(`❌ Error creating prompt ${promptData.template.name}:`, error);
        // Continue with other prompts
      }
    }

    // Create default prompt set
    console.log('📦 Creating default prompt set...');
    
    const defaultSet = await promptService.createPromptSet(
      'Default WordPress Prompts v1.0',
      'Default prompt set migrated from WordPress implementation with core prompts',
      createdPrompts,
      'system-seed'
    );
    
    console.log(`✅ Created prompt set: ${defaultSet.name}`);
    
    // Activate the default set
    await promptService.activatePromptSet(defaultSet._id.toString());
    console.log(`✅ Activated default prompt set`);
    
    const result = {
      templatesCreated: createdPrompts.length,
      promptSetId: defaultSet._id,
      promptSetName: defaultSet.name,
      activePrompts: createdPrompts.map(p => p.templateName)
    };
    
    console.log('🎉 Prompt seeding completed successfully!');
    console.log('📊 Summary:', result);
    
    return result;

  } catch (error) {
    console.error('❌ Error during prompt seeding:', error);
    throw error;
  }
}

// CLI runner for the seed script
import { fileURLToPath } from 'url';
import { dirname } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Check if this script is being run directly
if (import.meta.url === `file://${process.argv[1]}`) {
  (async () => {
    try {
      // Connect to database first
      await connectToDatabase();
      
      const result = await seedDefaultPrompts();
      console.log('✅ Seeding completed successfully:', result);
      process.exit(0);
    } catch (error) {
      console.error('❌ Seeding failed:', error);
      process.exit(1);
    }
  })();
}