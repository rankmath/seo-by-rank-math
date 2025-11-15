/**
 * External Dependencies
 */
import { fromPairs, isEmpty } from 'lodash'
import { useSearchParams } from 'react-router-dom'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect } from '@wordpress/data'

/**
 * Internal Dependencies
 */
import { TabPanel } from '@rank-math/components'
import TabContent from './TabContent'
import getTabs from '../pages'

const SettingsPanel = ( { currentPage, data } ) => {
	const [ tabs, setTabs ] = useState( null )
	const [ searchParams, setSearchParams ] = useSearchParams( {
		view: '',
	} )
	useEffect( () => {
		getTabs( currentPage, rankMath.tabs ).then( ( cfg ) => {
			setTabs( cfg )
		} )
	}, [ currentPage ] )

	if ( isEmpty( tabs ) ) {
		return
	}

	const activeTab = searchParams.get( 'view' )

	return (
		<div className="wrap rank-math-wrap rank-math-wrap-settings">
			<TabPanel
				orientation="vertical"
				tabs={ tabs }
				initialTabName={ activeTab }
				onSelect={ ( tab ) => {
					if ( tab !== activeTab ) {
						setSearchParams( ( params ) => fromPairs( [ ...params, [ 'view', tab ] ] ) )
					}
				} }
			>
				{ ( { fields, header } ) => {
					return (
						<>
							<TabContent
								header={ header }
								settings={ data }
								fields={ fields }
								type={ currentPage }
								tabs={ tabs }
								footer={ {
									discardButton: {
										children: __( 'Reset Options', 'rank-math' ),
									},
									applyButton: {
										children: __( 'Save Changes', 'rank-math' ),
									},
								} }
							/>
						</>
					)
				} }
			</TabPanel>
		</div>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			currentPage: rankMath.optionPage,
			data: select( 'rank-math-settings' ).getData(),
		}
	} )
)( SettingsPanel )
