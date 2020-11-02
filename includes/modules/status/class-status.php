<?php
/**
 * The Status module.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Traits\Hooker;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Status class.
 */
class Status extends Base {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'status',
				'directory' => $directory,
			]
		);

		$this->filter( 'rank_math/tools/pages', 'add_status_page', 12 );

		parent::__construct();
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-status',
			esc_html__( 'Status & Tools', 'rank-math' ),
			[
				'position' => 70,
				'parent'   => 'rank-math',
				'classes'  => [ 'rank-math-page' ],
				'render'   => $this->directory . '/views/main.php',
				'assets'   => [
					'styles'  => [
						'rank-math-common' => '',
						'rank-math-status' => $uri . '/assets/css/status.css',
					],
					'scripts' => [
						'rank-math-dashboard' => '',
						'rank-math-status'    => $uri . '/assets/js/status.js',
					],
				],
			]
		);
	}

	/**
	 * Display dashabord tabs.
	 */
	public function display_nav() {
		$default_tab = $this->do_filter( 'tools/default_tab', 'status' );
		?>
		<div class="rank-math-tab-nav" role="tablist" aria-orientation="horizontal">
			<?php
			foreach ( $this->get_views() as $id => $link ) :
				if ( isset( $link['cap'] ) && ! current_user_can( $link['cap'] ) ) {
					continue;
				}
				?>
			<a class="rank-math-tab<?php echo Param::get( 'view', $default_tab ) === sanitize_html_class( $id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( Helper::get_admin_url( $link['url'], $link['args'] ) ); ?>" title="<?php echo esc_attr( $link['title'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Display view body.
	 *
	 * @param string $view Current view.
	 */
	public function display_body( $view ) {
		$hash = $this->get_views();
		$hash = new $hash[ $view ]['class']();
		$hash->display();
	}

	/**
	 * Add subpage to Status & Tools screen.
	 *
	 * @param array $pages Pages.
	 * @return array       New pages.
	 */
	public function add_status_page( $pages ) {
		$pages['status'] = [
			'url'   => 'status',
			'args'  => 'view=status',
			'cap'   => 'manage_options',
			'title' => __( 'System Status', 'rank-math' ),
			'class' => '\\RankMath\\Status\\System_Status',
		];

		return $pages;
	}

	/**
	 * Get dashbaord navigation links
	 *
	 * @return array
	 */
	private function get_views() {
		return $this->do_filter( 'tools/pages', [] );
	}
}
