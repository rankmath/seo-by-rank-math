/*global ETBuilderBackendDynamic*/

/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { createElement, render } from '@wordpress/element'
import { addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import PrimaryTermSelect from './components/PrimaryTermSelect'
import { find, get } from 'lodash'

export default class PrimaryTerm {
	static initRecursionTimer
	static attemptsRun = 0
	static maxAttempts = 10
	static attemptInterval = 1000
	constructor() {
		jQuery( document ).on(
			'click',
			'.et-fb-button--toggle-setting',
			() => {
				clearTimeout( this.initRecursionTimer )
				this.attemptsRun = 0
				this.init.call( this )
			}
		)
	}
	async init() {
		if ( typeof this.hasPostPrimaryTaxonomySupport === 'undefined' ) {
			await this.cacheTaxonomyData()
		}
		if ( ! this.hasPostPrimaryTaxonomySupport ) {
			return
		}
		this.attemptsRun++
		if ( this.attemptsRun > this.maxAttempts ) {
			return
		}
		const modalFound = this.cacheDom()
		if ( ! modalFound ) {
			this.initRecursionTimer = setTimeout( this.init.bind( this ), this.attemptInterval )
		} else {
			this.renderContainer()
			this.renderComponent()
			this.bindEvents()
		}
	}
	async cacheTaxonomyData() {
		const primaryTaxSlug = get( rankMath, 'assessor.primaryTaxonomy.name' ) || '',
			currentPostTaxonomies = get( ETBuilderBackendDynamic, 'getTaxonomies', {} )
		this.primaryTaxonomyTerms = currentPostTaxonomies[ primaryTaxSlug ] || []
		await apiFetch( { path: '/wp/v2/taxonomies' } )
			.then( ( resp ) => {
				this.primaryTaxonomy = Object.keys( currentPostTaxonomies )
					.includes( primaryTaxSlug ) && resp[ primaryTaxSlug ]
					? resp[ primaryTaxSlug ]
					: {}
			} )
		this.hasPostPrimaryTaxonomySupport = jQuery.isEmptyObject( this.primaryTaxonomy ) ? false : true
		this.primaryTaxonomyValue = get(
			ETBuilderBackendDynamic,
			`pageSettingsValues.et_pb_post_settings_${ this.primaryTaxonomy.rest_base }`,
			''
		)
	}
	cacheDom() {
		this.$diviSettingsModal = jQuery( document ).find( '.et-fb-modal__page-settings' )
		if ( ! this.$diviSettingsModal.length ) {
			return false
		}
		this.$diviTermSettingInputs = this.$diviSettingsModal.find(
			`.et-fb-option--${ this.primaryTaxonomy.rest_base }`
		)
		this.$diviTermSettingArea = this.$diviTermSettingInputs.parents( '.et-fb-form__group' )
		if ( ! this.$diviTermSettingArea.length ) {
			// NOTE: See fn definition for notes and instructions. This line of code can simply be
			//       removed once a fix has been published. It overwrites the proper
			//       implementaion found right above. Please still test the code afterwards to
			//       make sure it runs as expected.
			this.$diviTermSettingArea = this.workaroundForFalseDiviTaxonomySelector()
		}
		this.$PrimaryTermSelectContainer = jQuery( '<div id="rank-math-primary-term-input" />' )
		return true
	}
	// NOTE: This is a workaround until Divi issues an update on their theme's selectors.
	//       The Divi selectors are false, they all read `et-fb-option--categories`.
	//       Therefore, the workaround is looking at the taxonomy setting label text.
	// TODO: Remove this workaround once Divi has published the fix.
	workaroundForFalseDiviTaxonomySelector() {
		const name = this.primaryTaxonomy.name.toLowerCase()
		return this.$diviSettingsModal.find( '.et-fb-form__label-text' )
			.filter( ( i, elem ) => jQuery( elem ).text().toLowerCase() === name )
			.parents( '.et-fb-form__group' )
	}
	renderContainer() {
		this.$diviTermSettingArea.after( this.$PrimaryTermSelectContainer )
	}
	renderComponent( terms ) {
		const props = {
			taxonomySlug: this.primaryTaxonomy.slug,
			options: this.formatActiveTerms( terms ),
		}
		render(
			createElement( PrimaryTermSelect, props ),
			this.$PrimaryTermSelectContainer[ 0 ]
		)
	}
	bindEvents() {
		addFilter(
			'et.builder.store.setting.update',
			'rank-math',
			( value, setting ) => {
				if ( `et_pb_post_settings_${ this.primaryTaxonomy.rest_base }` === setting ) {
					this.renderComponent( value )
				}
				return value
			}
		)
	}
	formatActiveTerms( termIds = this.primaryTaxonomyValue ) {
		if ( ! this.primaryTaxonomyTerms.length || ! termIds.trim() ) {
			return []
		}
		return termIds.split( ',' ).map( ( termId ) => {
			const { name: label, term_id: value } = find(
				this.primaryTaxonomyTerms,
				[ 'term_id', parseInt( termId ) ]
			) || {}
			return { label, value }
		} )
	}
}
