<?php
/**
 * Metabox - Book Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$book_dep = [ [ 'rank_math_rich_snippet', 'book' ] ];

$cmb->add_field([
	'id'              => 'rank_math_snippet_book_rating',
	'type'            => 'text',
	'name'            => esc_html__( 'Rating', 'rank-math' ),
	'desc'            => esc_html__( 'Rating score of the book. Optional.', 'rank-math' ),
	'classes'         => 'cmb-row-33',
	'dep'             => $book_dep,
	'escape_cb'       => [ '\RankMath\CMB2', 'sanitize_float' ],
	'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_float' ],
]);

$cmb->add_field([
	'id'              => 'rank_math_snippet_book_rating_min',
	'type'            => 'text',
	'name'            => esc_html__( 'Rating Minimum', 'rank-math' ),
	'desc'            => esc_html__( 'Rating minimum score of the book.', 'rank-math' ),
	'classes'         => 'cmb-row-33',
	'default'         => 1,
	'dep'             => $book_dep,
	'escape_cb'       => 'absint',
	'sanitization_cb' => 'absint',
]);

$cmb->add_field([
	'id'              => 'rank_math_snippet_book_rating_max',
	'type'            => 'text',
	'name'            => esc_html__( 'Rating Maximum', 'rank-math' ),
	'desc'            => esc_html__( 'Rating maximum score of the book.', 'rank-math' ),
	'classes'         => 'cmb-row-33',
	'default'         => 5,
	'dep'             => $book_dep,
	'escape_cb'       => 'absint',
	'sanitization_cb' => 'absint',
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_book_editions',
	'type'    => 'group',
	'name'    => esc_html__( 'Book Editions', 'rank-math' ),
	'desc'    => esc_html__( 'Either a specific edition of the written work, or the volume of the work.', 'rank-math' ),
	'options' => [
		'closed'        => true,
		'sortable'      => true,
		'add_button'    => esc_html__( 'Add New', 'rank-math' ),
		'group_title'   => esc_html__( 'Book Edition {#}', 'rank-math' ),
		'remove_button' => esc_html__( 'Remove', 'rank-math' ),
	],
	'classes' => 'cmb-group-fix-me nob',
	'dep'     => $book_dep,
	'fields'  => [
		[
			'id'   => 'name',
			'type' => 'text',
			'name' => esc_html__( 'Title', 'rank-math' ),
			'desc' => __( 'The title of the tome. Use for the title of the tome if it differs from the book.<br>*Optional when tome has the same title as the book.', 'rank-math' ),
		],

		[
			'id'   => 'book_edition',
			'type' => 'text',
			'name' => esc_html__( 'Edition', 'rank-math' ),
			'desc' => esc_html__( 'The edition of the book.', 'rank-math' ),
		],

		[
			'id'   => 'isbn',
			'type' => 'text',
			'name' => esc_html__( 'ISBN', 'rank-math' ),
			'desc' => esc_html__( 'The ISBN of the print book.', 'rank-math' ),
		],

		[
			'id'         => 'url',
			'type'       => 'text_url',
			'name'       => esc_html__( 'URL', 'rank-math' ),
			'desc'       => esc_html__( 'URL specific to this edition if one exists.', 'rank-math' ),
			'attributes' => [
				'data-rule-url' => 'true',
			],
			'classes'    => 'rank-math-validate-field',
		],

		[
			'id'   => 'author',
			'type' => 'text',
			'name' => esc_html__( 'Author(s)', 'rank-math' ),
			'desc' => __( 'The author(s) of the tome. Use if the author(s) of the tome differ from the related book. Provide one Person entity per author.<br>*Optional when the tome has the same set of authors as the book.', 'rank-math' ),
		],

		[
			'id'   => 'date_published',
			'type' => 'text_date',
			'name' => esc_html__( 'Date Published', 'rank-math' ),
			'desc' => esc_html__( 'Date of first publication of this tome.', 'rank-math' ),
		],

		[
			'id'      => 'book_format',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Book Format', 'rank-math' ),
			'desc'    => esc_html__( 'The format of the book.', 'rank-math' ),
			'options' => [
				'EBook'     => esc_html__( 'EBook', 'rank-math' ),
				'Hardcover' => esc_html__( 'Hardcover', 'rank-math' ),
				'Paperback' => esc_html__( 'Paperback', 'rank-math' ),
				'AudioBook' => esc_html__( 'Audio Book', 'rank-math' ),
			],
			'default' => 'Hardcover',
		],
	],
]);
