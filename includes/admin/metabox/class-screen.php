<?php
/**
 * Metabox localization methods.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Meta;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Locale;
use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Url;

defined( 'ABSPATH' ) || exit;

/**
 * Screen.
 */
class Screen implements IScreen {

	use Meta;
	use Hooker;

	/**
	 * Current screen object.
	 *
	 * @var IScreen
	 */
	private $screen = null;

	/**
	 * Class construct
	 */
	public function __construct() {
		$this->load_screen();
	}

	/**
	 * Is creen loaded.
	 *
	 * @return bool
	 */
	public function is_loaded() {
		return ! is_null( $this->screen );
	}

	/**
	 * Get object id
	 *
	 * @return int
	 */
	public function get_object_id() {
		return $this->screen->get_object_id();
	}

	/**
	 * Get object type
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->screen->get_object_type();
	}

	/**
	 * Get object types to register metabox to
	 *
	 * @return array
	 */
	public function get_object_types() {
		return $this->screen->get_object_types();
	}

	/**
	 * Enqueue Styles and Scripts required for screen.
	 */
	public function enqueue() {
		$this->screen->enqueue();
	}

	/**
	 * Get analysis to run.
	 *
	 * @return array
	 */
	public function get_analysis() {
		$analyses = $this->do_filter(
			'researches/tests',
			$this->screen->get_analysis(),
			$this->screen->get_object_type()
		);

		return array_keys( $analyses );
	}

	/**
	 * Get values for localize.
	 */
	public function localize() {
		$values = $this->get_values();
		if ( empty( $values ) ) {
			return;
		}

		foreach ( $values as $key => $value ) {
			Helper::add_json( $key, $value );
		}
	}

	/**
	 * Get common values.
	 *
	 * @return array
	 */
	public function get_values() {
		$editor      = Helper::get_current_editor();
		$trends_link = KB::get( 'pro', 'CE General Tab Trends' );
		if ( 'gutenberg' === $editor ) {
			$trends_link = KB::get( 'pro', 'Gutenberg General Tab Trends' );
		} elseif ( 'elementor' === $editor ) {
			$trends_link = KB::get( 'pro', 'Elementor General Tab Trends' );
		}

		$values = array_merge_recursive(
			$this->screen->get_values(),
			[
				'homeUrl'             => home_url(),
				'objectID'            => $this->get_object_id(),
				'objectType'          => $this->get_object_type(),
				'locale'              => Locale::get_site_language(),
				'localeFull'          => get_locale(),
				'overlayImages'       => Helper::choices_overlay_images(),
				'defautOgImage'       => Helper::get_settings( 'titles.open_graph_image', rank_math()->plugin_url() . 'assets/admin/img/social-placeholder.jpg' ),
				'customPermalinks'    => (bool) get_option( 'permalink_structure', false ),
				'isUserRegistered'    => Helper::is_site_connected(),
				'autoSuggestKeywords' => Helper::is_site_connected(),
				'connectSiteUrl'      => Admin_Helper::get_activate_url( Url::get_current_url() ),
				'maxTags'             => $this->do_filter( 'focus_keyword/maxtags', 5 ),
				'trendsIcon'          => Admin_Helper::get_trends_icon_svg(),
				'showScore'           => Helper::is_score_enabled(),
				'siteFavIcon'         => $this->get_site_icon(),
				'canUser'             => [
					'general'    => Helper::has_cap( 'onpage_general' ),
					'advanced'   => Helper::has_cap( 'onpage_advanced' ) && Helper::is_advanced_mode(),
					'snippet'    => Helper::has_cap( 'onpage_snippet' ),
					'social'     => Helper::has_cap( 'onpage_social' ),
					'analysis'   => Helper::has_cap( 'onpage_analysis' ),
					'analytics'  => Helper::has_cap( 'analytics' ),
					'content_ai' => Helper::has_cap( 'content_ai' ),
				],
				'assessor'            => [
					'serpData'        => $this->get_object_values(),
					'powerWords'      => $this->power_words(),
					'diacritics'      => $this->diacritics(),
					'researchesTests' => $this->get_analysis(),
					'hasRedirection'  => Helper::is_module_active( 'redirections' ),
					'hasBreadcrumb'   => Helper::is_breadcrumbs_enabled(),
				],
				'isPro'               => defined( 'RANK_MATH_PRO_FILE' ),
				'is_front_page'       => Admin_Helper::is_home_page(),
				'trendsUpgradeLink'   => esc_url_raw( $trends_link ),
				'trendsUpgradeLabel'  => esc_html__( 'Upgrade', 'rank-math' ),
				'trendsPreviewImage'  => esc_url( rank_math()->plugin_url() . 'assets/admin/img/trends-preview.jpg' ),
				'currentEditor'       => $editor,
			]
		);

		$values = $this->do_filter( 'metabox/values', $values, $this );
		return $this->do_filter( 'metabox/' . $this->get_object_type() . '/values', $values, $this );
	}

