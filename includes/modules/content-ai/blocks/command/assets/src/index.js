/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import edit from './edit'
import metadata from './block.json'

const { name } = metadata

export { metadata, name }

export const settings = {
	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17.08 18.02"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M16.65,12.08a.83.83,0,0,0-1.11.35A7.38,7.38,0,1,1,9,1.63a7.11,7.11,0,0,1,.92.06A2.52,2.52,0,0,1,11,.23,8.87,8.87,0,0,0,9,0a9,9,0,1,0,8,13.19A.83.83,0,0,0,16.65,12.08Z"/><path d="M7.68,7.29A1.58,1.58,0,0,0,6.2,8.94a1.57,1.57,0,0,0,1.48,1.64A1.56,1.56,0,0,0,9.16,8.94,1.57,1.57,0,0,0,7.68,7.29Z" /><path d="M13.34,4.71a2.45,2.45,0,0,1-1,.2A2.53,2.53,0,0,1,9.93,3,7.18,7.18,0,0,0,9.12,3a6,6,0,1,0,4.22,1.73ZM10.53,11.3a.75.75,0,1,1-1.5,0v-.06a2.4,2.4,0,0,1-1.66.69,2.81,2.81,0,0,1-2.58-3,2.82,2.82,0,0,1,2.58-3A2.39,2.39,0,0,1,9,6.64a.75.75,0,0,1,1.5.07Zm2.56,0a.75.75,0,1,1-1.5,0V6.71a.75.75,0,1,1,1.5,0Z" /><circle cx="12.42" cy="2.37" r="1.45" /></g></g></svg>,
	edit,
}

registerBlockType( { name, ...metadata }, settings )
