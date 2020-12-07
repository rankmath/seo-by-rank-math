<?php
/**
 * The Yoast Block Converter.
 *
 * @since      1.0.37
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

/**
 * Yoast_HowTo_Converter class.
 */
class Yoast_HowTo_Converter {

	/**
	 * Convert blocks to rank math
	 *
	 * @param array $block Block to convert.
	 *
	 * @return array
	 */
	public function convert( $block ) {
		$attrs     = $block['attrs'];
		$new_block = [
			'blockName' => 'rank-math/howto-block',
			'attrs'     => [
				'hasDuration'       => isset( $attrs['hasDuration'] ) ? $attrs['hasDuration'] : '',
				'days'              => isset( $attrs['days'] ) ? $attrs['days'] : '',
				'hours'             => isset( $attrs['hours'] ) ? $attrs['hours'] : '',
				'minutes'           => isset( $attrs['minutes'] ) ? $attrs['minutes'] : '',
				'description'       => isset( $attrs['jsonDescription'] ) ? $attrs['jsonDescription'] : '',
				'listStyle'         => isset( $attrs['unorderedList'] ) && $attrs['unorderedList'] ? 'ol' : '',
				'timeLabel'         => isset( $attrs['durationText'] ) ? $attrs['durationText'] : '',
				'textAlign'         => 'left',
				'titleWrapper'      => 'h3',
				'listCssClasses'    => '',
				'titleCssClasses'   => '',
				'contentCssClasses' => '',
				'steps'             => array_map( [ $this, 'get_step' ], $attrs['steps'] ),
				'className'         => isset( $attrs['className'] ) ? $attrs['className'] : '',
			],
		];

		$new_block['innerContent'][] = $this->get_html( $new_block['attrs'] );

		return $new_block;
	}

	/**
	 * Replace block in content.
	 *
	 * @param string $post_content Post content.
	 * @param array  $blocks       Blocks.
	 *
	 * @return string
	 */
	public function replace( $post_content, $blocks ) {
		preg_match_all( '/<!-- wp:yoast\/how-to-block.*-->.*<!-- \/wp:yoast\/how-to-block -->/iUs', $post_content, $matches );

		foreach ( $matches[0] as $index => $match ) {
			$post_content = \str_replace( $match, $blocks[ $index ], $post_content );
		}

		return $post_content;
	}

	/**
	 * Gormat steps.
	 *
	 * @param array $step Steps.
	 *
	 * @return array
	 */
	public function get_step( $step ) {
		return [
			'id'      => uniqid( 'howto-step-' ),
			'visible' => true,
			'title'   => $step['jsonName'],
			'content' => $step['jsonText'],
		];
	}

	/**
	 * [get_html description]
	 *
	 * @param array $attributes [description].
	 *
	 * @return string
	 */
	private function get_html( $attributes ) {
		// HTML.
		$out = [ '<div class="wp-block-rank-math-howto-block">' ];

		// Steps.
		foreach ( $attributes['steps'] as $step ) {
			if ( empty( $step['visible'] ) ) {
				continue;
			}

			$out[] = '<div class="rank-math-howto-step">';

			if ( ! empty( $step['title'] ) ) {
				$out[] = sprintf(
					'<%1$s class="rank-math-howto-title">%2$s</%1$s>',
					$attributes['titleWrapper'],
					$step['title']
				);
			}

			if ( ! empty( $step['content'] ) ) {
				$out[] = sprintf(
					'<div class="rank-math-howto-content">%1$s</div>',
					$step['content']
				);
			}

			$out[] = '</div>';
		}

		$out[] = '</div>';

		return join( '', $out );
	}
}
