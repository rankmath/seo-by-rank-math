<?php
/**
 * Shortcode - Music
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
		esc_html__( 'Type', 'seo-by-rank-math' ),
		'@type'
	);
	?>

</div>
