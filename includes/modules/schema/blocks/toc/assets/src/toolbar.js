/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { BlockControls } from '@wordpress/block-editor'
import {
	Toolbar,
	ToolbarButton,
} from '@wordpress/components'
import { formatListBullets, formatListNumbered, alignLeft } from '@wordpress/icons'

export default ( { setAttributes } ) => {
	return (
		<BlockControls>
			<Toolbar label={ __( 'Table of Content Options', 'rank-math' ) }>
				<ToolbarButton
					icon={ formatListBullets }
					label={ __( 'Unordered List', 'rank-math' ) }
					onClick={ () => setAttributes( { listStyle: 'ul' } ) }
				/>
				<ToolbarButton
					icon={ formatListNumbered }
					label={ __( 'Ordered List', 'rank-math' ) }
					onClick={ () => setAttributes( { listStyle: 'ol' } ) }
				/>
				<ToolbarButton
					icon={ alignLeft }
					label={ __( 'None', 'rank-math' ) }
					onClick={ () => setAttributes( { listStyle: 'div' } ) }
				/>
			</Toolbar>
		</BlockControls>
	)
}
