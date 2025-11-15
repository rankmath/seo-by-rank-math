/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Icon } from '@wordpress/components'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { Button, Table } from '@rank-math/components'
import CheckListDescription from './CheckListDescription'
import PluginConflictTable from './PluginConflictTable'
import getPluginCompatibilityFields from './helpers/getPluginCompatibilityFields'

export default ( props ) => {
	const { allGood, phpVersionRecommended } = props
	const [ showTable, setShowTable ] = useState( ! allGood )

	return (
		<>
			{ allGood && (
				<>
					<br />
					<h2 className="text-center compatibility-check">
						<Icon icon={ phpVersionRecommended ? 'warning' : 'yes' } />
						{ __( 'Your website is compatible to run Rank Math SEO', 'rank-math' ) }
						<Button
							size="small"
							variant="link"
							className="rank-math-collapsible-trigger"
							onClick={ () => setShowTable( ( prev ) => ! prev ) }
						>
							<Icon icon={ showTable ? 'arrow-up-alt2' : 'arrow-down-alt2' }>
								<span>
									{ showTable
										? __( 'Less', 'rank-math' )
										: __( 'More', 'rank-math' ) }
								</span>
							</Icon>
						</Button>
					</h2>
				</>
			) }

			{ showTable && (
				<div id="rank-math-compatibility-collapsible">
					<Table fields={ getPluginCompatibilityFields( props ) } addHeader={ false } useThOnly={ true } />
					<CheckListDescription { ...props } />
					<PluginConflictTable { ...props } />
				</div>
			) }
		</>
	)
}
