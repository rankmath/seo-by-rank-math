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
			array(
				'render_callback' => array( $this, 'render' ),
			)
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

	public function render( $attributes ) {
		// @TODO cache with count($attributes['headings']) maybe

		$headings     = $this->linear_to_nested_heading_list( $attributes['headings'] );
		$list_out_put = $this->list_output( $headings );

		/**
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

		// Settings
		$title_wrapper    = $attributes['titleWrapper'];
		$list_style       = Helper::get_settings( 'general.toc_block_list_style' ) ?? $attributes['listStyle'];
		$title            = ! empty( $attributes['title'] ) ? $attributes['title'] : Helper::get_settings( 'general.toc_block_title' );
		$exclude_headings = Helper::get_settings( 'general.toc_block_exclude_headings' );

		$list_tag = self::get()->get_list_style( $list_style );
		$item_tag = self::get()->get_list_item_style( $list_style );

		// dump($attributes);

		$class = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// HTML.
		$out   = array();
		$out[] = sprintf(
			'<div id="rank-math-toc" class="%1$s"%2$s><%3$s>%4$s</%3$s>',
			$class,
			self::get()->get_styles( $attributes ),
			$attributes['titleWrapper'],
			$title,
		);

		$out[] = '<nav><div>';
		$out[] = $list_out_put;
		$out[] = '</div></nav>';
		$out[] = '</div>';

		return join( "\n", $out );

	}

	/**
	 * Add default TOC title.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block         The full block, including name and attributes.
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
			$data['toc'] = array();
		}

		foreach ( $attributes['headings'] as $heading ) {
			if ( ! empty( $heading['disable'] ) ) {
				continue;
			}

			$data['toc'][] = array(
				'@context' => 'https://schema.org',
				'@type'    => 'SiteNavigationElement',
				'@id'      => '#rank-math-toc',
				'name'     => $heading['content'],
				'url'      => get_permalink() . $heading['link'],
			);
		}

		if ( empty( $data['toc'] ) ) {
			unset( $data['toc'] );
		}

		return $data;
	}


	/**
	 * Nest heading based on the Heading level.
	 *
	 * @param array $heading_list The flat list of headings to nest.
	 *
	 * @return array The nested list of headings.
	 */
	private function linear_to_nested_heading_list( $heading_list ) {
		$nexted_heading_list = array();
		// dump($heading_list);
		foreach ( $heading_list as $key => $heading ) {
			if ( empty( $heading['content'] ) ) {
				continue;
			}

			// Make sure we are only working with the same level as the first iteration in our set.
			if ( $heading['level'] === $heading_list[0]['level'] ) {

				// Propagate to children only (those whose level is lower than their parent ie $heading['level'].
				// @TODO.. don't pass on higher level headers below lower, this causes mulitple values of the same!!!
				// @TODO slice upto where we have the higher level because that's where the children end!
				if ( isset( $heading_list[ $key + 1 ]['level'] ) && $heading_list[ $key + 1 ]['level'] > $heading['level'] ) {

					// endOfSlice should be upto where heading level is smaller than then current
					$endOfSlice = $this->get_end_of_slice( $heading_list );
					$children   = $this->linear_to_nested_heading_list(
						array_slice( $heading_list, $key + 1, $endOfSlice )
					);

					array_push(
						$nexted_heading_list,
						array(
							'item'     => $heading,
							'children' => $children,
						)
					);
				} else {
					array_push(
						$nexted_heading_list,
						array(
							'item'     => $heading,
							'children' => null,
						)
					);
				}
			} elseif ( $heading['level'] < $heading_list[0]['level'] ) {
				$endOfSlice  = count( $heading_list );
				$items_array = array_slice( $heading_list, $key, $endOfSlice );
				$items       = $this->linear_to_nested_heading_list( $items_array );

				array_push( $nexted_heading_list, $items[0] );

			}
		}

		return $nexted_heading_list;

	}

	private function list_output( $headings ) {
		$out[] = '<ul>';

		foreach ( $headings as $heading ) {
			$out[] = sprintf(
				'<div><a href="%1$s">%2$s</a></div>',
				$heading['item']['link'],
				$heading['item']['content']
			);

			if ( $heading['children'] ) {
				$out[] = $this->list_output( $heading['children'] );
			}
		}

		$out[] = '</ul>';
		return join( "\n", $out );

	}

	private function get_end_of_slice( $list ) {

		foreach ( $list as $key => $item ) {

			if ( $list[0]['level'] > $item['level'] && 0 !== $key ) {
				return $key - 1;
			}
		}

		return count( $list );
	}
}
