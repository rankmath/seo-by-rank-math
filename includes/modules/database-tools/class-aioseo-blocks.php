<?php
/**
 * The AIOSEO Block Converter imports editor blocks (TOC) from AIOSEO to Rank Math.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * AIOSEO_Blocks class.
 */
class AIOSEO_Blocks extends \WP_Background_Process {

	/**
	 * TOC Converter.
	 *
	 * @var AIOSEO_TOC_Converter
	 */
	private $toc_converter;

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'convert_aioseo_blocks';

	/**
	 * Main instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return AIOSEO_Blocks
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof AIOSEO_Blocks ) ) {
			$instance = new AIOSEO_Blocks();
		}

		return $instance;
	}

	/**
	 * Start creating batches.
	 *
	 * @param array $posts Posts to process.
	 */
	public function start( $posts ) {
		$chunks = array_chunk( $posts, 10 );
		foreach ( $chunks as $chunk ) {
			$this->push_to_queue( $chunk );
		}

		$this->save()->dispatch();
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$posts = get_option( 'rank_math_aioseo_block_posts' );
		delete_option( 'rank_math_aioseo_block_posts' );
		Helper::add_notification(
			// Translators: placeholder is the number of modified posts.
			sprintf( _n( 'Blocks successfully converted in %d post.', 'Blocks successfully converted in %d posts.', $posts['count'], 'rank-math' ), $posts['count'] ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_aioseo_block_posts',
				'classes' => 'rank-math-notice',
			]
		);

		parent::complete();
	}

	/**
	 * Task to perform.
	 *
	 * @param string $posts Posts to process.
	 */
	public function wizard( $posts ) {
		$this->task( $posts );
	}

	/**
	 * Task to perform.
	 *
	 * @param array $posts Posts to process.
	 *
	 * @return bool
	 */
	protected function task( $posts ) {
		try {
			remove_filter( 'pre_kses', 'wp_pre_kses_block_attributes', 10 );
			$this->toc_converter = new AIOSEO_TOC_Converter();
			foreach ( $posts as $post_id ) {
				$post = get_post( $post_id );
				$this->convert( $post );
			}
			return false;
		} catch ( Exception $error ) {
			return true;
		}
	}

	/**
	 * Convert post.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function convert( $post ) {
		$dirty   = false;
		$blocks  = $this->parse_blocks( $post->post_content );
		$content = '';

		if ( isset( $blocks['aioseo/table-of-contents'] ) && ! empty( $blocks['aioseo/table-of-contents'] ) ) {
			$dirty   = true;
			$content = $this->toc_converter->replace( $post->post_content, $blocks['aioseo/table-of-contents'] );
		}

		if ( $dirty ) {
			$post->post_content = $content;
			wp_update_post( $post );
		}
	}

	/**
	 * Find posts with AIOSEO blocks.
	 *
	 * @return array
	 */
	public function find_posts() {
		$posts = get_option( 'rank_math_aioseo_block_posts' );
		if ( false !== $posts ) {
			return $posts;
		}

		// TOC Posts.
		$args = [
			's'             => 'wp:aioseo/table-of-contents ',
			'post_status'   => 'any',
			'numberposts'   => -1,
			'fields'        => 'ids',
			'no_found_rows' => true,
			'post_type'     => 'any',
		];

		$toc_posts  = get_posts( $args );
		$posts_data = [
			'posts' => $toc_posts,
			'count' => count( $toc_posts ),
		];
		update_option( 'rank_math_aioseo_block_posts', $posts_data, false );

		return $posts_data;
	}

	/**
	 * Parse blocks to get data.
	 *
	 * @param string $content Post content to parse.
	 *
	 * @return array
	 */
	private function parse_blocks( $content ) {
		$parsed_blocks = parse_blocks( $content );

		$blocks = [];
		foreach ( $parsed_blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			$name = strtolower( $block['blockName'] );
			if ( ! isset( $blocks[ $name ] ) || ! is_array( $blocks[ $name ] ) ) {
				$blocks[ $name ] = [];
			}

			if ( ! isset( $block['innerContent'] ) ) {
				$block['innerContent'] = [];
			}

			if ( 'aioseo/table-of-contents' === $name ) {
				$block             = $this->toc_converter->convert( $block );
				$blocks[ $name ][] = \serialize_block( $block );
			}
		}

		return $blocks;
	}
}
