<?php
/**
 * Shortcode - Book
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
		esc_html__( 'URL', 'seo-by-rank-math' ),
		'url'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Author', 'seo-by-rank-math' ),
		'author.name'
	);
	?>

	<?php
	if ( ! empty( $schema['hasPart'] ) ) {
		$hash = [
			'edition'       => __( 'Edition', 'seo-by-rank-math' ),
			'name'          => __( 'Name', 'seo-by-rank-math' ),
			'url'           => __( 'Url', 'seo-by-rank-math' ),
			'author'        => __( 'Author', 'seo-by-rank-math' ),
			'isbn'          => __( 'ISBN', 'seo-by-rank-math' ),
			'datePublished' => __( 'Date Published', 'seo-by-rank-math' ),
			'bookFormat'    => __( 'Format', 'seo-by-rank-math' ),
		];
		foreach ( $schema['hasPart'] as $edition ) {
			$this->schema = $edition;
			foreach ( $hash as $key => $label ) {
				$this->get_field( $label, $key );
			}
		}
		$this->schema = $schema;
	}
	?>

	<?php $this->show_ratings(); ?>

</div>
