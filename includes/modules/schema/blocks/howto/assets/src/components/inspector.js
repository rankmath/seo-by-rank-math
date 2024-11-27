/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { InspectorControls } from '@wordpress/block-editor'
import { PanelBody, SelectControl, TextControl } from '@wordpress/components'

/**
 * Format array of image sizes.
 *
 * @param  {Array} imageSizes Array of image sizes.
 * @return {Array} Formatted array.
 */
const getImageSizeOptions = ( imageSizes ) => {
	return map( imageSizes, ( { name, slug } ) => ( {
		value: slug,
		label: name,
	} ) )
}

/**
 * Adds controls to the editor sidebar to control params.
 *
 * @param {Object} props This component's props.
 */
const Inspector = ( { imageSizes, attributes, setAttributes } ) => {
	const imageSizeOptions = getImageSizeOptions( imageSizes )

	return (
		<InspectorControls key={ 'inspector' }>
			<PanelBody title={ __( 'HowTo Options', 'rank-math' ) }>
				<SelectControl
					label={ __( 'List Style', 'rank-math' ) }
					value={ attributes.listStyle }
					options={ [
						{
							value: '',
							label: __( 'None', 'rank-math' ),
						},
						{
							value: 'numbered',
							label: __( 'Numbered', 'rank-math' ),
						},
						{
							value: 'unordered',
							label: __( 'Unordered', 'rank-math' ),
						},
					] }
					onChange={ ( listStyle ) => {
						setAttributes( { listStyle } )
					} }
				/>

				<SelectControl
					label={ __( 'Title Wrapper', 'rank-math' ) }
					value={ attributes.titleWrapper }
					options={ [
						{ value: 'h2', label: __( 'H2', 'rank-math' ) },
						{ value: 'h3', label: __( 'H3', 'rank-math' ) },
						{ value: 'h4', label: __( 'H4', 'rank-math' ) },
						{ value: 'h5', label: __( 'H5', 'rank-math' ) },
						{ value: 'h6', label: __( 'H6', 'rank-math' ) },
						{ value: 'p', label: __( 'P', 'rank-math' ) },
						{ value: 'div', label: __( 'DIV', 'rank-math' ) },
					] }
					onChange={ ( titleWrapper ) => {
						setAttributes( { titleWrapper } )
					} }
				/>

				<SelectControl
					label={ __( 'Main Image Size', 'rank-math' ) }
					value={ attributes.mainSizeSlug }
					options={ imageSizeOptions }
					onChange={ ( mainSizeSlug ) => {
						setAttributes( { mainSizeSlug } )
					} }
				/>

				<SelectControl
					label={ __( 'Image Size', 'rank-math' ) }
					value={ attributes.sizeSlug }
					options={ imageSizeOptions }
					onChange={ ( sizeSlug ) => {
						setAttributes( { sizeSlug } )
					} }
				/>
			</PanelBody>

			<PanelBody title={ __( 'Styling Options', 'rank-math' ) }>
				<TextControl
					label={ __(
						'Step Title Wrapper CSS Class(es)',
						'rank-math'
					) }
					value={ attributes.titleCssClasses }
					onChange={ ( titleCssClasses ) => {
						setAttributes( { titleCssClasses } )
					} }
				/>

				<TextControl
					label={ __(
						'Step Content Wrapper CSS Class(es)',
						'rank-math'
					) }
					value={ attributes.contentCssClasses }
					onChange={ ( contentCssClasses ) => {
						setAttributes( { contentCssClasses } )
					} }
				/>

				<TextControl
					label={ __( 'Step List CSS Class(es)', 'rank-math' ) }
					value={ attributes.listCssClasses }
					onChange={ ( listCssClasses ) => {
						setAttributes( { listCssClasses } )
					} }
				/>
			</PanelBody>
		</InspectorControls>
	)
}

export default withSelect( ( select, props ) => {
	const { getSettings } = select( 'core/block-editor' )
	const { imageSizes } = getSettings()

	return {
		...props,
		imageSizes,
	}
} )( Inspector )
