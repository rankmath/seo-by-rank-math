/**
 * External dependencies
 */
import { reject, mapValues } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesPostTypes from '../../../helpers/choicesPostTypes'
import BuyContentAICredits from './BuyContentAICredits'
import ConnectRankMath from './ConnectRankMath'

const {
	credits,
	countries,
	isSiteConnected,
	defaultLanguage,
} = rankMath

const postTypes = reject( choicesPostTypes, { id: 'attachment' } )

const contentAIFields = [
	{
		id: 'content_ai_country',
		type: 'select',
		name: __( 'Default Country', 'rank-math' ),
		desc: __(
			'Content AI tailors keyword research to the target country for highly relevant suggestions. You can override this in individual posts/pages/CPTs.',
			'rank-math'
		),
		options: countries,
		default: 'all',
	},
	{
		id: 'content_ai_tone',
		type: 'selectSearch',
		name: __( 'Default Tone', 'rank-math' ),
		desc: __(
			'This feature enables the default primary tone or writing style that characterizes your content. You can override this in individual tools.',
			'rank-math'
		),
		options: {
			Analytical: __( 'Analytical', 'rank-math' ),
			Argumentative: __( 'Argumentative', 'rank-math' ),
			Casual: __( 'Casual', 'rank-math' ),
			Conversational: __( 'Conversational', 'rank-math' ),
			Creative: __( 'Creative', 'rank-math' ),
			Descriptive: __( 'Descriptive', 'rank-math' ),
			Emotional: __( 'Emotional', 'rank-math' ),
			Empathetic: __( 'Empathetic', 'rank-math' ),
			Expository: __( 'Expository', 'rank-math' ),
			Factual: __( 'Factual', 'rank-math' ),
			Formal: __( 'Formal', 'rank-math' ),
			Friendly: __( 'Friendly', 'rank-math' ),
			Humorous: __( 'Humorous', 'rank-math' ),
			Informal: __( 'Informal', 'rank-math' ),
			Journalese: __( 'Journalese', 'rank-math' ),
			Narrative: __( 'Narrative', 'rank-math' ),
			Objective: __( 'Objective', 'rank-math' ),
			Opinionated: __( 'Opinionated', 'rank-math' ),
			Persuasive: __( 'Persuasive', 'rank-math' ),
			Poetic: __( 'Poetic', 'rank-math' ),
			Satirical: __( 'Satirical', 'rank-math' ),
			'Story-telling': __( 'Story-telling', 'rank-math' ),
			Subjective: __( 'Subjective', 'rank-math' ),
			Technical: __( 'Technical', 'rank-math' ),
		},
		default: 'Formal',
	},
	{
		id: 'content_ai_audience',
		type: 'selectSearch',
		name: __( 'Default Audience', 'rank-math' ),
		desc: __(
			'This option lets you set the default audience that usually reads your content. You can override this in individual tools.',
			'rank-math'
		),
		options: {
			Activists: __( 'Activists', 'rank-math' ),
			Artists: __( 'Artists', 'rank-math' ),
			Authors: __( 'Authors', 'rank-math' ),
			'Bargain Hunters': __( 'Bargain Hunters', 'rank-math' ),
			Bloggers: __( 'Bloggers', 'rank-math' ),
			'Business Owners': __( 'Business Owners', 'rank-math' ),
			Collectors: __( 'Collectors', 'rank-math' ),
			Cooks: __( 'Cooks', 'rank-math' ),
			Crafters: __( 'Crafters', 'rank-math' ),
			Dancers: __( 'Dancers', 'rank-math' ),
			DIYers: __( 'DIYers', 'rank-math' ),
			Designers: __( 'Designers', 'rank-math' ),
			Educators: __( 'Educators', 'rank-math' ),
			Engineers: __( 'Engineers', 'rank-math' ),
			Entrepreneurs: __( 'Entrepreneurs', 'rank-math' ),
			Environmentalists: __( 'Environmentalists', 'rank-math' ),
			Fashionistas: __( 'Fashionistas', 'rank-math' ),
			'Fitness Enthusiasts': __( 'Fitness Enthusiasts', 'rank-math' ),
			Foodies: __( 'Foodies', 'rank-math' ),
			'Gaming Enthusiasts': __( 'Gaming Enthusiasts', 'rank-math' ),
			Gardeners: __( 'Gardeners', 'rank-math' ),
			'General Audience': __( 'General Audience', 'rank-math' ),
			'Health Enthusiasts': __( 'Health Enthusiasts', 'rank-math' ),
			'Healthcare Professionals': __( 'Healthcare Professionals', 'rank-math' ),
			'Indoor Hobbyists': __( 'Indoor Hobbyists', 'rank-math' ),
			Investors: __( 'Investors', 'rank-math' ),
			'Job Seekers': __( 'Job Seekers', 'rank-math' ),
			'Movie Buffs': __( 'Movie Buffs', 'rank-math' ),
			Musicians: __( 'Musicians', 'rank-math' ),
			'Outdoor Enthusiasts': __( 'Outdoor Enthusiasts', 'rank-math' ),
			Parents: __( 'Parents', 'rank-math' ),
			'Pet Owners': __( 'Pet Owners', 'rank-math' ),
			Photographers: __( 'Photographers', 'rank-math' ),
			'Podcast Listeners': __( 'Podcast Listeners', 'rank-math' ),
			Professionals: __( 'Professionals', 'rank-math' ),
			Retirees: __( 'Retirees', 'rank-math' ),
			Russian: __( 'Russian', 'rank-math' ),
			Seniors: __( 'Seniors', 'rank-math' ),
			'Social Media Users': __( 'Social Media Users', 'rank-math' ),
			'Sports Fans': __( 'Sports Fans', 'rank-math' ),
			Students: __( 'Students', 'rank-math' ),
			'Tech Enthusiasts': __( 'Tech Enthusiasts', 'rank-math' ),
			Travelers: __( 'Travelers', 'rank-math' ),
			'TV Enthusiasts': __( 'TV Enthusiasts', 'rank-math' ),
			'Video Creators': __( 'Video Creators', 'rank-math' ),
			Writers: __( 'Writers', 'rank-math' ),
		},
		default: 'General Audience',
	},
	{
		id: 'content_ai_language',
		type: 'selectSearch',
		name: __( 'Default Language', 'rank-math' ),
		desc: __(
			'This option lets you set the default language for content generated using Content AI. You can override this in individual tools.',
			'rank-math'
		),
		options: {
			'US English': __( 'US English', 'rank-math' ),
			'UK English': __( 'UK English', 'rank-math' ),
			Arabic: __( 'Arabic', 'rank-math' ),
			Bulgarian: __( 'Bulgarian', 'rank-math' ),
			Chinese: __( 'Chinese', 'rank-math' ),
			Czech: __( 'Czech', 'rank-math' ),
			Danish: __( 'Danish', 'rank-math' ),
			Dutch: __( 'Dutch', 'rank-math' ),
			Estonian: __( 'Estonian', 'rank-math' ),
			Finnish: __( 'Finnish', 'rank-math' ),
			French: __( 'French', 'rank-math' ),
			German: __( 'German', 'rank-math' ),
			Greek: __( 'Greek', 'rank-math' ),
			Hebrew: __( 'Hebrew', 'rank-math' ),
			Hungarian: __( 'Hungarian', 'rank-math' ),
			Indonesian: __( 'Indonesian', 'rank-math' ),
			Italian: __( 'Italian', 'rank-math' ),
			Japanese: __( 'Japanese', 'rank-math' ),
			Korean: __( 'Korean', 'rank-math' ),
			Latvian: __( 'Latvian', 'rank-math' ),
			Lithuanian: __( 'Lithuanian', 'rank-math' ),
			Norwegian: __( 'Norwegian', 'rank-math' ),
			Polish: __( 'Polish', 'rank-math' ),
			Portuguese: __( 'Portuguese', 'rank-math' ),
			Romanian: __( 'Romanian', 'rank-math' ),
			Russian: __( 'Russian', 'rank-math' ),
			Slovak: __( 'Slovak', 'rank-math' ),
			Slovenian: __( 'Slovenian', 'rank-math' ),
			Spanish: __( 'Spanish', 'rank-math' ),
			Swedish: __( 'Swedish', 'rank-math' ),
			Turkish: __( 'Turkish', 'rank-math' ),
		},
		default: defaultLanguage,
	},
	{
		id: 'content_ai_post_types',
		type: 'checkboxlist',
		name: __( 'Select Post Type', 'rank-math' ),
		desc: __(
			'Choose the type of posts/pages/CPTs where you want to use Content AI.',
			'rank-math'
		),
		options: postTypes,
		toggleAll: true,
		default: mapValues( postTypes, ( postType ) => postType.id ),
	},
	...( false !== credits
		? [
			{
				id: 'content_ai_credits',
				type: 'component',
				Component: BuyContentAICredits,
			},
		]
		: []
	),
]

const contentAISettings = [
	{
		id: 'rank_math_content_ai_settings',
		type: 'component',
		Component: ConnectRankMath,
	},
]

export default isSiteConnected ? contentAIFields : contentAISettings
