/**
 * External dependencies
 */
import jQuery from 'jquery'
import { map, includes, startCase, isEmpty, isArray, isString, isUndefined, isObject } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import ContentAiText from '../components/ContentAiText'
import insertContent from '../helpers/insertContent'
import CopyButton from '../components/CopyButton'
import generateId from '@helpers/generateId'

/**
 * Convert content to FAQ block.
 *
 * @param {Array}    value    Content.
 * @param {endpoint} endpoint Selected endpoint.
 *
 * @return {string} Formatted content in the form of FAQ block.
 */
const getFormattedContent = ( value, endpoint ) => {
	if ( 'Frequently_Asked_Questions' !== endpoint ) {
		return value
	}

	let data = '<div class="wp-block-rank-math-faq-block">'
	const questions = []
	questions.questions = map( value, ( content ) => {
		data += '<div class="rank-math-faq-item"><h3 class="rank-math-question">' + content.question + '</h3><div class="rank-math-answer">' + content.answer + '</div></div>'
		return {
			id: generateId( 'faq-question' ),
			title: content.question,
			content: content.answer,
			visible: true,
		}
	} )

	data += '</div>'

	return '<!-- wp:rank-math/faq-block {"questions":' + JSON.stringify( questions.questions ) + '} -->' + data + '<!-- /wp:rank-math/faq-block -->'
}

/**
 * Copy Button component.
 *
 * @param {Object}  props               Component props.
 * @param {string}  props.value         Content returned from API.
 * @param {string}  props.index         Index key.
 * @param {string}  props.isPage        Is Content AI Page.
 * @param {string}  props.endpoint      Current endpoint.
 * @param {boolean} props.typingEffect  Whether to add text using typing effect.
 * @param {boolean} props.isSerpPreview Whether the request is for the SERP Preview.
 */
export default ( { value, index = 0, isPage = false, endpoint, typingEffect = true, isSerpPreview = false } ) => {
	const content = getFormattedContent( value, endpoint )
	let aiText = value
	if ( isArray( value ) ) {
		aiText = ''
		map( value, ( val ) => {
			aiText += '<h2>' + val.question + '</h2><span>' + val.answer + '</span>'
		} )
	}

	if ( isObject( value ) && ! isArray( value ) ) {
		aiText = ''
		map( value, ( val, key ) => {
			aiText += '<h4>' + startCase( key ) + '</h4><span>' + val + '</span>'
		} )
	}

	return (
		<div className="output-item" key={ index }>
			<div className="output-actions">
				<CopyButton value={ isString( content ) ? content : aiText } />

				{
					( isUndefined( rankMath.currentEditor ) || includes( [ 'gutenberg', 'classic', 'elementor' ], rankMath.currentEditor ) || isSerpPreview ) &&
					! isPage &&
					<Button
						variant="secondary"
						className="button structured-data-test is-small"
						onClick={ () => {
							let inserted = false
							if ( ! isPage ) {
								if (
									endpoint === 'SEO_Title' ||
									( endpoint === 'SEO_Meta' && ! isEmpty( value.title ) )
								) {
									const title = ! isUndefined( value.title ) ? value.title : value
									dispatch( 'rank-math' ).updateSerpTitle( title )
									dispatch( 'rank-math' ).updateTitle( title )
									inserted = true
								}

								if (
									endpoint === 'SEO_Description' ||
									( endpoint === 'SEO_Meta' && ! isEmpty( value.description ) )
								) {
									const description = ! isUndefined( value.description ) ? value.description : value
									dispatch( 'rank-math' ).updateSerpDescription( description )
									dispatch( 'rank-math' ).updateDescription( description )
									inserted = true
								}

								if ( endpoint === 'Opengraph' ) {
									const isTwitter = 'twitter' === wp.data.select( 'rank-math' ).getSocialTab()
									if ( ! isEmpty( value.title ) ) {
										if ( isTwitter ) {
											dispatch( 'rank-math' ).updateTwitterTitle( value.title )
										} else {
											dispatch( 'rank-math' ).updateFacebookTitle( value.title )
										}

										inserted = true
									}

									if ( ! isEmpty( value.description ) ) {
										if ( isTwitter ) {
											dispatch( 'rank-math' ).updateTwitterDescription( value.description )
										} else {
											dispatch( 'rank-math' ).updateFacebookDescription( value.description )
										}
										inserted = true
									}
								}
							}

							if ( ! inserted ) {
								insertContent( value, endpoint )
							} else {
								jQuery( '.rank-math-contentai-modal-overlay .components-modal__header button' ).trigger( 'click' )
							}
						} }
					>
						<i className="rm-icon rm-icon-plus"></i>
						<span>{ __( 'Insert', 'rank-math' ) }</span>
					</Button>
				}
			</div>
			{ <ContentAiText value={ aiText } addTypingEffect={ typingEffect } /> }
		</div>
	)
}
