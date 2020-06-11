/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { BaseControl, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'

const Canonical = ( { canonicalUrl, placeholder, onUrlChange } ) => (
	<BaseControl className="rank-math-canonical">
		<span className="components-base-control__label">
			{ __( 'Canonical URL', 'rank-math' ) }
			<Tooltip>
				{ __(
					'The canonical URL informs search crawlers which page is the main page if you have double content',
					'rank-math'
				) }
			</Tooltip>
		</span>

		<TextControl
			type="url"
			autoComplete="off"
			value={ canonicalUrl }
			placeholder={ placeholder }
			onChange={ ( value ) => onUrlChange( value ) }
		/>
	</BaseControl>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		const dataCollector = rankMathEditor.assessor.dataCollector
		const placeholder = ( function() {
			if ( repo.getCanonicalUrl() ) {
				return repo.getCanonicalUrl()
			}

			if ( dataCollector.getPermalink() ) {
				return dataCollector.getPermalink()
			}

			return 'https://rankmath.com/'
		} )()

		return {
			placeholder,
			canonicalUrl: repo.getCanonicalUrl(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			onUrlChange( canonical ) {
				dispatch( 'rank-math' ).updateCanonicalUrl( canonical )
			},
		}
	} )
)( Canonical )
