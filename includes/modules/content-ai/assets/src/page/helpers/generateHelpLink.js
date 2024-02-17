/**
 * External dependencies
 */
import { isUndefined, includes } from 'lodash'

// KB URLs
const KB_URLS = {
	Blog_Post_Idea: 'https://rankmath.com/kb/content-ai-blog-post-idea-tool/',
	Blog_Post_Outline: 'https://rankmath.com/kb/content-ai-blog-post-outline-tool/',
	Blog_Post_Introduction: 'https://rankmath.com/kb/content-ai-blog-post-introduction-tool/',
	Blog_Post_Conclusion: 'https://rankmath.com/kb/content-ai-blog-post-conclusion-tool/',
	Post_Title: 'https://rankmath.com/kb/content-ai-post-title-tool/',
	Topic_Research: 'https://rankmath.com/kb/content-ai-topic-research-tool/?play-video=jbl6YfxdDMA',
	SEO_Title: 'https://rankmath.com/kb/content-ai-seo-title-tool/?play-video=IGzjfbZ0r8g',
	SEO_Description: 'https://rankmath.com/kb/content-ai-seo-description-tool/?play-video=chKiMSDIN14',
	Paragraph_Writing: 'https://rankmath.com/kb/content-ai-paragraph-writing-tool/',
	Sentence_Expander: 'https://rankmath.com/kb/content-ai-sentence-expander-tool/',
	Paragraph_Rewritter: 'https://rankmath.com/kb/content-ai-paragraph-rewritter-tool/',
	Text_Summarizer: 'https://rankmath.com/kb/content-ai-text-summarizer-tool/',
	Fix_Grammar: 'https://rankmath.com/kb/content-ai-fix-grammar-tool/',
	Analogy: 'https://rankmath.com/kb/content-ai-analogy-tool/',
	Product_Description: 'https://rankmath.com/kb/content-ai-product-description-tool/',
	Product_Pros_and_Cons: 'https://rankmath.com/kb/content-ai-product-pros-and-cons-tool/',
	Product_Review: 'https://rankmath.com/kb/content-ai-product-review-tool/',
	Frequently_Asked_Questions: 'https://rankmath.com/kb/content-ai-frequently-asked-questions-tool/',
	Comment_Reply: 'https://rankmath.com/kb/content-ai-comment-reply-tool/',
	Personal_Bio: 'https://rankmath.com/kb/content-ai-personal-bio-tool/',
	Company_Bio: 'https://rankmath.com/kb/content-ai-company-bio-tool/',
	Job_Description: 'https://rankmath.com/kb/content-ai-job-description-tool/',
	Testimonial: 'https://rankmath.com/kb/content-ai-testimonial-tool/',
	Facebook_Post: 'https://rankmath.com/kb/content-ai-facebook-post-tool/?play-video=_tBBi26JAiU',
	Facebook_Comment_Reply: 'https://rankmath.com/kb/content-ai-facebook-comment-reply-tool/',
	Tweet: 'https://rankmath.com/kb/content-ai-tweet-tool/',
	Tweet_Reply: 'https://rankmath.com/kb/content-ai-tweet-reply-tool/',
	Instagram_Caption: 'https://rankmath.com/kb/content-ai-instagram-caption-tool/?play-video=GHk4JwcOpRY',
	Email: 'https://rankmath.com/kb/content-ai-email-tool/?play-video=hJSmY0_WTK0',
	Email_Reply: 'https://rankmath.com/kb/content-ai-email-reply-tool/?play-video=j5R8TGVtDLY',
	AIDA: 'https://rankmath.com/kb/content-ai-aida-tool/?play-video=pHH1w_yNy4o',
	IDCA: 'https://rankmath.com/kb/content-ai-idca-tool/',
	PAS: 'https://rankmath.com/kb/content-ai-pas-tool/',
	HERO: 'https://rankmath.com/kb/content-ai-hero-tool/',
	BAB: 'https://rankmath.com/kb/content-ai-bab-tool/',
	SPIN: 'https://rankmath.com/kb/content-ai-spin-tool/',
	Youtube_Video_Script: 'https://rankmath.com/kb/content-ai-youtube-video-script-tool/',
	Youtube_Video_Description: 'https://rankmath.com/kb/content-ai-youtube-video-description-tool/',
	Podcast_Episode_Outline: 'https://rankmath.com/kb/content-ai-podcast-episode-outline-tool/',
	Recipe: 'https://rankmath.com/kb/content-ai-recipe-tool/',
	Freeform_Writing: 'https://rankmath.com/kb/content-ai-freeform-writing-tool/',
	AI_Command: 'https://rankmath.com/kb/content-ai-command-tool/',
	SEO_Meta: 'https://rankmath.com/kb/content-ai-seo-meta-tool/?play-video=fqC81KMX5IY',
	Open_Graph: 'https://rankmath.com/kb/content-ai-open-graph-tool/',
	Write: 'https://rankmath.com/kb/content-ai-editor/',
}

export default ( endpoint, isLabel = false ) => {
	let kb = KB_URLS[ endpoint ]
	if ( isLabel && includes( kb, 'play-video' ) ) {
		kb = kb.substring( 0, kb.indexOf( '?' ) )
	}
	return isUndefined( kb ) ? false : `${ kb }?utm_source=Plugin&utm_medium=AI+Tool&utm_campaign=WP`
}
