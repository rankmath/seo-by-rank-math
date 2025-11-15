/**
 * External dependencies
 */
import { some } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Icon } from '@wordpress/components'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Modes from './Modes'
import CheckLists from './CheckLists'
import { Button } from '@rank-math/components'

export default ( { data, saveData, skipStep } ) => {
	const [ mode, setMode ] = useState( data.setup_mode )
	const { phpVersionOk, extensions } = data
	const hasMissingExtension = some( extensions, ( value ) => value === false )
	const allGood = phpVersionOk && ! hasMissingExtension
	return (
		<>
			<div
				className={ `field-metabox rank-math-setup-mode ${
					! rankMath.isPro ? 'is-free' : ''
				}` }
			>
				<Modes value={ mode } onChange={ setMode } />
			</div>

			<CheckLists { ...data } allGood={ allGood } />

			<footer className="form-footer rank-math-custom wp-core-ui rank-math-ui text-center">
				{ allGood && (
					<Button
						variant="animate"
						onClick={ () => {
							data.setup_mode = mode
							saveData( data )
						} }
					>
						{ __( 'Start Wizard', 'rank-math' ) }
						<Icon icon="arrow-right-alt2" />
					</Button>
				) }
			</footer>
		</>
	)
}
