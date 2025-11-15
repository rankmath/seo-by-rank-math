/* global confirm */

/**
 * External Dependencies
 */
import { map } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { Button, TextareaControl, CheckboxControl } from '@rank-math/components'
import { runToolsAction } from './runToolsAction'

export default ( props ) => {
	const { data } = props
	const tools = data.tools
	const [ logger, setLogger ] = useState( [] )
	const [ updateAll, setUpdateAll ] = useState( true )
	return (
		<table className="rank-math-status-table striped rank-math-tools-table widefat rank-math-box">
			<tbody className="tools">
				{ map( tools, ( { title, description, button_text: buttonText, confirm_text: confirmText }, id ) => (
					<tr key={ id } className={ id }>
						<th>
							<h4 className="name">{ title }</h4>
							<p className="description" dangerouslySetInnerHTML={ { __html: description } } />
						</th>

						<td className="run-tool">
							<Button
								isDestructive
								size="large"
								className="tools-action"
								onClick={ () => {
									// eslint-disable-next-line no-alert
									if ( confirmText && ! confirm( confirmText ) ) {
										return false
									}

									const args = updateAll ? { update_all_scores: true } : {}
									runToolsAction( id, logger, setLogger, args )
								} }
							>
								{ buttonText }
							</Button>
							{
								id === 'update_seo_score' && rankMath.totalPostsWithoutScore > 0 &&
								<div className="update_all_scores">
									<CheckboxControl
										__nextHasNoMarginBottom
										label={ __( 'Include posts/pages where the score is already set', 'rank-math' ) }
										checked={ updateAll }
										onChange={ setUpdateAll }
									/>
								</div>
							}

						</td>
					</tr>
				) ) }
				{
					logger.length !== 0 &&
					<tr key="update-score-logger">
						<td colSpan={ 2 }>
							<TextareaControl
								disable="true"
								value={ logger.join( '\n' ) }
								className="import-progress-area large-text"
								rows="8"
								style={ { marginRight: '20px', background: '#eee' } }
							/>
						</td>
					</tr>
				}
			</tbody>
		</table>
	)
}
