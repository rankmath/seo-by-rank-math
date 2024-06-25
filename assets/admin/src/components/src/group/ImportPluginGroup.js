/**
 * External dependencies
 */
import { entries, map } from 'lodash'

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'
import { Button, Icon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import CheckboxControl from '../controls/CheckboxControl'
import CheckboxList from '../controls/CheckboxList'

/**
 * Import plugin component.
 *
 * @param {Object} props        Component props.
 * @param {Object} props.plugin The plugin details.
 */
const ImportPlugin = ( { plugin } ) => {
	const [ showPanel, setShowPanel ] = useState( false )
	const { importFrom, meta, recalculate } = plugin
	const metaOptions = map( entries( meta.options ), ( [ id, label ] ) => ( { id, label } ) )

	return (
		<>
			<div className={ `plugin-title ${ showPanel ? 'is-open' : '' }` }>
				<CheckboxControl checked={ false } onChange={ () => {} } />

				<h3>
					{ importFrom.name }

					<Button
						icon={
							<Icon icon={ showPanel ? 'arrow-up-alt2' : 'arrow-down-alt2' } />
						}
						onClick={ () => {
							setShowPanel( ( prev ) => ! prev )
						} }
					/>
				</h3>
			</div>

			{ showPanel && (
				<div className="inside">
					<CheckboxList options={ metaOptions } toggleAll={ { show: true } } />

					<p
						className="description"
						dangerouslySetInnerHTML={ { __html: meta.desc } }
					/>

					<CheckboxControl
						label={ recalculate.desc }
						checked={ false }
						onChange={ () => {} }
					/>
				</div>
			) }
		</>
	)
}

export default ( { options } ) => {
	return (
		<>
			{ map( options, ( option, index ) => <ImportPlugin key={ index } plugin={ option } /> ) }
		</>
	)
}
