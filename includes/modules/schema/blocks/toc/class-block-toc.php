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
		$this->filter( 'render_block_rank-math/toc-block', 'render_toc_block_content', 10, 2 );
		$this->filter( 'rank_math/metabox/post/values', 'block_settings_metadata' );
		//register_block_type( RANK_MATH_PATH . 'includes/modules/schema/blocks/toc/block.json' );
		register_block_type(
			RANK_MATH_PATH . 'includes/modules/schema/blocks/toc/block.json',
			[
				'render_callback' => [ $this, 'render'],
				//'editor_style'    => 'rank-math-block-admin',
				'attributes'      => [
					'title' => [
						'type'    => 'string',
						'default' => '',
					],
					'headings' => [
						'type'    => 'array',
						'default' => [],
						'items'   => [ 'type' => 'object' ],
					],
					'listStyle'   => [
						'type'    => 'string',
					],
					'titleWrapper'           => [
						'type'    => 'string',
						'default' => 'h2',
					],
					'excludeHeadings' => [
						'type'    => 'array',
						'default' => '',
					],
					'textAlign'         => [
						'type'    => 'string',
						'default' => 'left',
					],
				],
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
		$values['tocExcludeHeadings'] = Helper::get_settings( 'general.toc_block_exclude_headings', [] );
		$values['listStyle']          = Helper::get_settings( 'general.toc_block_list_style', 'ul' );

		return $values;
	}

	public function render( $attributes ) {
		$list_tag = self::get()->get_list_style( $attributes['listStyle'] );
		$item_tag = self::get()->get_list_item_style( $attributes['listStyle'] );
		$class    = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// TODO:: loop through headings and group them together with levels.
		// TODO:: maybe an issue with how they are loaded to start with.

		$title = ! empty( $attributes['title'] ) ? $attributes['title'] : Helper::get_settings( 'general.toc_block_title' );

		//dump(self::get()->get_styles( $attributes ));
//		dump($title);
		dump($attributes);
//		dump(Helper::get_settings( 'general.toc_block_title' ));

		## @TODO for heading children

		// HTML.
		$out   = [];
		$out[] = sprintf(
			'<div id="rank-math-toc" class="%1$s"%2$s><%3$s>%4$s</%3$s>',
			$class,
			self::get()->get_styles( $attributes ),
			$attributes['titleWrapper'],
			$title,
		);
		//$out[] = sprintf( '<%1$s class="rank-math-list %2$s">', $list_tag, $attributes['listCssClasses'] );
		$out[] = '<nav><div>';
		foreach ( $attributes['headings'] as $heading ) {

			if ( empty( $heading['link'] ) ) {
				continue;
			}

			dump($heading['level']);
			dump($heading['content']);

			$out[] = sprintf(
				'<div><a href="%1$s">%2$s</a></div>',
				$heading['link'],
				$heading['content']
			);
			//dump($heading);
		}
//		dump($class);
//		dump($list_tag);
//		dump($item_tag);
//		dump($attributes);

		//$out[] = sprintf( '</%1$s>', $list_tag );
		$out[] ='</div></nav>';
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

		$block_content = preg_replace_callback( '/(<div class=".*?wp-block-rank-math-toc-block.*?"\>)/i', function( $value ) use ( $title, $block_content ) {
			if ( ! isset( $value[0] ) ) {
				return $block_content;
			}

			$value[0] = str_replace( '>', ' id="rank-math-toc">', $value[0] );
			return $value[0] . '<h2>' . esc_html( $title ) . '</h2>';
		}, $block_content );

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
				"@context" => 'https://schema.org',
				"@type"    => 'SiteNavigationElement',
				"@id"      => '#rank-math-toc',
				"name"     => $heading['content'],
				"url"      => get_permalink() . $heading['link'],
			];
		}

		if ( empty( $data['toc'] ) ) {
			unset( $data['toc'] );
		}

		return $data;
	}
}
