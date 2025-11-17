/**
 * External dependencies
 */
import { has, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Analysis from '@root/analyzer/Analysis'
import AnalysisResult from '@root/analyzer/AnalysisResult'

class ContentHasAssets extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Add a few images and/or videos to make your content appealing.', 'rank-math' ) )
			.setTooltip( __( 'Content with images and/or video feels more inviting to users. It also helps supplement your textual content.', 'rank-math' ) )
	}

	/**
	 * Executes the assessment and return its result
	 *
	 * @param {Paper}      paper      The paper to run this assessment on.
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {AnalysisResult} an AnalysisResult with the score and the formatted text.
	 */
	getResult( paper, researcher ) {
		const analysisResult = this.newResult()

		if ( ! paper.hasText() ) {
			if ( paper.hasThumbnail() ) {
				analysisResult
					.setScore( 1 )
					.setText( this.translateScore( analysisResult ) )
			}

			return analysisResult
		}

		analysisResult
			.setScore(
				this.calculateScore( paper )
			)
			.setText( this.translateScore( analysisResult ) )

		return analysisResult
	}

	/**
	 * Checks whether paper meet analysis requirements.
	 *
	 * @param {Paper} paper The paper to use for the assessment.
	 *
	 * @return {boolean} True when requirements meet.
	 */
	isApplicable( paper ) {
		return paper.hasText() || paper.hasThumbnail()
	}

	/**
	 * Calculates the score.
	 *
	 * @param {Paper} paper The paper to run this assessment on.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( paper ) {
		let score = 0

		score += this.calculateImagesScore( this.getImages( paper ) )
		score += this.calculateVideosScore( this.getVideos( paper.getText() ) )

		return Math.min( this.getScore(), score )
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_contentHasAssets_score', 6 )
	}

	/**
	 * Calculates the images score.
	 *
	 * @param {number} images Total number of images.
	 *
	 * @return {number} The calculated score.
	 */
	calculateImagesScore( images ) {
		const scorehash = {
			0: 0,
			1: 1,
			2: 2,
			3: 4,
		}

		if ( has( scorehash, images ) ) {
			return scorehash[ images ]
		}

		return 6
	}

	/**
	 * Calculates the videos score.
	 *
	 * @param {number} videos Total number of videos.
	 *
	 * @return {number} The calculated score.
	 */
	calculateVideosScore( videos ) {
		const scorehash = {
			0: 0,
			1: 1,
		}

		if ( has( scorehash, videos ) ) {
			return scorehash[ videos ]
		}

		return 2
	}

	/**
	 * Translates the score to a message the user can understand.
	 *
	 * @param {AnalysisResult} analysisResult AnalysisResult with the score and the formatted text.
	 *
	 * @return {string} The translated string.
	 */
	translateScore( analysisResult ) {
		return analysisResult.hasScore() ?
			__( 'Your content contains images and/or video(s).', 'rank-math' ) :
			__( 'You are not using rich media like images or videos.', 'rank-math' )
	}

	/**
	 * Get all the images.
	 *
	 * @param {Paper}  paper The paper to run this assessment on.
	 * @param {string} text  The text.
	 *
	 * @return {number} Count of found images.
	 */
	getImages( paper, text = null ) {
		text = ! isNull( text ) ? text : paper.getText()
		const images = [].concat(
			this.match( text, '<img(?:[^>]+)?>' ),
			this.match( text, '\\[gallery( [^\\]]+?)?\\]' )
		)

		if ( paper.hasThumbnail() ) {
			images.push( paper.getThumbnail() )
		}

		return images.length
	}

	/**
	 * Has video URL
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {Array} The video URL matches from the text.
	 */
	hasVideoUrl( text ) {
		return this.match( text, /(http:\/\/|https:\/\/|)(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/ )
	}
	
	/**
	 * Get videos from the iFrames.
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {Object} The videos count and the updated string.
	 */
	getVideosFromIframe( text ) {
		const videos = this.match( text, "<iframe(?:[^>]+)?>" ).filter( content => {
			if ( this.hasVideoUrl( content ) ) {
				text = text.replace( content, '' )
				return true
			}
			return false
		} )
	
		return {
			count: videos.length,
			text,
		}
	}
	
	/**
	 * Get videos from the video tag.
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {Object} The videos count and the updated string.
	 */
	getVideosFromVideoTag( text ) {
		const videos = this.match( text, "<video(?:[^>]+)?>" ).filter( videoContent => {
			if ( this.hasVideoUrl( videoContent ) ) {
				text = text.replace( videoContent, '' )
				return true
			}
			return false
		} )
	
		return {
			count: videos.length,
			text,
		}
	}

	/**
	 * Get videos from the Shortcode
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {Object} The videos count and the updated string.
	 */
	getVideosFromShortcodes( text ) {
		const videos = this.match( text, "\\[video( [^\\]]+?)?\\]" ).filter( videoContent => {
			if ( this.hasVideoUrl( videoContent ) ) {
				text = text.replace( videoContent, '' )
				return true
			}
			return false
		} )
	
		return {
			count: videos.length,
			text,
		}
	}

	/**
	 * Get videos by URL.
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {Object} The videos count and the updated string.
	 */
	getVideosByURL( text ) {
		const videos = this.hasVideoUrl( text )

		return {
			count: videos.length,
			text,
		}
	}

	/**
	 * Get all the videos.
	 *
	 * @param {string} text The text to use for the assessment.
	 *
	 * @return {number} Count of found videos.
	 */
	getVideos( text ) {
		let count = 0

		// Get video count from the <iframe /> tags.
		const iFrameVideos = this.getVideosFromIframe( text )
		count += parseInt( iFrameVideos.count )
		text = iFrameVideos.text
	
		// Get video count from the <video /> tags.
		const tagVideos = this.getVideosFromVideoTag( text )
		count += parseInt( tagVideos.count )
		text = tagVideos.text

		// Get video count from the [video] shortcode.
		const shortcodeVideos = this.getVideosFromShortcodes( text )
		count += parseInt( shortcodeVideos.count )
		text = shortcodeVideos.text

		// Finally get video count from the URLs.
		const videoURLs = this.getVideosByURL( text )
		count += parseInt( videoURLs.count )
		text = videoURLs.text

		return count
	}

	/**
	 * Match the assets.
	 *
	 * @param {string} text        The text to use for the assessment.
	 * @param {string} regexString The regex to test the text against.
	 *
	 * @return {Array} The matched set.
	 */
	match( text, regexString ) {
		const regex = new RegExp( regexString, 'ig' )
		const matches = text.match( regex )

		return null === matches ? [] : matches
	}
}

export default ContentHasAssets
