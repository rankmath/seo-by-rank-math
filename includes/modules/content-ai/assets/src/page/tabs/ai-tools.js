/**
 * External dependencies
 */
import { map, lowerCase } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { SelectControl, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getTools from '../helpers/getTools'
import Modal from '../modal'
import SearchField from '../components/SearchField'
import ErrorCTA from '@components/ErrorCTA'

/**
 * AI_Tools component
 *
 * @param {Object}  props                   Props passed to the component.
 * @param {boolean} props.showMinimal       Set to true to hide the categories.
 * @param {boolean} props.isPage            Set to true on Content AI Admin page.
 * @param {Object}  props.setCredits        State function to update the Credits count.
 * @param {boolean} props.hasContentAiError Show blurred section on error.
 */
export default ( { showMinimal = false, isPage = false, setCredits = false, hasContentAiError = false } ) => {
	const [ category, setCategory ] = useState( 'all' )
	const [ search, setSearch ] = useState()
	const [ endpoint, setEndpoint ] = useState()

	const categories = {
		all: __( 'All', 'rank-math' ),
		seo: __( 'SEO', 'rank-math' ),
		blog: __( 'Blog', 'rank-math' ),
		'marketing-sales': __( 'Marketing & Sales', 'rank-math' ),
		ecommerce: __( 'eCommerce', 'rank-math' ),
		misc: __( 'Misc', 'rank-math' ),
	}

	return (
		<>
			<div className={ hasContentAiError ? 'rank-math-ui module-listing blurred' : 'rank-math-ui module-listing' }>
				<div className="content-ai-header">
					<div className="content-ai-filter">
						{
							! isPage &&
							<SelectControl
								options={
									map( categories, ( label, value ) => {
										return {
											value,
											label,
										}
									} )
								}
								onChange={ ( key ) => setCategory( key ) }
							/>
						}
						{
							! showMinimal && isPage &&
							<div>
								{
									map( categories, ( value, key ) => {
										return (
											<Button
												className={ category === key ? 'active' : '' }
												key={ key }
												onClick={ () => setCategory( key ) }
											>
												{ value }
											</Button>
										)
									} )
								}
							</div>
						}

						<SearchField search={ search } setSearch={ setSearch } />
					</div>
				</div>
				<div className="grid">
					{
						map( getTools(), ( value, key ) => {
							if ( 'all' !== category && category !== value.category ) {
								return
							}

							if (
								search &&
								! lowerCase( value.title ).includes( lowerCase( search ) ) &&
								! lowerCase( value.endpoint ).includes( lowerCase( search ) )
							) {
								return
							}

							return (
								<Button
									key={ key }
									className="rank-math-box"
									onClick={ () => {
										setEndpoint( value )
									} }
								>
									<i className={ value.endpoint + ' ai-icon ' + value.icon }></i>
									<header>
										<h3>{ value.title }</h3>
										{ ! showMinimal && <p>{ value.description }</p> }
									</header>
								</Button>
							)
						} )
					}
				</div>
				{
					endpoint && <Modal data={ endpoint } setEndpoint={ setEndpoint } isPage={ isPage } setCredits={ setCredits } />
				}
			</div>
			{ hasContentAiError && <ErrorCTA /> }
		</>
	)
}
