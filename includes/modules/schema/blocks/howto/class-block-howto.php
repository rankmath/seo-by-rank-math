<?php
/**
 * The HowTo Block
 *
 * @since      1.0.233
 * @package    RankMath
 * @subpackage RankMath\HowTo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use WP_Block_Type_Registry;
use RankMath\Traits\Hooker;
use RankMath\Paper\Paper;
use RankMath\Helpers\Attachment;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * HowTo Block class.
 */
class Block_HowTo extends Block {

	use Hooker;

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	private $block_type = 'rank-math/howto-block';

	/**
	 * The single instance of the class.
	 *
	 * @var Block_HowTo
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_HowTo instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Block_HowTo
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Block_HowTo ) ) {
			self::$instance = new Block_HowTo();
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

		register_block_type(
			RANK_MATH_PATH . 'includes/modules/schema/blocks/howto/block.json',
			[
				'render_callback' => [ $this, 'render' ],
			]
		);

		add_filter( 'rank_math/schema/block/howto-block', [ $this, 'add_graph' ], 10, 2 );
	}

	/**
	 * Add HowTO schema data in JSON-LD array..
	 *
	 * @param array $data  Array of JSON-LD data.
	 * @param array $block JsonLD Instance.
	 *
	 * @return array
	 */
	public function add_graph( $data, $block ) {
		// Early bail.
		if ( ! $this->has_steps( $block['attrs'] ) ) {
			return $data;
		}

		$attrs = $block['attrs'];

		if ( ! isset( $data['howto'] ) ) {
			$data['howto'] = [
				'@type'       => 'HowTo',
				'name'        => Paper::get()->get_title(),
				'description' => isset( $attrs['description'] ) ? $this->clean_text( do_shortcode( $attrs['description'] ) ) : '',
				'totalTime'   => '',
				'step'        => [],
			];
		}

		$this->add_step_image( $data['howto'], $attrs );
		$this->add_duration( $data['howto'], $attrs );
		$permalink = get_permalink() . '#';

		foreach ( $attrs['steps'] as $index => $step ) {
			if ( empty( $step['visible'] ) ) {
				continue;
			}

			$schema_step = $this->add_step( $step, $permalink . $step['id'] );
			if ( $schema_step ) {
				$data['howto']['step'][] = $schema_step;
			}
		}

		return $data;
	}

	/**
	 * Render block content.
	 *
	 * @param array $attributes Array of attributes.
	 * @return string
	 */
	public static function markup( $attributes = [] ) {
		$list_style          = isset( $attributes['listStyle'] ) ? esc_attr( $attributes['listStyle'] ) : '';
		$list_css_classes    = isset( $attributes['listCssClasses'] ) ? esc_attr( $attributes['listCssClasses'] ) : '';
		$title_wrapper       = isset( $attributes['titleWrapper'] ) ? esc_attr( $attributes['titleWrapper'] ) : 'h3';
		$title_css_classes   = isset( $attributes['titleCssClasses'] ) ? esc_attr( $attributes['titleCssClasses'] ) : '';
		$content_css_classes = isset( $attributes['contentCssClasses'] ) ? esc_attr( $attributes['contentCssClasses'] ) : '';
		$size_slug           = isset( $attributes['sizeSlug'] ) ? esc_attr( $attributes['sizeSlug'] ) : '';

		$list_tag = self::get()->get_list_style( $list_style );
		$item_tag = self::get()->get_list_item_style( $list_style );
		$class    = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// HTML.
		$out   = [];
		$out[] = sprintf( '<div id="rank-math-howto" class="%1$s" %2$s>', esc_attr( $class ), self::get()->get_styles( $attributes ) );

		// HeaderContent.
		$out[] = '<div class="rank-math-howto-description">';

		if ( ! empty( $attributes['imageUrl'] ) ) {
			$out[] = '<img src="' . esc_url( $attributes['imageUrl'] ) . '" />';
		} elseif ( ! empty( $attributes['mainSizeSlug'] ) ) {
			$out[] = self::get()->get_image( $attributes, $attributes['mainSizeSlug'], '' );
		}

		if ( ! empty( $attributes['description'] ) ) {
			$out[] = self::get()->normalize_text( $attributes['description'], 'howto' );
		}

		$out[] = '</div>';

		$out[] = self::get()->build_duration( $attributes );

		$out[] = sprintf( '<%1$s class="rank-math-steps %2$s">', $list_tag, $list_css_classes );

		// Steps.
		foreach ( $attributes['steps'] as $index => $step ) {
			if ( empty( $step['visible'] ) ) {
				continue;
			}

			$step_id = isset( $step['id'] ) ? esc_attr( $step['id'] ) : '';

			$out[] = sprintf( '<%1$s id="%2$s" class="rank-math-step">', $item_tag, $step_id );

			if ( ! empty( $step['title'] ) ) {
				$out[] = sprintf(
					'<%1$s class="rank-math-step-title %2$s">%3$s</%1$s>',
					self::get()->get_title_wrapper( $title_wrapper, 'howto' ),
					$title_css_classes,
					wp_kses_post( $step['title'] )
				);
			}

			$step_content = ! empty( $step['content'] ) ? self::get()->normalize_text( $step['content'], 'howto' ) : '';
			$step_image   = ! empty( $step['imageUrl'] ) ? '<img src="' . esc_url( $step['imageUrl'] ) . '" />' : self::get()->get_image( $step, $size_slug, '' );

			$out[] = sprintf(
				'<div class="rank-math-step-content %1$s">%3$s%2$s</div>',
				$content_css_classes,
				$step_content,
				$step_image
			);

			$out[] = sprintf( '</%1$s>', $item_tag );
		}

		$out[] = sprintf( '</%1$s>', $list_tag );
		$out[] = '</div>';

		return apply_filters(
			'rank_math/schema/block/howto/content',
			wp_kses_post( join( "\n", $out ) ),
			$out,
			$attributes
		);
	}

