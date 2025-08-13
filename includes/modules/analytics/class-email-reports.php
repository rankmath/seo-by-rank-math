<?php
/**
 * Analytics Email Reports.
 *
 * @since      1.0.68
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Google\Console;
use RankMath\Admin\Admin_Helper;

use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Email_Reports class.
 */
class Email_Reports {

	use Hooker;

	/**
	 * Email content variables.
	 *
	 * @var array
	 */
	private $variables = [];

	/**
	 * Path to the views directory.
	 *
	 * @var array
	 */
	private $views_path = '';

	/**
	 * URL to the assets directory.
	 *
	 * @var array
	 */
	private $assets_url = '';

	/**
	 * Charts Account.
	 *
	 * @var string
	 */
	private $charts_account = 'rankmath';

	/**
	 * Charts Key.
	 *
	 * @var string
	 */
	private $charts_key = '10042B42-9193-428A-ABA7-5753F3370F84';

	/**
	 * Graph data.
	 *
	 * @var array
	 */
	private $graph_data = [];

	/**
	 * Debug mode.
	 *
	 * @var boolean
	 */
	private $debug = false;

	/**
	 * The constructor.
	 */
	public function __construct() {
		if ( ! Console::is_console_connected() ) {
			return;
		}

		$directory        = __DIR__;
		$this->views_path = $directory . '/views/email-reports/';

		$url              = plugin_dir_url( __FILE__ );
		$this->assets_url = $this->do_filter( 'analytics/email_report_assets_url', $url . 'assets/' );

		$this->hooks();
	}

	/**
	 * Add filter & action hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		$this->action( 'rank_math/analytics/email_report_event', 'email_report' );
		$this->action( 'wp_loaded', 'maybe_debug' );

		$this->action( 'rank_math/analytics/email_report_html', 'replace_variables' );
		$this->action( 'rank_math/analytics/email_report_html', 'strip_comments' );
	}

	/**
	 * Send Analytics report or error message.
	 *
	 * @return void
	 */
	public function email_report() {
		$this->setup_variables();
		$this->send_report();
	}

	/**
	 * Collect variables to be used in the Report template.
	 *
	 * @return void
	 */
	public function setup_variables() {
		$stats = $this->get_stats();
		$date  = $this->get_date();

		// Translators: placeholder is "rankmath.com" as a link.
		$footer_text  = sprintf( esc_html__( 'This email was sent to you as a registered member of %s.', 'rank-math' ), '<a href="###SITE_URL###">###SITE_URL_SIMPLE###</a>' );
		$footer_text .= ' ';

		// Translators: placeholder is "click here" as a link.
		$footer_text .= sprintf( esc_html__( 'To update your email preferences, %s.', 'rank-math' ), '<a href="###SETTINGS_URL###">' . esc_html__( 'click here', 'rank-math' ) . '</a>' );

		$footer_text .= '###ADDRESS###';

		$this->variables = [
			'site_url'                    => get_home_url(),
			'site_url_simple'             => explode( '://', get_home_url() )[1],
			'settings_url'                => Helper::get_settings_url( 'general', 'analytics' ),
			'report_url'                  => Helper::get_admin_url( 'analytics' ),
			'assets_url'                  => $this->assets_url,
			'address'                     => '<br/> [rank_math_contact_info show="address"]',
			'logo_link'                   => KB::get( 'email-reports-logo', 'Email Report Logo' ),

			'period_days'                 => $date['period'],
			'start_date'                  => $date['start'],
			'end_date'                    => $date['end'],

			'stats_clicks'                => $stats['clicks'],
			'stats_clicks_diff'           => $stats['clicks_diff'],
			'stats_traffic'               => $stats['traffic'],
			'stats_traffic_diff'          => $stats['traffic_diff'],
			'stats_impressions'           => $stats['impressions'],
			'stats_impressions_diff'      => $stats['impressions_diff'],
			'stats_keywords'              => $stats['keywords'],
			'stats_keywords_diff'         => $stats['keywords_diff'],
			'stats_position'              => $stats['position'],
			'stats_position_diff'         => $stats['position_diff'],
			'stats_top_3_positions'       => $stats['top_3_positions'],
			'stats_top_3_positions_diff'  => $stats['top_3_positions_diff'],
			'stats_top_10_positions'      => $stats['top_10_positions'],
			'stats_top_10_positions_diff' => $stats['top_10_positions_diff'],
			'stats_top_50_positions'      => $stats['top_50_positions'],
			'stats_top_50_positions_diff' => $stats['top_50_positions_diff'],
			'stats_invalid_data'          => $stats['invalid_data'],
			'footer_html'                 => $footer_text,
		];

		$this->variables = $this->do_filter( 'analytics/email_report_variables', $this->variables );
	}

