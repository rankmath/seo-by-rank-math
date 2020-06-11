/*!
 * Rank Math
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'

( function( $ ) {
	// Document Ready
	$( function() {
		window.rankMathValidate = {
			init() {
				this.extendLibrary()
				this.watchFields()
			},

			extendLibrary() {
				// Add new regex rule.
				$.validator.addMethod(
					'regex',
					function( value, element, regexp ) {
						const re = new RegExp(
							'string' === typeof regexp
								? regexp
								: $( element ).data( 'validate-pattern' )
						)
						return this.optional( element ) || re.test( value )
					},
					rankMath.validationl10n.regexErrorDefault
				)

				// Add default errors, for translation.
				$.extend( $.validator.messages, {
					required: rankMath.validationl10n.requiredErrorDefault,
					email: rankMath.validationl10n.emailErrorDefault,
					url: rankMath.validationl10n.urlErrorDefault,
				} )

				// Set default error class.
				$.extend( $.validator.defaults, { errorClass: 'invalid' } )
			},

			watchFields() {
				const self = this,
					fieldSelectors =
						'input[type=text], input[type=password], input[type=url], input[type=email], input[type=number], textarea'

				$( '.rank-math-validate-field' ).on(
					'focus',
					fieldSelectors,
					function() {
						self.fieldValidation( $( this ).closest( 'form' ) )
					}
				)
			},

			fieldValidation( form ) {
				if ( '1' === form.data( 'validated' ) ) {
					return false
				}

				form.data( 'validated', '1' ).validate()
				return true
			},
		}

		window.rankMathValidate.init()
	} )
}( jQuery ) )