	/**
	 * Render block content.
	 *
	 * @param array $attributes Array of attributes.
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		// Early bail.
		if ( ! $this->has_steps( $attributes ) ) {
			return '';
		}

		return self::markup( $attributes );
	}

	/**
	 * Add Step
	 *
	 * @param array  $step Step.
	 * @param string $permalink Permalink.
	 */
	private function add_step( $step, $permalink ) {
		$name = wp_strip_all_tags( do_shortcode( $step['title'] ) );
		$text = $this->clean_text( do_shortcode( $step['content'] ) );

		if ( empty( $name ) && empty( $text ) ) {
			return false;
		}

		$schema_step = [
			'@type' => 'HowToStep',
			'url'   => '' . esc_url( $permalink ),
		];

		if ( empty( $name ) ) {
			$schema_step['text'] = '';

			if ( empty( $text ) && empty( $schema_step['image'] ) ) {
				return false;
			}

			if ( ! empty( $text ) ) {
				$schema_step['text'] = $text;
			}
		} elseif ( empty( $text ) ) {
			$schema_step['text'] = $name;
		} else {
			$schema_step['name'] = $name;
			if ( ! empty( $text ) ) {
				$schema_step['itemListElement'] = [
					[
						'@type' => 'HowToDirection',
						'text'  => $text,
					],
				];
			}
		}

		if ( false === $this->add_step_image( $schema_step, $step ) ) {
			$this->add_step_image_from_content( $schema_step, $step );
		}

		return $schema_step;
	}

