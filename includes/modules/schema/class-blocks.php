<?php
/**
 * The Schema Blocks
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Blocks class.
 */
class Blocks {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init' );

		$filter = version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ? 'block_categories_all' : 'block_categories';
		$this->filter( $filter, 'block_categories' );
		$this->action( 'enqueue_block_editor_assets', 'editor_assets' ); // Backend.
	}

	/**
	 * Init blocks.
	 */
	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_style( 'rank-math-common', rank_math()->plugin_url() . 'assets/admin/css/common.css', null, rank_math()->version );
		wp_register_style(
			'rank-math-block-admin',
			rank_math()->plugin_url() . 'assets/admin/css/blocks.css',
			[
				'rank-math-common',
				'dashicons',
			],
			rank_math()->version
		);

		new Blocks\Admin();
		new Block_FAQ();
		new Block_HowTo();
		new Block_TOC();
		new Block_Schema();
	}

	/**
	 * Create a new (Rank Math) block category.
	 *
	 * @param array $categories Array of block categories.
	 *
	 * @return array
	 */
	public function block_categories( $categories ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'rank-math-blocks',
					'title' => __( 'Rank Math', 'rank-math' ),
					'icon'  => 'wordpress',
				],
			]
		);
	}

	/**
	 * Enqueue Styles and Scripts required for blocks at backend.
	 */
	public function editor_assets() {
		if ( ! $this->is_block_faq() && ! $this->is_block_howto() ) {
			return;
		}

		Helper::add_json(
			'blocks',
			[
				'faq'   => $this->is_block_faq(),
				'howTo' => $this->is_block_howto(),
			]
		);
	}

	/**
	 * Is FAQ Block enabled.
	 *
	 * @return boolean
	 */
	private function is_block_faq() {
		return true;
	}

	/**
	 * Is HowTo Block enabled.
	 *
	 * @return boolean
	 */
	private function is_block_howto() {
		return true;
	}
}
