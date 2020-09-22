/**
 * External dependencies
 */
import { unescape as unescapeString, invoke, difference } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import { addQueryArgs } from '@wordpress/url'
import { Component } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import { SelectControl, Spinner } from '@wordpress/components'

/**
 * Primary Term Picker
 *
 * Class inspiration taken from Yoast (https://github.com/Yoast/wordpress-seo/)
 *
 * @extends Component
 */
class TermPicker extends Component {
	constructor() {
		super( ...arguments )
		this.onChange = this.onChange.bind( this )
		this.state = {
			loading: true,
			availableTerms: [],
			selectedTerms: [],
		}
	}

	componentDidMount() {
		this.fetchTerms()
	}

	componentWillUnmount() {
		invoke( this.fetchRequest, [ 'abort' ] )
	}

	componentDidUpdate( prevProps, prevState ) {
		// Check if a term has been added and retrieve new terms if so.
		if (
			prevProps.selectedTermIds.length < this.props.selectedTermIds.length
		) {
			const newId = difference(
				this.props.selectedTermIds,
				prevProps.selectedTermIds
			)[ 0 ]
			if ( ! this.termIsAvailable( newId ) ) {
				this.fetchTerms()
				return
			}
		}

		// Check if the selected terms have changed.
		if ( prevProps.selectedTermIds !== this.props.selectedTermIds ) {
			this.updateSelectedTerms(
				this.state.availableTerms,
				this.props.selectedTermIds
			)
		}

		// Handle terms change.
		if ( prevState.selectedTerms !== this.state.selectedTerms ) {
			this.handleSelectedTermsChange()
		}
	}

	termIsAvailable( termId ) {
		return !! this.state.availableTerms.find(
			( term ) => term.id === termId
		)
	}

	updateSelectedTerms( terms, selectedTermIds ) {
		this.setState( {
			selectedTerms: this.filterSelectedTerms( terms, selectedTermIds ),
		} )
	}

	handleSelectedTermsChange() {
		const { selectedTerms } = this.state
		const { primaryTermID } = this.props
		const selectedTerm = selectedTerms.find(
			( term ) => term.id === primaryTermID
		)

		if ( ! selectedTerm ) {
			/**
			 * If the selected term is no longer available, set the primary term ID to
			 * the first term, and to -1 if no term is available.
			 */
			this.onChange( selectedTerms.length ? selectedTerms[ 0 ].id : '' )
		}
	}

	fetchTerms() {
		const { taxonomy } = this.props

		if ( ! taxonomy ) {
			return
		}

		this.fetchRequest = apiFetch( {
			path: addQueryArgs( `/wp/v2/${ taxonomy.rest_base }`, {
				per_page: -1,
				orderby: 'count',
				order: 'desc',
				_fields: 'id,name',
			} ),
		} )

		this.fetchRequest.then(
			( terms ) => {
				// resolve
				this.fetchRequest = null
				this.setState( {
					loading: false,
					availableTerms: terms,
					selectedTerms: this.filterSelectedTerms(
						terms,
						this.props.selectedTermIds
					),
				} )
			},
			( xhr ) => {
				// reject
				if ( xhr.statusText === 'abort' ) {
					return
				}
				this.fetchRequest = null
				this.setState( {
					loading: false,
				} )
			}
		)
	}

	filterSelectedTerms( terms, selectedTermIds ) {
		return terms.filter( ( term ) => selectedTermIds.includes( term.id ) )
	}

	onChange( termId ) {
		dispatch( 'rank-math' ).updatePrimaryTermID(
			termId,
			this.props.taxonomy.slug
		)
	}

	shouldComponentUpdate( nextProps, nextState ) {
		return (
			this.props.selectedTermIds !== nextProps.selectedTermIds ||
			this.props.primaryTermID !== nextProps.primaryTermID ||
			this.state.selectedTerms !== nextState.selectedTerms
		)
	}

	render() {
		if ( this.state.selectedTerms.length < 2 ) {
			return null
		}

		if ( this.state.loading ) {
			return [
				<Spinner key="spinner" />,
				<p key="spinner-text">Loading</p>,
			]
		}

		return (
			<SelectControl
				label={ __( 'Select Primary Term', 'rank-math' ) }
				value={ this.props.primaryTermID }
				options={ this.state.selectedTerms.map( ( term ) => {
					return {
						value: term.id,
						label: unescapeString( term.name ),
					}
				} ) }
				onChange={ this.onChange }
			/>
		)
	}
}

export default TermPicker
