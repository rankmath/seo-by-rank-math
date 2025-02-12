<?php
/**
 * The Schema Block
 *
 * @since      1.0.233
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use WP_Block_Type_Registry;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Schema Block class.
 */
class Block_Schema {

	use Hooker;

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/rich-snippet';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_Schema
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_TOC instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_Schema
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_Schema ) ) {
			self::$instance = new Block_Schema();
		}

		return self::$instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( WP_Block_Type_Registry::get_instance()->is_registered( $this->block_type ) ) {
			return;
		}

		register_block_type(
			RANK_MATH_PATH . 'includes/modules/schema/blocks/schema/block.json',
			[
				'render_callback' => [ $this, 'rich_snippet' ],
			]
		);
	}

	/**
	 * Schema Block render callback.
	 *
	 * @param array $attributes Block Attributes.
	 */
	public function rich_snippet( $attributes ) {
		$output = '';
		foreach ( $attributes as $key => $value ) {
			$output .= $key . '="' . esc_attr( $value ) . '" ';
		}

		return do_shortcode( '[rank_math_rich_snippet ' . trim( $output ) . ']' );
	}
}
