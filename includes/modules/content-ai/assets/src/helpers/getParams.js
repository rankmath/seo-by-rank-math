	/**
	 * WordPress dependencies
	 */
	import { __ } from '@wordpress/i18n'
	
	export default ( key ) => {
		const params = {
	    "topic_brief": {
	        "label": __( 'Topic Brief', 'rank-math' ),
	        "placeholder": __( 'Enter a short summary of your topic', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": "400"
	    },
	    "audience": {
	        "label": __( 'Audience', 'rank-math' ),
	        "tooltip": __( 'The target audience for the content.', 'rank-math' ),
	        "placeholder": __( 'Select or Write Custom', 'rank-math' ),
	        "options": [
	            {
	                "value": "General Audience",
	                "icon": "🌏"
	            },
	            {
	                "value": "Consumers",
	                "icon": "🛍"
	            },
	            {
	                "value": "Students",
	                "icon": "📚"
	            },
	            {
	                "value": "Professionals",
	                "icon": "💼"
	            },
	            {
	                "value": "Business Owners",
	                "icon": "🏭"
	            },
	            {
	                "value": "Job Seekers",
	                "icon": "🔍"
	            },
	            {
	                "value": "Investors",
	                "icon": "💰"
	            },
	            {
	                "value": "Entrepreneurs",
	                "icon": "🚀"
	            },
	            {
	                "value": "Social Media Users",
	                "icon": "📱"
	            },
	            {
	                "value": "Travelers",
	                "icon": "🛫"
	            },
	            {
	                "value": "Pet Owners",
	                "icon": "🐾"
	            },
	            {
	                "value": "Seniors",
	                "icon": "🧓"
	            },
	            {
	                "value": "Gaming Enthusiasts",
	                "icon": "🎮"
	            },
	            {
	                "value": "Environmentalists",
	                "icon": "🌍"
	            },
	            {
	                "value": "Sports Fans",
	                "icon": "⚽️"
	            },
	            {
	                "value": "Health Enthusiasts",
	                "icon": "💊"
	            },
	            {
	                "value": "Tech Enthusiasts",
	                "icon": "💻"
	            },
	            {
	                "value": "Parents",
	                "icon": "👨‍👧‍👦"
	            },
	            {
	                "value": "Artists",
	                "icon": "🎨"
	            },
	            {
	                "value": "Musicians",
	                "icon": "🎸"
	            },
	            {
	                "value": "Photographers",
	                "icon": "📷"
	            },
	            {
	                "value": "Writers",
	                "icon": "✍️"
	            },
	            {
	                "value": "Retirees",
	                "icon": "👴"
	            },
	            {
	                "value": "Healthcare Professionals",
	                "icon": "👩‍⚕️"
	            },
	            {
	                "value": "Educators",
	                "icon": "👩‍🏫"
	            },
	            {
	                "value": "Activists",
	                "icon": "👩‍⚖️"
	            },
	            {
	                "value": "Foodies",
	                "icon": "🍕"
	            },
	            {
	                "value": "Cooks",
	                "icon": "👩‍🍳"
	            },
	            {
	                "value": "Fitness Enthusiasts",
	                "icon": "🏋️‍♀️"
	            },
	            {
	                "value": "Bargain Hunters",
	                "icon": "🛍"
	            },
	            {
	                "value": "Fashionistas",
	                "icon": "👗"
	            },
	            {
	                "value": "Outdoor Enthusiasts",
	                "icon": "🏕"
	            },
	            {
	                "value": "Indoor Hobbyists",
	                "icon": "🎨"
	            },
	            {
	                "value": "Gardeners",
	                "icon": "🌱"
	            },
	            {
	                "value": "DIYers",
	                "icon": "🔧"
	            },
	            {
	                "value": "Crafters",
	                "icon": "🧶"
	            },
	            {
	                "value": "Collectors",
	                "icon": "📚"
	            },
	            {
	                "value": "Dancers",
	                "icon": "💃"
	            },
	            {
	                "value": "Gamers",
	                "icon": "🎮"
	            },
	            {
	                "value": "Movie Buffs",
	                "icon": "🎥"
	            },
	            {
	                "value": "TV Enthusiasts",
	                "icon": "📺"
	            },
	            {
	                "value": "Video Creators",
	                "icon": "🎥"
	            },
	            {
	                "value": "Engineers",
	                "icon": "🔧"
	            },
	            {
	                "value": "Designers",
	                "icon": "🎨"
	            },
	            {
	                "value": "Podcast Listeners",
	                "icon": "🎧"
	            },
	            {
	                "value": "Bloggers",
	                "icon": "📝"
	            },
	            {
	                "value": "Authors",
	                "icon": "📚"
	            }
	        ],
	        "default": rankMath.contentAI.audience,
	        "maxlength": "200"
	    },
	    "tone": {
	        "label": __( 'Tone', 'rank-math' ),
	        "tooltip": __( 'The tone of the content.', 'rank-math' ),
	        "placeholder": __( 'Select or Write Custom', 'rank-math' ),
	        "options": [
	            {
	                "value": "Formal",
	                "icon": "🤵"
	            },
	            {
	                "value": "Informal",
	                "icon": "🤗"
	            },
	            {
	                "value": "Friendly",
	                "icon": "😊"
	            },
	            {
	                "value": "Casual",
	                "icon": "💁‍♀️"
	            },
	            {
	                "value": "Conversational",
	                "icon": "🗣️"
	            },
	            {
	                "value": "Descriptive",
	                "icon": "📚"
	            },
	            {
	                "value": "Persuasive",
	                "icon": "🤝"
	            },
	            {
	                "value": "Creative",
	                "icon": "🎨"
	            },
	            {
	                "value": "Technical",
	                "icon": "🔧"
	            },
	            {
	                "value": "Analytical",
	                "icon": "📊"
	            },
	            {
	                "value": "Journalese",
	                "icon": "📰"
	            },
	            {
	                "value": "Poetic",
	                "icon": "🌺"
	            },
	            {
	                "value": "Factual",
	                "icon": "📊"
	            },
	            {
	                "value": "Emotional",
	                "icon": "💔"
	            },
	            {
	                "value": "Satirical",
	                "icon": "😅"
	            },
	            {
	                "value": "Empathetic",
	                "icon": "😔"
	            },
	            {
	                "value": "Opinionated",
	                "icon": "💬"
	            },
	            {
	                "value": "Humorous",
	                "icon": "😂"
	            },
	            {
	                "value": "Story-telling",
	                "icon": "📚"
	            },
	            {
	                "value": "Narrative",
	                "icon": "📖"
	            },
	            {
	                "value": "Expository",
	                "icon": "📚"
	            },
	            {
	                "value": "Argumentative",
	                "icon": "🗣️"
	            },
	            {
	                "value": "Objective",
	                "icon": "📊"
	            },
	            {
	                "value": "Subjective",
	                "icon": "💬"
	            }
	        ],
	        "default": rankMath.contentAI.tone,
	        "maxlength": "200"
	    },
	    "style": {
	        "label": __( 'Style', 'rank-math' ),
	        "tooltip": __( 'The style of the content.', 'rank-math' ),
			"help_link": "https://rankmath.com/kb/blog-post-idea/?utm_source=Plugin&utm_medium=AI+Tool+Style&utm_campaign=WP#style",
	        "placeholder": __( 'Select or Write Custom', 'rank-math' ),
	        "options": [
	            {
	                "value": "Listicle",
	                "icon": "🔢"
	            },
	            {
	                "value": "Tutorial",
	                "icon": "📖"
	            },
	            {
	                "value": "Review",
	                "icon": "⭐️"
	            },
	            {
	                "value": "Case Study",
	                "icon": "🕵️‍♂️"
	            },
	            {
	                "value": "Opinion",
	                "icon": "🗣️"
	            },
	            {
	                "value": "News",
	                "icon": "📰"
	            },
	            {
	                "value": "Newsjacking",
	                "icon": "🗞"
	            },
	            {
	                "value": "Personal",
	                "icon": "💬"
	            },
	            {
	                "value": "Story-telling",
	                "icon": "📚"
	            },
	            {
	                "value": "Guide",
	                "icon": "🗺️"
	            },
	            {
	                "value": "Research-based",
	                "icon": "🔬"
	            },
	            {
	                "value": "Interview",
	                "icon": "🎤"
	            },
	            {
	                "value": "Infographic",
	                "icon": "📊"
	            },
	            {
	                "value": "Debate",
	                "icon": "🤔"
	            },
	            {
	                "value": "Video Blog",
	                "icon": "🎥"
	            },
	            {
	                "value": "Vlog",
	                "icon": "📹"
	            },
	            {
	                "value": "Podcast",
	                "icon": "🎧"
	            },
	            {
	                "value": "Audio Blog",
	                "icon": "🎙"
	            },
	            {
	                "value": "Quiz",
	                "icon": "🎲"
	            },
	            {
	                "value": "Contest",
	                "icon": "🎉"
	            },
	            {
	                "value": "Poll",
	                "icon": "📊"
	            },
	            {
	                "value": "Comparison",
	                "icon": "🔎"
	            },
	            {
	                "value": "How-to",
	                "icon": "📖"
	            },
	            {
	                "value": "FAQ",
	                "icon": "❓"
	            }
	        ],
	        "maxlength": "200"
	    },
	    "language": {
	        "label": __( 'Output Language', 'rank-math' ),
	        "placeholder": __( '', 'rank-math' ),
	        "type": "select",
	        "options": [
				{
					"value": "US English",
					"icon": "🇺🇸"
				},
				{
					"value": "UK English",
					"icon": "🇬🇧"
				},
				{
					"value": "Arabic",
					"icon": "🇦🇪"
				},
				{
					"value": "Bulgarian",
					"icon": "🇧🇬"
				},
				{
					"value": "Chinese",
					"icon": "🇨🇳"
				},
				{
					"value": "Czech",
					"icon": "🇨🇿"
				},
				{
					"value": "Danish",
					"icon": "🇩🇰"
				},
				{
					"value": "Dutch",
					"icon": "🇳🇱"
				},
				{
					"value": "Estonian",
					"icon": "🇪🇪"
				},
				{
					"value": "Finnish",
					"icon": "🇫🇮"
				},
				{
					"value": "French",
					"icon": "🇫🇷"
				},
				{
					"value": "German",
					"icon": "🇩🇪"
				},
				{
					"value": "Greek",
					"icon": "🇬🇷"
				},
				{
					"value": "Hebrew",
					"icon": "🇮🇱"
				},
				{
					"value": "Hungarian",
					"icon": "🇭🇺"
				},
				{
					"value": "Indonesian",
					"icon": "🇮🇩"
				},
				{
					"value": "Italian",
					"icon": "🇮🇹"
				},
				{
					"value": "Japanese",
					"icon": "🇯🇵"
				},
				{
					"value": "Korean",
					"icon": "🇰🇷"
				},
				{
					"value": "Latvian",
					"icon": "🇱🇻"
				},
				{
					"value": "Lithuanian",
					"icon": "🇱🇹"
				},
				{
					"value": "Norwegian",
					"icon": "🇳🇴"
				},
				{
					"value": "Polish",
					"icon": "🇵🇱"
				},
				{
					"value": "Portuguese",
					"icon": "🇵🇹"
				},
				{
					"value": "Romanian",
					"icon": "🇷🇴"
				},
				{
					"value": "Russian",
					"icon": "🇷🇺"
				},
				{
					"value": "Slovak",
					"icon": "🇸🇰"
				},
				{
					"value": "Slovenian",
					"icon": "🇸🇮"
				},
				{
					"value": "Spanish",
					"icon": "🇪🇸"
				},
				{
					"value": "Swedish",
					"icon": "🇸🇪"
				}
				,
				{
					"value": "Turkish",
					"icon": "🇹🇷"
				}
			],				
			"default": rankMath.contentAI.language,
			"maxTags": 1,
	    },
	    "topic": {
	        "label": __( 'Topic', 'rank-math' ),
	        "placeholder": __( 'Enter a short summary of your topic', 'rank-math' ),
	        "maxlength": "200"
	    },
	    "main_points": {
	        "label": __( 'Main points &amp; ideas', 'rank-math' ),
	        "placeholder": __( 'Enter the main points you want to cover, separated by comma', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": "400"
	    },
	    "focus_keyword": {
	        "label": __( 'Focus Keyword', 'rank-math' ),
	        "placeholder": __( 'Enter the main keywords to focus on', 'rank-math' ),
	        "maxlength": "200",
			"options": [],
			"default": [],
	    },
	    "title": {
	        "label": __( 'Post title', 'rank-math' ),
	        "placeholder": __( 'Enter your post title', 'rank-math' ),
	        "maxlength": 200
	    },
	    "main_argument": {
	        "label": __( 'Main Argument', 'rank-math' ),
			"placeholder": __( 'Enter the main point you want to make', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": "400"
	    },
	    "call_to_action": {
	        "label": __( 'Call to Action', 'rank-math' ),
	        "placeholder": __( 'Select or Write Custom', 'rank-math' ),
	        "type": "select",
	        "options": [
	            "Subscribe to our newsletter",
	            "Follow social media accounts",
	            "Download a resource or guide",
	            "Share the blog post on social media",
	            "Comment on the blog post",
	            "Check out related resources",
	            "Sign up for a webinar or event",
	            "Contact for more information",
	            "Purchase a product or service"
	        ],
	        "maxlength": "300"
	    },
	    "post_brief": {
	        "label": __( 'Post Brief', 'rank-math' ),
	        "placeholder": __( 'Enter a short summary of your post', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": "400"
	    },
	    "length": {
	        "label": __( 'Length', 'rank-math' ),
	        "placeholder": __( '', 'rank-math' ),
	        "type": "button",
	        "options": [
	            {
	                "value": "short"
	            },
	            {
	                "value": "medium"
	            },
	            {
	                "value": "long"
	            }
	        ],
	        "maxlength": 200,
			"default": "medium",
	    },
	    "relevance": {
	        "label": __( 'Relevance', 'rank-math' ),
	        "placeholder": __( 'Select or Write Custom', 'rank-math' ),
	        "options": [
	            {
	                "value": "Recent",
	                "icon": "🗓️"
	            },
	            {
	                "value": "Historical",
	                "icon": "📜"
	            },
	            {
	                "value": "Regional",
	                "icon": "🗺️"
	            },
	            {
	                "value": "Comparative",
	                "icon": "⚖️"
	            },
	            {
	                "value": "Specific",
	                "icon": "🎯"
	            },
	            {
	                "value": "Longitudinal",
	                "icon": "📈"
	            },
	            {
	                "value": "Cross-cultural",
	                "icon": "🌍"
	            },
	            {
	                "value": "Theoretical",
	                "icon": "📚"
	            },
	            {
	                "value": "Empirical",
	                "icon": "📊"
	            },
	            {
	                "value": "Applied",
	                "icon": "🛠️"
	            }
	        ],
	        "maxlength": 200
	    },
	    "format": {
	        "label": __( 'Format', 'rank-math' ),
	        "placeholder": __( 'Select or Write desired output format', 'rank-math' ),
	        "options": [
	            "Summary",
	            "List",
	            "Outline"
	        ],
	        "maxlength": 200
	    },
	    "post_title": {
	        "label": __( 'Post Title', 'rank-math' ),
	        "placeholder": __( 'Enter your post title', 'rank-math' ),
	        "maxlength": 200
	    },
	    "seo_title": {
	        "label": __( 'SEO Title', 'rank-math' ),
	        "placeholder": __( 'Enter your SEO title', 'rank-math' ),
	        "maxlength": 200
	    },
	    "supporting_points": {
	        "label": __( 'Supporting Points', 'rank-math' ),
	        "placeholder": __( 'The supporting points you want to include in the paragraph', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 500
	    },
	    "original_paragraph": {
	        "label": __( 'Original Paragraph', 'rank-math' ),
	        "placeholder": __( 'Enter the paragraph you want to rephrase', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 1000
	    },
	    "sentence": {
	        "label": __( 'Sentence', 'rank-math' ),
	        "placeholder": __( 'Enter a short or incomplete sentence', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 200
	    },
	    "text": {
	        "label": __( 'Original Text', 'rank-math' ),
	        "placeholder": __( 'Enter the text to summarize', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 2000
	    },
	    "product_name": {
	        "label": __( 'Product Name', 'rank-math' ),
	        "placeholder": __( 'Enter the name of the product', 'rank-math' ),
	        "maxlength": "200"
	    },
	    "features_and_benefits": {
	        "label": __( 'Features and Benefits', 'rank-math' ),
	        "placeholder": __( 'Enter a list of features and benefits, separated by commas', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 600
	    },
	    "limitations_and_drawbacks": {
	        "label": __( 'Limitations and Drawbacks', 'rank-math' ),
	        "placeholder": __( '', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 600
	    },
	    "reply_brief": {
	        "label": __( 'Reply Brief', 'rank-math' ),
	        "placeholder": __( 'Enter a short summary of the required response', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "original_comment": {
	        "label": __( 'Original Comment', 'rank-math' ),
	        "placeholder": __( 'The original comment that requires a response', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 600
	    },
	    "personal_information": {
	        "label": __( 'Personal Information', 'rank-math' ),
	        "placeholder": __( 'Enter personal details, such as your name, age, occupation, etc.', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "purpose": {
	        "label": __( 'Purpose', 'rank-math' ),
	        "placeholder": __( 'What is the purpose of this bio?', 'rank-math' ),
	        "maxlength": 200
	    },
	    "personal_achievements": {
	        "label": __( 'Personal Achievements', 'rank-math' ),
	        "placeholder": __( 'Enter a list of your personal achievements, separated by commas', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "company_name": {
	        "label": __( 'Company Name', 'rank-math' ),
	        "placeholder": __( 'Enter the name of your company', 'rank-math' ),
	        "maxlength": 200
	    },
	    "company_information": {
	        "label": __( 'Company Information', 'rank-math' ),
	        "placeholder": __( 'Enter company details, such as the company name, location, and industry', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 500
	    },
	    "company_history": {
	        "label": __( 'Company History', 'rank-math' ),
	        "placeholder": __( 'Enter a brief history of the company', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 500
	    },
	    "team": {
	        "label": __( 'Team', 'rank-math' ),
	        "placeholder": __( 'Enter a brief description of the team', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 500
	    },
	    "job_title": {
	        "label": __( 'Job Title', 'rank-math' ),
	        "placeholder": __( 'Enter the job title.', 'rank-math' ),
	        "maxlength": 200
	    },
	    "requirements": {
	        "label": __( 'Requirements', 'rank-math' ),
	        "placeholder": __( 'Enter the key requirements for the position, separated by commas', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "responsibilities": {
	        "label": __( 'Responsibilities', 'rank-math' ),
	        "placeholder": __( 'Enter a list of responsibilities, separated by commas', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "comment": {
	        "label": __( 'Comment', 'rank-math' ),
	        "placeholder": __( 'The comment you want to reply to.', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 600
	    },
	    "hashtags": {
	        "label": __( 'Hashtags', 'rank-math' ),
	        "placeholder": __( 'Enter one or more hashtags, separated by commas', 'rank-math' ),
	        "maxlength": 200
	    },
	    "tweet": {
	        "label": __( 'Tweet', 'rank-math' ),
	        "placeholder": __( 'Enter the original tweet to reply to.', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "email_brief": {
	        "label": __( 'Email Brief', 'rank-math' ),
	        "placeholder": __( 'Enter a brief description of the email', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 500
	    },
	    "email": {
	        "label": __( 'Email', 'rank-math' ),
	        "placeholder": __( 'Enter the original email', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 1000
	    },
	    "product_description": {
	        "label": __( 'Product Description', 'rank-math' ),
	        "placeholder": __( 'Introduce your product here. Provide a detailed description of its features and benefits, highlighting what sets it apart from competitors and why it\'s the perfect solution for your target audience.', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 200
	    },
	    "visual_elements": {
	        "label": __( 'Visual Elements', 'rank-math' ),
	        "placeholder": __( 'Enter the visual elements you want to include in the video', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "key_points": {
	        "label": __( 'Key Points', 'rank-math' ),
	        "placeholder": __( 'Enter the main points you want to cover, separated by commas.', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": "400"
	    },
	    "host": {
	        "label": __( 'Host', 'rank-math' ),
	        "placeholder": __( 'Enter the name of the host of the podcast', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 200
	    },
	    "co_host": {
	        "label": __( 'Guest(s) or co-host', 'rank-math' ),
	        "placeholder": __( 'Enter the name(s) separated by comma', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 200
	    },
	    "cuisine": {
	        "label": __( 'Cuisine', 'rank-math' ),
	        "placeholder": __( 'e.g. Italian, Chinese, Mexican', 'rank-math' ),
	        "maxlength": 200
	    },
	    "type": {
	        "label": __( 'Type of Dish', 'rank-math' ),
	        "placeholder": __( 'e.g. soup, salad, casserole', 'rank-math' ),
	        "maxlength": 200
	    },
	    "ingredients": {
	        "label": __( 'Ingredients', 'rank-math' ),
	        "placeholder": __( 'Enter the ingredients needed for the recipe, separated by commas (e.g. flour, sugar, eggs, milk)', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 1000
	    },
	    "dietary_restrictions": {
	        "label": __( 'Dietary restrictions', 'rank-math' ),
	        "placeholder": __( 'List any dietary restrictions that the recipe should adhere to (e.g. gluten-free, vegan, low-carb)', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 400
	    },
	    "command": {
	        "label": __( 'Command', 'rank-math' ),
	        "placeholder": __( 'Enter your command', 'rank-math' ),
	        "type": "textarea",
	        "maxlength": 1000
	    },
	    "instructions": {
	        "label": __( 'Instructions', 'rank-math' ),
			"type": "textarea",
	        "placeholder": __( 'Enter instructions', 'rank-math' ),
	        "maxlength": 600
	    },
	    "document_title": {
	        "label": __( 'Document Title', 'rank-math' ),
	        "placeholder": __( 'Enter the document title', 'rank-math' ),
	        "maxlength": 200
	    }
	}

	return params[ key ]
}