	/**
	 * Checks if we have an inline image and add it.
	 *
	 * @param array $schema_step Our Schema output for the Step.
	 * @param array $step        The step block data.
	 */
	private function add_step_image_from_content( &$schema_step, $step ) {
		// Early Bail.
		if ( empty( $step['content'] ) || ! Str::contains( '<img', $step['content'] ) ) {
			return;
		}

		// Search for image.
		preg_match_all( '/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $step['content'], $matches );

		if ( ! isset( $matches[1][0] ) || empty( $matches[1][0] ) ) {
			return;
		}

		$schema_image = [
			'@type' => 'ImageObject',
			'url'   => esc_url( $matches[1][0] ),
		];

		$image_id = Attachment::get_by_url( $schema_image['url'] );

		if ( $image_id > 0 ) {
			$this->add_caption( $schema_image, $image_id );
			$this->add_image_size( $schema_image, $image_id );
		}

		$schema_step['image'] = $schema_image;
	}

	/**
	 * Checks if we have a step image and add it.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param array $schema_step Our Schema output for the Step.
	 * @param array $step        The step block data.
	 */
	private function add_step_image( &$schema_step, $step ) {
		if ( empty( $step['imageID'] ) ) {
			return false;
		}

		$image_id = absint( $step['imageID'] );
		if ( ! ( $image_id > 0 ) ) {
			return false;
		}

		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( ! $image_url ) {
			return false;
		}

		$schema_image = [
			'@type' => 'ImageObject',
			'url'   => esc_url( $image_url ),
		];

		$this->add_caption( $schema_image, $image_id );
		$this->add_image_size( $schema_image, $image_id );

		$schema_step['image'] = $schema_image;

		return true;
	}

	/**
	 * Add caption to schema.
	 *
	 * @param array $schema_image Our Schema output for the Image.
	 * @param int   $image_id     The image ID.
	 */
	private function add_caption( &$schema_image, $image_id ) {
		$caption = wp_get_attachment_caption( $image_id );
		if ( ! empty( $caption ) ) {
			$schema_image['caption'] = esc_html( $caption );
			return;
		}

		$caption = Attachment::get_alt_tag( $image_id );
		if ( ! empty( $caption ) ) {
			$schema_image['caption'] = esc_html( $caption );
		}
	}

	/**
	 * Add image size to schema.
	 *
	 * @param array $schema_image Our Schema output for the Image.
	 * @param int   $image_id     The image ID.
	 */
	private function add_image_size( &$schema_image, $image_id ) {
		$image_meta = wp_get_attachment_metadata( $image_id );
		if ( empty( $image_meta['width'] ) || empty( $image_meta['height'] ) ) {
			return;
		}

		$schema_image['width']  = absint( $image_meta['width'] );
		$schema_image['height'] = absint( $image_meta['height'] );
	}

	/**
	 * Add duration to schema.
	 *
	 * @param array $data  Our Schema output.
	 * @param array $attrs The block attributes.
	 */
	private function add_duration( &$data, $attrs ) {
		if ( ! empty( $attrs['hasDuration'] ) ) {
			$days    = absint( $attrs['days'] ?? 0 );
			$hours   = absint( $attrs['hours'] ?? 0 );
			$minutes = absint( $attrs['minutes'] ?? 0 );
			if ( ( $days + $hours + $minutes ) > 0 ) {
				$data['totalTime'] = esc_attr( 'P' . $days . 'DT' . $hours . 'H' . $minutes . 'M' );
			}
		}
	}

	/**
	 * Generate HowTo duration property.
	 *
	 * @param array $attrs The block attributes.
	 *
	 * @return string
	 */
	private function build_duration( $attrs ) {
		if ( empty( $attrs['hasDuration'] ) ) {
			return '';
		}

		$days    = isset( $attrs['days'] ) ? absint( $attrs['days'] ) : 0;
		$hours   = isset( $attrs['hours'] ) ? absint( $attrs['hours'] ) : 0;
		$minutes = isset( $attrs['minutes'] ) ? absint( $attrs['minutes'] ) : 0;

		$elements = [];
		if ( $days > 0 ) {
			/* translators: %d is the number of days. */
			$elements[] = sprintf( _n( '%d day', '%d days', $days, 'rank-math' ), $days );
		}

		if ( $hours > 0 ) {
			/* translators: %d is the number of hours. */
			$elements[] = sprintf( _n( '%d hour', '%d hours', $hours, 'rank-math' ), $hours );
		}

		if ( $minutes > 0 ) {
			/* translators: %d is the number of minutes. */
			$elements[] = sprintf( _n( '%d minute', '%d minutes', $minutes, 'rank-math' ), $minutes );
		}

		$count   = count( $elements );
		$formats = [
			1 => '%1$s',
			/* translators: placeholders are units of time, e.g. '1 hour and 30 minutes' */
			2 => __( '%1$s and %2$s', 'rank-math' ),
			/* translators: placeholders are units of time, e.g. '1 day, 8 hours and 30 minutes' */
			3 => __( '%1$s, %2$s and %3$s', 'rank-math' ),
		];

		return sprintf(
			'<p class="rank-math-howto-duration"><strong>%2$s</strong> <span>%1$s</span></p>',
			isset( $formats[ $count ] ) ? vsprintf( $formats[ $count ], $elements ) : '',
			empty( $attrs['timeLabel'] ) ? __( 'Total Time:', 'rank-math' ) : esc_html( $attrs['timeLabel'] )
		);
	}

	/**
	 * Function to check the HowTo block have steps data.
	 *
	 * @param array $attributes Array of attributes.
	 *
	 * @return boolean
	 */
	private function has_steps( $attributes ) {
		return ! isset( $attributes['steps'] ) || empty( $attributes['steps'] ) ? false : true;
	}
}
