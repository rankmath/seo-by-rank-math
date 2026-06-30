/**
 * ActionButtons — View / Edit / Disable action buttons for a brand table row.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { memo } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import Button from './Button'
import './ActionButtons.scss'

/**
 * @param {Object}   props
 * @param {string}   props.status      Brand status ('active' | 'inactive').
 * @param {Function} [props.onView]    View handler.
 * @param {Function} [props.onEdit]    Edit handler.
 * @param {Function} [props.onDisable] Disable/Enable handler.
 * @return {JSX.Element} Rendered component.
 */
const ActionButtons = memo( ( { status, onView, onEdit, onDisable } ) => {
	const ns = 'rank-math-ai-visibility-action-buttons'

	const handleStatus = () => {
		onDisable( status === 'inactive' ? 'activate' : 'deactivate' )
	}

	return (
		<div className={ ns }>

			<Button
				variant="icon"
				className={ `${ ns }__view` }
				onClick={ onView }
				aria-label={ __( 'View', 'seo-by-rank-math' ) }
			>
				<span className={ `${ ns }__inner` }>
					<span className="rm-icon-eye" />
					<span className={ `${ ns }__label` }>{ __( 'View', 'seo-by-rank-math' ) }</span>
				</span>
			</Button>

			<Button
				variant="icon"
				className={ `${ ns }__edit` }
				onClick={ onEdit }
				aria-label={ __( 'Edit', 'seo-by-rank-math' ) }
			>
				<span className={ `${ ns }__inner` }>
					<span className="rm-icon rm-icon-edit" />
					<span className={ `${ ns }__label` }>{ __( 'Edit', 'seo-by-rank-math' ) }</span>
				</span>
			</Button>

			<Button
				variant="icon"
				className={ `${ status === 'inactive' ? `${ ns }__inactive` : `${ ns }__active` }` }
				onClick={ handleStatus }
				aria-label={ status === 'inactive' ? __( 'Activate', 'seo-by-rank-math' ) : __( 'Deactivate', 'seo-by-rank-math' ) }
			>
				{
					status === 'inactive' ? (
						<span className={ `${ ns }__inner` }>
							<span className="dashicons dashicons-controls-play" />
							<span className={ `${ ns }__label` }>{ __( 'Activate', 'seo-by-rank-math' ) }</span>
						</span>
					) : (
						<span className={ `${ ns }__inner` }>
							<span className="rm-icon-off" />
							<span className={ `${ ns }__label` }>{ __( 'Deactivate', 'seo-by-rank-math' ) }</span>
						</span>
					)
				}
			</Button>

		</div>
	)
} )

ActionButtons.displayName = 'ActionButtons'

export default ActionButtons
