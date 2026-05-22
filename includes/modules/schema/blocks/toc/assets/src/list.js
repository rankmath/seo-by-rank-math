/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button, TextControl } from '@wordpress/components'
import { RichText } from '@wordpress/block-editor'

export default function List( { headings = {}, onHeadingUpdate = {}, edit = {}, toggleEdit = {}, hideHeading = {}, ListStyle = 'ul', isSave = false } ) {
	if ( isEmpty( headings ) ) {
		return null
	}

	return (
		<>
			{ headings.map( ( heading ) => {
				if ( isSave && heading.heading.disable ) {
					return false
				}

				const { content, link, disable, key } = heading.heading
				const TagName = 'div' === ListStyle ? 'div' : 'li'
				return (
					<TagName key={ key } className={ disable ? 'disabled' : '' }>
						{
							isSave &&
							<a href={ link }>
								{ content }
							</a>
						}
						{
							! isSave &&
							<RichText
								tagName="a"
								value={ content }
								allowedFormats={ [] }
								onChange={ ( newContent ) => onHeadingUpdate( newContent, key, true ) }
								placeholder={ __( 'Heading text', 'seo-by-rank-math' ) }
							/>
						}
						{
							heading.children &&
							<ListStyle>
								<List
									headings={ heading.children }
									onHeadingUpdate={ onHeadingUpdate }
									edit={ edit }
									toggleEdit={ toggleEdit }
									hideHeading={ hideHeading }
									ListStyle={ ListStyle }
									isSave={ isSave }
								/>
							</ListStyle>
						}
						{
							key === edit &&
							<TextControl
								placeholder={ __( 'Heading Link', 'seo-by-rank-math' ) }
								value={ link }
								onChange={ ( newLink ) => onHeadingUpdate( newLink, key ) }
								__next40pxDefaultSize={ true }
								__nextHasNoMarginBottom={ true }
							/>
						}
						{
							! isSave &&
							<span className="rank-math-block-actions">
								<Button
									icon={ edit === key ? 'saved' : 'admin-links' }
									className="rank-math-item-visbility"
									onClick={ () => toggleEdit( edit === key ? false : key ) }
									title={ __( 'Edit Link', 'seo-by-rank-math' ) }
								/>

								<Button
									className="rank-math-item-delete"
									icon={ ! disable ? 'visibility' : 'hidden' }
									onClick={ () => hideHeading( ! disable, key ) }
									title={ __( 'Hide', 'seo-by-rank-math' ) }
								/>
							</span>
						}
					</TagName>
				)
			} ) }
		</>
	)
}
