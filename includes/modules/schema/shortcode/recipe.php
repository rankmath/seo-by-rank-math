<?php
/**
 * Shortcode - Recipe
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

defined( 'ABSPATH' ) || exit;

$this->get_title();
$this->get_image();
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	$this->get_field(
		esc_html__( 'Type', 'rank-math' ),
		'recipeCategory'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Cuisine', 'rank-math' ),
		'recipeCuisine'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Keywords', 'rank-math' ),
		'keywords'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Recipe Yield', 'rank-math' ),
		'recipeYield'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Calories', 'rank-math' ),
		'nutrition.calories'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Preparation Time', 'rank-math' ),
		'prepTime'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Cooking Time', 'rank-math' ),
		'cookTime'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Total Time', 'rank-math' ),
		'totalTime'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Recipe Video Name', 'rank-math' ),
		'video.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Recipe Video Description', 'rank-math' ),
		'video.description'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Recipe Video Thumbnail', 'rank-math' ),
		'video.thumbnailUrl'
	);
	?>

	<?php
	global $wp_embed;
	if ( ! empty( $this->schema['video'] ) ) {
		if ( ! empty( $this->schema['video']['embedUrl'] ) ) {
			echo do_shortcode( $wp_embed->autoembed( $this->schema['video']['embedUrl'] ) );
		} elseif ( ! empty( $this->schema['video']['contentUrl'] ) ) {
			echo do_shortcode( $wp_embed->autoembed( $this->schema['video']['contentUrl'] ) );
		}
	}
	?>

	<?php
	$ingredient = $this->get_field_value( 'recipeIngredient' );
	$this->output_field(
		esc_html__( 'Recipe Ingredients', 'rank-math' ),
		'<ul><li>' . join( '</li><li>', $ingredient ) . '</li></ul>'
	);
	?>

	<?php
	$instructions = $this->get_field_value( 'recipeInstructions' );
	if ( is_string( $instructions ) ) {
		$this->get_field(
			esc_html__( 'Recipe Instructions', 'rank-math' ),
			'recipeInstructions'
		);
	} else {
		// HowTo Array.
		if ( isset( $instructions[0]['@type'] ) && 'HowtoStep' === $instructions[0]['@type'] ) {
			$instructions = wp_list_pluck( $instructions, 'text' );
			$this->output_field(
				esc_html__( 'Recipe Instructions', 'rank-math' ),
				'<ul><li>' . join( '</li><li>', $instructions ) . '</li></ul>'
			);
		}

		// Single HowToSection data.
		if ( ! empty( $instructions['itemListElement'] ) ) {
			$this->output_field(
				esc_html__( 'Recipe Instructions', 'rank-math' ),
				''
			);

			$this->output_field(
				$instructions['name'],
				'<ul><li>' . join( '</li><li>', wp_list_pluck( $instructions['itemListElement'], 'text' ) ) . '</li></ul>'
			);
		}

		// Multiple HowToSection data.
		if ( isset( $instructions[0]['@type'] ) && 'HowToSection' === $instructions[0]['@type'] ) {
			$this->output_field(
				esc_html__( 'Recipe Instructions', 'rank-math' ),
				''
			);

			foreach ( $instructions as $section ) {
				if ( empty( $section['itemListElement'] ) ) {
					continue;
				}

				$this->output_field(
					$section['name'],
					'<ul><li>' . join( '</li><li>', wp_list_pluck( $section['itemListElement'], 'text' ) ) . '</li></ul>'
				);
			}
		}
	}
	?>

	<?php $this->show_ratings(); ?>

</div>
