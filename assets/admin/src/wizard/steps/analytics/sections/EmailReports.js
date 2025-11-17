/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { createElement } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { ToggleControl, SelectControl } from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( { data, updateData } ) => {
	if ( ! data.showEmailReports ) {
		return
	}

	const tagName = rankMath.isSettingsPage ? 'h3' : 'h1'

	return (
		<>
			<div className={ `field-row email-reports-header ${ rankMath.isSettingsPage ? '' : 'text-center' }` }>
				{ createElement( tagName, null, __( 'Email Reports', 'rank-math' ) ) }

				{
					rankMath.isSettingsPage ? (
						<div>
							{ __( 'Receive periodic SEO Performance reports via email. Once enabled and options are saved, you can see', 'rank-math' ) }
							{ ' ' }
							<a
								target="_blank"
								rel="noreferrer"
								href={ `${rankMath.homeUrl}/?rank_math_analytics_report_preview=1` }
							>
								{ __( 'the preview here', 'rank-math' ) }
							</a>
						</div>
					) : (
						<div>
							{ __( 'Receive Analytics reports periodically in email.', 'rank-math' ) }
							{ ' ' }
							<a
								target="_blank"
								rel="noreferrer"
								href={ getLink( 'seo-email-reporting', 'SW Analytics Step' ) }
							>
								{ __( 'Learn more about Email Reports.', 'rank-math' ) }
							</a>
						</div>
					)
				}				
			</div>

			<div className="field-row field-type-toggle field-id-console-email-reports">
				<div className="field-th">
					<label htmlFor="console_email_reports">
						{ __( 'Email Reports', 'rank-math' ) }
					</label>
				</div>

				<div className="field-td">
					<ToggleControl
						id="console_email_reports"
						checked={ data.console_email_reports }
						onChange={ ( isChecked ) => {
							updateData( 'console_email_reports', isChecked )
						} }
					/>

					{
						rankMath.isSettingsPage && (
							<div className="field-description">
								{ __( 'Turn on email reports.', 'rank-math' ) }
							</div>
						)
					}
				</div>
			</div>

			{
				rankMath.isSettingsPage && data.console_email_reports && (
					<div className="field-row field-type-text field-id-console_email_frequency">
						<div className="field-th">
							<label htmlFor="console_email_frequency">
								{ __( 'Email Frequency', 'rank-math' ) }
								{ ! rankMath.isPro && (
									<span className="rank-math-pro-badge">
										<a
											href={ getLink( 'seo-email-reporting', 'Email Frequency Toggle' ) }
											target="_blank"
											rel="noopener noreferrer"
										>
											{ __( 'PRO', 'rank-math' ) }
										</a>
									</span>
								) }
							</label>
						</div>
						<div className="field-td">
							{
								! rankMath.isPro && (
									<SelectControl
										value=''
										options={
											{
												'monthly': __( 'Every 30 days', 'rank-math' ),
											}
										}
										label=''
										disabled={ true }
									/>
								)
							}

							{ applyFilters( 'rank_math_analytics_console_email_frequency', '', data, updateData ) }

							<div className="field-description">
								{ __( 'Email report frequency.', 'rank-math' ) }
							</div>
						</div>
					</div>
				)
			}

			{ applyFilters( 'rank_math_analytics_options_email_report', '', data, updateData ) }
		</>
	)
}
