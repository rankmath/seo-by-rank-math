/**
 * External Dependencies
 */
import { isEmpty, includes } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { RawHTML, useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import Tooltip from '../others/Tooltip'
import Button from '../buttons/Button'
import getStatus from './helpers/getStatus'
import getContent from './helpers/getContent'

export default ( { result } ) => {
	const [ showHowToFix, setShowHowToFix ] = useState( false )

	const { status, title, tooltip, kb_link: kbLink, fix, message, data } = result

	const readMoreLink = kbLink || 'https://rankmath.com/kb/seo-analysis'

	const hasFix = includes( [ 'fail', 'warning' ], status ) && fix

	return (
		<>
			<div className="row-title">
				{ result && getStatus( status ) }

				<h3>
					<span dangerouslySetInnerHTML={ { __html: title } }></span>

					{ tooltip && (
						<Tooltip text={ tooltip }>
							<a href={ kbLink } target="_blank" rel="noreferrer">
								<em className="dashicons-before dashicons-editor-help" />
							</a>
						</Tooltip>
					) }
				</h3>
			</div>

			<div className="row-description">
				<div className="row-content">
					{ hasFix && (
						<Button
							variant="secondary"
							size="small"
							className="result-action"
							onClick={ () => setShowHowToFix( ! showHowToFix ) }
						>
							{ __( 'How to fix', 'rank-math' ) }
						</Button>
					) }

					<RawHTML>{ message }</RawHTML>

					{ showHowToFix && (
						<div className="how-to-fix-wrapper">
							<div className="analysis-test-how-to-fix">
								<RawHTML>{ fix }</RawHTML>

								{ ! /<\/a><\/p>$/i.test( fix.trim() ) && (
									<p>
										<Button
											variant="link"
											href={ readMoreLink }
											target="_blank"
											className="analysis-read-more"
											rel="noreferrer"
										>
											{ __( 'Read more', 'rank-math' ) }
										</Button>
									</p>
								) }
							</div>
						</div>
					) }

					<div className="clear" />

					{ data && ! isEmpty( data ) && getContent( result ) }
				</div>
			</div>
		</>
	)
}
