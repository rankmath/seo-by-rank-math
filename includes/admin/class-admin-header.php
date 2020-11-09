<?php
/**
 * Header for the Rank Math pages
 *
 * @since      1.0.44
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\KB;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Header class.
 *
 * @codeCoverageIgnore
 */
class Admin_Header {

	/**
	 * Hold current screen ID.
	 *
	 * @var Current screen ID.
	 */
	private $screen_id = '';

	/**
	 * Display Header.
	 */
	public function display() {
		$logo_url        = '<a href="' . esc_url( Helper::get_admin_url() ) . '"><i class="rm-icon rm-icon-rank-math"></i></a>';
		$this->screen_id = $this->get_current_screen();
		?>
		<div class="rank-math-header">
			<div class="rank-math-logo">
				<?php echo $logo_url; // phpcs:ignore ?>
			</div>
			<h1 class="rank-math-logo-text">Rank Math SEO <?php if ( defined( 'RANK_MATH_PRO_FILE' ) ) echo '<span class="rank-math-pro-badge">PRO</span>'; ?></h1>
			<?php $this->get_search_options(); ?>
			<?php $this->get_mode_selector(); ?>
			<a href="<?php echo esc_url( $this->get_help_link() ); ?>" target="_blank" class="button rank-math-help"><i class="rm-icon rm-icon-help"></i></a>
		</div>
		<?php

		// Breadcrumbs.
		rank_math()->admin->display_admin_breadcrumbs();
	}

	/**
	 * Get Search Options.
	 */
	private function get_search_options() {
		if (
			! in_array(
				$this->screen_id,
				[
					'rank-math_page_rank-math-options-general',
					'rank-math_page_rank-math-options-titles',
					'rank-math_page_rank-math-options-sitemap',
				],
				true
			)
		) {
			return;
		}
		?>
		<div class="rank-math-search-options">
			<div class="search-field">
				<i class="rm-icon rm-icon-search"></i>
				<input type="text" value="" placeholder="<?php esc_attr_e( 'Search Options', 'rank-math' ); ?>">
				<em class="clear-search dashicons dashicons-no-alt"></em>
			</div>
		</div>
		<?php
	}

	/**
	 * Get Mode Selector.
	 */
	private function get_mode_selector() {
		if (
			! in_array(
				$this->screen_id,
				[
					'toplevel_page_rank-math',
					'rank-math_page_rank-math-status',
				],
				true
			)
		) {
			return;
		}

		$is_advanced_mode = Helper::is_advanced_mode();
		?>
		<div class="rank-math-mode-selector">
			<a href="#" class="<?php echo ! $is_advanced_mode ? 'active' : ''; ?>" data-mode="easy"><?php esc_attr_e( 'Easy Mode', 'rank-math' ); ?></a>
			<a href="#" class="<?php echo $is_advanced_mode ? 'active' : ''; ?>" data-mode="advanced"><?php esc_attr_e( 'Advanced Mode', 'rank-math' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Get Current Screen ID.
	 */
	private function get_help_link() {
		$links = [
			'import-export-settings' => 'import_export' === Param::get( 'view' ),
			'version-control'        => 'version_control' === Param::get( 'view' ) || 'rank-math-status' === Param::get( 'page' ),
			'general-settings'       => 'rank-math-options-general' === Param::get( 'page' ),
			'titles-meta'            => 'rank-math-options-titles' === Param::get( 'page' ),
			'sitemap-general'        => 'rank-math-options-sitemap' === Param::get( 'page' ),
			'role-manager'           => 'rank-math-role-manager' === Param::get( 'page' ),
			'seo-analysis'           => 'rank-math-seo-analysis' === Param::get( 'page' ),
			'seo-analysis'           => 'rank-math-seo-analysis' === Param::get( 'page' ),
		];

		$link = 'https://rankmath.com/kb/?utm_source=Plugin&utm_medium=RM%20Header%20KB%20Icon&utm_campaign=WP';
		foreach ( $links as $key => $value ) {
			if ( $value ) {
				$link = KB::get( $key );
				break;
			}
		}

		return $link;
	}

	/**
	 * Get Current Screen ID.
	 */
	private function get_current_screen() {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return '';
		}

		return $screen->id;
	}
}
