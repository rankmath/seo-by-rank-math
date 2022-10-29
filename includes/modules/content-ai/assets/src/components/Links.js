/**
 * External dependencies
 */
import { forEach, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { PanelBody } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const Links = ( props ) => {
	const links = []
	if ( isEmpty( props.caData.data.links ) ) {
		return (
			<h3 className="no-data">
				{ __( 'There are no recommended Links for this researched keyword.', 'rank-math' ) }
			</h3>
		)
	}

	forEach( props.caData.data.links, ( value ) => (
		links.push(
			<li>
				<a href={ value } rel="noreferrer" target="_blank">{ value }</a>
			</li>
		)
	) )
	return (
		<Fragment>
			<PanelBody initialOpen={ true }>
				<div className="rank-math-section-heading">
					<h2>
						{ __( 'Related External Links', 'rank-math' ) }
						<a href={ getLink( 'content-ai-links', 'Sidebar Links KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" title={ __( 'Know more about Links.', 'rank-math' ) }>﹖</a>
					</h2>
					<p>{ __( 'Use some of these external links in the content area. It is recommended to add', 'rank-math' ) } <a href={ getLink( 'about-and-mentions-schema', 'Use Some External Links' ) } rel="noreferrer" target="_blank">{ __( 'about or mention Schema.', 'rank-math' ) }</a></p>
				</div>
				<ul>
					{ links }
				</ul>
			</PanelBody>
		</Fragment>
	)
}

export default Links
