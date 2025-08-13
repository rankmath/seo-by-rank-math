<?php
/**
 * The WooCommerce module - admin side functionality.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\WooCommerce;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Module\Base;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = __DIR__;
		$this->config(
			[
				'id'        => 'woocommerce',
				'directory' => $directory,
			]
		);
		parent::__construct();

		// Permalink Manager.
		$this->filter( 'rank_math/settings/general', 'add_general_settings' );
		$this->filter( 'rank_math/flush_fields', 'flush_fields' );

		$this->action( 'rank_math/admin/editor_scripts', 'enqueue' );
	}

	/**
	 * Enqueue script to analyze product's short description.
	 */
	public function enqueue() {
		$screen = get_current_screen();
		if ( ! Admin_Helper::is_post_edit() || 'product' !== $screen->post_type || ! $this->do_filter( 'woocommerce/analyze_short_description', true ) ) {
			return;
		}

		wp_enqueue_script( 'rank-math-description-analysis', rank_math()->plugin_url() . 'includes/modules/woocommerce/assets/js/woocommerce.js', [ 'rank-math-editor' ], rank_math()->version, true );
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_general_settings( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'woocommerce' => [
					'icon'  => 'rm-icon rm-icon-cart',
					'title' => esc_html__( 'WooCommerce', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'  => sprintf( esc_html__( 'Choose how you want Rank Math to handle your WooCommerce SEO. %s.', 'rank-math' ), '<a href="' . KB::get( 'woocommerce-settings', 'Options Panel WooCommerce Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => $this->directory . '/views/options-general.php',
					'json'  => [
						'brandTaxonomies' => Helper::get_object_taxonomies( 'product', 'choices', false ),
					],
				],
			],
			7
		);

		return $tabs;
	}

	/**
	 * Fields after updation of which we need to flush rewrite rules.
	 *
	 * @param array $fields Fields to flush rewrite rules on.
	 *
	 * @return array
	 */
	public function flush_fields( $fields ) {
		$fields[] = 'wc_remove_product_base';
		$fields[] = 'wc_remove_category_base';
		$fields[] = 'wc_remove_category_parent_slugs';

		return $fields;
	}
}
