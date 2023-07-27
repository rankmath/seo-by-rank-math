import { linearToNestedHeadingList } from './utils'
import { useBlockProps } from '@wordpress/block-editor'
import List from './list'

const blockAttr = {
	title: {
		type: 'text',
	},
	headings: {
		type: 'array',
		items: {
			type: 'object',
		},
	},
	listStyle: {
		type: 'text',
	},
	titleWrapper: {
		type: 'text',
		default: 'h2',
	},
	excludeHeadings: {
		type: 'array',
	},
}

const v1 = {
	attributes: blockAttr,
	save( { attributes } ) {
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
	},
}

const v2 = {
	attributes: blockAttr,
	save( { attributes } ) {
		if ( attributes.headings.length === 0 ) {
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
	},
}
export default [ v2, v1 ]
