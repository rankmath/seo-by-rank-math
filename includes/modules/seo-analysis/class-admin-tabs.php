<?php
/**
 * The nav tabs on the Dashboard page.
 *
 * @since      1.0.40
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Helper;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Tabs class.
 *
 * @codeCoverageIgnore
 */
class Admin_Tabs {

	/**
	 * Display dashboard tabs.
	 */
	public function display() {
		$nav_links = $this->get_nav_links();
		if ( empty( $nav_links ) ) {
			return;
		}
		?>
		<div class="rank-math-tab-nav" role="tablist" aria-orientation="horizontal">
			<?php
			foreach ( $nav_links as $id => $link ) {
				$this->nav_link( $link );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get URL for dashboard nav links.
	 *
	 * @param  array $link Link data.
	 * @return string      Link URL.
	 */
	public function get_link_url( $link ) {
		return Helper::get_admin_url( $link['url'], $link['args'] );
	}

	/**
	 * Output dashboard nav link.
	 *
	 * @param  array $link Link data.
	 * @return void
	 */
	public function nav_link( $link ) {
		if ( isset( $link['cap'] ) && ! current_user_can( $link['cap'] ) ) {
			return;
		}

		$default_tab = 'seo_analyzer';
		?>
		<a
			class="rank-math-tab<?php echo Param::get( 'view', $default_tab ) === sanitize_html_class( $link['id'] ) ? ' is-active' : ''; ?>"
			href="<?php echo esc_url( $this->get_link_url( $link ) ); ?>"
			title="<?php echo esc_attr( $link['title'] ); ?>">
			<?php echo ! empty( $link['icon'] ) ? '<i class="' . esc_attr( $link['icon'] ) . '"></i>' : ''; ?>
			<?php echo esc_html( $link['title'] ); ?>
		</a>
		<?php
	}

	/**
	 * Get dashbaord navigation links
	 *
	 * @return array
	 */
	private function get_nav_links() {
		$links = [
			'seo_analyzer'           => [
				'id'    => 'seo_analyzer',
				'url'   => 'seo-analysis',
				'args'  => 'view=seo_analyzer',
				'cap'   => 'rank_math_site_analysis',
				'title' => esc_html__( 'SEO Analyzer', 'rank-math' ),
				'icon'  => 'rm-icon rm-icon-analyzer',
			],
			'competitor_analyzer' => [
				'id'    => 'competitor_analyzer',
				'url'   => 'seo-analysis',
				'args'  => 'view=competitor_analyzer',
				'cap'   => 'rank_math_site_analysis',
				'title' => esc_html__( 'Competitor Analyzer', 'rank-math' ),
				'icon'  => 'rm-icon rm-icon-users',
			],
		];

		return apply_filters( 'rank_math/seo_analysis/admin_tab_links', $links );
	}
}
