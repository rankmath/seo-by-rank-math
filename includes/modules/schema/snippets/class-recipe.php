<?php
/**
 * The Recipe Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Recipe class.
 */
class Recipe implements Snippet {

	/**
	 * Recipe rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$cook_time  = Helper::get_post_meta( 'snippet_recipe_cooktime' );
		$prep_time  = Helper::get_post_meta( 'snippet_recipe_preptime' );
		$total_time = Helper::get_post_meta( 'snippet_recipe_totaltime' );
		$entity     = [
			'@type'            => 'Recipe',
			'name'             => $jsonld->parts['title'],
			'description'      => $jsonld->parts['desc'],
			'datePublished'    => $jsonld->parts['published'],
			'author'           => [
				'@type' => 'Person',
				'name'  => $jsonld->parts['author'],
			],
			'prepTime'         => $prep_time ? 'PT' . $prep_time : '',
			'cookTime'         => $cook_time ? 'PT' . $cook_time : '',
			'totalTime'        => $total_time ? 'PT' . $total_time : '',
			'recipeCategory'   => Helper::get_post_meta( 'snippet_recipe_type' ),
			'recipeCuisine'    => Helper::get_post_meta( 'snippet_recipe_cuisine' ),
			'keywords'         => Helper::get_post_meta( 'snippet_recipe_keywords' ),
			'recipeYield'      => Helper::get_post_meta( 'snippet_recipe_yield' ),
			'recipeIngredient' => Str::to_arr_no_empty( Helper::get_post_meta( 'snippet_recipe_ingredients' ) ),
		];

		$instruction_type = Helper::get_post_meta( 'snippet_recipe_instruction_type' );

		switch ( $instruction_type ) {
			case 'HowToSection':
				$this->set_how_to_section( $entity );
				break;

			case 'HowToStep':
				$this->set_how_to_step( $entity );
				break;

			default:
				$entity['recipeInstructions'] = Helper::get_post_meta( 'snippet_recipe_single_instructions' );
				break;
		}

		$jsonld->add_ratings( 'recipe', $entity );
		$this->set_calories( $entity );
		$this->set_video( $entity );

		return $entity;
	}

	/**
	 * Set recipe how to section.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function set_how_to_section( &$entity ) {
		$instructions = Helper::get_post_meta( 'snippet_recipe_instructions' );
		if ( ! is_array( $instructions ) ) {
			return;
		}

		foreach ( $instructions as $instruction ) {
			$steps = Str::to_arr_no_empty( $instruction['text'] );
			if ( empty( $steps ) ) {
				continue;
			}

			$entity['recipeInstructions'][] = [
				'type'            => 'HowToSection',
				'name'            => $instruction['name'],
				'itemListElement' => $this->get_list_how_to_section( $steps ),
			];
		}
	}

	/**
	 * Get steps for how to section.
	 *
	 * @param  array $steps Array of steps.
	 * @return mixed
	 */
	private function get_list_how_to_section( $steps ) {
		if ( 1 < count( $steps ) ) {
			$list = [];
			foreach ( $steps as $step ) {
				$list[] = [
					'type' => 'HowtoStep',
					'text' => $step,
				];
			}

			return $list;
		}

		return [
			'type' => 'HowtoStep',
			'text' => $steps,
		];
	}

	/**
	 * Set reciepe how to step.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function set_how_to_step( &$entity ) {
		$instructions = Str::to_arr_no_empty( Helper::get_post_meta( 'snippet_recipe_single_instructions' ) );
		if ( empty( $instructions ) ) {
			return;
		}

		$count = count( $instructions );
		if ( 1 > $count ) {
			return;
		}

		if ( 1 === $count ) {
			$entity['recipeInstructions'] = $instructions;
			return;
		}

		$entity['recipeInstructions']['type'] = 'HowToSection';
		$entity['recipeInstructions']['name'] = Helper::get_post_meta( 'snippet_recipe_instruction_name' );
		foreach ( $instructions as $instruction ) {
			$entity['recipeInstructions']['itemListElement'][] = [
				'type' => 'HowtoStep',
				'text' => $instruction,
			];
		}
	}

	/**
	 * Set recipe calories.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function set_calories( &$entity ) {
		if ( $calories = Helper::get_post_meta( 'snippet_recipe_calories' ) ) { // phpcs:ignore
			$entity['nutrition'] = [
				'@type'    => 'NutritionInformation',
				'calories' => $calories,
			];
		}
	}

	/**
	 * Set recipe video.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function set_video( &$entity ) {
		if ( $video = Helper::get_post_meta( 'snippet_recipe_video' ) ) { // phpcs:ignore
			$entity['video'] = [
				'@type'        => 'VideoObject',
				'embedUrl'     => $video,
				'contentUrl'   => Helper::get_post_meta( 'snippet_recipe_video_content_url' ),
				'description'  => Helper::get_post_meta( 'snippet_recipe_video_description' ),
				'name'         => Helper::get_post_meta( 'snippet_recipe_video_name' ),
				'thumbnailUrl' => Helper::get_post_meta( 'snippet_recipe_video_thumbnail' ),
				'uploadDate'   => Helper::get_post_meta( 'snippet_recipe_video_date' ),
			];
		}
	}
}
