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
import FreePlanNotice from '../components/FreePlanNotice'
import ErrorCTA from '@components/ErrorCTA'

/**
 * AI_Tools component
 *
 * @param {Object} props Props passed to the component.
 */
export default ( props ) => {
	const { showMinimal = false, isContentAIPage = false } = props
	const [ category, setCategory ] = useState( 'all' )
	const [ search, setSearch ] = useState()
	const [ tool, setTool ] = useState()

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
			<div className={ props.hasError ? 'rank-math-ui module-listing blurred' : 'rank-math-ui module-listing' }>
				{ ! isContentAIPage && <FreePlanNotice /> }
				<div className="content-ai-header">
					<div className="content-ai-filter">
						{
							! isContentAIPage &&
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
							! showMinimal && isContentAIPage &&
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
										setTool( value )
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
					tool && <Modal { ...props } tool={ tool } setTool={ setTool } />
				}
			</div>
			{ props.hasError && <ErrorCTA /> }
		</>
	)
}
