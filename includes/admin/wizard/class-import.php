<?php
/**
 * The Import wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Admin\Importers\Detector;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Import implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Analytics step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$detector = new Detector();
		$plugins  = $detector->detect();
		$plugins  = self::set_priority( $plugins );
		return [
			'importablePlugins' => $plugins,
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
		delete_option( 'rank_math_yoast_block_posts' );
		return true;
	}

	/**
	 * Set plugins priority.
	 *
	 * @param array $plugins Array of detected plugins.
	 *
	 * @return array
	 */
	private static function set_priority( $plugins ) {
		$checked  = false;
		$priority = array_intersect( [ 'seopress', 'yoast', 'yoast-premium', 'aioseo' ], array_keys( $plugins ) );

		foreach ( $priority as $slug ) {
			if ( ! $checked ) {
				$checked                     = true;
				$plugins[ $slug ]['checked'] = true;
				continue;
			}

			$plugins[ $slug ]['checked'] = false;
		}

		return $plugins;
	}

	/**
	 * Get description for choice field.
	 *
	 * @param string  $slug      Plugin slug.
	 * @param array   $plugin    Plugin info array.
	 * @param boolean $is_active Is plugin active.
	 *
	 * @return string
	 */
	private function get_choice_description( $slug, $plugin, $is_active ) {
		/* translators: 1 is plugin name */
		$desc = 'aio-rich-snippet' === $slug ? esc_html__( 'Import meta data from the %1$s plugin.', 'rank-math' ) : esc_html__( 'Import settings and meta data from the %1$s plugin.', 'rank-math' );

		/* translators: 2 is link to Knowledge Base article */
		$desc .= ' ' . __( 'The process may take a few minutes if you have a large number of posts or pages <a href="%2$s" target="_blank">Learn more about the import process here.</a>', 'rank-math' );

		if ( $is_active ) {
			/* translators: 1 is plugin name */
			$desc .= '<br>' . __( ' %1$s plugin will be disabled automatically moving forward to avoid conflicts. <strong>It is thus recommended to import the data you need now.</strong>', 'rank-math' );
		}

		return sprintf( wp_kses_post( $desc ), $plugin['name'], KB::get( 'seo-import', 'SW Import Step' ) );
	}
}
