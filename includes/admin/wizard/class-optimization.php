<?php
/**
 * The Optimization wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Optimization implements Wizard_Step {
	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		return [
			'noindex_empty_taxonomies'  => Helper::get_settings( 'titles.noindex_empty_taxonomies' ),
			'nofollow_external_links'   => Helper::get_settings( 'general.nofollow_external_links' ),
			'new_window_external_links' => Helper::get_settings( 'general.new_window_external_links' ),
		];
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		$settings = rank_math()->settings->all_raw();

		$settings['titles']['noindex_empty_taxonomies'] = $values['noindex_empty_taxonomies'] ? 'on' : 'off';

		if ( isset( $values['attachment_redirect_urls'] ) && 'on' === $values['attachment_redirect_urls'] ) {
			$settings['general']['attachment_redirect_urls']    = 'on';
			$settings['general']['attachment_redirect_default'] = sanitize_url( $values['attachment_redirect_default'] );
		}

		$settings['general']['nofollow_external_links']   = ! empty( $values['nofollow_external_links'] ) ? 'on' : 'off';
		$settings['general']['new_window_external_links'] = $values['new_window_external_links'] ? 'on' : 'off';

		Helper::update_all_settings( $settings['general'], $settings['titles'], null );
		Helper::schedule_flush_rewrite();

		return true;
	}
}