	/**
	 * Get object values for localize
	 *
	 * @return array
	 */
	public function get_object_values() {
		$keys = $this->do_filter(
			'metabox/' . $this->get_object_type() . '/meta_keys',
			[
				'title'                    => 'title',
				'description'              => 'description',
				'focusKeywords'            => 'focus_keyword',
				'pillarContent'            => 'pillar_content',
				'canonicalUrl'             => 'canonical_url',
				'breadcrumbTitle'          => 'breadcrumb_title',
				'advancedRobots'           => 'advanced_robots',

				// Facebook.
				'facebookTitle'            => 'facebook_title',
				'facebookDescription'      => 'facebook_description',
				'facebookImage'            => 'facebook_image',
				'facebookImageID'          => 'facebook_image_id',
				'facebookHasOverlay'       => 'facebook_enable_image_overlay',
				'facebookImageOverlay'     => 'facebook_image_overlay',
				'facebookAuthor'           => 'facebook_author',

				// Twitter.
				'twitterCardType'          => 'twitter_card_type',
				'twitterUseFacebook'       => 'twitter_use_facebook',
				'twitterTitle'             => 'twitter_title',
				'twitterDescription'       => 'twitter_description',
				'twitterImage'             => 'twitter_image',
				'twitterImageID'           => 'twitter_image_id',
				'twitterHasOverlay'        => 'twitter_enable_image_overlay',
				'twitterImageOverlay'      => 'twitter_image_overlay',

				// Player.
				'twitterPlayerUrl'         => 'twitter_player_url',
				'twitterPlayerSize'        => 'twitter_player_size',
				'twitterPlayerStream'      => 'twitter_player_stream',
				'twitterPlayerStreamCtype' => 'twitter_player_stream_ctype',

				// App.
				'twitterAppDescription'    => 'twitter_app_description',
				'twitterAppIphoneName'     => 'twitter_app_iphone_name',
				'twitterAppIphoneID'       => 'twitter_app_iphone_id',
				'twitterAppIphoneUrl'      => 'twitter_app_iphone_url',
				'twitterAppIpadName'       => 'twitter_app_ipad_name',
				'twitterAppIpadID'         => 'twitter_app_ipad_id',
				'twitterAppIpadUrl'        => 'twitter_app_ipad_url',
				'twitterAppGoogleplayName' => 'twitter_app_googleplay_name',
				'twitterAppGoogleplayID'   => 'twitter_app_googleplay_id',
				'twitterAppGoogleplayUrl'  => 'twitter_app_googleplay_url',
				'twitterAppCountry'        => 'twitter_app_country',
			]
		);

		// Generate data.
		$data        = [];
		$object_id   = $this->get_object_id();
		$object_type = $this->get_object_type();
		foreach ( $keys as $id => $key ) {
			$data[ $id ] = $this->get_meta( $object_type, $object_id, 'rank_math_' . $key );
		}

		// Robots.
		$data['robots'] = $this->normalize_robots( $this->get_meta( $object_type, $object_id, 'rank_math_robots' ) );

		// Advanced Robots.
		$data['advancedRobots'] = $this->normalize_advanced_robots( $this->get_meta( $object_type, $object_id, 'rank_math_advanced_robots' ) );

		$data['pillarContent'] = 'on' === $data['pillarContent'];

		// Username, avatar & Name.
		$twitter_username           = Helper::get_settings( 'titles.twitter_author_names' );
		$data['twitterAuthor']      = $twitter_username ? $twitter_username : esc_html__( 'username', 'rank-math' );
		$data['twitterUseFacebook'] = 'off' === $data['twitterUseFacebook'] ? false : true;
		$data['facebookHasOverlay'] = empty( $data['facebookHasOverlay'] ) || 'off' === $data['facebookHasOverlay'] ? false : true;
		$data['twitterHasOverlay']  = empty( $data['twitterHasOverlay'] ) || 'off' === $data['twitterHasOverlay'] ? false : true;

		return wp_parse_args( $this->screen->get_object_values(), $data );
	}

