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
		esc_html__( 'URL', 'rank-math' ),
		'url'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Author', 'rank-math' ),
		'author.name'
	);
	?>

	<?php
	if ( ! empty( $schema['hasPart'] ) ) {
		$hash = [
			'edition'       => __( 'Edition', 'rank-math' ),
			'name'          => __( 'Name', 'rank-math' ),
			'url'           => __( 'Url', 'rank-math' ),
			'author'        => __( 'Author', 'rank-math' ),
			'isbn'          => __( 'ISBN', 'rank-math' ),
			'datePublished' => __( 'Date Published', 'rank-math' ),
			'bookFormat'    => __( 'Format', 'rank-math' ),
		];
		foreach ( $schema['hasPart'] as $edition ) {
			$this->schema = $edition;
			foreach ( $hash as $id => $label ) {
				$this->get_field( $label, $id );
			}
		}
		$this->schema = $schema;
	}
	?>

	<?php $this->show_ratings(); ?>

</div>
