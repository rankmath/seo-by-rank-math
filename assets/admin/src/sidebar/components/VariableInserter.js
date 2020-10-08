/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component, Fragment } from '@wordpress/element'
import { Button, TextControl } from '@wordpress/components'
import { decodeEntities } from '@wordpress/html-entities'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

class VariableInserter extends Component {
	/**
	 * Component state
	 *
	 * @type {Object}
	 */
	state = {}

	constructor( props ) {
		super( props )
		this.state.variables = this.getFiltered()
		this.state.filtered = this.state.variables
		this.state.display = 'none'
		this.state.isOpen = false
		this.handleOutsideClick = this.handleOutsideClick.bind( this )
	}

	getFiltered() {
		const { exclude } = this.props
		const variables = Object.values( rankMath.variables )

		return isUndefined( exclude )
			? variables
			: variables.filter( ( variable ) => {
				return ! exclude.includes( variable.variable )
			} )
	}

	render() {
		return (
			<Fragment>
				<div
					className="rank-math-variables-dropdown"
					style={ { display: this.state.display } }
					ref={ ( node ) => {
						this.node = node
					} }
				>
					<TextControl
						autoComplete="off"
						placeholder={ decodeEntities(
							__( 'Search &hellip;', 'rank-math' )
						) }
						onChange={ ( value ) => {
							this.handleSearch( value )
						} }
					/>

					<ul>
						{ this.state.filtered.map( ( variable, index ) => {
							return (
								<li
									key={ index }
									data-var={ variable.variable }
									role="presentation"
									onClick={ () => {
										this.props.onClick( variable )
									} }
								>
									<strong>{ variable.name }</strong>
									<span>
										<Interpolate tags="strong">
											{ variable.description
												.replace(
													'<strong>',
													'{{strong}}'
												)
												.replace(
													'</strong>',
													'{{/strong}}'
												) }
										</Interpolate>
									</span>
								</li>
							)
						}, this ) }
					</ul>
				</div>
				<Button
					icon="arrow-down-alt2"
					onClick={ () => {
						this.toggle()
					} }
				/>
			</Fragment>
		)
	}

	toggle() {
		if ( ! this.state.isOpen ) {
			document.addEventListener( 'click', this.handleOutsideClick, false )
		} else {
			document.removeEventListener(
				'click',
				this.handleOutsideClick,
				false
			)
		}

		this.setState( {
			display: this.state.isOpen ? 'none' : 'block',
			isOpen: ! this.state.isOpen,
		} )
	}

	handleOutsideClick( e ) {
		if ( this.node.contains( e.target ) ) {
			return
		}

		this.toggle()
	}

	handleSearch( value ) {
		const query = value.toLowerCase()

		if ( 2 > query.length ) {
			this.setState( { filtered: this.state.variables } )
			return
		}

		this.setState( {
			filtered: this.state.variables.filter( ( variable ) => {
				const searchValue = Object.values( variable )
					.join( ' ' )
					.toLowerCase()
				return searchValue.indexOf( query ) !== -1
			} ),
		} )
	}
}

export default VariableInserter
