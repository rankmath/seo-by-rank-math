<?php
/**
 * SEO Analysis admin page contents.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

defined( 'ABSPATH' ) || exit;

?>
<h3 class="health-check-accordion-heading">
	<button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" type="button">
		<span class="title">
			<?php echo esc_html( $details['label'] ); ?>
			<?php

			if ( isset( $details['show_count'] ) && $details['show_count'] ) {
				printf( '(%d)', count( $details['fields'] ) );
			}

			?>
		</span>
		<span class="icon"></span>
	</button>
</h3>

<div id="health-check-accordion-block-<?php echo esc_attr( $section ); ?>" class="health-check-accordion-panel" hidden="hidden">
	<?php

	if ( isset( $details['description'] ) && ! empty( $details['description'] ) ) {
		printf( '<p>%s</p>', $details['description'] );
	}

	?>
	<table class="widefat striped health-check-table" role="presentation">
		<tbody>
		<?php $this->display_system_info_fields( $details['fields'] ); ?>
		</tbody>
	</table>
</div>