	/**
	 * Get date data.
	 *
	 * @return array
	 */
	public function get_date() {
		$period = self::get_period_from_frequency();

		// Shift 3 days prior.
		$subtract = DAY_IN_SECONDS * 3;
		$start    = strtotime( '-' . $period . ' days' ) - $subtract;
		$end      = strtotime( $this->do_filter( 'analytics/report_end_date', 'today' ) ) - $subtract;

		$start = date_i18n( 'd M Y', $start );
		$end   = date_i18n( 'd M Y', $end );
		return compact( 'start', 'end', 'period' );
	}

	/**
	 * Get Analytics stats.
	 *
	 * @return array
	 */
	public function get_stats() {
		$period = self::get_period_from_frequency();
		$stats  = Stats::get();
		$stats->set_date_range( "-{$period} days" );

		// Basic stats.
		$data = (array) $stats->get_analytics_summary();

		$analytics              = get_option( 'rank_math_google_analytic_options' );
		$is_analytics_connected = ! empty( $analytics ) && ! empty( $analytics['view_id'] );

		$out = [];

		$out['impressions']      = $data['impressions']['total'];
		$out['impressions_diff'] = $data['impressions']['difference'];

		$out['traffic']      = 0;
		$out['traffic_diff'] = 0;
		if ( $is_analytics_connected && defined( 'RANK_MATH_PRO_FILE' ) && isset( $data['pageviews'] ) ) {
			$out['traffic']      = $data['pageviews']['total'];
			$out['traffic_diff'] = $data['pageviews']['difference'];
		}

		$out['clicks']      = 0;
		$out['clicks_diff'] = 0;
		if ( ! $is_analytics_connected || ( $is_analytics_connected && ! defined( 'RANK_MATH_PRO_FILE' ) ) ) {
			$out['clicks']      = $data['clicks']['total'];
			$out['clicks_diff'] = $data['clicks']['difference'];
		}

		$out['keywords']      = $data['keywords']['total'];
		$out['keywords_diff'] = $data['keywords']['difference'];

		$out['position']      = $data['position']['total'];
		$out['position_diff'] = $data['position']['difference'];

		// Keyword stats.
		$kw_data = (array) $stats->get_top_keywords();

		$out['top_3_positions']      = $kw_data['top3']['total'];
		$out['top_3_positions_diff'] = $kw_data['top3']['difference'];

		$out['top_10_positions']      = $kw_data['top10']['total'];
		$out['top_10_positions_diff'] = $kw_data['top10']['difference'];

		$out['top_50_positions']      = $kw_data['top50']['total'];
		$out['top_50_positions_diff'] = $kw_data['top50']['difference'];

		$out['invalid_data'] = false;
		if ( ! count( array_filter( $out ) ) ) {
			$out['invalid_data'] = true;
		}

		return $out;
	}

	/**
	 * Get date period (days) from the frequency option.
	 *
	 * @param string $frequency Frequency string.
	 *
	 * @return string
	 */
	public static function get_period_from_frequency( $frequency = null ) {
		$periods = [
			'monthly' => 30,
		];

		$periods = apply_filters( 'rank_math/analytics/email_report_periods', $periods );

		if ( empty( $frequency ) ) {
			$frequency = self::get_setting( 'frequency', 'monthly' );
		}

		if ( isset( $periods[ $frequency ] ) ) {
			return absint( $periods[ $frequency ] );
		}

		return absint( reset( $periods ) );
	}

	/**
	 * Send report data.
	 *
	 * @return void
	 */
	public function send_report() {
		$account      = Admin_Helper::get_registration_data();
		$report_email = [
			'to'      => $account['email'],
			'subject' => sprintf(
				// Translators: placeholder is the site URL.
				__( 'Rank Math [SEO Report] - %s', 'rank-math' ),
				explode( '://', get_home_url() )[1]
			),
			'message' => $this->get_template( 'report' ),
			'headers' => 'Content-Type: text/html; charset=UTF-8',
		];

		/**
		 * Filter: rank_math/analytics/email_report_parameters
		 * Filters the report email parameters.
		 */
		$report_email = $this->do_filter( 'analytics/email_report_parameters', $report_email );

		wp_mail(
			$report_email['to'],
			$report_email['subject'],
			$report_email['message'],
			$report_email['headers']
		);
	}

