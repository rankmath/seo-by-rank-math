<?php
/**
 * The TOC Block
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use WP_Block_Type_Registry;
use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Content AI Command Block class.
 */
class Block_Command {

	use Hooker;

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/command';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_Command
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_Command instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_Command
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_Command ) ) {
			self::$instance = new Block_Command();
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

		register_block_type( RANK_MATH_PATH . 'includes/modules/content-ai/blocks/command/assets/src/block.json' );
	}
}
