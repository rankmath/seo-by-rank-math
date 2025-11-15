/**
 * External dependencies
 */
import { entries, forEach, join, values } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import titlesTaxonomyFields from './fields'

/**
 * Add taxonomy tabs in the Title Settings panel.
 */
const taxonomySettings = () => {
	const icons = rankMath.choices.choicesTaxonomyIcons

	const hashName = {
		category: 'category archive pages',
		product_cat: 'Product category pages',
		product_tag: 'Product tag pages',
	}

	const hashLink = {
		category: getLink( 'category-settings', 'Options Panel Meta Categories Tab' ),
		post_tag: getLink( 'tag-settings', 'Options Panel Meta Tags Tab' ),
		product_cat: getLink( 'product-categories-settings', 'Options Panel Meta Product Categories Tab' ),
		product_tag: getLink( 'product-tags-settings', 'Options Panel Meta Product Tags Tab' ),
	}

	const taxonomiesData = {}
	forEach( values( rankMath.choices.accessibleTaxonomies ), ( taxonomy ) => {
		const attached = join( values( taxonomy.object_type ), ' + ' )

		if ( ! taxonomiesData[ attached ] ) {
			taxonomiesData[ attached ] = {}
		}

		taxonomiesData[ attached ][ taxonomy.name ] = taxonomy
	} )

	const taxonomyPages = {}
	const taxonomyFields = {}

	forEach( entries( taxonomiesData ), ( [ attached, taxonomies ] ) => {
		taxonomyPages[ attached ] = {
			// Taxonomy label seprator.
			name: `${ attached }_taxonomies`,
			title: attached + ':',
			className: 'separator',
			disabled: true,
		}

		forEach( values( taxonomies ), ( { name, label, labels } ) => {
			if ( name === 'post_format' ) {
				return
			}

			const fields = titlesTaxonomyFields( name, labels )
			taxonomyFields[ name ] = fields

			const link = hashLink[ name ] || ''
			const taxonomyName = hashName[ name ] || label
			const icon = icons[ name ] || icons.default

			taxonomyPages[ 'taxonomy-' + name ] = {
				fields,
				name: 'taxonomy-' + name,
				title: (
					<>
						<i className={ icon }></i>
						{ label }
					</>
				),
				header: {
					title: label,
					description: sprintf(
						/* translators: 1. taxonomy name */
						__(
							'Change Global SEO, Schema, and other settings for %s.',
							'rank-math'
						),
						taxonomyName
					),
					link,
				},
			}
		} )
	} )

	return taxonomyPages
}

export default taxonomySettings()