	/**
	 * Get full HTML template for email.
	 *
	 * @param string $template Template name.
	 * @return string
	 */
	private function get_template( $template ) {
		$file = $this->locate_template( $template );

		/**
		 * Filter template file.
		 */
		$file = $this->do_filter( 'analytics/email_report_template', $file, $template );

		if ( ! file_exists( $file ) ) {
			return '';
		}

		ob_start();
		include_once $file;
		$content = ob_get_clean();

		/**
		 * Filter template HTML.
		 */
		return $this->do_filter( 'analytics/email_report_html', $content );
	}

	/**
	 * Locate and include template part.
	 *
	 * @param string $part Template part.
	 * @param array  $args Template arguments.
	 * @return mixed
	 */
	private function template_part( $part, $args = [] ) {
		$file = $this->locate_template( $part );

		/**
		 * Filter template part.
		 */
		$file = $this->do_filter( 'analytics/email_report_template_part', $file, $part, $args );

		if ( ! file_exists( $file ) ) {
			return '';
		}

		extract( $args, EXTR_SKIP ); // phpcs:ignore
		include $file;
	}

	/**
	 * Replace variables in content.
	 *
	 * @param string $content   Email content.
	 * @param string $recursion Recursion count, to account for double-encoded variables.
	 * @return string
	 */
	public function replace_variables( $content, $recursion = 1 ) {
		foreach ( $this->variables as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			// Variables must be uppercase.
			$key = mb_strtoupper( $key );

			$content = str_replace( "###$key###", $value, $content );
		}

		if ( $recursion ) {
			--$recursion;
			$content = $this->replace_variables( $content, $recursion );
		}

		return do_shortcode( $content );
	}

	/**
	 * Strip HTML & CSS comments.
	 *
	 * @param string $content Email content.
	 * @return string
	 */
	public function strip_comments( $content ) {
		$content = preg_replace( '[(<!--(.*)-->|/\*(.*)\*/)]isU', '', $content );

		return $content;
	}

	/**
	 * Init debug mode if requested and allowed.
	 *
	 * @return void
	 */
	public function maybe_debug() {
		if ( 1 !== absint( Param::get( 'rank_math_analytics_report_preview' ) ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'analytics' ) ) {
			return;
		}

		$send   = boolval( Param::get( 'send' ) );
		$values = boolval( Param::get( 'values', '1' ) );

		$this->debug( $send, $values );
	}

	/**
	 * Send or output the report email.
	 *
	 * @param boolean $send   Send email or output to browser.
	 * @param boolean $values Replace variables with actual values.
	 * @return void
	 */
	private function debug( $send = false, $values = true ) {
		$this->debug = true;

		if ( $values ) {
			$this->setup_variables();
		}

		if ( $send ) {
			// Send it now.
			$this->send_report();
			$url = remove_query_arg(
				[
					'rank_math_analytics_report_preview',
					'send',
					'values',
				]
			);
			Helper::redirect( $url );
			exit;
		}

		// Output it to the browser.
		echo $this->get_template( 'report' ); // phpcs:ignore
		die();
	}

	/**
	 * Variable getter, whenever the value is needed in PHP.
	 *
	 * @param string $name Variable name.
	 * @return mixed
	 */
	public function get_variable( $name ) {
		if ( isset( $this->variables[ $name ] ) ) {
			return $this->variables[ $name ];
		}

		return "###$name###";
	}

	/**
	 * Setting getter.
	 *
	 * @param string $option        Option name.
	 * @param mixed  $default_value Default value.
	 * @return mixed
	 */
	public static function get_setting( $option, $default_value = false ) {
		return Helper::get_settings( 'general.console_email_' . $option, $default_value );
	}

