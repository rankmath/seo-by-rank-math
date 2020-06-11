<?php
/**
 * Search console options.
 *
 * @package Rank_Math
 */

use RankMath\KB;
use RankMath\Helper;
use MyThemeShop\Helpers\Str;
use RankMath\Search_Console\DB;
use RankMath\Search_Console\Data_Fetcher;

$data           = Helper::search_console_data();
$db_info        = DB::info();
$is_empty       = DB::is_empty();
$is_queue_empty = Data_Fetcher::get()->is_empty();

$primary   = '<button type="button" class="button button-primary rank-math-authorize-account">' . ( $data['authorized'] ? esc_html__( 'De-authorize Account', 'rank-math' ) : esc_html__( 'Authorize', 'rank-math' ) ) . '</button>';
$secondary = '<a href="' . esc_url( Helper::get_console_auth_url() ) . '" class="button button-primary rank-math-get-authorization-code"' . ( $data['authorized'] ? ' style="display:none;"' : '' ) . '>' . esc_html__( 'Get Authorization Code', 'rank-math' ) . '</a><br />';
/* translators: Link to 'How To Create a Google API Project For Connecting Search Console' KB article */
$desc = '<div class="cmb2-metabox-description">' . sprintf( esc_html__( 'You can also create your own app and connect that instead. %s', 'rank-math' ), '<a href="' . KB::get( 'custom-gsc-project' ) . '" target="_blank">' . esc_html__( 'Follow this tutorial.', 'rank-math' ) . '</a>' ) . '</div>';
$cmb->add_field(
	[
		'id'              => 'console_authorization_code',
		'type'            => 'text',
		'name'            => esc_html__( 'Search Console', 'rank-math' ),
		'attributes'      => [ 'data-authorized' => $data['authorized'] ? 'true' : 'false' ],
		'after_field'     => $primary . $secondary . $desc,
		'classes'         => $data['authorized'] ? 'authorized' : 'unauthorized',
		'sanitization_cb' => '__return_empty_string', // Don't save this.
	]
);

$profile       = Helper::get_settings( 'general.console_profile' );
$profile_label = str_replace( 'sc-domain:', __( 'Domain Property: ', 'rank-math' ), $profile );
foreach ( $data['profiles'] as $key => $value ) {
	$data['profiles'][ $key ] = str_replace( 'sc-domain:', __( 'Domain Property: ', 'rank-math' ), $value );
}
if ( ! $data['authorized'] ) {
	$profile = false;
}

$cmb->add_field(
	[
		'id'           => 'console_profile',
		'type'         => 'select',
		'name'         => esc_html__( 'Search Console Profile', 'rank-math' ),
		'desc'         => esc_html__( 'After authenticating with Google Search Console, select the site from the dropdown list.', 'rank-math' ) .
			' <span id="gsc-dp-info" class="hidden">' . __( 'Please note that the Sitemaps overview in the Search Console module will not be available when using a Domain Property.', 'rank-math' ) . '</span>' .
			/* translators: setting url */
			'<br><br><span style="color: orange;">' . sprintf( __( 'Is your site not listed? <a href="%1$s" target="_blank">Click here</a> to get your website verified.', 'rank-math' ), Helper::get_admin_url( 'options-general#setting-panel-webmaster' ) ) . '</span>',
		'options'      => $profile ? [ $profile => $profile_label ] : $data['profiles'],
		'default'      => $profile,
		'before_field' => '<button class="button button-primary hidden rank-math-refresh" ' . ( $data['authorized'] ? '' : 'disabled="disabled"' ) . '>' . esc_html__( 'Refresh Sites', 'rank-math' ) . '</button>',
		'attributes'   => $data['authorized'] ? [ 'data-s2' => '' ] : [
			'disabled' => 'disabled',
			'data-s2'  => '',
		],
	]
);

if ( $is_empty ) {
	$cmb->add_field(
		[
			'id'      => 'console_data_empty',
			'type'    => 'notice',
			'what'    => 'error',
			'classes' => 'rank-math-notice',
			/* translators: date */
			'content' => sprintf( __( 'The data sets are empty in your cache. You can wait for the next cronjob (%s) or <strong>Update Manually</strong>.', 'rank-math' ), date_i18n( 'd/m/Y 00:00:00', strtotime( '+1 days' ) ) ) .
			'<p class="note">' . __( '<strong>Note:</strong> Please update your data by clicking on \'Update Manually\'. Google only stores data from the last 90 days - a dataset older than that can\'t be updated anymore.', 'rank-math' ) . '</p>',
		]
	);
}

$disable = ( ! $data['authorized'] || ! $is_queue_empty ) ? true : false;

$cmb->add_field(
	[
		'id'          => 'console_caching_control',
		'type'        => 'text',
		'name'        => __( 'Cache Limit <br><small>Days to keep data rows in cache</small>', 'rank-math' ),
		'default'     => 90,
		'after_field' => '<br>' .
		'<button class="button button-small console-cache-delete"  data-days="90">' . esc_html__( 'Delete Recent Cache (last 90 days)', 'rank-math' ) . '</button>' .
		'&nbsp;&nbsp;<button class="button button-small console-cache-delete" data-days="-1">' . esc_html__( 'Delete Cache', 'rank-math' ) . '</button>' .
		'&nbsp;&nbsp;<button class="button button-small console-cache-update-manually"' . ( $disable ? ' disabled="disabled"' : '' ) . '>' . ( $is_queue_empty ? esc_html__( 'Update Cache manually', 'rank-math' ) : esc_html__( 'Fetching in Progress', 'rank-math' ) ) . '</button><br>' .
		/* translators: number of days */
		'<div class="rank-math-console-db-info"><i class="rm-icon rm-icon-calendar"></i> ' . sprintf( esc_html__( 'Cached Days: %s', 'rank-math' ), '<strong>' . $db_info['days'] . '</strong>' ) . '</div>' .
		/* translators: number of rows */
		'<div class="rank-math-console-db-info"><i class="rm-icon rm-icon-faq"></i> ' . sprintf( esc_html__( 'Data Rows: %s', 'rank-math' ), '<strong>' . Str::human_number( $db_info['rows'] ) . '</strong>' ) . '</div>' .
		/* translators: database size */
		'<div class="rank-math-console-db-info"><i class="rm-icon rm-icon-database"></i> ' . sprintf( esc_html__( 'Size: %s', 'rank-math' ), '<strong>' . size_format( $db_info['size'] ) . '</strong>' ) . '</div>',
	]
);
