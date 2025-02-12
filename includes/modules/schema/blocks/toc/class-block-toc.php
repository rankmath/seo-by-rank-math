<?php
/**
 * The TOC Block
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use WP_Block_Type_Registry;
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * HowTo Block class.
 */
class Block_TOC extends Block {

	use Hooker;

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/toc-block';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_TOC
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_TOC instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_TOC
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_TOC ) ) {
			self::$instance = new Block_TOC();
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

		$this->filter( 'rank_math/schema/block/toc-block', 'add_graph', 10, 2 );
		$this->filter( 'render_block_rank-math/toc-block', 'render_toc_block_content', 10, 2 );
		$this->filter( 'rank_math/metabox/post/values', 'block_settings_metadata' );
		$this->action( 'admin_enqueue_scripts', 'add_json_data' );
		register_block_type( RANK_MATH_PATH . 'includes/modules/schema/blocks/toc/block.json' );
		$this->action( 'wp_enqueue_scripts', 'register_block_style' );
	}

	/**
	 * Register block style.
	 */
	public function register_block_style() {
		wp_register_style( 'rank-math-toc-block', rank_math()->plugin_url() . 'includes/modules/schema/blocks/toc/assets/css/toc_list_style.css', [], rank_math()->version );
	}

	/**
	 * Add meta data to use in the TOC block.
	 *
	 * @param array $values Aray of tabs.
	 *
	 * @return array
	 */
	public function block_settings_metadata( $values ) {
		$values['tocTitle']           = Helper::get_settings( 'general.toc_block_title' );
		$values['tocExcludeHeadings'] = Helper::get_settings( 'general.toc_block_exclude_headings', [] );
		$values['listStyle']          = Helper::get_settings( 'general.toc_block_list_style', 'ul' );

		return $values;
	}

	/**
	 * Add default Block values on FSE Template page.
	 *
	 * @return void
	 */
	public function add_json_data() {
		if ( Param::get( 'postType' ) !== 'wp_template' ) {
			return;
		}

		Helper::add_json( 'tocTitle', Helper::get_settings( 'general.toc_block_title' ) );
		Helper::add_json( 'tocExcludeHeadings', Helper::get_settings( 'general.toc_block_exclude_headings', [] ) );
		Helper::add_json( 'listStyle', Helper::get_settings( 'general.toc_block_list_style', 'ul' ) );
	}

	/**
	 * Add default TOC title.
	 *
	 * @param string $block_content Block content.
	 * @param array  $parsed_block  The full block, including name and attributes.
	 *
	 * @return string
	 */
	public function render_toc_block_content( $block_content, $parsed_block ) {
		wp_enqueue_style( 'rank-math-toc-block' );
		if ( isset( $parsed_block['attrs']['title'] ) ) {
			return $block_content;
		}

		$title = Helper::get_settings( 'general.toc_block_title' );
		if ( ! $title ) {
			return $block_content;
		}

		$allowed_tags  = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ];
		$title_wrapper = isset( $parsed_block['attrs']['titleWrapper'] ) && in_array( $parsed_block['attrs']['titleWrapper'], $allowed_tags, true ) ? $parsed_block['attrs']['titleWrapper'] : 'h2';

		$block_content = preg_replace_callback(
			'/(<div class=".*?wp-block-rank-math-toc-block.*?"\>)/i',
			function( $value ) use ( $title, $block_content, $title_wrapper ) {
				if ( ! isset( $value[0] ) ) {
					return $block_content;
				}

				$value[0] = str_replace( '>', ' id="rank-math-toc">', $value[0] );
				return $value[0] . '<' . tag_escape( $title_wrapper ) . '>' . esc_html( $title ) . '</' . tag_escape( $title_wrapper ) . '>';
			},
			$block_content
		);
		$block_content = str_replace( 'class=""', '', $block_content );

		return apply_filters(
			'rank_math/schema/block/toc/content',
			wp_kses_post( $block_content ),
			$block_content,
			$parsed_block['attrs'],
		);
	}

	/**
	 * Add TOC schema data in JSON-LD array.
	 *
	 * @param array $data  Array of JSON-LD data.
	 * @param array $block JsonLD Instance.
	 *
	 * @return array
	 */
	public function add_graph( $data, $block ) {
		$attributes = $block['attrs'];
		// Early bail.
		if ( empty( $attributes['headings'] ) ) {
			return $data;
		}

		if ( ! isset( $data['toc'] ) ) {
			$data['toc'] = [];
		}

		foreach ( $attributes['headings'] as $heading ) {
			if ( ! empty( $heading['disable'] ) ) {
				continue;
			}

			$data['toc'][] = [
				'@context' => 'https://schema.org',
				'@type'    => 'SiteNavigationElement',
				'@id'      => '#rank-math-toc',
				'name'     => esc_html( $heading['content'] ),
				'url'      => esc_url( get_permalink() . $heading['link'] ),
			];
		}

		if ( empty( $data['toc'] ) ) {
			unset( $data['toc'] );
		}

		return $data;
	}
}
