<?php
/**
 * Social preview tab template.
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

global $post;

$thumbnail = has_post_thumbnail() ? absint( get_post_thumbnail_id() ) : '';

// Facebook Image.
$fb_thumbnail = '';
if ( Admin_Helper::is_post_edit() ) {
	$fb_thumbnail = get_post_meta( $post->ID, 'rank_math_facebook_image_id', true );
} elseif ( Admin_Helper::is_term_edit() ) {
	$term_id      = Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
	$fb_thumbnail = get_term_meta( $term_id, 'rank_math_facebook_image_id', true );
} elseif ( Admin_Helper::is_user_edit() ) {
	global $user_id;
	$fb_thumbnail = get_user_meta( $user_id, 'rank_math_facebook_image_id', true );
}
$fb_thumbnail = $fb_thumbnail ? absint( $fb_thumbnail ) : $thumbnail;
if ( ! is_string( $fb_thumbnail ) ) {
	$image_src    = wp_get_attachment_image_src( $fb_thumbnail, 'full' );
	$fb_thumbnail = $image_src[0];
}

// Twitter Image.
$tw_thumbnail = '';
if ( Admin_Helper::is_post_edit() ) {
	$tw_thumbnail = get_post_meta( $post->ID, 'rank_math_twitter_image_id', true );
} elseif ( Admin_Helper::is_term_edit() ) {
	$term_id      = Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
	$tw_thumbnail = get_term_meta( $term_id, 'rank_math_twitter_image_id', true );
} elseif ( Admin_Helper::is_user_edit() ) {
	global $user_id;
	$tw_thumbnail = get_user_meta( $user_id, 'rank_math_twitter_image_id', true );
}
$tw_thumbnail = $tw_thumbnail ? absint( $tw_thumbnail ) : $thumbnail;
if ( ! is_string( $tw_thumbnail ) ) {
	$image_src    = wp_get_attachment_image_src( $tw_thumbnail, 'full' );
	$tw_thumbnail = $image_src[0];
}

// Publisher URL.
$publisher_url = str_replace( array( 'http://', 'https://' ), '', get_bloginfo( 'url' ) );
$publisher_url = explode( '/', $publisher_url );
$publisher_url = isset( $publisher_url[0] ) ? $publisher_url[0] : '';

// Username, avatar & Name.
$name             = get_the_author_meta( 'display_name' );
$twitter_username = Helper::get_settings( 'titles.twitter_author_names' );
$twitter_username = $twitter_username ? $twitter_username : esc_html( 'username' );
?>
<div id="setting-panel-container-social-tabs" class="rank-math-tabs">

	<div class="social-tabs-navigation-wrapper">
		<div class="rank-math-tabs-navigation rank-math-custom social-tabs-navigation wp-clearfix" data-active-class="tab-active">
			<a href="#setting-panel-social-facebook" class="preview-network tab-facebook"><span class="dashicons dashicons-facebook-alt"></span><?php esc_html_e( 'Facebook', 'rank-math' ); ?></a><a href="#setting-panel-social-twitter" class="preview-network tab-twitter"><span class="dashicons dashicons-twitter"></span><?php esc_html_e( 'Twitter', 'rank-math' ); ?></a>
		</div>
	</div>

	<div class="rank-math-social-preview">

		<a href="#" class="rank-math-social-preview-button"><strong data-facebook="<?php esc_html_e( 'Facebook Preview', 'rank-math' ); ?>" data-twitter="<?php esc_html_e( 'Twitter Preview', 'rank-math' ); ?>"></strong><span class="dashicons dashicons-arrow-down"></span></a>

		<div class="rank-math-social-preview-item">

			<div class="rank-math-social-preview-meta facebook-meta">
				<div class="social-profile-image"></div>
				<div class="social-name"><?php echo esc_attr( $name ); ?></div>
				<div class="social-time"><span><?php esc_html_e( '2 hrs', 'rank-math' ); ?></span><span class="dashicons dashicons-admin-site"></span></div>
			</div>

			<div class="rank-math-social-preview-meta twitter-meta">
				<div class="social-profile-image"></div>
				<div class="social-name"><?php echo esc_attr( $name ); ?><span class="social-username">@<?php echo esc_attr( $twitter_username ); ?></span><span class="social-time"><?php esc_html_e( '2h', 'rank-math' ); ?></span></div>
				<div class="social-text">The card for your website will look little something like this!</div>
			</div>

			<div class="rank-math-social-preview-item-wrapper">

				<div class="rank-math-social-preview-image">
					<?php the_post_thumbnail( 'full', 'id=rank_math_post_thumbnail' ); ?>
					<img class="facebook-thumbnail" src="<?php echo esc_url( $fb_thumbnail ); ?>" width="526" height="275" />
					<img class="twitter-thumbnail" src="<?php echo esc_url( $tw_thumbnail ); ?>" width="526" height="275" />
					<img src="" class="rank-math-social-preview-image-overlay">
				</div>

				<div class="rank-math-social-preview-caption">
					<h4 class="rank-math-social-preview-publisher facebook"><?php echo $publisher_url; ?></h4>
					<h3 class="rank-math-social-preview-title"></h3>
					<p class="rank-math-social-preview-description"></p>
					<h4 class="rank-math-social-preview-publisher twitter"><svg viewBox="0 0 24 24" class="r-4qtqp9 r-yyyyoo r-1xvli5t r-dnmrzs r-bnwqim r-1plcrui r-lrvibr"><g><path d="M11.96 14.945c-.067 0-.136-.01-.203-.027-1.13-.318-2.097-.986-2.795-1.932-.832-1.125-1.176-2.508-.968-3.893s.942-2.605 2.068-3.438l3.53-2.608c2.322-1.716 5.61-1.224 7.33 1.1.83 1.127 1.175 2.51.967 3.895s-.943 2.605-2.07 3.438l-1.48 1.094c-.333.246-.804.175-1.05-.158-.246-.334-.176-.804.158-1.05l1.48-1.095c.803-.592 1.327-1.463 1.476-2.45.148-.988-.098-1.975-.69-2.778-1.225-1.656-3.572-2.01-5.23-.784l-3.53 2.608c-.802.593-1.326 1.464-1.475 2.45-.15.99.097 1.975.69 2.778.498.675 1.187 1.15 1.992 1.377.4.114.633.528.52.928-.092.33-.394.547-.722.547z"></path><path d="M7.27 22.054c-1.61 0-3.197-.735-4.225-2.125-.832-1.127-1.176-2.51-.968-3.894s.943-2.605 2.07-3.438l1.478-1.094c.334-.245.805-.175 1.05.158s.177.804-.157 1.05l-1.48 1.095c-.803.593-1.326 1.464-1.475 2.45-.148.99.097 1.975.69 2.778 1.225 1.657 3.57 2.01 5.23.785l3.528-2.608c1.658-1.225 2.01-3.57.785-5.23-.498-.674-1.187-1.15-1.992-1.376-.4-.113-.633-.527-.52-.927.112-.4.528-.63.926-.522 1.13.318 2.096.986 2.794 1.932 1.717 2.324 1.224 5.612-1.1 7.33l-3.53 2.608c-.933.693-2.023 1.026-3.105 1.026z"></path></g></svg> <?php echo $publisher_url; ?></h4>
				</div>

			</div>
			<div class="error-msg">
				<?php
				printf(
					/* translators: Link to global title setting */
					__( 'Set your default image for Facebook & Twitter by adding <a href="%s" target="_blank">OpenGraph Thumbnail</a>', 'rank-math' ),
					Helper::get_admin_url( 'options-titles#setting-panel-global' )
				);
				?>
			</div>
		</div>

	</div>

	<div class="notice notice-alt notice-info info inline rank-math-notice">
		<?php /* translators: link to title setting screen */ ?>
		<p><?php printf( wp_kses_post( __( 'Customize the title, description and images of your post used while sharing on Facebook and Twitter. <a href="%s" target="_blank">Read more</a>', 'rank-math' ) ), \RankMath\KB::get( 'social-tab' ) ); ?></p>
	</div>
