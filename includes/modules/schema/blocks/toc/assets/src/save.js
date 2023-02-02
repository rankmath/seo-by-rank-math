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
	if ( attributes.headings.length === 0 ) {
		return null
	}

	const TitleWrapper = attributes.titleWrapper
	const headings = linearToNestedHeadingList( attributes.headings )
	const ListStyle = attributes.listStyle

	return (
		<div { ...useBlockProps.save() }>
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
