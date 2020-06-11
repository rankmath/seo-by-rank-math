/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import Address from '@components/Address'
import { convertTimestamp } from '@helpers/time'
import DateTimePicker from '@components/DateTimePicker'

const JobPostingSnippet = ( props ) => {
	const jobpostingStartdate = convertTimestamp( props.jobpostingStartdate )
	const jobpostingExpirydate = convertTimestamp( props.jobpostingExpirydate )
	const jobpostingEmploymentType =
		'string' === typeof props.jobpostingEmploymentType
			? [ props.jobpostingEmploymentType ]
			: props.jobpostingEmploymentType

	return (
		<Fragment>
			<PanelBody initialOpen={ true }>
				{ props.jobpostingUnpublish &&
					'off' !== props.jobpostingUnpublish &&
					props.updateUnpublish( true ) }
				<TextControl
					label={ __( 'Salary (Recommended)', 'rank-math' ) }
					help={ __(
						'Insert amount, e.g. "50.00", or a salary range, e.g. "40.00-50.00".',
						'rank-math'
					) }
					value={ props.jobpostingSalary }
					onChange={ props.updateSalary }
				/>

				<TextControl
					label={ __( 'Salary Currency', 'rank-math' ) }
					help={ __(
						'ISO 4217 Currency code. Example: EUR',
						'rank-math'
					) }
					value={ props.jobpostingCurrency }
					onChange={ props.updateCurrency }
				/>

				<SelectControl
					label={ __( 'Payroll (Recommended)', 'rank-math' ) }
					help={ __( 'Salary amount is for', 'rank-math' ) }
					value={ props.jobpostingPayroll }
					options={ [
						{ value: '', label: __( 'None', 'rank-math' ) },
						{ value: 'YEAR', label: __( 'Yearly', 'rank-math' ) },
						{ value: 'MONTH', label: __( 'Monthly', 'rank-math' ) },
						{ value: 'WEEK', label: __( 'Weekly', 'rank-math' ) },
						{ value: 'DAY', label: __( 'Daily', 'rank-math' ) },
						{ value: 'HOUR', label: __( 'Hourly', 'rank-math' ) },
					] }
					onChange={ props.updatePayroll }
				/>

				<DateTimePicker
					value={ jobpostingStartdate }
					onChange={ props.updateStartdate }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'Date Posted', 'rank-math' ) }
						help={ __(
							'The original date on which employer posted the job. You can leave it empty to use the post publication date as job posted date.',
							'rank-math'
						) }
						value={ jobpostingStartdate }
						onChange={ props.updateStartdate }
					/>
				</DateTimePicker>

				<DateTimePicker
					value={ jobpostingExpirydate }
					onChange={ props.updateExpirydate }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'Expiry Posted', 'rank-math' ) }
						help={ __(
							'The date when the job posting will expire. If a job posting never expires, or you do not know when the job will expire, do not include this property.',
							'rank-math'
						) }
						value={ jobpostingExpirydate }
						onChange={ props.updateExpirydate }
					/>
				</DateTimePicker>

				<ToggleControl
					label={ __( 'Unpublish when expired', 'rank-math' ) }
					help={ __(
						'If checked, post status will be changed to Draft and its URL will return a 404 error, as required by the Rich Result guidelines.',
						'rank-math'
					) }
					checked={ props.jobpostingUnpublish }
					onChange={ props.updateUnpublish }
				/>

				<SelectControl
					multiple
					label={ __( 'Employment Type (Recommended)', 'rank-math' ) }
					help={ __(
						'Type of employment. You can choose more than one value.',
						'rank-math'
					) }
					value={ jobpostingEmploymentType }
					options={ [
						{
							value: '',
							label: __( 'None', 'rank-math' ),
						},
						{
							value: 'FULL_TIME',
							label: __( 'Full Time', 'rank-math' ),
						},
						{
							value: 'PART_TIME',
							label: __( 'Part Time', 'rank-math' ),
						},
						{
							value: 'CONTRACTOR',
							label: __( 'Contractor', 'rank-math' ),
						},
						{
							value: 'TEMPORARY',
							label: __( 'Temporary', 'rank-math' ),
						},
						{
							value: 'INTERN',
							label: __( 'Intern', 'rank-math' ),
						},
						{
							value: 'VOLUNTEER',
							label: __( 'Volunteer', 'rank-math' ),
						},
						{
							value: 'PER_DIEM',
							label: __( 'Per Diem', 'rank-math' ),
						},
						{ value: 'OTHER', label: __( 'Other', 'rank-math' ) },
					] }
					onChange={ props.updateEmploymentType }
				/>

				<TextControl
					label={ __( 'Hiring Organization', 'rank-math' ) }
					help={ __(
						'The name of the company. Leave empty to use your own company information.',
						'rank-math'
					) }
					value={ props.jobpostingOrganization }
					onChange={ props.updateOrganization }
				/>

				<TextControl
					label={ __( 'Posting ID (Recommended)', 'rank-math' ) }
					help={ __(
						"The hiring organization's unique identifier for the job. Leave empty to use the post ID.",
						'rank-math'
					) }
					value={ props.jobpostingId }
					onChange={ props.updateId }
				/>

				<TextControl
					type="url"
					label={ __(
						'Organization URL (Recommended)',
						'rank-math'
					) }
					help={ __(
						'The URL of the organization offering the job position. Leave empty to use your own company information.',
						'rank-math'
					) }
					value={ props.jobpostingUrl }
					onChange={ props.updateUrl }
				/>

				<TextControl
					type="url"
					label={ __(
						'Organization Logo (Recommended)',
						'rank-math'
					) }
					help={ __(
						'Logo URL of the organization offering the job position. Leave empty to use your own company information.',
						'rank-math'
					) }
					value={ props.jobpostingLogo }
					onChange={ props.updateLogo }
				/>
			</PanelBody>

			<Address
				label={ __( 'Location', 'rank-math' ) }
				value={
					'' !== props.jobpostingAddress
						? props.jobpostingAddress
						: {}
				}
				onChange={ props.updateAddress }
			/>
		</Fragment>
	)
}

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			jobpostingSalary: data.jobpostingSalary,
			jobpostingCurrency: data.jobpostingCurrency,
			jobpostingPayroll: data.jobpostingPayroll,
			jobpostingStartdate: data.jobpostingStartdate,
			jobpostingExpirydate: data.jobpostingExpirydate,
			jobpostingUnpublish:
				data.jobpostingUnpublish && 'off' !== data.jobpostingUnpublish,
			jobpostingEmploymentType: data.jobpostingEmploymentType,
			jobpostingOrganization: data.jobpostingOrganization,
			jobpostingId: data.jobpostingId,
			jobpostingUrl: data.jobpostingUrl,
			jobpostingLogo: data.jobpostingLogo,
			jobpostingAddress: data.jobpostingAddress,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateSalary( salary ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingSalary',
					'jobposting_salary',
					salary
				)
			},

			updateCurrency( currency ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingCurrency',
					'jobposting_currency',
					currency
				)
			},

			updatePayroll( payroll ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingPayroll',
					'jobposting_payroll',
					payroll
				)
			},

			updateStartdate( date ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingStartdate',
					'jobposting_startdate',
					date
				)
			},

			updateExpirydate( date ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingExpirydate',
					'jobposting_expirydate',
					date
				)
			},

			updateUnpublish( unpublish ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingUnpublish',
					'jobposting_unpublish',
					true === unpublish ? 'on' : 'off'
				)
			},

			updateEmploymentType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingEmploymentType',
					'jobposting_employment_type',
					type
				)
			},

			updateOrganization( name ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingOrganization',
					'jobposting_organization',
					name
				)
			},

			updateId( id ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingId',
					'jobposting_id',
					id
				)
			},

			updateUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingUrl',
					'jobposting_url',
					url
				)
			},

			updateLogo( logo ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingLogo',
					'jobposting_logo',
					logo
				)
			},

			updateAddress( address ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'jobpostingAddress',
					'jobposting_address',
					address
				)
			},
		}
	} )
)( JobPostingSnippet )
