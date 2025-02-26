<?php
/**
 * The Redirections import/export panel.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Import_Export class.
 */
class Import_Export {

	use Hooker;

	/**
	 * Limit
	 *
	 * @var string
	 */
	public $limit;

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Import_Export
	 */
	public static function get() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->limit = $this->do_filter( 'redirections/export_notice_limit', 1000 );
	}

	/**
	 * The hooks.
	 */
	public function hooks() {
		$this->action( 'rank_math/redirections/export_tab_content', 'export_tab' );
	}

	/**
	 * Export Tab contents.
	 *
	 * @return void
	 */
	public function export_tab() {
		// Show a notice if the number of redirections is too high.
		$count = DB::get_redirections(
			[
				'limit'  => $this->limit,
				'status' => 'active',
			]
		);

		if ( $count['count'] >= $this->limit ) {
			?>
			<div class="inline notice notice-warning notice-alt rank-math-notice" style="margin: 10px 10px 0;">
				<p>
					<?php
					// Translators: Placeholder expands to number of redirections.
					printf( esc_html__( 'Warning: you have more than %d active redirections. Exporting them to your .htaccess file may cause performance issues.', 'rank-math' ), absint( $this->limit ) );
					?>
				</p>
			</div>
			<?php
		}

		?>
		<div class="rank-math-redirections-export-options">
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( Helper::get_admin_url( 'redirections', 'export=apache' ), 'rank-math-export-redirections' ) ); ?>"><?php esc_html_e( 'Export to .htaccess', 'rank-math' ); ?></a>
			<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( Helper::get_admin_url( 'redirections', 'export=nginx' ), 'rank-math-export-redirections' ) ); ?>"><?php esc_html_e( 'Export to Nginx config file', 'rank-math' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Display form.
	 */
	public function display_form() {
		$tabs = $this->do_filter(
			'redirections/export_tabs',
			[
				'export' => [
					'name'  => __( 'Export', 'rank-math' ),
					'icon'  => 'rm-icon-export',
					'class' => 'active-tab',
				],
			]
		);

		?>
		<div id="import-export-box" class="rank-math-box no-padding">
			<div class="rank-math-box-tabs wp-clearfix">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<a href="#panel-<?php echo sanitize_html_class( $tab_id ); ?>" class="<?php echo esc_attr( $tab['class'] ); ?>">
						<i class="rm-icon <?php echo esc_attr( $tab['icon'] ); ?>"></i>
						<span class="rank-math-tab-text"><?php echo esc_html( $tab['name'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="rank-math-box-content">

				<div class="rank-math-box-inner">
					<?php foreach ( $tabs as $tab_id => $tab ) : ?>
						<form id="panel-<?php echo sanitize_html_class( $tab_id ); ?>" class="rank-math-<?php echo sanitize_html_class( $tab_id ); ?>-form cmb2-form <?php echo esc_attr( $tab['class'] ); ?>" action="#import-export-box" method="post" enctype="multipart/form-data" accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
							<?php $this->do_action( 'redirections/' . $tab_id . '_tab_content' ); ?>
						</form>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<?php
	}
}
