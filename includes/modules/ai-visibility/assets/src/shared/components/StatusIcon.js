/**
 * StatusIcon — inline status indicator (icon + hover-reveal label).
 *
 * The root keeps a fixed footprint; the pill expands as an absolutely
 * positioned overlay, so revealing the label never shifts surrounding layout.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Tooltip } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './StatusIcon.scss'

const VARIANTS = {
	error: {
		icon: 'dashicons-warning',
		label: __( 'Error', 'seo-by-rank-math' ),
		tooltip: __( 'Last analysis failed. It will be retried automatically.', 'seo-by-rank-math' ),
	},
	running: {
		icon: 'dashicons-update',
		label: __( 'Running', 'seo-by-rank-math' ),
		tooltip: __( 'Analysis in progress — data will appear in approximately 10 minutes.', 'seo-by-rank-math' ),
	},
	pending: {
		icon: 'dashicons-update',
		label: __( 'Pending', 'seo-by-rank-math' ),
		tooltip: __( 'Analysis in progress — data will appear in approximately 10 minutes.', 'seo-by-rank-math' ),
	},
}

/**
 * StatusIcon component.
 *
 * @param {Object}                      props
 * @param {'error'|'running'|'pending'} props.variant Status variant.
 * @return {JSX.Element|null} Status icon, or null for unknown variants.
 */
const StatusIcon = ( { variant } ) => {
	const config = VARIANTS[ variant ]
	if ( ! config ) {
		return null
	}

	const ns = 'rank-math-ai-visibility-status-icon'

	return (
		<Tooltip text={ config.tooltip }>
			<span className={ `${ ns } ${ ns }--${ variant }` }>
				<span className={ `${ ns }__pill` }>
					<span className={ `dashicons ${ config.icon }` } />
					<span className={ `${ ns }__label` }>{ config.label }</span>
				</span>
			</span>
		</Tooltip>
	)
}

StatusIcon.displayName = 'StatusIcon'

export default StatusIcon