	/**
	 * Output image inside the email template.
	 *
	 * @param string $url    Image URL.
	 * @param string $width  Image width.
	 * @param string $height Image height.
	 * @param string $alt    ALT text.
	 * @param array  $attr   Additional attributes.
	 * @return void
	 */
	public function image( $url, $width = 0, $height = 0, $alt = '', $attr = [] ) {
		$atts           = $attr;
		$atts['border'] = '0';

		if ( ! isset( $atts['src'] ) ) {
			$atts['src'] = $url;
		}

		if ( ! isset( $atts['width'] ) && $width ) {
			$atts['width'] = $width;
		}

		if ( ! isset( $atts['height'] ) && $height ) {
			$atts['height'] = $height;
		}

		if ( ! isset( $atts['alt'] ) ) {
			$atts['alt'] = $alt;
		}

		if ( ! isset( $atts['style'] ) ) {
			$atts['style'] = 'border: 0; outline: none; text-decoration: none; display: inline-block;';
		}

		if ( substr( $atts['src'], 0, 4 ) !== 'http' && substr( $atts['src'], 0, 3 ) !== '###' ) {
			$atts['src'] = $this->assets_url . 'img/' . $atts['src'];
		}

		$atts = $this->do_filter( 'analytics/email_report_image_atts', $atts, $url, $width, $height, $alt, $attr );

		$attributes = '';
		foreach ( $atts as $name => $value ) {
			if ( ! empty( $value ) ) {
				$value       = ( 'src' === $name ) ? esc_url_raw( $value ) : esc_attr( $value );
				$attributes .= ' ' . $name . '="' . $value . '"';
			}
		}

		$image = "<img $attributes>";
		$image = $this->do_filter( 'analytics/email_report_image_html', $image, $url, $width, $height, $alt, $attr );

		echo $image; // phpcs:ignore
	}

	/**
	 * Gets template path.
	 *
	 * @param string $template_name    Template name.
	 * @param bool   $return_full_path Return the full path or not.
	 * @return string
	 */
	public function locate_template( $template_name, $return_full_path = true ) {
		$default_paths  = [ $this->views_path ];
		$template_paths = $this->do_filter( 'analytics/email_report_template_paths', $default_paths );

		$paths        = array_reverse( $template_paths );
		$located      = '';
		$path_partial = '';
		foreach ( $paths as $path ) {
			if ( file_exists( $full_path = trailingslashit( $path ) . $template_name . '.php' ) ) { // phpcs:ignore
				$located      = $full_path;
				$path_partial = $path;
				break;
			}
		}

		return $return_full_path ? $located : $path_partial;
	}

	/**
	 * Load all graph data into memory.
	 *
	 * @return void
	 */
	private function load_graph_data() {
		$period = self::get_period_from_frequency();
		$stats  = Stats::get();
		$stats->set_date_range( "-{$period} days" );
		$this->graph_data = (array) $stats->get_analytics_summary_graph();
	}

	/**
	 * Get data points for graph.
	 *
	 * @param string $chart Chart to get data for.
	 * @return array
	 */
	public function get_graph_data( $chart ) {
		if ( empty( $this->graph_data ) ) {
			$this->load_graph_data();
		}

		$data  = [];
		$group = 'merged';
		$prop  = $chart;
		if ( 'traffic' === $chart ) {
			$group = 'traffic';
			$prop  = 'pageviews';
		}

		if ( empty( $this->graph_data[ $group ] ) ) {
			return $data;
		}

		foreach ( (array) $this->graph_data[ $group ] as $range_data ) {
			$range_data = (array) $range_data;
			if ( isset( $range_data[ $prop ] ) ) {
				$data[] = $range_data[ $prop ];
			}
		}

		return $data;
	}

	/**
	 * Charts API sign request.
	 *
	 * @param string $query Query.
	 * @param string $code  Code.
	 * @return string
	 */
	private function charts_api_sign( $query, $code ) {
		return hash_hmac( 'sha256', $query, $code );
	}

	/**
	 * Generate URL for the Charts API image.
	 *
	 * @param  array $graph_data Graph data points.
	 * @param  int   $width      Image height.
	 * @param  int   $height     Image width.
	 *
	 * @return string
	 */
	private function charts_api_url( $graph_data, $width = 192, $height = 102 ) {
		$params = [
			'chco' => '80ace7',
			'chds' => 'a',
			'chf'  => 'bg,s,f7f9fb',
			'chls' => 4,
			'chm'  => 'B,e2eeff,0,0,0',
			'chs'  => "{$width}x{$height}",
			'cht'  => 'ls',
			'chd'  => 'a:' . join( ',', $graph_data ),
			'icac' => $this->charts_account,
		];

		$query_string = urldecode( http_build_query( $params ) );
		$signature    = $this->charts_api_sign( $query_string, $this->charts_key );

		return 'https://charts.rankmath.com/chart?' . $query_string . '&ichm=' . $signature;
	}

	/**
	 * Check if fields should be hidden.
	 *
	 * @return bool
	 */
	public static function are_fields_hidden() {
		return apply_filters( 'rank_math/analytics/hide_email_report_options', false );
	}
}
