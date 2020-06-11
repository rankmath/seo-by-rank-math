/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Local from './Local'

const RestaurantSnippet = ( props ) => (
	<Fragment>
		<Local />

		<PanelBody initialOpen={ true }>
			<TextControl
				label={ __( 'Serves Cuisine', 'rank-math' ) }
				help={ __(
					'The type of cuisine we serve. Separated by comma.',
					'rank-math'
				) }
				value={ props.restaurantServesCuisine }
				onChange={ props.updateServesCuisine }
			/>

			<TextControl
				type="url"
				label={ __( 'Menu URL', 'rank-math' ) }
				help={ __(
					'URL pointing to the menu of the restaurant.',
					'rank-math'
				) }
				value={ props.restaurantMenu }
				onChange={ props.updateMenu }
			/>
		</PanelBody>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			restaurantServesCuisine: data.restaurantServesCuisine,
			restaurantMenu: data.restaurantMenu,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateServesCuisine( cuisine ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'restaurantServesCuisine',
					'restaurant_serves_cuisine',
					cuisine
				)
			},

			updateMenu( menu ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'restaurantMenu',
					'restaurant_menu',
					menu
				)
			},
		}
	} )
)( RestaurantSnippet )
