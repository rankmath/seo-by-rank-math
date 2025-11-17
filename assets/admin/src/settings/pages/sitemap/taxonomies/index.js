/**
 * External dependencies
 */
import { forEach, includes, lowerCase, values } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import sitemapTaxonomyFields from './fields'

const { accessibleTaxonomies, choicesTaxonomyIcons } = rankMath.choices

/**
 * Add taxonomy tabs in the Sitemap Settings options panel.
 */
const taxonomySettings = () => {
	const hashLinks = {
		category: '#categories',
		post_tag: '#tag',
		product_cat: '#product-categories',
		product_tag: '#product-tags',
	}

	const taxonomyFields = {}
	const taxonomyPages = {
		t_types: {
			// Taxonomy label seprator.
			name: 't_types',
			title: __( 'Taxonomies:', 'rank-math' ),
			className: 'separator',
			disabled: true,
		},
	}

	forEach( values( accessibleTaxonomies ), ( { name, label, labels } ) => {
		if ( name === 'post_format' ) {
			return
		}

		const fields = sitemapTaxonomyFields( name )
		taxonomyFields[ name ] = fields

		const taxonomyName = lowerCase( name )
		const icon = choicesTaxonomyIcons[ name ] || choicesTaxonomyIcons.default

		const link = hashLinks[ name ]
			? getLink(
				'configure-sitemaps',
				`Options Panel Sitemap ${ labels?.name } Tab`
			) + hashLinks[ taxonomyName ]
			: getLink( 'configure-sitemaps' )

		const hashName = includes( [ 'product_cat', 'product_tag' ], name )
			? sprintf(
				/* translators: Taxonomy singular label */
				__( 'your product %s pages', 'rank-math' ),
				lowerCase( labels?.singular_name )
			)
			: sprintf(
				/* translators: Taxonomy singular label */
				__( '%s archives', 'rank-math' ),
				lowerCase( labels?.singular_name )
			)

		taxonomyPages[ 'sitemap-taxonomy-' + name ] = {
			fields,
			name: 'sitemap-taxonomy-' + name,
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
					__( 'Change Sitemap settings of %s.', 'rank-math' ),
					hashName
				),
				link,
			},
			className: `rank-math-sitemap-taxonomy-${ name }-tab rank-math-tab`,
		}
	} )

	return taxonomyPages
}

export default taxonomySettings()
