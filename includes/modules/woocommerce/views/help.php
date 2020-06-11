<?php
/**
 * WooCommerce general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 */

use RankMath\Helper;
?>

<h3><?php esc_html_e( 'WooCommerce', 'rank-math' ); ?></h3>

<p><?php esc_html_e( 'SEO is the backbone of any website and it couldn\'t be more true for a WooCommerce store.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'When you sell something online, you want people to buy it. And, SEO is the best way to do so in the long run.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'With the Rank Math SEO plugin, you can easily optimize your WooCommerce store in general and product pages in particular.', 'rank-math' ); ?></p>

<p><strong><?php esc_html_e( 'Optimizing Your WooCommerce Store', 'rank-math' ); ?></strong></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'Rank Math can help you make your product category or tag archives `noindex`. You can do that from <a href="%1$s">WordPress Dashboard > Rank Math > Titles & Meta > Product Categories</a>', 'rank-math' ),
		Helper::get_admin_url( 'options-titles#setting-panel-taxonomy-product_cat' )
	);
	?>
</p>

<p><?php esc_html_e( 'or', 'rank-math' ); ?></p>

<p>
	<a href="<?php echo Helper::get_admin_url( 'options-titles#setting-panel-taxonomy-product_tag' ); ?>"><?php esc_html_e( 'WordPress Dashboard > Rank Math > Titles & Meta > Product Tags.', 'rank-math' ); ?></a>
</p>

<p><img src="<?php echo rank_math()->plugin_url() . 'assets/admin/img/help/product-archive-settings.jpg'; ?>" alt="make categories noindex" /></p>

<p><strong><?php esc_html_e( 'Optimizing Your Product Pages', 'rank-math' ); ?></strong></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'You can customize and automate the SEO Title/Description generation easily as well. Just head over to <a href="%1$s">WordPress Dashboard > Rank Math > Titles & Meta > Products</a>', 'rank-math' ),
		Helper::get_admin_url( 'options-titles#setting-panel-post-type-product' )
	);
	?>
</p>

<p><img src="<?php echo rank_math()->plugin_url() . 'assets/admin/img/help/individual-product-settings.jpg'; ?>" alt="product seo title" /></p>

<p><?php esc_html_e( 'You can also add rich snippets to your product pages easily with Rank Math, apart from doing the regular SEO like you would do on posts.', 'rank-math' ); ?></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'Do that from the product pages themeselve. Go to <a href="%1$s">WordPress Dashboard > Products > Add New</a>', 'rank-math' ),
		admin_url( 'post-new.php?post_type=product' )
	);
	?>
</p>

<p><?php esc_html_e( 'And, choose the product schema from the Rich Snippets tab.', 'rank-math' ); ?></p>

<p><img src="<?php echo rank_math()->plugin_url() . 'assets/admin/img/help/product-rich-snippets.jpg'; ?>" alt="product rich snippets" /></p>

<p><strong><?php esc_html_e( 'Optimizing Your Product URLs', 'rank-math' ); ?></strong></p>

<p><?php esc_html_e( 'Rank Math offers you to remove category base from your product archive URLs so the URLs are cleaner, more SEO friendly and easier to remember.', 'rank-math' ); ?></p>

<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'To access those options, head over to <a href="%1$s">WordPress Dashboard > Rank Math > General Settings > WooCommerce</a>.', 'rank-math' ),
		Helper::get_admin_url( 'options-general#setting-panel-woocommerce' )
	);
	?>
</p>

<p><img src="<?php echo rank_math()->plugin_url() . 'assets/admin/img/help/woocommerce-url-settings.jpg'; ?>" alt="product category base" /></p>
