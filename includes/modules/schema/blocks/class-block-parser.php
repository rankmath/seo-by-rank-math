<?php
/**
 * The Block Parser
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Block_Parser class.
 */
class Block_Parser {

	use Hooker;

	/**
	 * Holds the parsed blocks.
	 *
	 * @var array
	 */
	private $blocks = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/json_ld', 'parse', 8 );
	}

	/**
	 * Filter function to add Blocks data in schema.
	 *
	 * @param array $data Array of JSON-LD data.
	 *
	 * @return array
	 */
	public function parse( $data ) {
		if ( ! function_exists( 'parse_blocks' ) || ! is_singular() ) {
			return $data;
		}

		$this->get_parsed_blocks();
		foreach ( $this->blocks as $block_type => $blocks ) {
			foreach ( $blocks as $block ) {
				/**
				 * Filter: 'rank_math/schema/block/<block-type>' - Allows filtering graph output per block.
				 *
				 * @param array $data  Array of JSON-LD data.
				 * @param array $block The block.
				 */
				$data = $this->do_filter( 'schema/block/' . $block_type, $data, $block );
			}
		}

		return $data;
	}

	/**
	 * Parse the blocks and loop through them.
	 */
	private function get_parsed_blocks() {
		$post          = get_post();
		$parsed_blocks = parse_blocks( $post->post_content );

		/**
		 * Filter: 'rank_math/schema/block/before_filter'
		 *
		 * @param array $parsed_blocks Array of parsed blocks.
		 */
		$parsed_blocks = $this->do_filter( 'schema/block/before_filter', $parsed_blocks );

		$this->filter_blocks( $parsed_blocks );
	}

	/**
	 * Filter blocks.
	 *
	 * @param array $blocks Blocks to filter.
	 */
	private function filter_blocks( $blocks ) {
		foreach ( $blocks as $block ) {
			if ( $this->is_nested_block( $block ) || ! $this->is_valid_block( $block ) ) {
				continue;
			}

			$name = \str_replace( 'rank-math/', '', $block['blockName'] );
			$name = strtolower( $name );
			if ( ! isset( $this->blocks[ $name ] ) || ! is_array( $this->blocks[ $name ] ) ) {
				$this->blocks[ $name ] = [];
			}

			$this->blocks[ $name ][] = $block;
		}
	}

	/**
	 * Is nested block.
	 *
	 * @param array $block Block.
	 *
	 * @return boolean
	 */
	private function is_nested_block( $block ) {
		if ( empty( $block['blockName'] ) ) {
			return false;
		}

		/**
		 * Filter: 'rank_math/schema/nested_blocks' - Allows filtering for nested blocks.
		 *
		 * @param array $data  Array of JSON-LD data.
		 * @param array $block The block.
		 */
		$nested = $this->do_filter(
			'schema/nested_blocks',
			[
				'core/group',
				'core/columns',
				'core/column',
			]
		);

		if ( ! in_array( $block['blockName'], $nested, true ) ) {
			return false;
		}

		$this->filter_blocks( $block['innerBlocks'] );

		return true;
	}

	/**
	 * Is block valid.
	 *
	 * @param array $block Block.
	 *
	 * @return boolean
	 */
	private function is_valid_block( $block ) {
		return ! empty( $block['blockName'] ) && Str::starts_with( 'rank-math', $block['blockName'] );
	}
}
