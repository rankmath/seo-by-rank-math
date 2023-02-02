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
			<PanelBody title={ __( 'Settings', 'rank-math' ) }>

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

				<br />
				<h3>{ __( 'Exclude Headings', 'rank-math' ) }</h3>
				<div className="rank-math-toc-exclude-headings">
					{
						map( [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], ( value ) => {
							return (
								<CheckboxControl
									key={ value }
									label={ __( 'Heading ', 'rank-math' ) + toUpper( value ) }
									checked={ includes( excludeHeadings, value ) }
									onChange={ ( newVlaue ) => setExcludeHeadings( value, newVlaue ) }
								/>
							)
						} )
					}
				</div>
			</PanelBody>
		</InspectorControls>
	)
}
