/**
 * Derive a brand's display analysis state from its `analysis_status`.
 *
 * API status semantics:
 *   success → ran successfully (a future weekly run is masked as success)
 *   partial → last run completed partially
 *   pending → awaiting first run, or an overdue run is due
 *   running → a run is currently in progress
 *   error   → the latest run failed
 *   null    → no analysis at all
 *
 * Completed runs (success/partial) and brands with no analysis show no icon;
 * pending/running surface the running icon, error surfaces the error icon.
 *
 * @since 1.0.273
 */

/**
 * @param {Object} brand Brand rollup row.
 * @return {'running'|'error'|null} null when there's nothing to flag.
 */
export const getAnalysisState = ( brand ) => {
	if ( ! brand ) {
		return null
	}

	if ( brand.status === 'inactive' ) {
		return null
	}

	switch ( brand.analysis_status ) {
		case 'pending':
		case 'running':
			return 'running'
		case 'error':
			return 'error'
		default:
			return null
	}
}