	/**
	 * Get site fav icon.
	 *
	 * @return string
	 */
	private function get_site_icon() {
		$favicon = get_site_icon_url( 16 );

		return ! empty( $favicon ) ? $favicon : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABs0lEQVR4AWL4//8/RRjO8Iucx+noO0MWUDo16FYABMGP6ZfUcRnWtm27jVPbtm3bttuH2t3eFPcY9pLz7NxiLjCyVd87pKnHyqXyxtCs8APd0rnyxiu4qSeA3QEDrAwBDrT1s1Rc/OrjLZwqVmOSu6+Lamcpp2KKMA9PH1BYXMe1mUP5qotvXTywsOEEYHXxrY+3cqk6TMkYpNr2FeoY3KIr0RPtn9wQ2unlA+GMkRw6+9TFw4YTwDUzx/JVvARj9KaedXRO8P5B1Du2S32smzqUrcKGEyA+uAgQjKX7zf0boWHGfn71jIKj2689gxp7OAGShNcBUmLMPVjZuiKcA2vuWHHDCQxMCz629kXAIU4ApY15QwggAFbfOP9DhgBJ+nWVJ1AZAfICAj1pAlY6hCADZnveQf7bQIwzVONGJonhLIlS9gr5mFg44Xd+4S3XHoGNPdJl1INIwKyEgHckEhgTe1bGiFY9GSFBYUwLh1IkiJUbY407E7syBSFxKTszEoiE/YdrgCEayDmtaJwCI9uu8TKMuZSVfSa4BpGgzvomBR/INhLGzrqDotp01ZR8pn/1L0JN9d9XNyx0AAAAAElFTkSuQmCC';
	}

	/**
	 * Normalize robots.
	 *
	 * @param array $robots Array to normalize.
	 *
	 * @return array
	 */
	private function normalize_robots( $robots ) {
		if ( ! is_array( $robots ) || empty( $robots ) ) {
			$robots = Helper::get_robots_defaults();
		}

		return array_fill_keys( $robots, true );
	}

	/**
	 * Normalize advanced robots.
	 *
	 * @param array $advanced_robots Array to normalize.
	 *
	 * @return array
	 */
	private function normalize_advanced_robots( $advanced_robots ) {
		if ( ! empty( $advanced_robots ) ) {
			return $advanced_robots;
		}

		return Helper::get_advanced_robots_defaults();
	}

	/**
	 * Return power words.
	 *
	 * @return array
	 */
	private function power_words() {
		static $words;
		$locale = Locale::get_site_language();
		$file   = rank_math()->plugin_dir() . 'assets/vendor/powerwords/' . $locale . '.php';
		if ( ! file_exists( $file ) ) {
			return $this->do_filter( 'metabox/power_words', [], $locale );
		}

		$words = $words ? $words : include $file;
		return $this->do_filter( 'metabox/power_words', array_map( 'strtolower', $words ), $locale );
	}

	/**
	 * Get diacritics (accents).
	 *
	 * @return array
	 */
	private function diacritics() {
		$locale = Locale::get_site_language();
		$locale = in_array( $locale, [ 'en', 'de', 'ru' ], true ) ? $locale : 'en';
		$file   = rank_math()->plugin_dir() . 'assets/vendor/diacritics/' . $locale . '.php';
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$diacritics = include_once $file;
		return $this->do_filter( 'metabox/diacritics', $diacritics, $locale );
	}

	/**
	 * Load required screen.
	 *
	 * @param string $manual To load any screen manually.
	 */
	public function load_screen( $manual = '' ) {
		if ( Admin_Helper::is_post_edit() || 'post' === $manual ) {
			$this->screen = new Post_Screen();
			return;
		}

		if ( Admin_Helper::is_term_edit() || 'term' === $manual ) {
			$this->screen = new Taxonomy_Screen();
			return;
		}

		if ( User_Screen::is_enable() || 'user' === $manual ) {
			$this->screen = new User_Screen();
			return;
		}
	}
}
