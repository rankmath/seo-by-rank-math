/**
 * External dependencies
 */
import { forEach, isNull } from 'lodash'

/**
 * Sets up event listeners to toggle the editing state of the Form.
 *
 * @param {Function} setIsEditing Callback to update the isEditing state.
 *
 * @return {void} A cleanup function to remove the event listeners.
 */
export default ( setIsEditing ) => {
	const addNewLink = document.querySelector( '.rank-math-add-new-redirection' )
	if ( isNull( addNewLink ) ) {
		return
	}

	const editLinks = document.querySelectorAll( '.value-url_from, .rank-math-redirection-edit' )
	const handleEditClick = () => setIsEditing( true )
	const handleAddNewClick = () => setIsEditing( false )

	forEach( editLinks, ( link ) => link.addEventListener( 'click', handleEditClick ) )

	addNewLink.addEventListener( 'click', handleAddNewClick )

	return () => {
		forEach( editLinks, ( link ) => link.removeEventListener( 'click', handleEditClick ) )

		addNewLink.removeEventListener( 'click', handleAddNewClick )
	}
}
