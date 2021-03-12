<?php
/**
 * Shortcode - Video
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

defined( 'ABSPATH' ) || exit;

global $wp_embed;
$this->get_title();
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	if ( ! empty( $this->schema['embedUrl'] ) ) {
		echo do_shortcode( $wp_embed->autoembed( $this->schema['embedUrl'] ) );
	} elseif ( ! empty( $this->schema['contentUrl'] ) ) {
		echo do_shortcode( $wp_embed->autoembed( $this->schema['contentUrl'] ) );
	}
	?>

</div>
