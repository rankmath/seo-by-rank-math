<?php
/**
 * Shortcode - Video
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
		esc_html__( 'Content URL', 'rank-math' ),
		'contentUrl'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Embed URL', 'rank-math' ),
		'embedUrl'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Duration', 'rank-math' ),
		'duration'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Views', 'rank-math' ),
		'interactionCount'
	);
	?>

</div>
