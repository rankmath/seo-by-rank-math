<?php
/**
 * The Yoast Block Converter imports editor blocks (FAQ, HowTo, Local Business) from Yoast to Rank Math.
 *
 * @since      1.0.37
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast_Blocks class.
 */
class Yoast_Blocks extends \WP_Background_Process {

	/**
	 * FAQ Converter.
	 *
	 * @var Yoast_FAQ_Converter
	 */
	private $faq_converter;

	/**
	 * FAQ Converter.
	 *
	 * @var Yoast_HowTo_Converter
	 */
	private $howto_converter;

	/**
	 * TOC Converter.
	 *
	 * @var Yoast_TOC_Converter
	 */
	private $toc_converter;

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'convert_yoast_blocks';

	/**
	 * Main instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Yoast_Blocks
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Yoast_Blocks ) ) {
			$instance = new Yoast_Blocks();
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
		$posts = get_option( 'rank_math_yoast_block_posts' );
		delete_option( 'rank_math_yoast_block_posts' );
		Helper::add_notification(
			// Translators: placeholder is the number of modified posts.
			sprintf( _n( 'Blocks successfully converted in %d post.', 'Blocks successfully converted in %d posts.', $posts['count'], 'rank-math' ), $posts['count'] ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_yoast_block_posts',
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
			$this->faq_converter   = new Yoast_FAQ_Converter();
			$this->howto_converter = new Yoast_HowTo_Converter();
			$this->local_converter = new Yoast_Local_Converter();
			$this->toc_converter   = new Yoast_TOC_Converter();
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
		$dirty  = false;
		$blocks = $this->parse_blocks( $post->post_content );

		$content = '';
		if ( isset( $blocks['yoast/faq-block'] ) && ! empty( $blocks['yoast/faq-block'] ) ) {
			$dirty   = true;
			$content = $this->faq_converter->replace( $post->post_content, $blocks['yoast/faq-block'] );
		}

		if ( isset( $blocks['yoast/how-to-block'] ) && ! empty( $blocks['yoast/how-to-block'] ) ) {
			$dirty   = true;
			$content = $this->howto_converter->replace( $post->post_content, $blocks['yoast/how-to-block'] );
		}

		if ( isset( $blocks['yoast-seo/table-of-contents'] ) && ! empty( $blocks['yoast-seo/table-of-contents'] ) ) {
			$dirty   = true;
			$content = $this->toc_converter->replace( $post->post_content, $blocks['yoast-seo/table-of-contents'] );
		}

		if ( ! empty( array_intersect( array_keys( $blocks ), $this->local_converter->yoast_blocks ) ) ) {
			$dirty   = true;
			$content = $this->local_converter->replace( $post->post_content, $blocks );
		}

		if ( $dirty ) {
			$post->post_content = $content;
			wp_update_post( $post );
		}
	}

	/**
	 * Find posts with Yoast blocks.
	 *
	 * @return array
	 */
	public function find_posts() {
		$posts = get_option( 'rank_math_yoast_block_posts' );
		if ( false !== $posts ) {
			return $posts;
		}

		// FAQs Posts.
		$args = [
			's'             => 'wp:yoast/faq-block',
			'post_status'   => 'any',
			'numberposts'   => -1,
			'fields'        => 'ids',
			'no_found_rows' => true,
			'post_type'     => 'any',
		];
		$faqs = get_posts( $args );

		// HowTo Posts.
		$args['s'] = 'wp:yoast/how-to-block';
		$howto     = get_posts( $args );

		// TOC Posts.
		$args['s'] = 'wp:yoast-seo/table-of-contents';
		$toc       = get_posts( $args );

		// Local Business Posts.
		$args['s']      = ':yoast-seo-local/';
		$local_business = get_posts( $args );
		$posts          = array_merge( $faqs, $howto, $toc, $local_business );

		$posts_data = [
			'posts' => $posts,
			'count' => count( $posts ),
		];
		update_option( 'rank_math_yoast_block_posts', $posts_data );

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

			if ( 'yoast/faq-block' === $name ) {
				$block             = $this->faq_converter->convert( $block );
				$blocks[ $name ][] = \serialize_block( $block );
			}

			if ( 'yoast/how-to-block' === $name ) {
				$block             = $this->howto_converter->convert( $block );
				$blocks[ $name ][] = \serialize_block( $block );
			}

			if ( 'yoast-seo/table-of-contents' === $name ) {
				$block             = $this->toc_converter->convert( $block );
				$blocks[ $name ][] = \serialize_block( $block );
			}

			if ( in_array( $name, $this->local_converter->yoast_blocks, true ) ) {
				$block             = $this->local_converter->convert( $block );
				$blocks[ $name ][] = \serialize_block( $block );
			}
		}

		return $blocks;
	}
}
