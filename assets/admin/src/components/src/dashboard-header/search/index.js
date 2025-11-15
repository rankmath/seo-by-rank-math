/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { SearchControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import useClickOutside from '../../hooks/useClickOutside'
import Button from '../../buttons/Button'
import getFieldsData from './getFieldsData'
import filterFields from './filterFields'
import handleNavigation from './handleNavigation'

/**
 * Entry component that renders a search input field and shows filtered field results.
 * Handles navigation to appropriate setting/tab on selection.
 *
 * @param {Object} props
 * @param {string} props.page The current settings page identifier (e.g., 'general').
 */
export default ( { page } ) => {
	if ( ! includes( [ 'general', 'titles', 'sitemap' ], page ) ) {
		return null
	}

	const [ searchValue, setSearchValue ] = useState( '' )
	const [ fields, setFields ] = useState( {} )
	const [ showDropdown, setShowDropdown, ref ] = useClickOutside()

	useEffect( () => {
		getFieldsData().then( setFields )
	}, [] )

	const matches = filterFields( fields, searchValue )

	return (
		<div ref={ ref } className="rank-math-search-options">
			<SearchControl
				__nextHasNoMarginBottom
				value={ searchValue }
				onChange={ setSearchValue }
				onClick={ () => setShowDropdown( true ) }
				placeholder={ __( 'Search Options', 'rank-math' ) }
			/>

			{ searchValue && showDropdown && (
				<div className="rank-math-search-dropdown">
					{ matches.length === 0 ? (
						<span className="empty">{ __( 'Nothing found.', 'rank-math' ) }</span>
					) : (
						<>
							{ matches.map( ( field, index ) => (
								<Button
									variant="secondary"
									key={ `${ field.id }-${ index }` }
									onClick={ () => handleNavigation( field, page ) }
								>
									<h1>{ field.name }</h1>
									<p
										className="field-description"
										dangerouslySetInnerHTML={ { __html: field.desc } }
									/>
								</Button>
							) ) }
						</>
					) }
				</div>
			) }
		</div>
	)
}
