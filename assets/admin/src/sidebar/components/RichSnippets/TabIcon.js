/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { Dashicon } from '@wordpress/components'

const iconHash = {
	off: 'rm-icon rm-icon-misc',
	article: 'rm-icon rm-icon-post',
	book: 'rm-icon rm-icon-book',
	course: 'rm-icon rm-icon-course',
	event: 'rm-icon rm-icon-calendar',
	jobposting: 'rm-icon rm-icon-job',
	local: 'rm-icon rm-icon-local-seo',
	music: 'rm-icon rm-icon-music',
	product: 'rm-icon rm-icon-cart',
	recipe: 'rm-icon rm-icon-recipe',
	restaurant: 'rm-icon rm-icon-restaurant',
	video: 'rm-icon rm-icon-video',
	person: 'rm-icon rm-icon-users',
	review: 'rm-icon rm-icon-star',
	service: 'rm-icon rm-icon-service',
	software: 'rm-icon rm-icon-software',
}

const RichSnippetTabIcon = ( { type } ) => {
	const icon = get( iconHash, type, 'index-card' )
	return (
		<Fragment>
			<i className={ icon } title={ __( 'Schema', 'rank-math' ) }></i>
			<span>{ __( 'Schema', 'rank-math' ) }</span>
		</Fragment>
	)
}

export default withSelect( ( select ) => {
	return {
		type: select( 'rank-math' ).getRichSnippets().snippetType,
	}
} )( RichSnippetTabIcon )
