/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import { linearToNestedHeadingList } from './utils'
import List from './list'

export default function save( { attributes } ) {
	if ( isUndefined( attributes.headings ) || attributes.headings.length === 0 ) {
		return null
	}

	const TitleWrapper = attributes.titleWrapper
	const headings = linearToNestedHeadingList( attributes.headings )
	const ListStyle = attributes.listStyle

	return (
		<div { ...useBlockProps.save() } id="rank-math-toc">
			{ attributes.title && <TitleWrapper dangerouslySetInnerHTML={ { __html: attributes.title } }></TitleWrapper> }
			<nav>
				<ListStyle>
					<List
						headings={ headings }
						ListStyle={ ListStyle }
						isSave={ true }
					/>
				</ListStyle>
			</nav>
		</div>
	)
}
