/**
 * External dependencies
 */
import jQuery from 'jquery'
import { forEach, isArray, isObject, isString, isUndefined, map, get, has, find } from 'lodash'

/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data'
import { applyFilters, addAction, addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import {
	generateValidSchema,
	generateValidSchemaByMap,
	getSchemaFromData,
	getPropertyDefault,
} from '@schema/functions'

/**
 * Check if value is scalar.
 *
 * @param  {Object|string} value Value to check.
 * @return {Object} Converted value.
 */
const maybeScalarValue = ( value ) => {
	if ( isObject( value ) ) {
		return value
	}

	return [ value ]
}

/**
 * Register hooks to process values and maps.
 */
const registerDefaultHooks = () => {
	jQuery( function( $ ) {
		const clipboard = new ClipboardJS( '.structured-data-copy' )

		// Debug information copy section.
		clipboard.on( 'success', function( e ) {
			const button = $( e.trigger )
			button.addClass( 'copied' )

			setTimeout( function() {
				button.removeClass( 'copied' )
			}, 2000 )
		} )
	} )

	addAction(
		'rank_math_loaded',
		'rank-math',
		() => {
			const editSchemas = {}
			const schemas = get( rankMath, 'schemas', {} )
			map( schemas, ( schema, id ) => {
				const type = get( schema, '@type' )
				schema = applyFilters( 'rank_math_pre_schema_' + type, schema )
				editSchemas[ id ] = generateValidSchema(
					applyFilters( 'rank_math_pre_schema', schema )
				)
			} )

			dispatch( 'rank-math' ).updateEditSchemas( editSchemas )
		}
	)

	/**
	 * Filter function to change schema type value to match it with the @type value from schemaMap.
	 *
	 * @param {string} type Schema type.
	 */
	addFilter(
		'rank_math_schema_type',
		'rank-math',
		( type ) => {
			if ( isUndefined( type ) ) {
				return type
			}

			if ( 'NewsArticle' === type || 'BlogPosting' === type ) {
				return 'Article'
			}

			if ( 'MusicGroup' === type || 'MusicAlbum' === type ) {
				return 'Music'
			}

			if ( type.includes( 'Event' ) || 'Festival' === type ) {
				return 'Event'
			}

			return type
		}
	)

	/**
	 * Filter function to convert author property to Object. This code is needed for backward compatibility where the author in some schema is stored as a string.
	 *
	 * @param {boolean}      check    The property value.
	 * @param {Object}       property Property to process.
	 * @param {Array|string} value    Property value.
	 */
	addFilter(
		'rank_math_schema_convert_author',
		'rank-math',
		( check, property, value ) => {
			if ( isObject( value ) ) {
				return check
			}

			if ( isUndefined( property.properties ) ) {
				return check
			}

			property.properties[ 1 ].value = value

			return property
		}
	)

	// Check for array values.
	addFilter(
		'rank_math_schema_convert_value',
		'rank-math',
		( check, property, key, value, isCustom = false ) => {
			if ( ! property.map.isArray ) {
				return check
			}

			const { arrayMap = false, arrayProps = {} } = property.map
			if ( arrayMap ) {
				forEach( value, ( newValue ) => {
					property.properties.push( generateValidSchemaByMap( newValue, arrayMap, arrayProps, isCustom ) )
				} )
				return property
			}

			value = maybeScalarValue( value )
			forEach( value, ( newValue, newKey ) => {
				const type = get( newValue, '@type', false )
				if ( false !== type ) {
					property.properties.push( generateValidSchemaByMap( newValue, type, arrayProps, isCustom ) )
					return
				}

				const newProperty = getPropertyDefault()

				newProperty.property = newKey
				newProperty.value = newValue
				property.properties.push( newProperty )
			} )

			return property
		}
	)

	// Check for array values.
	addFilter(
		'rank_math_schema_convert_value',
		'rank-math',
		( check, property, key, value, isCustom = false ) => {
			if ( property.map.isArray || ! property.map.isGroup || isCustom ) {
				return check
			}

			return getSchemaFromData( property, value, isCustom )
		},
		20
	)

	/**
	 * Filter function to change Brand from string to Object. This code is needed for backward compatibility where the Brand value was stored as a string.
	 *
	 * @param {Object} schema Product schema.
	 */
	addFilter(
		'rank_math_pre_schema_Product',
		'rank-math',
		( schema ) => {
			if ( has( schema, 'brand' ) && ! isObject( schema.brand ) ) {
				schema.brand = {
					'@type': 'Brand',
					name: schema.brand,
				}
			}

			return schema
		}
	)

	/**
	 * Filter function change the recipe instructions value before storing it in Database.
	 *
	 * @param {Object} schema Recipe schema.
	 */
	addFilter(
		'rank_math_processed_schema_Recipe',
		'rank-math',
		( schema ) => {
			const { instructionType } = schema
			delete schema.instructionType

			if ( 'SingleField' === instructionType ) {
				schema.recipeInstructions = schema.instructionsSingleField
				delete schema.instructionsSingleField
			}

			if ( 'HowToStep' === instructionType ) {
				if ( 1 === schema.instructionsHowToStep.length ) {
					schema.recipeInstructions = {
						'@type': 'HowToSection',
						name: schema.instructionsHowToStep[ 0 ].name,
						itemListElement: schema.instructionsHowToStep[ 0 ].itemListElement,
					}
				}

				if ( schema.instructionsHowToStep.length > 1 ) {
					schema.recipeInstructions = [ ...schema.instructionsHowToStep ]
				}

				delete schema.instructionsHowToStep
			}

			return schema
		},
		20
	)

	/**
	 * Filter function to conver the recipe instructions data before adding it in Schema Generator.
	 *
	 * @param {Object} schema Recipe schema.
	 */
	addFilter(
		'rank_math_pre_schema_Recipe',
		'rank-math',
		( schema ) => {
			const { recipeInstructions } = schema

			// Single.
			if ( isString( recipeInstructions ) ) {
				schema.instructionType = 'SingleField'
				schema.instructionsSingleField = recipeInstructions
				delete schema.recipeInstructions
			}

			forEach( [ 'cookTime', 'prepTime', 'totalTime' ], ( key ) => {
				if ( ! isUndefined( schema[ key ] ) && 'PT' === schema[ key ] ) {
					delete schema[ key ]
				}
			} )

			// Multiple.
			if ( isArray( recipeInstructions ) ) {
				schema.instructionType = 'HowToStep'
				forEach( recipeInstructions, ( instruction, key ) => {
					if ( ! isUndefined( instruction.type ) ) {
						instruction[ '@type' ] = instruction.type
						delete instruction.type
					}

					if ( ! isUndefined( instruction.itemListElement ) ) {
						if ( ! isUndefined( instruction.itemListElement.type ) ) {
							instruction.itemListElement[ '@type' ] = instruction.itemListElement.type
							delete instruction.itemListElement.type
						}

						if ( ! isArray( instruction.itemListElement ) ) {
							instruction.itemListElement = [ instruction.itemListElement ]
						}
					}

					recipeInstructions[ key ] = instruction
				} )

				schema.instructionsHowToStep = recipeInstructions
				delete schema.recipeInstructions
			}

			if ( ! isArray( recipeInstructions ) && isObject( recipeInstructions ) ) {
				const newStep = {
					...recipeInstructions,
					'@type': 'HowToSection',
				}

				schema.instructionType = 'HowToStep'
				schema.instructionsHowToStep = []
				schema.instructionsHowToStep.push( newStep )
				delete schema.recipeInstructions
			}

			return schema
		}
	)

	/**
	 * Filter to change event location to array.
	 *
	 * @param {Object} schema Event schema.
	 */
	addFilter(
		'rank_math_processed_schema',
		'rank-math',
		( schema ) => {
			const { eventAttendanceMode } = schema
			if ( isUndefined( eventAttendanceMode ) ) {
				return schema
			}

			if ( 'MixedEventAttendanceMode' === eventAttendanceMode ) {
				schema.location = [
					schema.VirtualLocation,
					schema.location,
				]

				delete schema.VirtualLocation
			}

			if ( 'OnlineEventAttendanceMode' === eventAttendanceMode ) {
				schema.location = schema.VirtualLocation
				delete schema.VirtualLocation
			}

			return schema
		},
		20
	)

	/**
	 * Filter to change event location to string.
	 *
	 * @param {Object} schema Event schema.
	 */
	addFilter(
		'rank_math_pre_schema',
		'rank-math',
		( schema ) => {
			const { eventAttendanceMode } = schema
			if ( isUndefined( eventAttendanceMode ) ) {
				return schema
			}

			if ( 'MixedEventAttendanceMode' === eventAttendanceMode ) {
				schema.VirtualLocation = find( schema.location, [ '@type', 'VirtualLocation' ] )
				schema.location = find( schema.location, [ '@type', 'Place' ] )
			}

			if ( 'OnlineEventAttendanceMode' === eventAttendanceMode ) {
				schema.VirtualLocation = schema.location
				delete schema.location
			}

			return schema
		}
	)
}

export default registerDefaultHooks
