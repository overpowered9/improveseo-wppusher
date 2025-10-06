import { PromptManagementService } from '../services/prompt-management.service.js';

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

Audience data: {{{audience_data}}}`
      },
      {
        template: {
          name: 'facts_generation',
          description: 'Generate interesting facts about the main keyword',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          variables: ['seed_keyword', 'audience_data']
        },
        prompt: `Now generate facts.

Main Keyword: {{seed_keyword}}

Audience data: {{{audience_data}}}`
      },
      {
        template: {
          name: 'introduction_toc',
          description: 'Generate introduction and table of contents for medium articles',
          category: 'format_template' as const,
          wordCountVariant: 'medium' as const,
          sectionType: 'introduction' as const,
          variables: ['nos_of_words', 'seed_keyword', 'title', 'lsi_keywords', 'voice_tone', 'point_of_view', 'audience_data', 'details_to_include', 'content_lang', 'call_to_action', 'facts_to_include']
        },
        prompt: `The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.

Include the following sections in the post:

Introduction - Provide a concise preview of the content's value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don't create a header for this section, only provide the paragraph. 

Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

Use the following formatting and structure for the output:
{
IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

<h2 id="table-of-contents">Table of Contents</h2> (Heading 2) - should not be more than 50 words. Format as clickable links: <ul><li><a href="#section-id">Section Title</a></li></ul>

Main Content Sections - Create 4 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 350-400 words. Use format: <h2 id="section-id">Section Title</h2> and <h3 id="subsection-id">Subsection Title</h3>

<h2 id="conclusion">Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.

<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.
Q: 
A:
Q: 
A: 
Q:
A: 

<h2>What's next?</h2> (Heading 2) - What's next? should not be more than 100-150 words.
}

Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don't provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type.

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:
                Main keyword: {{seed_keyword}}
                Title: "{{title}}"
                LSI keywords: {{lsi_keywords}}
                Tone of voice: {{voice_tone}} 
                Point of view: {{point_of_view}}
                Audience data: {{{audience_data}}}
                Details to include: {{details_to_include}} 
                Language: {{content_lang}}
                Call to action from user: \`{{call_to_action}}\`
                Facts to include: {{{facts_to_include}}} Do not print "Main Content Sections" text in output.`
      },
      {
        template: {
          name: 'introduction_toc',
          description: 'Generate introduction and table of contents for large articles',
          category: 'format_template' as const,
          wordCountVariant: 'large' as const,
          sectionType: 'introduction' as const,
          variables: ['nos_of_words', 'seed_keyword', 'title', 'lsi_keywords', 'voice_tone', 'point_of_view', 'audience_data', 'details_to_include', 'content_lang', 'call_to_action', 'facts_to_include']
        },
        prompt: `The user defines the main keyword, and you should make sure that the post is relevant to the main keyword.

The user provides a title and makes sure that the post is relevant to it. 

The user provides 50 LSI keywords and tries to incorporate them naturally throughout the content.

The user provides "Facts to include" that should be smoothly incorporated in the introduction and the Main content sections of the output.

Audience data: The user will include the audience data of the reader, including demographic information, tone preferences, reading level preference, and emotional needs/pain points. Use this information to tailor the content to the audience described in the audience data. Content should respond to their Emotional Needs and Pain Points.

Details to include: The user will define additional details that need to be incorporated into the blog post.

Language - The user defines that you should use US English, UK English, or German for the output. The headlines should be in the defined language as well.

Include the following sections in the post:

Introduction - Provide a concise preview of the content's value and insights and write an engaging and informative introduction, incorporating the primary keyword, applying NLP and EI principles for emotional resonance. Use the "Facts to include" provided by the user. Do not use all of them. Incorporate them smoothly so that it is part of the story flow and reads naturally. Don't create a header for this section, only provide the paragraph. 

Table of Contents - Outline main content areas of the post. Craft attention-grabbing subtitles that entice readers to click and read more. Use numbers, questions, and powerful words to draw interest. Use NLP techniques to craft subtitles that grab attention. Incorporate power words and questions to stimulate curiosity and engagement. Based on the main keyword and the audience data provided to you, you need to understand what are the emotions and intentions reader has while searching it. You should understand what deep questions and concerns user wants to answer and build your subtitles(subsections) based on these. Do not list Section titles, make short list of subtitles that will be described in Main Content Section, do not include numbering in the list of subtitles. Make engaging titles in the Table of Contents. 

Use the following formatting and structure for the output:
{
IMPORTANT: Never include the Blog Post Title. Start with the introduction paragraph

Introduction - Introduction should not be more than 100-150 words.(do not include any title, just paragraph)

<h2>Table of Contents</h2> (Heading 2) - should not be more than 50 words and formatted as a list with bullet points with normal text format

<h2>Main Content Sections</h2> (Heading 2) - Create 5 sections. Create 2-3 subsections and subtitles with formatting H3 for each section so it does not exceed required word quantity. IMPORTANT: Each section should not be more than 450-600 words. (Do not include the header 'Main Content Sections')

<h2>Conclusion</h2> (Heading 2) - Conclusion should not be more than 100-150 words. Do not include call to action details in the conclusion.

<h2>FAQs</h2> (Heading 2) - FAQs should not be more than 100-150 words.
Q: 
A:
Q: 
A: 
Q:
A: 

<h2>What's next?</h2> (Heading 2) - What's next? should not be more than 100-150 words.
}

Use the iterative approach to improve upon your initial draft. After each draft, critique your work, give it a score out of 10, and if the score is below 9, improve upon the previous draft. Repeat this process until you achieve a score of 9 or 10. When doing this, review and edit your work to remove any grammatical errors, unnecessary information, and superfluous sentences. Don't provide output of this critique, this is only for you to analyze internally. Also, check the formatting, output should not include a title of the blog post and each section/subsection should have a title with a specific heading type.

Now generate ONLY the Introduction and the Table of Contents based on the following parameters:
                Main keyword: {{seed_keyword}}
                Title: "{{title}}"
                LSI keywords: {{lsi_keywords}}
                Tone of voice: {{voice_tone}} 
                Point of view: {{point_of_view}}
                Audience data: {{{audience_data}}}
                Details to include: {{details_to_include}} 
                Language: {{content_lang}}
                Call to action from user: \`{{call_to_action}}\`
                Facts to include: {{{facts_to_include}}} Do not print "Main Content Sections" text in output. Do not print "#" text in output.`
      },
      {
        template: {
          name: 'conclusion',
          description: 'Generate conclusion section for all article sizes',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          sectionType: 'conclusion' as const,
          variables: ['min_words', 'max_words']
        },
        prompt: `Now generate the conclusion content. IMPORTANT: Output should not be more than {{min_words}}-{{max_words}} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
      },
      {
        template: {
          name: 'faqs',
          description: 'Generate FAQ section for all article sizes',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          sectionType: 'faqs' as const,
          variables: ['min_words', 'max_words']
        },
        prompt: `Now generate the FAQs content. IMPORTANT: Output should not be more than {{min_words}}-{{max_words}} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
      },
      {
        template: {
          name: 'whats_next',
          description: 'Generate "What\'s Next" section for all article sizes',
          category: 'content_generation' as const,
          wordCountVariant: 'all' as const,
          sectionType: 'whats_next' as const,
          variables: ['min_words', 'max_words']
        },
        prompt: `Now generate What is next? content. IMPORTANT: Output should not be more than {{min_words}}-{{max_words}} words. After writing an output check the word count and regenerate if it is not in the range. Do not include the word count in the output.`
      },
      {
        template: {
          name: 'meta_title',
          description: 'Generate SEO optimized meta title',
          category: 'meta_generation' as const,
          wordCountVariant: 'all' as const,
          sectionType: 'meta_title' as const,
          variables: ['title', 'seed_keyword']
        },
        prompt: `Create an SEO optimized meta title based on the blog post title \`{{title}}\` and the keyword \`{{seed_keyword}}\`. max length of title should be 50-60 characters including spaces.`
      },
      {
        template: {
          name: 'meta_description',
          description: 'Generate SEO optimized meta description',
          category: 'meta_generation' as const,
          wordCountVariant: 'all' as const,
          sectionType: 'meta_description' as const,
          variables: ['title', 'seed_keyword', 'content']
        },
        prompt: `Create an SEO optimized meta description. max length of description should be 70-80 characters including spaces. Meta description is based on the blog post title \`{{title}}\`, the keyword \`{{seed_keyword}}\` and the blog post content i.e. {{content}}.`
      }
    ];

    // Create templates and their initial versions
    const createdPrompts = [];
    for (const { template, prompt } of defaultPrompts) {
      try {
        // Create template
        const createdTemplate = await promptService.createPromptTemplate(
          template.name,
          template.description,
          template.category,
          template.wordCountVariant,
          template.sectionType,
          template.variables
        );

        // Create initial version
        const createdVersion = await promptService.createPromptVersion(
          createdTemplate._id.toString(),
          prompt,
          'system',
          {
            notes: 'Initial seeded version from WordPress implementation',
            expectedOutput: 'Generated content matching WordPress plugin behavior'
          }
        );

        // Activate this version
        await promptService.activatePromptVersion(
          createdTemplate._id.toString(),
          createdVersion._id.toString()
        );

        createdPrompts.push({
          template: createdTemplate,
          version: createdVersion
        });

        console.log(`✅ Created template '${template.name}' with version ${createdVersion.version}`);
      } catch (error) {
        if (error instanceof Error && error.message.includes('duplicate key')) {
          console.log(`⚠️  Template '${template.name}' already exists, skipping...`);
        } else {
          console.error(`❌ Failed to create template '${template.name}':`, error);
        }
      }
    }

    // Create default prompt set
    try {
      const promptMappings = createdPrompts.map(p => ({
        templateName: p.template.name,
        versionId: p.version._id.toString()
      }));

      const defaultSet = await promptService.createPromptSet(
        'Default Content Generation v1.0',
        'Initial prompt set migrated from WordPress implementation',
        promptMappings,
        'system'
      );

      // Activate the default set
      await promptService.activatePromptSet(defaultSet._id.toString());

      console.log(`✅ Created and activated default prompt set: ${defaultSet.name}`);
    } catch (error) {
      if (error instanceof Error && error.message.includes('duplicate key')) {
        console.log('⚠️  Default prompt set already exists, skipping...');
      } else {
        console.error('❌ Failed to create default prompt set:', error);
      }
    }

    console.log('🎉 Prompt seeding completed successfully!');
    
    // Return summary
    return {
      success: true,
      message: 'Prompt seeding completed',
      data: {
        templatesCreated: createdPrompts.length,
        totalDefaultPrompts: defaultPrompts.length
      }
    };

  } catch (error) {
    console.error('💥 Prompt seeding failed:', error);
    throw error;
  }
}

// CLI runner for the seed script
if (require.main === module) {
  seedDefaultPrompts()
    .then((result) => {
      console.log('Seeding result:', result);
      process.exit(0);
    })
    .catch((error) => {
      console.error('Seeding failed:', error);
      process.exit(1);
    });
}