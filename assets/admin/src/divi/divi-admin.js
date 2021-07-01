/**
 * External dependencies.
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n'
import { render, createInterpolateElement } from '@wordpress/element'

/**
 * Internal dependencies.
 */
import AdminNotice from './components/AdminNotice'

class diviAdmin {
	/**
	 * Constructor.
	 */
	constructor() {
		this.cacheDom()
		this.renderSeoSettingsNotice()
		this.disableDiviSeoInputs()
	}

	cacheDom() {
		this.$etSeoSettingsArea = jQuery( '#wrap-seo' )
		this.$etDisabledTextInputs = jQuery( '#divi_seo_home_titletext' )
			.add( '#divi_seo_home_descriptiontext' )
			.add( '#divi_seo_home_keywordstext' )
			.add( '#divi_seo_home_separate' )
			.add( '#divi_seo_single_field_title' )
			.add( '#divi_seo_single_field_description' )
			.add( '#divi_seo_single_field_keywords' )
			.add( '#divi_seo_single_separate' )
			.add( '#divi_seo_index_separate' )
		this.$etDisabledSelectInputs = jQuery( '#divi_seo_home_type' )
			.add( '#divi_seo_single_type' )
			.add( '#divi_seo_index_type' )
		this.$etDisabledToggleInputs = jQuery( '#divi_seo_home_title' )
			.add( '#divi_seo_home_description' )
			.add( '#divi_seo_home_keywords' )
			.add( '#divi_seo_home_canonical' )
			.add( '#divi_seo_single_title' )
			.add( '#divi_seo_single_description' )
			.add( '#divi_seo_single_keywords' )
			.add( '#divi_seo_single_canonical' )
			.add( '#divi_seo_index_canonical' )
			.add( '#divi_seo_index_description' )
	}

	renderSeoSettingsNotice() {
		const noticeContainerId = 'rank-math-divi-seo-admin-notice-container'
		this.$etSeoSettingsArea
			.find( '.et-tab-content' )
			.prepend( jQuery( `<div id="${ noticeContainerId }" />` ) )
		document.querySelectorAll( '#' + noticeContainerId ).forEach( function( elem ) {
			render( this.getAdminNotice(), elem )
		}, this )
	}

	disableDiviSeoInputs() {
		this.$etDisabledTextInputs
			.attr( 'readonly', true )
			.css( {
				cursor: 'not-allowed',
			} )
		this.$etDisabledSelectInputs
			.css( {
				cursor: 'not-allowed',
			} )
			.find( 'option' )
			.attr( 'disabled', true )
		this.$etDisabledToggleInputs
			.attr( 'disabled', true )
			.find( '+ .et_pb_yes_no_button' )
			.css( {
				cursor: 'not-allowed',
			} )
			.on( 'mousedown keydown', function() {
				const $this = jQuery( this )
				if ( $this.is( '.et_pb_on_state' ) ) {
					$this.removeClass( 'et_pb_on_state' )
						.addClass( 'et_pb_off_state' )
				} else if ( $this.is( '.et_pb_off_state' ) ) {
					$this.addClass( 'et_pb_on_state' )
						.removeClass( 'et_pb_off_state' )
				}
			} )
	}

	getAdminNotice() {
		return (
			<AdminNotice
				style={ {
					margin: '0',
					marginBottom: '30px',
					padding: '12px',
					borderTopColor: '#e4e4e5',
					borderRightColor: '#e4e4e5',
					borderBottomColor: '#e4e4e5',
				} }
			>
				<p>
					{ createInterpolateElement(
						/* translators: <settings_page_link/>: "settings page" text link */
						__( 'The below options are handled via the Rank Math SEO <settings_page_link/>.', 'rank-math' ),
						{
							settings_page_link: <a href="/wp-admin/admin.php?page=rank-math-options-titles">{ __( 'settings page', 'rank-math' ) }</a>,
						}
					) }
				</p>
			</AdminNotice>
		)
	}
}

jQuery( function() {
	new diviAdmin()
} )
