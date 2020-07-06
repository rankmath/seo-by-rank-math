<?php
/**
 * Metabox - Recipe Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$recipe = [ [ 'rank_math_rich_snippet', 'recipe' ] ];

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_type',
		'type'    => 'text',
		'name'    => esc_html__( 'Type', 'rank-math' ),
		'desc'    => esc_html__( 'Type of dish, for example "appetizer", or "dessert".', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_cuisine',
		'type'    => 'text',
		'name'    => esc_html__( 'Cuisine', 'rank-math' ),
		'desc'    => esc_html__( 'The cuisine of the recipe (for example, French or Ethiopian).', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_keywords',
		'type'    => 'text',
		'name'    => esc_html__( 'Keywords', 'rank-math' ),
		'desc'    => esc_html__( 'Other terms for your recipe such as the season, the holiday, or other descriptors. Separate multiple entries with commas.', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_yield',
		'type'    => 'text',
		'name'    => esc_html__( 'Recipe Yield', 'rank-math' ),
		'desc'    => esc_html__( 'Quantity produced by the recipe, for example "4 servings"', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_calories',
		'type'    => 'text',
		'name'    => esc_html__( 'Calories', 'rank-math' ),
		'desc'    => esc_html__( 'The number of calories in the recipe. Optional.', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_preptime',
		'type'       => 'text',
		'name'       => esc_html__( 'Preparation Time', 'rank-math' ),
		'desc'       => esc_html__( ' ISO 8601 duration format. Example: 1H30M', 'rank-math' ),
		'classes'    => 'cmb-row-33 rank-math-validate-field',
		'attributes' => [
			'data-rule-regex'       => 'true',
			'data-validate-pattern' => '^([0-9]+[A-Z])+$',
			'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 1H30M', 'rank-math' ),
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_cooktime',
		'type'       => 'text',
		'name'       => esc_html__( 'Cooking Time', 'rank-math' ),
		'desc'       => esc_html__( ' ISO 8601 duration format. Example: 1H30M', 'rank-math' ),
		'classes'    => 'cmb-row-50 rank-math-validate-field',
		'attributes' => [
			'data-rule-regex'       => 'true',
			'data-validate-pattern' => '^([0-9]+[A-Z])+$',
			'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 1H30M', 'rank-math' ),
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_totaltime',
		'type'       => 'text',
		'name'       => esc_html__( 'Total Time', 'rank-math' ),
		'desc'       => esc_html__( ' ISO 8601 duration format. Example: 1H30M', 'rank-math' ),
		'classes'    => 'cmb-row-50 rank-math-validate-field',
		'attributes' => [
			'data-rule-regex'       => 'true',
			'data-validate-pattern' => '^([0-9]+[A-Z])+$',
			'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 1H30M', 'rank-math' ),
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_recipe_rating',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating', 'rank-math' ),
		'desc'            => esc_html__( 'Rating score of the recipe. Optional.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'dep'             => $recipe,
		'escape_cb'       => [ '\RankMath\CMB2', 'sanitize_float' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_float' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_recipe_rating_min',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Minimum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating minimum score of the recipe.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 1,
		'dep'             => $recipe,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_recipe_rating_max',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Maximum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating maximum score of the recipe.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 5,
		'dep'             => $recipe,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_video',
		'type'       => 'text_url',
		'name'       => esc_html__( 'Recipe Video', 'rank-math' ),
		'desc'       => esc_html__( 'A recipe video URL. Optional.', 'rank-math' ),
		'classes'    => 'cmb-row-33 rank-math-validate-field',
		'attributes' => [
			'data-rule-url' => true,
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_video_content_url',
		'type'       => 'text_url',
		'name'       => esc_html__( 'Video Content URL', 'rank-math' ),
		'desc'       => esc_html__( 'A URL pointing to the actual video media file.', 'rank-math' ),
		'classes'    => 'cmb-row-33 rank-math-validate-field',
		'attributes' => [
			'data-rule-url' => true,
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_video_date',
		'type'    => 'text_date',
		'name'    => esc_html__( 'Video Upload Date', 'rank-math' ),
		'classes' => 'cmb-row-33',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_video_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Recipe Video Name', 'rank-math' ),
		'desc'    => esc_html__( 'A recipe video Name.', 'rank-math' ),
		'classes' => 'cmb-row-50',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_video_thumbnail',
		'type'       => 'text_url',
		'name'       => esc_html__( 'Recipe Video Thumbnail', 'rank-math' ),
		'desc'       => esc_html__( 'A recipe video thumbnail URL.', 'rank-math' ),
		'classes'    => 'cmb-row-50 rank-math-validate-field',
		'attributes' => [
			'data-rule-url' => true,
		],
		'dep'        => $recipe,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_video_description',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Recipe Video Description', 'rank-math' ),
		'desc'       => esc_html__( 'A recipe video Description.', 'rank-math' ),
		'classes'    => 'cmb-row-50',
		'attributes' => [
			'rows'            => 4,
			'data-autoresize' => true,
		],
		'dep'        => $recipe,
		'escape_cb'  => 'esc_textarea',
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_ingredients',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Recipe Ingredients', 'rank-math' ),
		'desc'       => esc_html__( 'Recipe ingredients, add one item per line', 'rank-math' ),
		'attributes' => [
			'rows'            => 4,
			'data-autoresize' => true,
		],
		'classes'    => 'cmb-row-50',
		'dep'        => $recipe,
		'escape_cb'  => 'esc_textarea',
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_instruction_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Instruction Type', 'rank-math' ),
		'options' => [
			'SingleField'  => esc_html__( 'Single Field', 'rank-math' ),
			'HowToStep'    => esc_html__( 'How To Step', 'rank-math' ),
			'HowToSection' => esc_html__( 'How To Section', 'rank-math' ),
		],
		'classes' => 'recipe-instruction-type',
		'default' => 'SingleField',
		'dep'     => $recipe,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_instruction_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Recipe Instruction Name', 'rank-math' ),
		'desc'    => esc_html__( 'Instruction name of the recipe.', 'rank-math' ),
		'classes' => 'nob',
		'dep'     => [
			'relation' => 'and',
			[ 'rank_math_rich_snippet', 'recipe' ],
			[ 'rank_math_snippet_recipe_instruction_type', 'HowToStep' ],
		],
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_recipe_single_instructions',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Recipe Instructions', 'rank-math' ),
		'attributes' => [
			'rows'            => 4,
			'data-autoresize' => true,
		],
		'classes'    => 'nob',
		'dep'        => [
			'relation' => 'and',
			[ 'rank_math_rich_snippet', 'recipe' ],
			[ 'rank_math_snippet_recipe_instruction_type', 'HowToStep,SingleField' ],
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_recipe_instructions',
		'type'    => 'group',
		'name'    => esc_html__( 'Recipe Instructions', 'rank-math' ),
		'desc'    => esc_html__( 'Steps to take, add one instruction per line', 'rank-math' ),
		'options' => [
			'closed'        => true,
			'sortable'      => true,
			'add_button'    => esc_html__( 'Add New Instruction', 'rank-math' ),
			'group_title'   => esc_html__( 'Instruction {#}', 'rank-math' ),
			'remove_button' => esc_html__( 'Remove', 'rank-math' ),
		],
		'classes' => 'cmb-group-fix-me nob',
		'dep'     => [
			'relation' => 'and',
			[ 'rank_math_rich_snippet', 'recipe' ],
			[ 'rank_math_snippet_recipe_instruction_type', 'HowToSection' ],
		],
		'fields'  => [
			[
				'id'   => 'name',
				'type' => 'text',
				'name' => esc_html__( 'Name', 'rank-math' ),
				'desc' => esc_html__( 'Instruction name of the recipe.', 'rank-math' ),
			],
			[
				'id'         => 'text',
				'type'       => 'textarea',
				'name'       => esc_html__( 'Text', 'rank-math' ),
				'attributes' => [
					'rows'            => 4,
					'data-autoresize' => true,
				],
				'desc'       => esc_html__( 'Steps to take, add one instruction per line', 'rank-math' ),
			],
		],
	]
);
