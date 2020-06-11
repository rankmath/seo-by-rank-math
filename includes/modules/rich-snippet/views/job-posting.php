<?php
/**
 * Metabox - Job Posting Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

use RankMath\Helper;

$jobposting = [ [ 'rank_math_rich_snippet', 'jobposting' ] ];

$cmb->add_field([
	'id'         => 'rank_math_snippet_jobposting_salary',
	'type'       => 'text',
	'name'       => esc_html__( 'Salary (Recommended)', 'rank-math' ),
	'desc'       => esc_html__( 'Insert amount, e.g. "50.00", or a salary range, e.g. "40.00-50.00".', 'rank-math' ),
	'classes'    => 'cmb-row-33 rank-math-validate-field',
	'dep'        => $jobposting,
	'attributes' => [
		'data-rule-regex'       => 'true',
		'data-validate-pattern' => '[\d -]+',
		'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 50000', 'rank-math' ),
	],
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_jobposting_currency',
	'type'       => 'text',
	'name'       => esc_html__( 'Salary Currency', 'rank-math' ),
	'desc'       => esc_html__( 'ISO 4217 Currency code. Example: EUR', 'rank-math' ),
	'classes'    => 'cmb-row-33 rank-math-validate-field',
	'attributes' => [
		'data-rule-regex'       => 'true',
		'data-validate-pattern' => '^[A-Z]{3}$',
		'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: EUR', 'rank-math' ),
	],
	'dep'        => $jobposting,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_jobposting_payroll',
	'type'    => 'select',
	'name'    => esc_html__( 'Payroll (Recommended)', 'rank-math' ),
	'desc'    => esc_html__( 'Salary amount is for', 'rank-math' ),
	'options' => [
		''      => esc_html__( 'None', 'rank-math' ),
		'YEAR'  => esc_html__( 'Yearly', 'rank-math' ),
		'MONTH' => esc_html__( 'Monthly', 'rank-math' ),
		'WEEK'  => esc_html__( 'Weekly', 'rank-math' ),
		'DAY'   => esc_html__( 'Daily', 'rank-math' ),
		'HOUR'  => esc_html__( 'Hourly', 'rank-math' ),
	],
	'classes' => 'cmb-row-33',
	'dep'     => $jobposting,
]);

$cmb->add_field([
	'id'          => 'rank_math_snippet_jobposting_startdate',
	'type'        => 'text_datetime_timestamp',
	'date_format' => 'Y-m-d',
	'name'        => esc_html__( 'Date Posted', 'rank-math' ),
	'desc'        => wp_kses_post( __( 'The original date on which employer posted the job. You can leave it empty to use the post publication date as job posted date.', 'rank-math' ) ),
	'classes'     => 'cmb-row-33',
	'dep'         => $jobposting,
]);

$cmb->add_field([
	'id'          => 'rank_math_snippet_jobposting_expirydate',
	'type'        => 'text_datetime_timestamp',
	'date_format' => 'Y-m-d',
	'name'        => esc_html__( 'Expiry Posted', 'rank-math' ),
	'desc'        => esc_html__( 'The date when the job posting will expire. If a job posting never expires, or you do not know when the job will expire, do not include this property.', 'rank-math' ),
	'classes'     => 'cmb-row-33',
	'dep'         => $jobposting,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_jobposting_unpublish',
	'type'    => 'toggle',
	'name'    => esc_html__( 'Unpublish when expired', 'rank-math' ),
	'desc'    => esc_html__( 'If checked, post status will be changed to Draft and its URL will return a 404 error, as required by the Rich Result guidelines.', 'rank-math' ),
	'classes' => 'cmb-row-33',
	'default' => 'on',
	'dep'     => $jobposting,
]);

$cmb->add_field([
	'id'                => 'rank_math_snippet_jobposting_employment_type',
	'type'              => 'multicheck_inline',
	'name'              => esc_html__( 'Employment Type (Recommended)', 'rank-math' ),
	'desc'              => esc_html__( 'Type of employment. You can choose more than one value.', 'rank-math' ),
	'options'           => [
		''           => esc_html__( 'None', 'rank-math' ),
		'FULL_TIME'  => esc_html__( 'Full Time', 'rank-math' ),
		'PART_TIME'  => esc_html__( 'Part Time', 'rank-math' ),
		'CONTRACTOR' => esc_html__( 'Contractor', 'rank-math' ),
		'TEMPORARY'  => esc_html__( 'Temporary', 'rank-math' ),
		'INTERN'     => esc_html__( 'Intern', 'rank-math' ),
		'VOLUNTEER'  => esc_html__( 'Volunteer', 'rank-math' ),
		'PER_DIEM'   => esc_html__( 'Per Diem', 'rank-math' ),
		'OTHER'      => esc_html__( 'Other', 'rank-math' ),
	],
	'dep'               => $jobposting,
	'select_all_button' => false,
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_jobposting_organization',
	'type'       => 'text',
	'name'       => esc_html__( 'Hiring Organization', 'rank-math' ),
	'desc'       => esc_html__( 'The name of the company. Leave empty to use your own company information.', 'rank-math' ),
	'attributes' => [
		'placeholder' => 'company' === Helper::get_settings( 'titles.knowledgegraph_type' ) ? Helper::get_settings( 'titles.knowledgegraph_name' ) : get_bloginfo( 'name' ),
	],
	'dep'        => $jobposting,
	'classes'    => 'cmb-row-50',
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_jobposting_id',
	'type'    => 'text',
	'name'    => esc_html__( 'Posting ID (Recommended)', 'rank-math' ),
	'desc'    => esc_html__( 'The hiring organization\'s unique identifier for the job. Leave empty to use the post ID.', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $jobposting,
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_jobposting_url',
	'type'       => 'text_url',
	'name'       => esc_html__( 'Organization URL (Recommended)', 'rank-math' ),
	'desc'       => esc_html__( 'The URL of the organization offering the job position. Leave empty to use your own company information.', 'rank-math' ),
	'classes'    => 'cmb-row-50 rank-math-validate-field',
	'attributes' => [
		'data-rule-url' => 'true',
	],
	'dep'        => $jobposting,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_jobposting_logo',
	'type'    => 'text_url',
	'name'    => esc_html__( 'Organization Logo (Recommended)', 'rank-math' ),
	'desc'    => esc_html__( 'Logo URL of the organization offering the job position. Leave empty to use your own company information.', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $jobposting,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_jobposting_address',
	'type'    => 'address',
	'name'    => esc_html__( 'Location', 'rank-math' ),
	'classes' => 'nob',
	'dep'     => $jobposting,
]);
