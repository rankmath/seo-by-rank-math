<?php
/**
 * The HowTo Block
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * HowTo Block class.
 */
class Block_HowTo extends Block {

	/**
	 * The Constructor.
	 */
	public function __construct() {
		register_block_type(
			'rank-math/howto-block',
			[
				'render_callback' => [ $this, 'render' ],
				'editor_style'    => 'rank-math-block-admin',
				'attributes'      => [
					'hasDuration'       => [
						'type'    => 'boolean',
						'default' => false,
					],
					'days'              => [
						'type'    => 'string',
						'default' => '',
					],
					'hours'             => [
						'type'    => 'string',
						'default' => '',
					],
					'minutes'           => [
						'type'    => 'string',
						'default' => '',
					],
					'description'       => [
						'type'    => 'string',
						'default' => '',
					],
					'steps'             => [
						'type'    => 'array',
						'default' => [],
						'items'   => [ 'type' => 'object' ],
					],
					'sizeSlug'          => [
						'type'    => 'string',
						'default' => 'full',
					],
					'imageID'           => [
						'type' => 'integer',
					],
					'mainSizeSlug'      => [
						'type'    => 'string',
						'default' => 'full',
					],
					'listStyle'         => [
						'type'    => 'string',
						'default' => '',
					],
					'timeLabel'         => [
						'type'    => 'string',
						'default' => '',
					],
					'titleWrapper'      => [
						'type'    => 'string',
						'default' => 'h3',
					],
					'listCssClasses'    => [
						'type'    => 'string',
						'default' => '',
					],
					'titleCssClasses'   => [
						'type'    => 'string',
						'default' => '',
					],
					'contentCssClasses' => [
						'type'    => 'string',
						'default' => '',
					],
					'textAlign'         => [
						'type'    => 'string',
						'default' => 'left',
					],
				],
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
				'description' => isset( $attrs['description'] ) ? $this->clean_text( $attrs['description'] ) : '',
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
	 * @param array $attributes Array of atributes.
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		// Early bail.
		if ( ! $this->has_steps( $attributes ) ) {
			return '';
		}

		$list_tag = $this->get_list_style( $attributes['listStyle'] );
		$item_tag = $this->get_list_item_style( $attributes['listStyle'] );
		$class    = 'rank-math-block';
		if ( ! empty( $attributes['className'] ) ) {
			$class .= ' ' . esc_attr( $attributes['className'] );
		}

		// HTML.
		$out   = [];
		$out[] = sprintf( '<div id="rank-math-howto" class="%1$s" %2$s>', $class, $this->get_styles( $attributes ) );

		// HeaderContent.
		$out[] = '<div class="rank-math-howto-description">';
		$out[] = $this->get_image( $attributes, $attributes['mainSizeSlug'], '' );
		$out[] = wpautop( $attributes['description'] );
		$out[] = '</div>';

		$out[] = $this->build_duration( $attributes );

		$out[] = sprintf( '<%1$s class="rank-math-steps %2$s">', $list_tag, $attributes['listCssClasses'] );

		// Steps.
		foreach ( $attributes['steps'] as $index => $step ) {
			if ( empty( $step['visible'] ) ) {
				continue;
			}

			$out[] = sprintf( '<%1$s id="%2$s" class="rank-math-step">', $item_tag, $step['id'] );

			if ( ! empty( $step['title'] ) ) {
				$out[] = sprintf(
					'<%1$s class="rank-math-step-title %2$s">%3$s</%1$s>',
					$attributes['titleWrapper'],
					$attributes['titleCssClasses'],
					$step['title']
				);
			}

			if ( ! empty( $step['content'] ) ) {
				$out[] = sprintf(
					'<div class="rank-math-step-content %2$s">%4$s%3$s</div>',
					$attributes['titleWrapper'],
					$attributes['contentCssClasses'],
					wpautop( $step['content'] ),
					$this->get_image( $step, $attributes['sizeSlug'], '' )
				);
			}

			$out[] = sprintf( '</%1$s>', $item_tag );
		}

		$out[] = sprintf( '</%1$s>', $list_tag );
		$out[] = '</div>';

		return apply_filters( 'rank_math/schema/block/howto/content', join( "\n", $out ), $out, $attributes );
	}

	/**
	 * Add Step
	 *
	 * @param array  $step Step.
	 * @param string $permalink Permalink.
	 */
	private function add_step( $step, $permalink ) {
		$name = wp_strip_all_tags( $step['title'] );
		$text = $this->clean_text( $step['content'] );

		if ( empty( $name ) && empty( $text ) ) {
			return false;
		}

		$schema_step = [
			'@type' => 'HowToStep',
			'url'   => '' . $permalink,
		];

		if ( empty( $name ) ) {
			$schema_step['text'] = '';

			if ( false === $this->add_step_image( $schema_step, $step ) ) {
				$this->add_step_image_from_content( $schema_step, $step );
			}

			// If there is no text and no image, don't output the step.
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

			if ( false === $this->add_step_image( $schema_step, $step ) ) {
				$this->add_step_image_from_content( $schema_step, $step );
			}
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
			'url'   => $matches[1][0],
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
		if ( ! isset( $step['imageID'] ) ) {
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
			'url'   => $image_url,
		];

		$this->add_caption( $schema_image, $image_id );
		$this->add_image_size( $schema_image, $image_id );

		$schema_step['image'] = $schema_image;

		return true;
	}

	/**
	 * Add Caption.
	 *
	 * @param [type] $schema_image [description].
	 * @param [type] $image_id     [description].
	 */
	private function add_caption( &$schema_image, $image_id ) {
		$caption = wp_get_attachment_caption( $image_id );
		if ( ! empty( $caption ) ) {
			$schema_image['caption'] = $caption;
			return;
		}

		$caption = Attachment::get_alt_tag( $image_id );
		if ( ! empty( $caption ) ) {
			$schema_image['caption'] = $caption;
		}
	}

	/**
	 * Add Image Size.
	 *
	 * @param [type] $schema_image [description].
	 * @param [type] $image_id     [description].
	 */
	private function add_image_size( &$schema_image, $image_id ) {
		$image_meta = wp_get_attachment_metadata( $image_id );
		if ( empty( $image_meta['width'] ) || empty( $image_meta['height'] ) ) {
			return;
		}

		$schema_image['width']  = $image_meta['width'];
		$schema_image['height'] = $image_meta['height'];
	}

	/**
	 * Add Duration.
	 *
	 * @param [type] $data  [description].
	 * @param [type] $attrs [description].
	 */
	private function add_duration( &$data, $attrs ) {
		if ( ! empty( $attrs['hasDuration'] ) && $attrs['hasDuration'] ) {
			$days    = empty( $attrs['days'] ) ? 0 : $attrs['days'];
			$hours   = empty( $attrs['hours'] ) ? 0 : $attrs['hours'];
			$minutes = empty( $attrs['minutes'] ) ? 0 : $attrs['minutes'];

			if ( ( $days + $hours + $minutes ) > 0 ) {
				$data['totalTime'] = esc_attr( 'P' . $days . 'DT' . $hours . 'H' . $minutes . 'M' );
			}
		}
	}

	/**
	 * HowTo Duration
	 *
	 * @param [type] $attrs [description].
	 *
	 * @return [type]        [description]
	 */
	private function build_duration( $attrs ) {
		if ( ! isset( $attrs['hasDuration'] ) || ! $attrs['hasDuration'] ) {
			return '';
		}

		$days    = isset( $attrs['days'] ) ? absint( $attrs['days'] ) : 0;
		$hours   = isset( $attrs['hours'] ) ? absint( $attrs['hours'] ) : 0;
		$minutes = isset( $attrs['minutes'] ) ? absint( $attrs['minutes'] ) : 0;

		$elements = [];
		if ( $days > 0 ) {
			/* translators: %s expands to a unit of time (e.g. 1 day). */
			$elements[] = sprintf( _n( '%d day', '%d days', $days, 'rank-math' ), $days );
		}

		if ( $hours > 0 ) {
			/* translators: %s expands to a unit of time (e.g. 1 hour). */
			$elements[] = sprintf( _n( '%d hour', '%d hours', $hours, 'rank-math' ), $hours );
		}

		if ( $minutes > 0 ) {
			/* translators: %s expands to a unit of time (e.g. 1 minute). */
			$elements[] = sprintf( _n( '%d minute', '%d minutes', $minutes, 'rank-math' ), $minutes );
		}

		$count   = count( $elements );
		$formats = [
			1 => '%1$s',
			/* translators: %s expands to a unit of time (e.g. 1 day). */
			2 => __( '%1$s and %2$s', 'rank-math' ),
			/* translators: %s expands to a unit of time (e.g. 1 day). */
			3 => __( '%1$s, %2$s and %3$s', 'rank-math' ),
		];

		return sprintf(
			'<p class="rank-math-howto-duration"><strong>%2$s</strong> <span>%1$s</span></p>',
			isset( $formats[ $count ] ) ? vsprintf( $formats[ $count ], $elements ) : '',
			empty( $attrs['timeLabel'] ) ? __( 'Total Time:', 'rank-math' ) : $attrs['timeLabel']
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
