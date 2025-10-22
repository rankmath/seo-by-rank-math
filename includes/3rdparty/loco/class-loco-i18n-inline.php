<?php
/**
 * Loco Translate inline i18n helper for injecting translations without JSON files.
 *
 * @since      1.0.256
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ThirdParty\Loco;

use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Loco_I18n_Inline class.
 */
class Loco_I18n_Inline {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rank-math/admin_enqueue_scripts', 'inject_settings_locale' );
	}

	/**
	 * Inject inline JED locale for settings screens when Loco is active.
	 */
	public function inject_settings_locale() {
		// Load translations from all Loco locations (Customization, System, Author).
		$json = $this->get_jed_json( 'rank-math', false );
		if ( empty( $json ) ) {
			return;
		}

		$inline = sprintf(
			'try{if(window.wp&&wp.i18n&&wp.i18n.setLocaleData){wp.i18n.setLocaleData(%s,"rank-math");var __rm=wp.i18n.getLocaleData&&wp.i18n.getLocaleData("rank-math");if(__rm){wp.i18n.setLocaleData(__rm,"seo-by-rank-math");}}}catch(e){}',
			$json
		);

		wp_add_inline_script( 'common', $inline, 'before' );
	}

	/**
	 * Build a JED locale JSON from the best available MO/PO for the current locale.
	 *
	 * @param string $domain    Text domain (e.g. 'rank-math').
	 * @param bool   $loco_only Whether to restrict to Loco-managed files only.
	 * @return string JSON string suitable for wp.i18n.setLocaleData(), or empty string.
	 */
	private function get_jed_json( $domain, $loco_only = true ) {
		$locale = get_user_locale();

		$file = $this->find_translation_file( $domain, $locale, $loco_only );
		if ( ! $file ) {
			return '';
		}

		return $this->build_jed_json( $file, $domain, $locale );
	}

	/**
	 * Build JED JSON from translation file.
	 *
	 * @param string $file   Path to MO or PO file.
	 * @param string $domain Text domain.
	 * @param string $locale Locale.
	 * @return string JSON string or false.
	 */
	private function build_jed_json( $file, $domain, $locale ) {
		require_once ABSPATH . WPINC . '/pomo/mo.php';
		require_once ABSPATH . WPINC . '/pomo/po.php';

		$translations = Str::ends_with( '.mo', $file ) ? new \MO() : new \PO();
		if ( ! $translations->import_from_file( $file ) ) {
			return false;
		}

		$data = [
			'' => [
				'domain' => $domain,
				'lang'   => $locale,
			],
		];

		if ( ! empty( $translations->headers['plural-forms'] ) ) {
			$data['']['plural-forms'] = $translations->headers['plural-forms'];
		}

		foreach ( $translations->entries as $entry ) {
			if ( empty( $entry->translations ) ) {
				continue;
			}
			$key          = empty( $entry->context ) ? $entry->singular : $entry->context . "\004" . $entry->singular;
			$data[ $key ] = $entry->translations;
		}

		return wp_json_encode( $data );
	}

	/**
	 * Find translation file for domain/locale.
	 *
	 * @param string $domain    Text domain.
	 * @param string $locale    Locale.
	 * @param bool   $loco_only Restrict to Loco files only.
	 * @return string|false File path or false.
	 */
	private function find_translation_file( $domain, $locale, $loco_only ) {
		$paths = [];

		// 1) Loco "Customization" location.
		$paths[] = WP_CONTENT_DIR . '/languages/loco/plugins/' . $domain . '-' . $locale . '.mo';
		$paths[] = WP_CONTENT_DIR . '/languages/loco/plugins/' . $domain . '-' . $locale . '.po';

		// 2) Loco "System" location (global WP languages dir).
		if ( ! $loco_only ) {
			$paths[] = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';
			$paths[] = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.po';
		}

		// 3) Loco "Author" location (plugin bundled languages directory).
		$paths[] = RANK_MATH_PATH . 'languages/' . $domain . '-' . $locale . '.mo';
		$paths[] = RANK_MATH_PATH . 'languages/' . $domain . '-' . $locale . '.po';

		foreach ( $paths as $path ) {
			if ( is_file( $path ) && is_readable( $path ) ) {
				return $path;
			}
		}

		return false;
	}
}
