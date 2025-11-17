/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { TabPanel } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import { Button, TextControl } from '@rank-math/components'

const HelpPanelVideo = () => (
	<a
		target="_blank"
		rel="noreferrer"
		href={ getLink( 'how-to-setup-your-site', 'SW Your Site Setup KB' ) }
	>
		{ __( 'Click here to learn how to setup Rank Math properly', 'rank-math' ) }
	</a>
)

const HelpPanelKnowledge = () => {
	const [ searchValue, setSearchValue ] = useState( '' )

	const handleSubmit = ( event ) => {
		event.preventDefault()
		window.open(
			`${ getLink( 'kb-search', 'SW Your Site Search' ) }&q=${ searchValue }`,
			'_blank',
			'noreferrer'
		)
	}

	return (
		<form
			onSubmit={ handleSubmit }
			className="search-form wp-core-ui rank-math-ui"
		>
			<label htmlFor="rank-math-search-input">
				{ __(
					'Search the Knowledge Base for answers to your questions:',
					'rank-math'
				) }
			</label>

			<TextControl
				autoCorrect="off"
				autoComplete="off"
				autoCapitalize="none"
				variant="regular-text"
				spellCheck={ false }
				value={ searchValue }
				onChange={ setSearchValue }
				placeholder={ __( 'Type here to search…', 'rank-math' ) }
			/>

			<Button type="submit" variant="primary" disabled={ ! searchValue }>
				{ __( 'Search', 'rank-math' ) }
			</Button>
		</form>
	)
}

export default () => {
	// Generate tab title
	const generateTitle = ( icon, text ) => (
		<>
			<span className={ `rm-icon rm-icon-${ icon }` }></span>
			{ text }
		</>
	)

	const tabs = [
		{
			name: 'help-panel-video',
			title: generateTitle( 'video', __( 'Setup Tutorial', 'rank-math' ) ),
			view: HelpPanelVideo,
		},
		{
			name: 'help-panel-knowledge',
			title: generateTitle( 'post', __( 'Knowledge Base', 'rank-math' ) ),
			view: HelpPanelKnowledge,
		},
	]

	return (
		<TabPanel tabs={ tabs }>
			{ ( { name, view: View } ) => (
				<div className="rank-math-tabs-content rank-math-custom">
					<div id={ name } className="rank-math-tab">
						<View />
					</div>
				</div>
			) }
		</TabPanel>
	)
}
