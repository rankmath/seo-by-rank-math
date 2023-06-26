<?php
/**
 * SEO Analyzer Google Preview.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

defined( 'ABSPATH' ) || exit;

$src_format = 'https://t0.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=%%SITEURL%%&size=128';
$favicon    = str_replace( '%%SITEURL%%', urlencode( $this->analyse_url ), $src_format );

if ( is_array( $this->results ) ) {
	if ( isset( $this->results['title_length'] ) ) {
		$title_data = $this->results['title_length']->get_result();
		$title      = $title_data['data'];
	}

	if ( isset( $this->results['description_length'] ) ) {
		$description_data = $this->results['description_length']->get_result();
		$description      = $description_data['data'];
	}
}

if ( empty( $title ) ) {
	$title = __( '(No Title)', 'rank-math' );
}
// Cut title to 60 characters.
if ( strlen( $title ) > 60 ) {
	$title = substr( $title, 0, 60 ) . '...';
}

if ( empty( $description ) ) {
	$description = __( '(No Description)', 'rank-math' );
}
// Cut description to 160 characters.
if ( strlen( $description ) > 160 ) {
	$description = substr( $description, 0, 160 ) . '...';
}

?>
<div class="serp-preview">
	<div class="serp-preview-body">
		<div class="serp-url-wrapper">
			<img src="<?php echo $favicon; // phpcs:ignore ?>" width="16" height="16" class="serp-favicon" />
			<span class="serp-url"><?php echo esc_url( $this->analyse_url ); ?></span>
		</div>
		<h5 class="serp-title"><?php echo esc_html( $title ); ?></h5>

		<p class="serp-description"><?php echo esc_html( $description ); ?></p>
	</div>
</div>
