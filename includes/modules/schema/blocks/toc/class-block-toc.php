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
	 * Toc options per attributes|options settings.
	 *
	 * @var array
	 */
	private $toc_options = [];

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

		add_shortcode( 'rank_math_toc', [ $this, 'render_toc_contents' ] );

		$this->filter( 'rank_math/schema/block/toc-block', 'add_graph', 10, 2 );
		// $this->filter( 'render_block_rank-math/toc-block', 'render_toc_block_content', 10, 2 );
		$this->filter( 'rank_math/metabox/post/values', 'block_settings_metadata' );
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
		$values['tocExcludeHeadings'] = Helper::get_settings( 'general.toc_block_exclude_headings', [] );
		$values['listStyle']          = Helper::get_settings( 'general.toc_block_list_style', 'ul' );

		return $values;
	}

	/**
	 * Renders the toc contents.
	 * Either for the Gutenberg or shortcode.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @return mixed|string|void
	 */
	public function render_toc_contents( $attributes = [] ) {
		$post_content = null;
		if ( ! isset( $attributes['postId'] ) ) {
			global $post;
			$post_content = $post->post_content;

		} else {
			global $wpdb;
			$sql  = "SELECT post_content FROM {$wpdb->posts} WHERE ID=%s";
			$post = $wpdb->get_results( $wpdb->prepare( $sql, $attributes['postId'] ) ); // phpcs:ignore
			if ( ! $post ) {
				exit();
			}

			$post_content = $post[0]->post_content;

		}

		// Update toc_options.
		$this->set_toc_options( $attributes );
		$toc_options         = $this->toc_options;
		$headings_to_include = [ '1', '2', '3', '4', '5', '6' ];

		if ( isset( $toc_options['excludeHeadings'] ) ) {
			$excluded = [];
			foreach ( $toc_options['excludeHeadings'] as $exclude ) {
				$excluded[] = str_replace( 'h', '', $exclude );
			}

			$headings_to_include = array_diff( $headings_to_include, $excluded );
		}

		// No headings to show because all are excluded!
		if ( ! $headings_to_include ) {
			ob_start();
			?>
			<div class="components-placeholder is-large">
				<div aria-hidden="true">
				</div>
				<div class="components-placeholder__label"><?php esc_html_e( 'Table of Contents', 'rank-math' ); ?></div>
				<fieldset class="components-placeholder__fieldset">
					<legend class="components-placeholder__instructions"> <?php esc_html_e( 'No headings to show because all are excluded.', 'rank-math' ); ?>
					</legend>
				</fieldset>
			</div>

			<?php
			return ob_get_clean();
		}

		$included_string = implode( '|', $headings_to_include );

		$heading_pattern = sprintf( '/(<h([%1$s]{1})[^>].*id="(.*)".*>)(.*)<\/h\2>/msuUD', $included_string );

		preg_match_all( $heading_pattern, $post_content, $headings, PREG_SET_ORDER );

		// No headings!
		if ( ! $headings ) {
			ob_start();
			?>
			<div class="components-placeholder is-large">
				<div aria-hidden="true">
				</div>
				<div class="components-placeholder__label"><?php esc_html_e( 'Table of Contents', 'rank-math' ); ?></div>
				<fieldset class="components-placeholder__fieldset">
					<legend class="components-placeholder__instructions"> <?php esc_html_e( 'Add Heading blocks to this page to generate the Table of Contents.', 'rank-math' ); ?>
					</legend>
				</fieldset>
			</div>

			<?php
			return ob_get_clean();
		}

		return $this->toc_output( $headings, $attributes );

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
	 * Sets the toc options for shortcode or the gutenberg block.
	 *
	 * @param  array $attributes The toc gutenberg attributes.
	 * @return void.
	 */
	public function set_toc_options( $attributes = [] ) {
		$toc_options['className']       = $attributes['className'] ?? Helper::get_settings( 'general.toc_block_class_name' );
		$toc_options['titleWrapper']    = $attributes['titleWrapper'] ?? Helper::get_settings( 'general.toc_block_title_wrapper', 'h2' );
		$toc_options['title']           = $attributes['title'] ?? Helper::get_settings( 'general.toc_block_title' );
		$toc_options['excludeHeadings'] = $attributes['excludeHeadings'] ?? Helper::get_settings( 'general.toc_block_exclude_headings', [] );
		$toc_options['listStyle']       = $attributes['listStyle'] ?? Helper::get_settings( 'general.toc_block_list_style', 'ul' );
		$this->toc_options              = $toc_options;
	}


	/**
	 * TOC heading list HTML.
	 *
	 * @param array $headings The heading and their children.
	 * @param array $attributes The attributes if gutenberg block.
	 * @return string|mixed The list HTML.
	 */
	private function toc_output( $headings, $attributes = [] ) {

		$toc_options = $this->toc_options;
		// Settings.
		$title_wrapper = $toc_options['titleWrapper'];
		$list_style    = $toc_options['listStyle'];
		$title         = $toc_options['title'];

		$class = 'rank-math-block';
		if ( ! empty( $toc_options['className'] ) ) {
			$class .= ' ' . esc_attr( $toc_options['className'] );
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

		// Heading array contains [heading markup, attr, heading_level, link|anchor, content]!
		foreach ( $headings as $key => $heading ) {
			// Nest lists accordingly, 3 variants <ul>, </ul> or none.
			$out[] = $this->list_prepend_or_append( $key, $heading, $headings, $list_tag, $item_tag, $out );
		}

		$out[] = sprintf( '</%1$s></nav>', $list_tag );
		$out[] = '</div>';
		return join( "\n", $out );

	}


	/**
	 * Nest lists accordingly, 3 variants 'prepend <ul>' || 'append </ul>' || none .
	 *
	 * @param int    $key          The index of heading in the heading list array.
	 * @param array  $heading      The current heading in the loop.
	 * @param array  $heading_list The list of headingsfound in post content.
	 * @param string $list_tag     List tag as saved in option settings or the block attr.
	 * @param string $item_tag     The list item tag according to the option settings or the block attr.
	 * @param array  $output       The array output upto the lastitem in the list without closing (</ul>) the main list.
	 *
	 * @return string|void
	 */
	private function list_prepend_or_append( $key, $heading, $heading_list, $list_tag, $item_tag, $output ) {
		// Nest lists accordingly, 3 variants 'prepend <ul>' || 'append </ul>' || none.

		// The last item in the list.
		if ( 0 === count( $heading_list ) || count( $heading_list ) - 1 === $key ) {
			// Search if <ul> or </ul> occur last in the html, output and add a </ul> (close the list) if needed.
			$output = join( "\n", $output );

			$list_pattern = sprintf( '/(<\/%1$s>)|(<%1$s>)/msuUD', $list_tag );
			preg_match_all( $list_pattern, $output, $matches );

			if ( 1 < count( $matches ) ) {
				if ( sprintf( '<%1$s>', $list_tag ) === end( $matches[0] ) ) {
					return sprintf(
						'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a></%5$s></%1$s>',
						$item_tag,
						$heading[2],
						$heading[3],
						$heading[4],
						$list_tag,
					);
				}
			}
			return sprintf(
				'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a></%1$s>',
				$item_tag,
				$heading[2],
				$heading[3],
				$heading[4],
			);
		}

		$next_heading = $heading_list[ $key + 1 ];

		// TRUE if h3 == h3!
		if ( $heading[2] === $next_heading[2] ) {

			return sprintf(
				'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a></%1$s>',
				$item_tag,
				$heading[2],
				$heading[3],
				$heading[4],
			);
		}

		// TRUE if h2 < h3!
		if ( $heading[2] < $next_heading[2] ) {
			return sprintf(
				'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a><%5$s></%1$s>',
				$item_tag,
				$heading[2],
				$heading[3],
				$heading[4],
				$list_tag,
			);
		}
		// TRUE if h3 > h2!
		if ( $heading[2] > $next_heading[2] ) {
			return sprintf(
				'<%1$s class="rank-math-toc-heading-level-%2$s"><a href="#%3$s">%4$s</a></%5$s></%1$s>',
				$item_tag,
				$heading[2],
				$heading[3],
				$heading[4],
				$list_tag,
			);
		}
	}

}
