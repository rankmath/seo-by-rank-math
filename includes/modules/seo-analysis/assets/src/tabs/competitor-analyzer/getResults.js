export default () => ( {
	metrices: {
		percent: 85,
		total: 29,
		statuses: {
			ok: 20,
			warning: 2,
			fail: 7,
		},
	},
	date: {
		date: 'October 17, 2024',
		time: '8:01 am',
	},
	serpData: {
		favicon:
			'https://t0.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https%3A%2F%2Fwww.rankmath.com%2F&size=128',
		url: 'https://www.rankmath.com/',
		title: 'Rank Math - Best Free WordPress SEO Tools in 2025',
		description:
			'Rank Math WordPress SEO plugin will help you rank higher in search engines. DOWNLOAD for FREE this plugin today to optimize WordPress website for higher ra...',
	},
	results: {
		basic: [
			{
				status: 'info',
				test_id: 'common_keywords',
				title: 'Common Keywords',
				tooltip:
					'A list of keywords that appear frequently in the text of the content.',
				kb_link: 'https://rankmath.com/kb/seo-analysis/#common-keywords-test',
				message: 'Here are the most common keywords we found on the page:',
				data: {
					rank: 31,
					math: 29,
					wordpress: 15,
					plugin: 10,
					best: 9,
					search: 8,
					google: 7,
					support: 7,
					features: 6,
					free: 6,
				},
			},
			{
				status: 'fail',
				test_id: 'description_length',
				title: 'SEO Description',
				tooltip: "SEO analysis of page's meta-description.",
				kb_link: 'https://rankmath.com/kb/seo-analysis/#seo-description-test',
				message:
					'The description of page has 184 characters. Most search engines will truncate meta description to 160 characters.',
				fix: '<p>Write a meta-description for page. Use target keyword(s) (in a natural way) and write with human readers in mind. Summarize the content - describe the topics article discusses.</p><p>The description should stimulate reader interest and get them to click on the article. Think of it as a mini-advert for content.</p>',
				data: [
					'Rank Math WordPress SEO plugin will help you rank higher in search engines. DOWNLOAD for FREE this plugin today to optimize WordPress website for higher rankings and more traffic.',
				],
			},
			{
				status: 'ok',
				test_id: 'h1_heading',
				title: 'H1 Heading',
				tooltip: 'SEO Analysis of the H1 Tags on the page.',
				kb_link: 'https://rankmath.com/kb/seo-analysis/#h1-heading-test',
				message: 'One H1 tag was found on the page.',
				data: [ 'WordPress SEO Made Easy' ],
			},
			{
				status: 'ok',
				test_id: 'h2_headings',
				title: 'H2 Headings',
				tooltip: 'SEO analysis of the H2 headings on your page.',
				kb_link: 'https://rankmath.com/kb/seo-analysis/#h2-headings-test',
				message: 'One or more H2 tags were found on the page.',
				data: [
					'Powering SEO optimization for businesses around the world',
					'What is Rank Math?',
					'Recommended By the Best SEOs On The Planet',
					'What you can do with Rank Math',
					'Take The Guesswork Out of WordPress SEO',
					'Your all-in-one solution for all the SEO needs',
					'Leading SEOs are Loving Rank Math!',
				],
			},
			{
				status: 'ok',
				test_id: 'img_alt',
				title: 'Image ALT Attributes',
				tooltip: 'SEO analysis of the "alt" attribute for image tags.',
				kb_link:
					'https://rankmath.com/kb/seo-analysis/#image-alt-attributes-test',
				message: 'All images on the page have alt attributes.',
			},
			{
				status: 'ok',
				test_id: 'keywords_meta',
				title: 'Keywords in Title & Description',
				tooltip:
					"SEO analysis of the HTML page's Title and meta description content.",
				kb_link:
					'https://rankmath.com/kb/seo-analysis/#keywords-in-title-and-description-test',
				message:
					'One or more common keywords were found in the title and description of the page.',
				data: {
					title: [ 'rank', 'math', 'wordpress', 'best', 'free' ],
					description: [
						'rank',
						'math',
						'wordpress',
						'plugin',
						'search',
						'free',
					],
				},
			},
		],
	},
} )
