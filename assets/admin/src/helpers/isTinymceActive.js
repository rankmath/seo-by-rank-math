/**
 * Checks if the Tinymce Editor is active.
 *
 * @return {boolean} True if the data API is available.
 */
export default () => {
	return (
		'undefined' !== typeof tinymce &&
		null !== tinymce.activeEditor &&
		true !== tinymce.activeEditor.isHidden() &&
		'content' === tinymce.activeEditor.id
	)
}
