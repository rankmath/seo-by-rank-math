<?php
/**
 * The SEO Analysis Result
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Result class.
 */
class Result {

	/**
	 * Result id.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Hold result data.
	 *
	 * @var array
	 */
	private $result;

	/**
	 * Is sub-page.
	 *
	 * @var array
	 */
	private $is_subpage;

	/**
	 * The Constructor.
	 *
	 * @param string $id         Result id.
	 * @param array  $data       Result data.
	 * @param bool   $is_subpage Is sub-page result.
	 */
	public function __construct( $id, $data, $is_subpage ) {
		if ( is_a( $data, 'RankMath\\SEO_Analysis\\Result' ) ) {
			$data = $data->result;
		}
		$this->id         = $id;
		$this->result     = $data;
		$this->is_subpage = $is_subpage;
	}

	/**
	 * Magic method.
	 */
	public function __toString() {
		ob_start();
		?>
		<div class="row-title">

			<h3><?php echo $this->result['title']; ?></h3>

			<?php if ( ! empty( $this->result['tooltip'] ) ) : ?>
			<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span><?php echo $this->result['tooltip']; ?></span></span>
			<?php endif; ?>

		</div>

		<div class="row-description">

			<?php $this->the_status(); ?>

			<div class="row-content">

				<?php if ( $this->has_fix() ) : ?>
				<a href="#" class="button button-secondary button-small result-action"><?php esc_html_e( 'How to fix', 'rank-math' ); ?></a>
				<?php endif; ?>

				<?php echo wp_kses_post( $this->result['message'] ); ?>

				<?php
				if ( isset( $this->result['data'] ) && ! empty( $this->result['data'] ) ) {
					$this->the_content();
				}
				?>

				<div class="clear"></div>

				<?php if ( $this->has_fix() ) : ?>
				<div class="how-to-fix-wrapper">
					<div class="analysis-test-how-to-fix"><?php echo $this->result['fix']; ?></div>
				</div>
				<?php endif; ?>

			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get result id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get category
	 *
	 * @return string
	 */
	public function get_category() {
		return is_array( $this->result ) && isset( $this->result['category'] ) ? $this->result['category'] : '';
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function get_status() {
		return is_array( $this->result ) && isset( $this->result['status'] ) ? $this->result['status'] : '';
	}

	/**
	 * Has fix content.
	 *
	 * @return bool
	 */
	private function has_fix() {
		return is_array( $this->result ) && in_array( $this->result['status'], [ 'fail', 'warning' ], true ) && ! empty( $this->result['fix'] );
	}

	/**
	 * Output test result status.
	 */
	private function the_status() {
		if ( ! is_array( $this->result ) ) {
			return;
		}

		$status = $this->result['status'];
		if ( ! empty( $this->result['is_info'] ) ) {
			$status = 'info';
		}

		$icons = [
			'ok'      => 'dashicons dashicons-yes',
			'fail'    => 'dashicons dashicons-no',
			'warning' => 'dashicons dashicons-warning',
			'info'    => 'dashicons dashicons-info',
		];

		$labels = [
			'ok'      => esc_html__( 'OK', 'rank-math' ),
			'fail'    => esc_html__( 'Failed', 'rank-math' ),
			'warning' => esc_html__( 'Warning', 'rank-math' ),
			'info'    => esc_html__( 'Info', 'rank-math' ),
		];

		printf(
			'<div class="status-icon status-%1$s %3$s" title="%2$s"></div>',
			$status,
			esc_attr( $labels[ $status ] ),
			esc_attr( $icons[ $status ] )
		);
	}

	/**
	 * Output test data
	 */
	private function the_content() {
		if ( ! is_array( $this->result ) ) {
			return;
		}

		$data = $this->result['data'];

		if ( 'common_keywords' === $this->id ) {
			$this->the_tag_cloud( $data );
			return;
		}

		if ( $this->is_list() || $this->is_reverse_heading() ) {
			$this->the_list( $data );
			return;
		}

		$explode = [ 'h1_heading', 'h2_headings', 'title_length', 'description_length', 'canonical' ];
		if ( in_array( $this->id, $explode, true ) ) {
			echo '<code class="full-width">' . join( ', ', (array) $data ) . '</code>';
			return;
		}
	}

	/**
	 * Render list
	 *
	 * @param array $data Keywords.
	 */
	private function the_list( $data ) {
		$is_reverse_heading = $this->is_reverse_heading();

		$html = '<ul class="info-list">';
		foreach ( $data as $label => $text ) {
			$text  = is_array( $text ) ? join( ', ', $text ) : $text;
			$html .= $is_reverse_heading ? '<li><strong>' . $label . ': </strong> ' . esc_html( $text ) . '</li>' :
				'<li>' . esc_html( ( is_string( $label ) ? $label . ' (' . $text . ')' : $text ) ) . '</li>';
		}
		echo $html . '</ul>';
	}

	/**
	 * Is data to be render as list.
	 *
	 * @return bool
	 */
	private function is_list() {
		return in_array( $this->id, [ 'img_alt', 'minify_css', 'minify_js', 'active_plugins' ], true );
	}

	/**
	 * Is data to be render as reverse heading.
	 *
	 * @return bool
	 */
	private function is_reverse_heading() {
		return in_array( $this->id, [ 'links_ratio', 'keywords_meta', 'page_objects' ], true );
	}

	/**
	 * Render tag cloud
	 *
	 * @param array $data Keywords.
	 */
	private function the_tag_cloud( $data ) {
		$font_size_max = 22;
		$font_size_min = 10;

		$max = max( $data );

		echo '<div class="wp-tag-cloud">';
		foreach ( $data as $keyword => $occurrences ) {
			$size = ( $occurrences / $max ) * ( $font_size_max - $font_size_min ) + $font_size_min;
			$size = round( $size, 2 );

			printf( '<span class="keyword-cloud-item" style="font-size: %.2fpx">%s</span>', $size, htmlspecialchars( $keyword, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8' ) );
		}
		echo '</div>';
	}

	/**
	 * Is test excluded.
	 *
	 * @return bool
	 */
	public function is_excluded() {
		$exclude_tests = [
			'active_plugins',
			'active_theme',
			'dirlist',
			'libwww_perl_access',
			'robots_txt',
			'safe_browsing',
			'xmlrpc',

			// Local tests.
			'comment_pagination',
			'site_description',
			'permalink_structure',
			'cache_plugin',
			'search_console',
			'focus_keywords',
			'post_titles',
		];

		return $this->is_subpage && in_array( $this->id, $exclude_tests, true );
	}

	/**
	 * Is test hidden.
	 *
	 * @return bool
	 */
	public function is_hidden() {
		$hidden_tests = [
			// Performance.
			'image_header',
			'minify_css',
			'minify_js',
			'page_objects',
			'page_size',
			'response_time',

			// Security.
			'directory_listing',
			'safe_browsing',
			'ssl',
			'active_plugins',
			'active_theme',
		];

		return ! Helper::is_advanced_mode() && in_array( $this->id, $hidden_tests, true );
	}

	/**
	 * Get tests score.
	 *
	 * @return int
	 */
	public function get_score() {
		$score = [
			'h1_heading'          => 5,
			'h2_headings'         => 2,
			'img_alt'             => 4,
			'keywords_meta'       => 5,
			'links_ratio'         => 3,
			'title_length'        => 4,
			'permalink_structure' => 7,
			'focus_keywords'      => 3,
			'post_titles'         => 4,

			// Advanced SEO.
			'canonical'           => 5,
			'noindex'             => 7,
			'non_www'             => 4,
			'opengraph'           => 2,
			'robots_txt'          => 3,
			'schema'              => 3,
			'sitemaps'            => 3,
			'search_console'      => 1,

			// Performance.
			'image_header'        => 3,
			'minify_css'          => 2,
			'minify_js'           => 1,
			'page_objects'        => 2,
			'page_size'           => 3,
			'response_time'       => 3,

			// Security.
			'directory_listing'   => 1,
			'safe_browsing'       => 8,
			'ssl'                 => 7,
		];

		return isset( $score[ $this->id ] ) ? $score[ $this->id ] : 0;
	}
}
