/**
 * Retrieve the corresponding status icon class based on the resuult status.
 *
 * @param {string} status
 */
export default ( status ) => {
	const iconsMap = {
		ok: `dashicons dashicons-yes status-ok`,
		fail: `dashicons dashicons-no-alt status-fail`,
		warning: `dashicons dashicons-warning status-warning`,
		info: `dashicons dashicons-info status-info`,
	}

	return iconsMap[ status ] || ''
}
