/**
 * External dependencies
 */
import { get, isEmpty, map } from 'lodash'

/**
 * Internal dependencies
 */
import decodeEntities from '@helpers/decodeEntities'

const serpData = rankMath.assessor.serpData
const hasRedirection = rankMath.assessor.hasRedirection
const richSnippets = get( rankMath, 'richSnippets', {} )
richSnippets.snippetType = get( richSnippets, 'snippetType', 'off' )
richSnippets.snippetType = isEmpty( richSnippets.snippetType )
	? 'off'
	: richSnippets.snippetType

map( richSnippets, ( value, key ) => {
	richSnippets[ key ] = decodeEntities( value )
} )

const DEFAULT_STATE = {
	postID: null,

	// General Tab.
	title: serpData.title ? serpData.title : serpData.titleTemplate,
	description: serpData.description,
	keywords: serpData.focusKeywords,
	pillarContent: serpData.pillarContent,
	featuredImage: '',
	permalink: false,
	primaryTerm: serpData.primaryTerm,

	// Advanced Tab.
	robots: serpData.robots,
	advancedRobots: serpData.advancedRobots,
	canonicalUrl: serpData.canonicalUrl,
	breadcrumbTitle: serpData.breadcrumbTitle,
	showScoreFrontend: serpData.showScoreFrontend,
	redirectionID: hasRedirection
		? get( rankMath.assessor, 'redirection.id', '' )
		: '',
	redirectionType: hasRedirection
		? get( rankMath.assessor, 'redirection.header_code', '' )
		: '',
	redirectionUrl: hasRedirection
		? get( rankMath.assessor, 'redirection.url_to', '' )
		: '',

	// Social - Facebook.
	facebookTitle: serpData.facebookTitle,
	facebookImage: serpData.facebookImage,
	facebookImageID: serpData.facebookImageID,
	facebookAuthor: serpData.facebookAuthor,
	facebookDescription: serpData.facebookDescription,
	facebookHasOverlay: serpData.facebookHasOverlay,
	facebookImageOverlay: serpData.facebookImageOverlay,

	// Social - Twitter.
	twitterTitle: serpData.twitterTitle,
	twitterImage: serpData.twitterImage,
	twitterAuthor: serpData.twitterAuthor,
	twitterImageID: serpData.twitterImageID,
	twitterCardType: serpData.twitterCardType,
	twitterUseFacebook: serpData.twitterUseFacebook,
	twitterDescription: serpData.twitterDescription,
	twitterHasOverlay: serpData.twitterHasOverlay,
	twitterImageOverlay: serpData.twitterImageOverlay,

	// Twitter - Player.
	twitterPlayerUrl: serpData.twitterPlayerUrl,
	twitterPlayerSize: serpData.twitterPlayerSize,
	twitterPlayerStream: serpData.twitterPlayerStream,
	twitterPlayerStreamCtype: serpData.twitterPlayerStreamCtype,

	// Twitter - App.
	twitterAppDescription: serpData.twitterAppDescription,
	twitterAppIphoneName: serpData.twitterAppIphoneName,
	twitterAppIphoneID: serpData.twitterAppIphoneID,
	twitterAppIphoneUrl: serpData.twitterAppIphoneUrl,
	twitterAppIpadName: serpData.twitterAppIpadName,
	twitterAppIpadID: serpData.twitterAppIpadID,
	twitterAppIpadUrl: serpData.twitterAppIpadUrl,
	twitterAppGoogleplayName: serpData.twitterAppGoogleplayName,
	twitterAppGoogleplayID: serpData.twitterAppGoogleplayID,
	twitterAppGoogleplayUrl: serpData.twitterAppGoogleplayUrl,
	twitterAppCountry: serpData.twitterAppCountry,

	richSnippets,

	// Misc.
	score: 0,
	dirtyMetadata: {},
}

/**
 * Reduces the dispatched action for the app state.
 *
 * @param {Object} state  The current state.
 * @param {Object} action The action that was just dispatched.
 *
 * @return {Object} The new state.
 */
export function appData( state = DEFAULT_STATE, action ) {
	const dirtyMetadata = {
		...state.dirtyMetadata,
	}

	if ( false !== action.metaKey ) {
		dirtyMetadata[ action.metaKey ] = action.metaValue
	}

	if ( 'RANK_MATH_APP_DATA' === action.type ) {
		if ( 'dirtyMetadata' === action.key ) {
			return {
				...state,
				dirtyMetadata: action.value,
			}
		}

		return {
			...state,
			[ action.key ]: action.value,
			dirtyMetadata,
		}
	}

	return state
}
