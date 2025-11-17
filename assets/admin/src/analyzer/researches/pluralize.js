/**
 * Internal dependencies
 */
import { uncountablesPlurals, pluralizationRules, irregularPluralsRules } from '@config/pluralize'

/**
 * Pluralize class.
 */
class Pluralize {
	/**
	 * Class constructor
	 */
	constructor() {
		this.irregularSingles = {}
		this.irregularPlurals = {}
		this.uncountables = uncountablesPlurals
		this.pluralizationRules = pluralizationRules

		irregularPluralsRules.forEach( function( rule ) {
			const single = rule[ 0 ],
				plural = rule[ 1 ]

			this.irregularSingles[ single ] = plural
			this.irregularPlurals[ plural ] = single
		}, this )
	}

	get( word ) {
		// Empty string or doesn't need fixing.
		if ( ! word.length ) {
			return word
		}

		// Get the correct token and case restoration functions.
		const token = word.toLowerCase()

		// Check against the keep object map.
		if ( this.irregularPlurals.hasOwnProperty( token ) ) {
			this.restoreCase( word, token )
		}

		// Check against the replacement map for a direct word replacement.
		if ( this.irregularSingles.hasOwnProperty( token ) ) {
			this.restoreCase( word, this.irregularSingles[ token ] )
		}

		// Save some time in the case that singular and plural are the same
		if ( this.uncountables.hasOwnProperty( token ) ) {
			return word
		}

		let len = this.pluralizationRules.length

		// Iterate over the sanitization rules and use the first one to match.
		while ( len-- ) {
			const rule = this.pluralizationRules[ len ]
			if ( rule[ 0 ].test( word ) ) {
				return this.replace( word, rule )
			}
		}

		return word
	}

	/**
	 * Pass in a word token to produce a function that can replicate the case on another word.
	 *
	 * @param {string} word  Word to restore.
	 * @param {string} token Token for it.
	 *
	 * @return {string} Restored word.
	 */
	restoreCase( word, token ) {
		// Tokens are an exact match.
		if ( word === token ) {
			return token
		}

		// Upper cased words. E.g. "HELLO".
		if ( word === word.toUpperCase() ) {
			return token.toUpperCase()
		}

		// Title cased words. E.g. "Title".
		if ( word[ 0 ] === word[ 0 ].toUpperCase() ) {
			return token.charAt( 0 ).toUpperCase() + token.substr( 1 ).toLowerCase()
		}

		// Lower cased words. E.g. "test".
		return token.toLowerCase()
	}

	/**
	 * Replace a word using a rule.
	 *
	 * @param {string} word Word to replace.
	 * @param {Array}  rule Rule to be applied.
	 *
	 * @return {string} Repalced word.
	 */
	replace( word, rule ) {
		return word.replace( rule[ 0 ], ( match, index ) => {
			const result = this.interpolate( rule[ 1 ], arguments )
			if ( '' === match ) {
				return this.restoreCase( word[ index - 1 ], result )
			}

			return this.restoreCase( match, result )
		} )
	}

	/**
	 * Interpolate a regexp string.
	 *
	 * @param {string} str  String to interpolate.
	 * @param {Array}  args Rules to be applied.
	 *
	 * @return {string} Interpolatedd string.
	 */
	interpolate( str, args ) {
		return str.replace( /\$(\d{1,2})/g, ( match, index ) => args[ index ] || '' )
	}
}

export default Pluralize
