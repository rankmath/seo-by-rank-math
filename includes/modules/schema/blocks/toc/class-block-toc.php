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

use RankMath\Paper\Paper;
use WP_Block_Type_Registry;
use RankMath\Helper;
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
		// $this->filter( 'render_block_rank-math/toc-block', 'render_toc_block_content', 10, 2 );
		$this->filter( 'rank_math/metabox/post/values', 'block_settings_metadata' );
		// register_block_type( RANK_MATH_PATH . 'includes/modules/schema/blocks/toc/block.json' );
		register_block_type(
			RANK_MATH_PATH . 'includes/modules/schema/blocks/toc/block.json',
			[
				'render_callback' => [ $this, 'render_toc_contents' ],
			]
		);

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
		$values['tocExcludeHeadings'] = Helper::get_settings( 'general.toc_block_exclude_headings', array() );
		$values['listStyle']          = Helper::get_settings( 'general.toc_block_list_style', 'ul' );

		return $values;
	}

	public function render_toc_contents( $attributes = [] ) {
		$post_content = null;
		if ( ! isset( $attributes['postId'] ) ) {
			global $post;
			$post_content = $post->post_content;

		} else {
			global $wpdb;
			$sql  = "SELECT post_content FROM {$wpdb->posts} WHERE ID=%s";
			$post = $wpdb->get_results( $wpdb->prepare( $sql, $attributes['postId'] ) );
			if ( ! $post ) {
				exit();
			}

			$post_content = $post[0]->post_content;

		}

		preg_match_all( '/(<h([1-6]{1})[^>].*id="(.*)".*>)(.*)<\/h\2>/msuU', $post_content, $headings, PREG_SET_ORDER );

		return $this->toc_output( $headings, $attributes );

		/**
		 *
		 * @TODO apply settings/options,
		 * @ /seo-by-rank-math/includes/modules/schema/blocks/views/options-general.php
		 * We have
		 * 1. attributes.titleWrapper
		 * 2. attributes.listStyle
		 * 3. attributes.title ?? rankMath.tocTitle
		 * 4. attributes.excludeHeadings ?? rankMath.tocExcludeHeadings
		 * Others to confirm include:
		 * 5. heading.disable
		 * 6. TagName = 'div' === ListStyle ? 'div' : 'li'
		 */

	}

	/**
	 * Add default TOC title.
	 *
	 * @param string $block_content Block content.
	 * @param array  $parsed_block         The full block, including name and attributes.
	 *
	 * @return string
	 */
	public function render_toc_block_content( $block_content, $parsed_block ) {
		if ( isset( $parsed_block['attrs']['title'] ) ) {
			return $block_content;
		}

		$title = Helper::get_settings( 'general.toc_block_title' );
		if ( ! $title ) {
			return $block_content;
		}

		$block_content = preg_replace_callback(
			'/(<div class=".*?wp-block-rank-math-toc-block.*?"\>)/i',
			function( $value ) use ( $title, $block_content ) {
				if ( ! isset( $value[0] ) ) {
					return $block_content;
				}

				$value[0] = str_replace( '>', ' id="rank-math-toc">', $value[0] );
				return $value[0] . '<h2>' . esc_html( $title ) . '</h2>';
			},
			$block_content
		);

		return str_replace( 'class=""', '', $block_content );
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
				'name'     => $heading['content'],
				'url'      => get_permalink() . $heading['link'],
			];
		}

		if ( empty( $data['toc'] ) ) {
			unset( $data['toc'] );
		}

		return $data;
	}


	/**
	 * TOC heading list HTML.
	 *
	 * @param array $headings The heading and their children.
	 * @param array $attributes The attributes if gutenberg block.
	 * @return string|mixed The list HTML.
	 */
	private function toc_output( $headings, $attributes = [] ) {

		// Settings.
		$title_wrapper    = $attributes['titleWrapper'] ?? 'h2';
		$list_style       = $attributes['listStyle'] ?? Helper::get_settings( 'general.toc_block_list_style' );
		$title            = $attributes['title'] ?? Helper::get_settings( 'general.toc_block_title' );
		$exclude_headings = $attributes['excludeHeadings'] ?? Helper::get_settings( 'general.toc_block_exclude_headings' );

		$class = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// HTML.
		$out   = [];
		$out[] = sprintf(
			'<div id="rank-math-toc" class="%1$s"%2$s><%3$s>%4$s</%3$s>',
			$class,
			self::get()->get_styles( $attributes ),
			$title_wrapper,
			$title,
		);

		// $list_tag = self::get()->get_list_style( $list_style );
		// $item_tag = self::get()->get_list_item_style( $list_style );

		$list_tag = $list_style;
		$item_tag = 'li';

		$out[] = sprintf( '<nav><%1$s>', $list_tag );

		foreach ( $headings as $heading ) {
			$out[] = sprintf(
				'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a></%1$s>',
				$item_tag,
				$heading[2],
				$heading[3],
				$heading[4],
			);

		}

		$out[] = sprintf( '</%1$s></nav>', $list_tag );
		$out[] = '</div>';
		return join( "\n", $out );

	}

}
