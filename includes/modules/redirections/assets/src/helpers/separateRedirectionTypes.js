/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Visually separate 410 & 451 from the remaining redirection types since these are different from the other redirections.
 */
export default () => {
	const headerCodeField = document.querySelector( '.field-id-header_code' )
	if ( ! headerCodeField ) {
		return
	}

	const headerCodeToggleGroup = headerCodeField.querySelector( '.rank-math-toggle-group-control' )
	if ( ! headerCodeToggleGroup ) {
		return
	}

	const createDiv = ( className ) => {
		const div = document.createElement( 'div' )
		div.className = className
		return div
	}

	// Maintenance field head
	const th = createDiv( 'field-th' )
	const label = document.createElement( 'label' )
	label.textContent = __( 'Maintenance Code', 'rank-math' )
	th.appendChild( label )

	// Maintenance field data
	const td = createDiv( 'field-td' )
	const baseContainer = createDiv( 'components-base-control' )
	const baseFieldContainer = createDiv( 'components-base-control__field' )
	const maintenanceToggleGroup = createDiv(
		'components-toggle-group-control rank-math-toggle-group-control css-ml4wxx e19lxcc00'
	)
	maintenanceToggleGroup.append(
		headerCodeToggleGroup.children[ 3 ],
		headerCodeToggleGroup.children[ 4 ]
	)
	baseFieldContainer.appendChild( maintenanceToggleGroup )
	baseContainer.appendChild( baseFieldContainer )
	td.appendChild( baseContainer )

	// Render maintenance field after header_code field
	const maintenanceField = createDiv(
		'field-row field-type-radio_inline field-id-maintenance'
	)
	maintenanceField.append( th, td )

	headerCodeField.after( maintenanceField )
}
