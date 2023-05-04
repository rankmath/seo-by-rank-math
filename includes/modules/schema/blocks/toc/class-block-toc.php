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
	 * To debug heading list hook because it's called twice.
	 * @TODO delete
	 * @var bool
	 */
	private static $called = false;

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
				'render_callback' => [ $this, 'render' ],
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

	public function render( $attributes ) {
		// @TODO cache with count($attributes['headings']) maybe

		if (self::$called) {
			return;
		} else {
			$headings     = $this->just_a_test( $attributes['headings'] );
			//dump($headings);
			self::$called = true;
		}


		//$headings     = $this->linear_to_nested_heading_list( $attributes['headings'] );
		$list_out_put = $this->list_output( $headings );

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

		// Settings.
		$title_wrapper    = $attributes['titleWrapper'];
		$list_style       = Helper::get_settings( 'general.toc_block_list_style' ) ?? $attributes['listStyle'];
		$title            = ! empty( $attributes['title'] ) ? $attributes['title'] : Helper::get_settings( 'general.toc_block_title' );
		$exclude_headings = Helper::get_settings( 'general.toc_block_exclude_headings' );

		$list_tag = self::get()->get_list_style( $list_style );
		$item_tag = self::get()->get_list_item_style( $list_style );

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
	 * Nest heading based on the Heading level.
	 *
	 * @param array $heading_list The flat list of headings to nest.
	 * @param bool $is_children Identify the parsed list if children.
	 *
	 * @return array The nested list of headings.
	 */
	private function linear_to_nested_heading_list( $heading_list, $is_children = false) {
		$nexted_heading_list = [];
		foreach ( $heading_list as $key => $heading ) {
			if ( empty( $heading['content'] ) ) {
				continue;
			}

			// Make sure we are only working with the same level as the first iteration in our set.
			if ( $heading['level'] === $heading_list[0]['level']) {

				// Propagate to children only (those whose level is lower than their parent ie $heading['level'].
				if ( isset( $heading_list[ $key + 1 ]['level'] ) && $heading_list[ $key + 1 ]['level'] > $heading['level'] ) {
					// endOfSlice should be upto where heading level is smaller (higher level) than then current.
					$end_of_slice   = $this->get_end_of_slice( $heading_list );
					//$end_of_slice   = count($heading_list);
					$children_array = array_slice( $heading_list, $key + 1, $end_of_slice );
					$children       = $this->linear_to_nested_heading_list( $children_array, true );
//					dump('|||||||||| here ||||||||||||||');
//					dump('|||||||||| here ||||||||||||||');
//					dump($end_of_slice);
//					dump($children_array);
//					dump($heading_list);
//					dump('|||||||||| here ||||||||||||||');
//					dump('|||||||||| here ||||||||||||||');
						$nexted_heading_list[] = [
							'item'     => $heading,
							'children' => $children,
						];
					} else {
					// if there are lower level headers in the $heading_list, that should disqualify any other higher level headers from being added here as they are added below in elseif block!
					// We check for the presiding heading ($key - 1), but to be more accurate, we should check for the highest level in $heading_list[i] and work with that

					if (!(isset($heading_list[$key - 1]) && $heading_list[$key - 1]['level'] < $heading['level'])) {
						$nexted_heading_list[] = [
							'item'     => $heading,
							'children' => null,
						];
					}

				}
				// BUG for heading listing pattern in http://localhost:10004/uncategorized/test-more-about-toc-001/256/
			} elseif ( $heading['level'] < $heading_list[0]['level'] && !$is_children) {
			//} elseif ( $heading['level'] < $heading_list[0]['level'] ) {
			//} else {

				//dump($heading_list);
				//if ($heading['level'] < $heading_list[0]['level']) {
					$end_of_slice = count( $heading_list );
					$items_array  = array_slice( $heading_list, $key, $end_of_slice );
					$items        = $this->linear_to_nested_heading_list( $items_array );
//					dump( '********** THE OTHER BUGS ********' );
//					dump( '********** THE OTHER BUGS ********' );
//					dump( $heading_list[0]['content'] );
//					dump( $heading['content'] );
//					dump( $heading_list[0]['level'] );
//					dump( $heading['level'] );
//					dump($heading['level'] < $heading_list[0]['level']);
//					dump( $items_array );
//					dump( $items );
//					dump( '********** THE OTHER BUGS ********' );
//					dump( '********** THE OTHER BUGS ********' );
					$nexted_heading_list[] = $items[0];
				//}

			}
		}

		return $nexted_heading_list;

	}

	/**
	 * TOC heading list HTML.
	 *
	 * @param array $headings The heading and their children.
	 * @return string|mixed The list HTML.
	 */
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

	/**
	 * Gets the point|length of the array.
	 *
	 * @param array $list Heading list.
	 *
	 * @return int|string The length of the array.
	 */
	private function get_end_of_slice( $list, $level ) {
		foreach ( $list as $key => $item ) {

			// @TODO solution is to get the last item (instead of the first) where heading level is higher than the passed level!!!
			// @TODO we can use key + 1 to have this detail!!
			// Make sure there are lower level headings before the higher level heading as well.
//			dump("************* here *********");
//			dump("************* here *********");
//			dump("++++++++++ list[key + 1]['level'] ++++++++++");
//			dump("++++++++++ list[key + 1]['level'] ++++++++++");
//			dump($level);
//			dump($item['level']);
////			dump($list[$key + 1]['level'] );
//			dump($item['content']);
////			dump($list[$key + 1]['content'] );
////			dump($list[$key + 1]['level'] <= $level && $level < $item['level']);
//			dump("++++++++++ list[key + 1]['level'] ++++++++++");
//			dump("++++++++++ list[key + 1]['level'] ++++++++++");
//			dump("************* here *********");
//			dump("************* here *********");

			//if ( $list[0]['level'] > $item['level'] || (isset($list[$key + 1]['level']) && $list[$key + 1]['level'] < $item['level'])) {
			// Level is greater (higher) the next element in the loop.
			// Heading level for the next element in the loop is higher (less) than the passed level.
			if ( $list[$key + 1]['level'] <= $level ) {

				// After above check
				// Current heading level is lower than the passed level ge h2 < h3
				// I think we should start with this logic and apply the other
				if ($level < $item['level']) {
					return $key - 1;
				}
			//if ( $level < $item['level']  ) {
//				dump("**************");
//			dump("**************");
//			dump($key);
//			dump($item['content']);
//			dump($item['level']);
//			dump($list[$key + 1]['level']);
//			dump("**************");
//			dump("**************");
				//return $key - 1;
//				dump("************* here *********");
//				dump("************* here *********");
////				dump($item['level']);
////				dump($level);
////				dump($key);
//				dump($item);
//				dump("************* here *********");
//				dump("************* here *********");
//				return $key - 1;
			}
//			elseif ( $list[$key + 1]['level'] > $item['level'] ) {
//				return $key;
//			}
		}
		return count($list) - 1;

	}


	private function just_a_test($heading_list) {
		//dump($heading_list);

		$nested_heading_list = [];
		foreach ($heading_list as $key => $heading ) {
			//dump($heading);

			// Make sure we're dealing with same or higher level headings only.
			if (  $heading['level'] === $heading_list[0]['level'] || $heading['level'] <  $heading_list[0]['level'] ) {

				// Has children.
				if ( isset( $heading_list[ $key + 1 ]['level'] ) && $heading_list[ $key + 1 ]['level'] > $heading_list[0]['level'] ) {

					$end_of_slice   = $this->get_end_of_slice( $heading_list, $heading['level']);
					//$end_of_slice   = count($heading_list);
					$children_array = array_slice( $heading_list, $key + 1, $end_of_slice );

//					dump("+++++++++++++ children ++++++++++++++++++++");
//					dump("+++++++++++++ children ++++++++++++++++++++");
//					dump("+++++++++++++ children ++++++++++++++++++++");
//					dump($end_of_slice);
//					dump($children_array);
//					dump($heading_list);
//					dump($heading['level']);
//					dump("+++++++++++++ children ++++++++++++++++++++");
//					dump("+++++++++++++ children ++++++++++++++++++++");
//					dump("+++++++++++++ children ++++++++++++++++++++");
					$nested_heading_list[] = [
						'level'     => $heading['level'],
						'item'     => $heading,
						//'children' => $this->just_a_test($children_array),
						'children' => null,
					];
				} else {
					// Has no children.
					$nested_heading_list[] = [
						'level'     => $heading['level'],
						'item'     => $heading,
						'children' => null,
					];
				}

			}
		}

//		dump($heading_list);
//		dump($nested_heading_list);
		return $nested_heading_list;

	}
}
