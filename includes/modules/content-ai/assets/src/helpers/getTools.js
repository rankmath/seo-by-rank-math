/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import generateHelpLink from './generateHelpLink'

export default () => {
	return [
		{
			endpoint: 'Blog_Post_Wizard',
			title: __( 'Blog Post Wizard', 'rank-math' ),
			description: __( 'Create a complete blog post in one go. Just fill in some details and Content AI will create a complete blog post for you.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-pencil',
			helpLink: generateHelpLink( 'Blog_Post_Wizard' ),
			output: {
				default: 5,
				max: 20,
			},
		},
		{
			endpoint: 'Blog_Post_Idea',
			title: __( 'Blog Post Idea', 'rank-math' ),
			description: __( 'Get fresh ideas for engaging blog posts that resonate with your niche and audience, ensuring captivating content.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-edit',
			helpLink: generateHelpLink( 'Blog_Post_Idea' ),
			params: {
				topic_brief: {
					isRequired: true,
					label: __( 'Describe Your Industry/Niche', 'rank-math' ),
					placeholder: __( 'e.g. Technology blog that covers latest gadgets, tech news, and reviews', 'rank-math' ),
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				style: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 5,
				max: 20,
			},
		},
		{
			endpoint: 'Blog_Post_Outline',
			title: __( 'Blog Post Outline', 'rank-math' ),
			description: __( 'Structure blog posts with a clear flow, guiding readers effortlessly for better understanding and engagement.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-howto',
			helpLink: generateHelpLink( 'Blog_Post_Outline' ),
			params: {
				topic: {
					isRequired: true,
				},
				main_points: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				style: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 3,
			},
		},
		{
			endpoint: 'Blog_Post_Introduction',
			title: __( 'Blog Post Introduction', 'rank-math' ),
			description: __( 'Craft attractive intros that captivate readers\' interest, compelling them to explore further into your blog.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-acf',
			helpLink: generateHelpLink( 'Blog_Post_Introduction' ),
			params: {
				title: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 2,
				max: 5,
			},
		},
		{
			endpoint: 'Blog_Post_Conclusion',
			title: __( 'Blog Post Conclusion', 'rank-math' ),
			description: __( 'End your blog posts with impactful summaries, reinforcing key takeaways and leaving a lasting impression.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-support',
			helpLink: generateHelpLink( 'Blog_Post_Conclusion' ),
			params: {
				topic: {
					isRequired: true,
				},
				main_argument: {
					isRequired: true,
				},
				call_to_action: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 2,
				max: 5,
			},
		},
		{
			endpoint: 'Post_Title',
			title: __( 'Post Title', 'rank-math' ),
			description: __( 'Create eye-catching headlines for articles and blogs, grabbing readers\' attention and boosting engagement.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-heading',
			helpLink: generateHelpLink( 'Post_Title' ),
			params: {
				post_brief: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				style: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 5,
				max: 25,
			},
		},
		{
			endpoint: 'Topic_Research',
			title: __( 'Topic Research', 'rank-math' ),
			description: __( 'Dive deep into comprehensive reports on specific topics, uncovering trends, history, and industry players.', 'rank-math' ),
			category: 'seo',
			icon: 'rm-icon rm-icon-analyzer',
			helpLink: generateHelpLink( 'Topic_Research' ),
			params: {
				topic: {
					isRequired: true,
				},
				relevance: {
					isRequired: false,
				},
				format: {
					isRequired: true,
				},
				focus_keyword: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'SEO_Title',
			title: __( 'SEO Title', 'rank-math' ),
			description: __( 'Optimize headlines for enhanced visibility, organic traffic, and a stronger online presence.', 'rank-math' ),
			category: 'seo',
			icon: 'rm-icon rm-icon-seo-title',
			helpLink: generateHelpLink( 'SEO_Title' ),
			params: {
				post_title: {
					isRequired: true,
				},
				focus_keyword: {
					isRequired: false,
				},
				post_brief: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 5,
				max: 25,
			},
		},
		{
			endpoint: 'SEO_Description',
			title: __( 'SEO Description', 'rank-math' ),
			description: __( 'Craft concise and persuasive summaries that captivate readers and search engines, improving click-through rates.', 'rank-math' ),
			category: 'seo',
			icon: 'rm-icon rm-icon-seo-description',
			helpLink: generateHelpLink( 'SEO_Description' ),
			params: {
				seo_title: {
					isRequired: true,
				},
				focus_keyword: {
					isRequired: false,
				},
				post_brief: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 25,
			},
		},
		{
			endpoint: 'Paragraph',
			title: __( 'Paragraph', 'rank-math' ),
			description: __( 'Generate well-structured and informative paragraphs, seamlessly blending into your content for better readability.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-text-align-left',
			helpLink: generateHelpLink( 'Paragraph_Writing' ),
			params: {
				topic: {
					isRequired: true,
				},
				main_argument: {
					isRequired: true,
				},
				tone: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				supporting_points: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 2,
				max: 5,
			},
		},
		{
			endpoint: 'Paragraph_Rewriter',
			title: __( 'Paragraph Rewriter', 'rank-math' ),
			description: __( 'Refine paragraphs while preserving meaning, ensuring originality, and enhancing clarity.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-book',
			helpLink: generateHelpLink( 'Paragraph_Rewritter' ),
			params: {
				original_paragraph: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 3,
			},
		},
		{
			endpoint: 'Sentence_Expander',
			title: __( 'Sentence Expander', 'rank-math' ),
			description: __( 'Transform incomplete sentences into polished expressions, adding depth and clarity to your writing.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-misc',
			helpLink: generateHelpLink( 'Sentence_Expander' ),
			params: {
				sentence: {
					isRequired: true,
				},
				topic: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 5,
			},
		},
		{
			endpoint: 'Text_Summarizer',
			title: __( 'Text Summarizer', 'rank-math' ),
			description: __( 'Condense complex texts into concise summaries, highlighting crucial points and essential information.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-page',
			helpLink: generateHelpLink( 'Text_Summarizer' ),
			params: {
				text: {
					isRequired: true,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 8,
			},
		},
		{
			endpoint: 'Fix_Grammar',
			title: __( 'Fix Grammar', 'rank-math' ),
			description: __( 'Utilize AI-powered grammar correction to polish your written content, eliminating errors and improving clarity.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-help',
			helpLink: generateHelpLink( 'Fix_Grammar' ),
			params: {
				text: {
					isRequired: true,
					label: __( 'Text', 'rank-math' ),
					placeholder: __( 'Enter the text to fix grammar', 'rank-math' ),
				},
			},
			output: {
				default: 1,
				max: 1,
			},
		},
		{
			endpoint: 'Analogy',
			title: __( 'Analogy', 'rank-math' ),
			description: __( 'Enhance clarity by rephrasing text using alternative words, providing a fresh perspective without altering meaning.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-sitemap',
			helpLink: generateHelpLink( 'Analogy' ),
			params: {
				text: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 8,
			},
		},
		{
			endpoint: 'Product_Description',
			title: __( 'Product Description', 'rank-math' ),
			description: __( 'Craft compelling descriptions that effectively showcase the unique benefits and features of your product.', 'rank-math' ),
			category: 'ecommerce',
			icon: 'rm-icon rm-icon-mobile',
			helpLink: generateHelpLink( 'Product_Description' ),
			params: {
				product_name: {
					isRequired: true,
				},
				features_and_benefits: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 2,
				max: 5,
			},
		},
		{
			endpoint: 'Product_Pros_And_Cons',
			title: __( 'Product Pros & Cons', 'rank-math' ),
			description: __( 'Present balanced overviews outlining the advantages and limitations, aiding informed decisions.', 'rank-math' ),
			category: 'ecommerce',
			icon: 'rm-icon rm-icon-thumbs-up',
			helpLink: generateHelpLink( 'Product_Pros_and_Cons' ),
			params: {
				product_name: {
					isRequired: true,
				},
				features_and_benefits: {
					isRequired: true,
				},
				limitations_and_drawbacks: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 2,
				max: 5,
			},
		},
		{
			endpoint: 'Product_Review',
			title: __( 'Product Review', 'rank-math' ),
			description: __( 'Provide detailed evaluations covering strengths, weaknesses, and practical recommendations.', 'rank-math' ),
			category: 'ecommerce',
			icon: 'rm-icon rm-icon-star',
			helpLink: generateHelpLink( 'Product_Review' ),
			params: {
				features_and_benefits: {
					isRequired: true,
				},
				product_name: {
					isRequired: true,
				},
				limitations_and_drawbacks: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Frequently_Asked_Questions',
			title: __( 'Frequently Asked Questions', 'rank-math' ),
			description: __( 'Address common queries with comprehensive answers, offering valuable information and guidance.', 'rank-math' ),
			category: 'ecommerce',
			icon: 'rm-icon rm-icon-faq',
			helpLink: generateHelpLink( 'Frequently_Asked_Questions' ),
			params: {
				topic: {
					isRequired: true,
				},
				features_and_benefits: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Comment_Reply',
			title: __( 'Comment Reply', 'rank-math' ),
			description: __( 'Engage your audience with thoughtful and engaging responses, fostering meaningful interactions.', 'rank-math' ),
			category: 'blog',
			icon: 'rm-icon rm-icon-comments',
			helpLink: generateHelpLink( 'Comment_Reply' ),
			params: {
				reply_brief: {
					isRequired: true,
				},
				original_comment: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 8,
			},
		},
		{
			endpoint: 'Personal_Bio',
			title: __( 'Personal Bio', 'rank-math' ),
			description: __( 'Create professional and captivating biographies highlighting accomplishments, expertise, and personality.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-user',
			helpLink: generateHelpLink( 'Personal_Bio' ),
			params: {
				personal_information: {
					isRequired: true,
				},
				purpose: {
					isRequired: true,
				},
				personal_achievements: {
					isRequired: true,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 1,
			},
		},
		{
			endpoint: 'Company_Bio',
			title: __( 'Company Bio', 'rank-math' ),
			description: __( 'Craft informative overviews of your company\'s history, values, mission, and team, building credibility.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-restaurant',
			helpLink: generateHelpLink( 'Company_Bio' ),
			params: {
				company_name: {
					isRequired: true,
				},
				purpose: {
					isRequired: true,
				},
				company_information: {
					isRequired: true,
				},
				company_history: {
					isRequired: false,
				},
				team: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 1,
			},
		},
		{
			endpoint: 'Job_Description',
			title: __( 'Job Description', 'rank-math' ),
			description: __( 'Create enticing and comprehensive descriptions outlining requirements, responsibilities, and opportunities.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-job',
			helpLink: generateHelpLink( 'Job_Description' ),
			params: {
				company_name: {
					isRequired: true,
				},
				job_title: {
					isRequired: true,
				},
				requirements: {
					isRequired: true,
				},
				responsibilities: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 3,
			},
		},
		{
			endpoint: 'Testimonial',
			title: __( 'Testimonial', 'rank-math' ),
			description: __( 'Develop persuasive testimonials sharing positive experiences, endorsing your product, service, or brand.', 'rank-math' ),
			category: 'ecommerce',
			icon: 'rm-icon rm-icon-schema',
			helpLink: generateHelpLink( 'Testimonial' ),
			params: {
				topic: {
					isRequired: true,
					label: __( 'Product or Service', 'rank-math' ),
				},
				features_and_benefits: {
					isRequired: true,
				},
				limitations_and_drawbacks: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Facebook_Post',
			title: __( 'Facebook Post', 'rank-math' ),
			description: __( 'Create intriguing and shareable content for Facebook, captivating your audience and boosting engagement.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-facebook',
			helpLink: generateHelpLink( 'Facebook_Post' ),
			params: {
				topic_brief: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Facebook_Comment_Reply',
			title: __( 'Facebook Comment Reply', 'rank-math' ),
			description: __( 'Generate relevant responses to Facebook comments, build relationships & encourage interaction.', 'rank-math' ),
			category: 'marketing-comments-reply',
			icon: 'rm-icon rm-icon-comments-reply',
			helpLink: generateHelpLink( 'Facebook_Comment_Reply' ),
			params: {
				reply_brief: {
					isRequired: true,
					label: __( 'Reply brief', 'rank-math' ),
				},
				comment: {
					isRequired: true,
				},
				post_brief: {
					isRequired: false,
					label: __( 'Post brief', 'rank-math' ),
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Tweet',
			title: __( 'Tweet', 'rank-math' ),
			description: __( 'Create engaging tweets, boost interaction, and foster connections with your followers.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-twitter',
			helpLink: generateHelpLink( 'Tweet' ),
			params: {
				topic_brief: {
					isRequired: true,
				},
				hashtags: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Tweet_Reply',
			title: __( 'Tweet Reply', 'rank-math' ),
			description: __( 'Generate optimized replies for tweets to promote engagement and strengthen connections.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-comments-reply',
			helpLink: generateHelpLink( 'Tweet_Reply' ),
			params: {
				reply_brief: {
					isRequired: true,
					label: __( 'Reply brief', 'rank-math' ),
				},
				tweet: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Instagram_Caption',
			title: __( 'Instagram Caption', 'rank-math' ),
			description: __( 'Craft catchy captions for Instagram posts to increase engagement and grab attention.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-instagram',
			helpLink: generateHelpLink( 'Instagram_Caption' ),
			params: {
				post_brief: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Email',
			title: __( 'Email', 'rank-math' ),
			description: __( 'Create effective emails for promotions, announcements, and follow-ups to achieve marketing goals.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-mail',
			helpLink: generateHelpLink( 'Email' ),
			params: {
				email_brief: {
					isRequired: true,
				},
				call_to_action: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Email_Reply',
			title: __( 'Email Reply', 'rank-math' ),
			description: __( 'Craft courteous email replies to promote interaction and strengthen relationships.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-mail-reply',
			helpLink: generateHelpLink( 'Email_Reply' ),
			params: {
				email: {
					isRequired: true,
				},
				reply_brief: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'AIDA',
			title: __( 'AIDA', 'rank-math' ),
			description: __( 'Write persuasive text using the Attention-Interest-Desire-Action formula to drive action.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-light-bulb',
			helpLink: generateHelpLink( 'AIDA' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'IDCA',
			title: __( 'IDCA', 'rank-math' ),
			description: __( 'Create compelling messages using the Identify-Develop-Communicate-Ask strategy to resonate.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-help',
			helpLink: generateHelpLink( 'IDCA' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'PAS',
			title: __( 'PAS', 'rank-math' ),
			description: __( 'Address customer problems with the Problem-Agitate-Solution technique to fulfill needs.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-tick',
			helpLink: generateHelpLink( 'PAS' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'HERO',
			title: __( 'HERO', 'rank-math' ),
			description: __( 'Craft captivating headlines using the HERO formula to engage, reveal, and offer value.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-text',
			helpLink: generateHelpLink( 'HERO' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'SPIN',
			title: __( 'SPIN', 'rank-math' ),
			description: __( 'Describe customer problems, highlight implications, and offer solutions using the SPIN method.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-social',
			helpLink: generateHelpLink( 'SPIN' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'BAB',
			title: __( 'BAB', 'rank-math' ),
			description: __( 'Create a compelling Before-After-Bridge narrative to demonstrate product or service value.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-dataset',
			helpLink: generateHelpLink( 'BAB' ),
			params: {
				product_name: {
					isRequired: true,
				},
				product_description: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 2,
			},
		},
		{
			endpoint: 'Youtube_Video_Script',
			title: __( 'YouTube Video Script', 'rank-math' ),
			description: __( 'Develop engaging video scripts for YouTube to inform, entertain, and align.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-youtube',
			helpLink: generateHelpLink( 'Youtube_Video_Script' ),
			params: {
				topic: {
					isRequired: true,
				},
				visual_elements: {
					isRequired: false,
				},
				key_points: {
					isRequired: true,
				},
				call_to_action: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Youtube_Video_Description',
			title: __( 'YouTube Video Description', 'rank-math' ),
			description: __( 'Generate informative and engaging video descriptions for YouTube.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-video',
			helpLink: generateHelpLink( 'Youtube_Video_Description' ),
			params: {
				topic: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: true,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Podcast_Episode_Outline',
			title: __( 'Podcast Episode Outline', 'rank-math' ),
			description: __( 'Create detailed outlines for podcast episodes, including topics and takeaways.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-podcast',
			helpLink: generateHelpLink( 'Podcast_Episode_Outline' ),
			params: {
				topic: {
					isRequired: true,
				},
				host: {
					isRequired: false,
				},
				co_host: {
					isRequired: false,
				},
				key_points: {
					isRequired: true,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Recipe',
			title: __( 'Recipe', 'rank-math' ),
			description: __( 'Create detailed and easy-to-follow recipes with ingredients, instructions, and nutrition.', 'rank-math' ),
			category: 'food-cooking',
			icon: 'rm-icon rm-icon-recipe',
			helpLink: generateHelpLink( 'Recipe' ),
			params: {
				cuisine: {
					isRequired: true,
				},
				type: {
					isRequired: true,
				},
				ingredients: {
					isRequired: true,
				},
				dietary_restrictions: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 10,
			},
		},
		{
			endpoint: 'Freeform_Writing',
			title: __( 'Freeform Writing', 'rank-math' ),
			description: __( 'Generate text based on prompts or topics, allowing for imaginative or technical writing.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-page',
			helpLink: generateHelpLink( 'Freeform_Writing' ),
			params: {
				text: {
					isRequired: true,
					label: __( 'What do you want to write?', 'rank-math' ),
				},
				main_points: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				length: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 1,
			},
		},
		{
			endpoint: 'AI_Command',
			title: __( 'AI Command', 'rank-math' ),
			description: __( 'Ask AI anything and receive relevant and informative responses for questions or requests.', 'rank-math' ),
			category: 'misc',
			icon: 'rm-icon rm-icon-code',
			helpLink: generateHelpLink( 'AI_Command' ),
			params: {
				command: {
					isRequired: true,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 1,
			},
		},
		{
			endpoint: 'SEO_Meta',
			title: __( 'SEO Meta', 'rank-math' ),
			description: __( 'Optimize headlines and descriptions to improve visibility on search engines.', 'rank-math' ),
			category: 'seo',
			icon: 'rm-icon rm-icon-seo',
			helpLink: generateHelpLink( 'SEO_Meta' ),
			params: {
				topic: {
					isRequired: true,
				},
				post_brief: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 3,
				max: 25,
			},
		},
		{
			endpoint: 'Opengraph',
			title: __( 'Open Graph', 'rank-math' ),
			description: __( 'Boost content visibility on social media with topic-specific meta tags for easy discovery.', 'rank-math' ),
			category: 'marketing-sales',
			icon: 'rm-icon rm-icon-social',
			helpLink: generateHelpLink( 'Open_Graph' ),
			params: {
				topic: {
					isRequired: true,
				},
				post_brief: {
					isRequired: false,
				},
				audience: {
					isRequired: false,
				},
				focus_keyword: {
					isRequired: false,
				},
				tone: {
					isRequired: false,
				},
				language: {
					isRequired: false,
				},
			},
			output: {
				default: 1,
				max: 5,
			},
		},
	]
}
