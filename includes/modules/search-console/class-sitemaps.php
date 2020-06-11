<?php
/**
 * The Search Console Sitemaps
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Helper;
use MyThemeShop\Helpers\Str;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemaps class.
 */
class Sitemaps {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'admin_init', 'admin_init' );
	}

	/**
	 * Admin Initialize.
	 */
	public function admin_init() {
		if ( ! empty( $_GET['refresh_sitemaps'] ) ) {
			check_admin_referer( 'rank_math_refresh_sitemaps', 'security' );

			if ( $this->sync_sitemaps() ) {
				Helper::add_notification( esc_html__( 'Sitemaps list refreshed.', 'rank-math' ), [ 'type' => 'success' ] );
			}
		}
	}

	/**
	 * Display data table.
	 */
	public function display_table() {
		echo '<form method="post">';

		$this->table = new Sitemaps_List;
		$this->table->prepare_items();
		$this->table->get_refresh_button();
		$this->table->display();

		echo '</form>';
	}

	/**
	 * Get sitemaps from api.
	 *
	 * @param boolean $with_index With index data.
	 * @param boolean $force      Purge cache and fetch new data.
	 *
	 * @return array
	 */
	public function get_sitemaps( $with_index = false, $force = false ) {
		return Client::get()->get_sitemaps( $with_index, $force );
	}

	/**
	 * Sync sitemaps with google search console.
	 */
	private function sync_sitemaps() {
		if ( $this->selected_site_is_domain_property() || ! $this->check_selected_site() ) {
			return false;
		}

		$data = $this->get_sitemap_to_sync();

		// Submit it.
		if ( ! $data['sitemaps_in_list'] ) {
			Client::get()->submit_sitemap( $data['local_sitemap'] );
		}

		if ( empty( $data['delete_sitemaps'] ) ) {
			return;
		}

		// Delete it.
		foreach ( $data['delete_sitemaps'] as $sitemap ) {
			Client::get()->delete_sitemap( $sitemap );
		}
	}

	/**
	 * Get sitemaps to sync.
	 *
	 * @return array
	 */
	private function get_sitemap_to_sync() {
		$delete_sitemaps  = [];
		$sitemaps_in_list = false;
		$local_sitemap    = trailingslashit( Client::get()->profile ) . 'sitemap_index.xml';

		// Early Bail if there are no sitemaps.
		if ( empty( $this->get_sitemaps() ) ) {
			return compact( 'delete_sitemaps', 'sitemaps_in_list', 'local_sitemap' );
		}

		foreach ( $this->get_sitemaps() as $sitemap ) {
			if ( $sitemap['path'] === $local_sitemap ) {
				$sitemaps_in_list = true;
				continue;
			}

			$delete_sitemaps[] = $sitemap['path'];
		}

		return compact( 'delete_sitemaps', 'sitemaps_in_list', 'local_sitemap' );
	}

	/**
	 * Check if selected profile same as site url.
	 *
	 * @return boolean
	 */
	private function check_selected_site() {

		if ( ! Helper::get_module( 'sitemap' ) || empty( Client::get()->profile ) ) {
			return false;
		}

		// Normalize URLs.
		$this_site     = trailingslashit( site_url( '', 'http' ) );
		$selected_site = trailingslashit( str_replace( 'https://', 'http://', Client::get()->profile ) );

		// Check if site URL matches.
		if ( $this_site !== $selected_site ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if selected profile is a Domain Property.
	 *
	 * @return boolean
	 */
	public function selected_site_is_domain_property() {

		if ( ! Helper::get_module( 'sitemap' ) || empty( Client::get()->profile ) ) {
			return false;
		}

		if ( Str::starts_with( 'sc-domain:', Client::get()->profile ) ) {
			return true;
		}

		return false;
	}
}
