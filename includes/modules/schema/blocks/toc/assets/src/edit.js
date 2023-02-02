/**
 * External dependencies
 */
import { isUndefined, map, includes, remove } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import {
	useBlockProps,
	RichText,
	store as blockEditorStore,
} from '@wordpress/block-editor'
import { useDispatch } from '@wordpress/data'
import { Placeholder } from '@wordpress/components'
import { useEffect, useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { GetLatestHeadings, linearToNestedHeadingList } from './utils'
import List from './list'
import InspectControls from './inspectControls'
import Toolbar from './toolbar'

export default ( {
	attributes,
	setAttributes,
} ) => {
	const blockProps = useBlockProps()

	// State to monitor edit heading links.
	const [ edit, toggleEdit ] = useState( false )
	const [ excludeHeading, toggleExcludeHeading ] = useState( {} )
	if ( ! attributes.listStyle ) {
		setAttributes( { listStyle: rankMath.listStyle } )
	}

	const ListStyle = attributes.listStyle
	const tocTitle = attributes.title ?? rankMath.tocTitle
	const excludeHeadings = ! isUndefined( attributes.excludeHeadings ) ? attributes.excludeHeadings : rankMath.tocExcludeHeadings

	// Function to hide certain heading.
	const hideHeading = ( value, key ) => {
		const headings = map( attributes.headings, ( heading ) => {
			if ( heading.key === key ) {
				heading.disable = value
			}

			return heading
		} )

		setAttributes( { headings } )
	}

	// Function to update Heading link.
	const onHeadingUpdate = ( value, key, isContent = false ) => {
		const headings = map( attributes.headings, ( heading ) => {
			if ( heading.key === key ) {
				if ( isContent ) {
					heading.content = value
					heading.isUpdated = true
				} else {
					heading.isGeneratedLink = false
					heading.link = value
				}
			}

			return heading
		} )

		setAttributes( { headings } )
	}

	const setExcludeHeadings = ( headingLevel ) => {
		if ( includes( excludeHeadings, headingLevel ) ) {
			remove( excludeHeadings, ( heading ) => {
				return heading === headingLevel
			} )
		} else {
			excludeHeadings.push( headingLevel )
		}
		setAttributes( { excludeHeadings } )
		toggleExcludeHeading( ! excludeHeading )
	}

	const { __unstableMarkNextChangeAsNotPersistent } = useDispatch( blockEditorStore )

	// Get Latest headings from the content.
	const latestHeadings = GetLatestHeadings( attributes.headings, excludeHeadings )
	useEffect( () => {
		if ( latestHeadings !== null ) {
			__unstableMarkNextChangeAsNotPersistent();
			setAttributes( { headings: latestHeadings } )
		}
	}, [ latestHeadings ] )

	const headingTree = linearToNestedHeadingList( attributes.headings )
	if ( isUndefined( attributes.headings ) || attributes.headings.length === 0 ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					label={ __( 'Table of Contents', 'rank-math' ) }
					instructions={ __( 'Add Heading blocks to this page to generate the Table of Contents.', 'rank-math' ) }
				/>
				<InspectControls attributes={ attributes } setAttributes={ setAttributes } excludeHeadings={ excludeHeadings } setExcludeHeadings={ setExcludeHeadings } />
			</div>
		)
	}

	return (
		<div { ...blockProps }>
			<RichText
				tagName={ attributes.titleWrapper }
				value={ tocTitle }
				onChange={ ( newTitle ) => {
					setAttributes( { title: newTitle } )
				} }
				placeholder={ __( 'Enter a title', 'rank-math' ) }
			/>
			<nav>
				<ListStyle>
					<List
						headings={ headingTree }
						onHeadingUpdate={ onHeadingUpdate }
						edit={ edit }
						toggleEdit={ toggleEdit }
						hideHeading={ hideHeading }
						ListStyle={ ListStyle }
					/>
				</ListStyle>
			</nav>
			<Toolbar setAttributes={ setAttributes } />
			<InspectControls attributes={ attributes } setAttributes={ setAttributes } excludeHeadings={ excludeHeadings } setExcludeHeadings={ setExcludeHeadings } />
		</div>
	)
}
