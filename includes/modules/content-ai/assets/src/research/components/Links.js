/**
 * External dependencies
 */
import { forEach, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { PanelBody, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const Links = ( props ) => {
	const links = []
	if ( isEmpty( props.researchedData.links ) ) {
		return (
			<h3 className="no-data">
				{ __( 'There are no recommended Links for this researched keyword.', 'rank-math' ) }
			</h3>
		)
	}

	forEach( props.researchedData.links, ( value, index ) => (
		links.push(
			<li key={ index }>
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
						<Button className='is-link' href={ getLink( 'content-ai-links', 'Sidebar Links KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" label={ __( 'Know more about Links.', 'rank-math' ) } showTooltip={ true }>﹖</Button>
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
