/**
 * External dependencies
 */
import { map, includes, toUpper } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { InspectorControls } from '@wordpress/block-editor'
import {
	PanelBody,
	SelectControl,
	CheckboxControl,
} from '@wordpress/components'

export default ( { attributes, setAttributes, excludeHeadings, setExcludeHeadings } ) => {
	return (
		<InspectorControls>
			<PanelBody title={ __( 'Settings', 'seo-by-rank-math' ) }>

				<SelectControl
					label={ __( 'Title Wrapper', 'seo-by-rank-math' ) }
					value={ attributes.titleWrapper }
					options={ [
						{ value: 'h2', label: __( 'H2', 'seo-by-rank-math' ) },
						{ value: 'h3', label: __( 'H3', 'seo-by-rank-math' ) },
						{ value: 'h4', label: __( 'H4', 'seo-by-rank-math' ) },
						{ value: 'h5', label: __( 'H5', 'seo-by-rank-math' ) },
						{ value: 'h6', label: __( 'H6', 'seo-by-rank-math' ) },
						{ value: 'p', label: __( 'P', 'seo-by-rank-math' ) },
						{ value: 'div', label: __( 'DIV', 'seo-by-rank-math' ) },
					] }
					onChange={ ( titleWrapper ) => {
						setAttributes( { titleWrapper } )
					} }
					__next40pxDefaultSize={ true }
					__nextHasNoMarginBottom={ true }
				/>

				<br />
				<h3>{ __( 'Exclude Headings', 'seo-by-rank-math' ) }</h3>
				<div className="rank-math-toc-exclude-headings">
					{
						map( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], ( value ) => {
							return (
								<CheckboxControl
									key={ value }
									label={ __( 'Heading ', 'seo-by-rank-math' ) + toUpper( value ) }
									checked={ includes( excludeHeadings, value ) }
									onChange={ ( newVlaue ) => setExcludeHeadings( value, newVlaue ) }
									__nextHasNoMarginBottom={ true }
								/>
							)
						} )
					}
				</div>
			</PanelBody>
		</InspectorControls>
	)
}
