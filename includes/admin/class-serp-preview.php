<?php
/**
 * The SERP preview functionality, in the metabox.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\CMB2;
use RankMath\Helper;
use RankMath\Rewrite;
use RankMath\Helpers\Sitepress;
use RankMath\Schema\DB;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\WordPress;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Serp_Preview class.
 */
class Serp_Preview {

	/**
	 * Display SERP preview.
	 */
	public function display() {
		$method = 'get_' . CMB2::current_object_type() . '_data';
		$data   = $this->$method();
		if ( 'post' === CMB2::current_object_type() && Helper::is_module_active( 'rich-snippet' ) ) {
			$snippet_preview = $this->get_snippet_html();
		}
		$favicon = esc_url( get_site_icon_url( 16 ) );
		if ( empty( $favicon ) ) {
			$favicon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABs0lEQVR4AWL4//8/RRjO8Iucx+noO0MWUDo16FYABMGP6ZfUcRnWtm27jVPbtm3bttuH2t3eFPcY9pLz7NxiLjCyVd87pKnHyqXyxtCs8APd0rnyxiu4qSeA3QEDrAwBDrT1s1Rc/OrjLZwqVmOSu6+Lamcpp2KKMA9PH1BYXMe1mUP5qotvXTywsOEEYHXxrY+3cqk6TMkYpNr2FeoY3KIr0RPtn9wQ2unlA+GMkRw6+9TFw4YTwDUzx/JVvARj9KaedXRO8P5B1Du2S32smzqUrcKGEyA+uAgQjKX7zf0boWHGfn71jIKj2689gxp7OAGShNcBUmLMPVjZuiKcA2vuWHHDCQxMCz629kXAIU4ApY15QwggAFbfOP9DhgBJ+nWVJ1AZAfICAj1pAlY6hCADZnveQf7bQIwzVONGJonhLIlS9gr5mFg44Xd+4S3XHoGNPdJl1INIwKyEgHckEhgTe1bGiFY9GSFBYUwLh1IkiJUbY407E7syBSFxKTszEoiE/YdrgCEayDmtaJwCI9uu8TKMuZSVfSa4BpGgzvomBR/INhLGzrqDotp01ZR8pn/1L0JN9d9XNyx0AAAAAElFTkSuQmCC';
		}
		$snippet_type    = isset( $snippet_preview['type'] ) ? $snippet_preview['type'] : '';
		$desktop_preview = isset( $snippet_preview['desktop'] ) ? $snippet_preview['desktop'] : '';
		$mobile_preview  = isset( $snippet_preview['mobile'] ) ? $snippet_preview['mobile'] : '';
		?>
		<div class="serp-preview desktop-preview serp-<?php echo esc_attr( $snippet_type ); ?>">

			<div class="serp-preview-title" data-title="<?php esc_attr_e( 'Preview', 'rank-math' ); ?>" data-desktop="<?php esc_attr_e( 'Desktop Preview', 'rank-math' ); ?>" data-mobile="<?php esc_attr_e( 'Mobile Preview', 'rank-math' ); ?>">
				<div class="alignright">
					<a href="#" class="button button-secondary button-small rank-math-select-device device-desktop" data-device="desktop"><span class="rm-icon rm-icon-desktop"></span></a>
					<a href="#" class="button button-secondary button-small rank-math-select-device device-mobile" data-device="mobile"><span class="rm-icon rm-icon-mobile"></span></a>
				</div>
			</div>

			<div class="serp-preview-wrapper">
				<div class="serp-preview-bg">
					<div class="serp-preview-input">
						<input type="text" value="RankMath" disabled />
						<span class="serp-search">
							<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path></svg>
						</span>
						<span class="serp-mic"></span>
					</div>
					<div class="serp-preview-menus">
						<ul>
							<li class="current"> <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0ibm9uZSIgZD0iTTAgMGgyNHYyNEgweiIvPjxwYXRoIGZpbGw9IiMzNEE4NTMiIGQ9Ik0xMCAydjJhNiA2IDAgMCAxIDYgNmgyYTggOCAwIDAgMC04LTh6Ii8+PHBhdGggZmlsbD0iI0VBNDMzNSIgZD0iTTEwIDRWMmE4IDggMCAwIDAtOCA4aDJjMC0zLjMgMi43LTYgNi02eiIvPjxwYXRoIGZpbGw9IiNGQkJDMDQiIGQ9Ik00IDEwSDJhOCA4IDAgMCAwIDggOHYtMmMtMy4zIDAtNi0yLjY5LTYtNnoiLz48cGF0aCBmaWxsPSIjNDI4NUY0IiBkPSJNMjIgMjAuNTlsLTUuNjktNS42OUE3Ljk2IDcuOTYgMCAwIDAgMTggMTBoLTJhNiA2IDAgMCAxLTYgNnYyYzEuODUgMCAzLjUyLS42NCA0Ljg4LTEuNjhsNS42OSA1LjY5TDIyIDIwLjU5eiIvPjwvc3ZnPgo=" alt="" data-atf="1"> All</li>
							<li><svg focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"></path><path d="M14 13l4 5H6l4-4 1.79 1.78L14 13zm-6.01-2.99A2 2 0 0 0 8 6a2 2 0 0 0-.01 4.01zM22 5v14a3 3 0 0 1-3 2.99H5c-1.64 0-3-1.36-3-3V5c0-1.64 1.36-3 3-3h14c1.65 0 3 1.36 3 3zm-2.01 0a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h7v-.01h7a1 1 0 0 0 1-1V5z"></path></svg> Images</li>
							<li><svg focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M0 0h24v24H0z" fill="none"></path><path clip-rule="evenodd" d="M10 16.5l6-4.5-6-4.5v9zM5 20h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1zm14.5 2H5a3 3 0 0 1-3-3V4.4A2.4 2.4 0 0 1 4.4 2h15.2A2.4 2.4 0 0 1 22 4.4v15.1a2.5 2.5 0 0 1-2.5 2.5z" fill-rule="evenodd"></path></svg> Videos</li>
							<li><svg focusable="false" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none"></path><path d="M12 11h6v2h-6v-2zm-6 6h12v-2H6v2zm0-4h4V7H6v6zm16-7.22v12.44c0 1.54-1.34 2.78-3 2.78H5c-1.64 0-3-1.25-3-2.78V5.78C2 4.26 3.36 3 5 3h14c1.64 0 3 1.25 3 2.78zM19.99 12V5.78c0-.42-.46-.78-1-.78H5c-.54 0-1 .36-1 .78v12.44c0 .42.46.78 1 .78h14c.54 0 1-.36 1-.78V12zM12 9h6V7h-6v2z"></path></svg> News</li>
							<li><svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg> More</li>
						</ul>
						<ul class="menus-right">
							<li>Settings</li>
							<li>Tools</li>
						</ul>
					</div>

					<div class="serp-preview-result-stats">
						About 43,700,000 results (0.32 seconds)&nbsp;
					</div>
				</div>

				<div class="serp-preview-body">
					<div class="serp-url-wrapper">
						<img src="<?php echo $favicon; // phpcs:ignore ?>" width="16" height="16" class="serp-favicon" />
						<span class="serp-url" data-baseurl="<?php echo trailingslashit( substr( $data['url'], 0, strrpos( $data['url'], '/' ) ) ); ?>" data-format="<?php echo esc_attr( $data['permalink_format'] ); ?>" data-empty-title="<?php esc_attr_e( 'Click to enter permalink', 'rank-math' ); ?>"><?php echo esc_url( $data['permalink'] ); ?></span>
					</div>
					<h5 class="serp-title" data-format="<?php echo esc_attr( $data['title_format'] ); ?>" data-empty-title="<?php esc_attr_e( 'Click to enter custom title', 'rank-math' ); ?>"></h5>

					<p class="serp-description" data-format="<?php echo esc_attr( $data['desc_format'] ); ?>" data-empty-title="<?php esc_attr_e( 'Click to enter custom meta description', 'rank-math' ); ?>"></p>

					<?php
					if ( 'event' !== $snippet_type ) {
						echo $desktop_preview; // phpcs:ignore
					}

					if ( 'event' === $snippet_type ) {
						echo $desktop_preview; // phpcs:ignore
					}

					echo $mobile_preview; // phpcs:ignore
					?>

				</div>

				<div class="serp-preview-noindex">
					<h3><?php esc_html_e( 'Noindex robots meta is enabled', 'rank-math' ); ?></h3>
					<p><?php esc_html_e( 'This page will not appear in search results. You can disable noindex in the Advanced tab.', 'rank-math' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get post data for SERP preview.
	 *
	 * @return array
	 */
	private function get_post_data() {
		global $post;
		setup_postdata( $post );

		$post_type    = WordPress::get_post_type();
		$title_format = Helper::get_settings( "titles.pt_{$post_type}_title" );
		$desc_format  = Helper::get_settings( "titles.pt_{$post_type}_description" );
		$title_format = $title_format ? $title_format : '%title%';

		// Get the permalink.
		list( $permalink_format ) = get_sample_permalink( $post->ID, null, null );

		$permalink = $permalink_format;
		if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
			$permalink = get_permalink( $post );
		} elseif ( 'auto-draft' === $post->post_status && 'post' === $post->post_type ) {
			$post_temp              = $post;
			$post_temp->post_status = 'publish';
			$permalink              = get_permalink( $post_temp, true );
			$permalink_format       = $permalink;
		} else {
			$permalink = str_replace( [ '%pagename%', '%postname%' ], ( $post->post_name ? $post->post_name : sanitize_title( $post->post_title ) ), $permalink_format );
		}

		$url = untrailingslashit( esc_url( $permalink ) );

		return compact( 'title_format', 'desc_format', 'url', 'permalink', 'permalink_format' );
	}

	/**
	 * Get term data for SERP preview.
	 *
	 * @return array
	 */
	private function get_term_data() {
		global $taxnow, $wp_rewrite;

		$term_id  = Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
		$term     = get_term( $term_id, $taxnow, OBJECT, 'edit' );
		$taxonomy = get_taxonomy( $term->taxonomy );

		$title_format = Helper::get_settings( "titles.tax_{$term->taxonomy}_title" );
		$desc_format  = Helper::get_settings( "titles.tax_{$term->taxonomy}_description" );
		$title_format = $title_format ? $title_format : '%term%';

		// Get the permalink.
		$permalink = untrailingslashit( esc_url( get_term_link( $term_id, $term->taxonomy ) ) );
		$termlink  = $wp_rewrite->get_extra_permastruct( $term->taxonomy );

		// Pretty permalinks disabled.
		if ( empty( $termlink ) ) {
			$permalink_format = $permalink;
		} else {
			Sitepress::get()->remove_home_url_filter();
			$termlink         = str_replace( $this->get_home_url(), '', $permalink );
			$termlink         = str_replace( $term->slug, '%postname%', $termlink );
			$permalink_format = $this->get_home_url( user_trailingslashit( $termlink, 'category' ) );
			Sitepress::get()->restore_home_url_filter();
		}

		$url = untrailingslashit( esc_url( $permalink ) );

		return compact( 'title_format', 'desc_format', 'url', 'permalink', 'permalink_format' );
	}

	/**
	 * Get Home URL based on the language if Polylang plugin is active.
	 *
	 * @param string $path Optional. Path relative to the home URL. Default empty.
	 *
	 * @return string
	 */
	private function get_home_url( $path = '' ) {
		if ( ! function_exists( 'pll_home_url' ) ) {
			return home_url( $path );
		}

		return trailingslashit( pll_home_url() ) . $path;
	}

	/**
	 * Get user data for SERP preview.
	 *
	 * @return array
	 */
	private function get_user_data() {
		global $user_id, $wp_rewrite;

		$title_format = Helper::get_settings( 'titles.author_archive_title' );
		$desc_format  = Helper::get_settings( 'titles.author_archive_description' );
		$title_format = $title_format ? $title_format : '%author%';

		Rewrite::change_author_base();
		$permalink        = untrailingslashit( esc_url( get_author_posts_url( $user_id ) ) );
		$link             = $wp_rewrite->get_author_permastruct();
		$permalink_format = empty( $link ) ? $permalink : $this->get_author_permalink( $link );
		$url              = untrailingslashit( esc_url( $permalink ) );

		return compact( 'title_format', 'desc_format', 'url', 'permalink', 'permalink_format' );
	}

	/**
	 * Get user permalink
	 *
	 * @param  string $link Permalink structure.
	 * @return string
	 */
	private function get_author_permalink( $link ) {
		$link = str_replace( '%author%', '%postname%', $link );
		return home_url( user_trailingslashit( $link ) );
	}

	/**
	 * Get Snippet HTML for SERP preview.
	 */
	private function get_snippet_html() {
		$snippet_data = $this->get_snippet_data();
		if ( ! $snippet_data || ! isset( $snippet_data['data'] ) ) {
			return false;
		}

		$data         = $snippet_data['data'];
		$rating       = isset( $data['rating'] ) ? $data['rating'] : '';
		$rating_count = isset( $data['rating_count'] ) ? $data['rating_count'] : '';
		unset( $data['rating'] );
		unset( $data['rating_count'] );

		if ( isset( $data['price'] ) && isset( $data['currency'] ) ) {
			$data['price'] = $data['currency'] . ' ' . $data['price'];
			unset( $data['currency'] );
		}

		$html = [
			'type'    => $snippet_data['type'],
			'desktop' => $this->get_desktop_preview( $data, $rating, $rating_count ),
			'mobile'  => $this->get_mobile_preview( $data, $rating, $rating_count ),
		];

		return $html;
	}

	/**
	 * Get desktop preview.
	 *
	 * @param  array $data         Snippet data array.
	 * @param  int   $rating       Ratings.
	 * @param  int   $rating_count Rating count.
	 * @return string
	 */
	private function get_desktop_preview( $data, $rating, $rating_count ) {
		$preview = '';
		$labels  = [
			'price_range' => esc_html__( 'Price range: ', 'rank-math' ),
			'calories'    => esc_html__( 'Calories: ', 'rank-math' ),
		];

		if ( $rating ) {
			$preview .= $this->get_ratings( $rating );
			/* translators: total reviews */
			$preview .= '<span class="serp-rating-label">' . sprintf( esc_html__( 'Rating: %s', 'rank-math' ), esc_html( $rating ) ) . '</span>';
			if ( $rating_count ) {
				/* translators: total reviews */
				$preview .= '<span class="serp-review-count"> - ' . sprintf( esc_html__( '%s reviews', 'rank-math' ), esc_html( $rating_count ) ) . '</span>';
			}
		}

		foreach ( $data as $key => $value ) {
			if ( ! $value || in_array( $key, [ 'min', 'max' ], true ) ) {
				continue;
			}

			if ( ! in_array( $key, [ 'event_date', 'event_place', 'event_name' ], true ) ) {
				$preview .= '<span class="separator"> - </span>';
			}

			$preview .= '<span class="serp-' . $key . '">';
			if ( isset( $labels[ $key ] ) ) {
				$preview .= $labels[ $key ];
			}

			$preview .= $value;

			$preview .= '</span>';
		}

		return '<div class="serp-snippet-data">' . $preview . '</div>';
	}

	/**
	 * Get mobile preview.
	 *
	 * @param  array $data         Snippet data array.
	 * @param  int   $rating       Ratings.
	 * @param  int   $rating_count Rating count.
	 * @return string
	 */
	private function get_mobile_preview( $data, $rating, $rating_count ) {
		$labels = [
			'price'       => esc_html__( 'Price', 'rank-math' ),
			'price_range' => esc_html__( 'Price range', 'rank-math' ),
			'time'        => esc_html__( 'Cooking time', 'rank-math' ),
			'calories'    => esc_html__( 'Calories', 'rank-math' ),
			'in_stock'    => esc_html__( 'In Stock', 'rank-math' ),
			'event_date'  => esc_html__( 'Date', 'rank-math' ),
			'event_place' => esc_html__( 'Location', 'rank-math' ),
		];

		$preview = '';
		if ( $rating ) {
			$preview .= '<span class="inner-wrapper">';
			$preview .= '<span class="serp-mobile-label">';
			$preview .= esc_html__( 'Rating', 'rank-math' );
			$preview .= '</span>';
			$preview .= '<span class="serp-rating-count">' . esc_html( $rating ) . '</span>';

			$preview .= $this->get_ratings( $rating );

			if ( $rating_count ) {
				$preview .= '<span class="serp-review-count">(' . esc_html( $rating_count ) . ')</span>';
			}

			$preview .= '</span>';
		}

		foreach ( $data as $key => $value ) {
			if ( ! $value || in_array( $key, [ 'event_name', 'min', 'max' ], true ) ) {
				continue;
			}

			$preview .= '<span class="inner-wrapper">';

			if ( isset( $labels[ $key ] ) ) {
				$preview .= '<span class="serp-mobile-label">';
				$preview .= $labels[ $key ];
				$preview .= '</span>';
			}

			if ( 'in_stock' !== $key ) {
				$preview .= '<span class="serp-' . $key . '">' . esc_html( $value ) . '</span>';
			}

			$preview .= '</span>';
		}

		return '<div class="serp-snippet-mobile">' . $preview . '</div>';
	}

	/**
	 * Get Star Ratings.
	 *
	 * @param int $rating Rating count.
	 */
	private function get_ratings( $rating ) {
		$html   = '';
		$rating = $rating * 20;

		for ( $i = 1; $i <= 5; $i++ ) {
			$html .= '<span class="dashicons dashicons-star-filled"></span>';
		}

		$html .= '<div class="serp-result" style="width:' . $rating . '%;">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$html .= '<span class="dashicons dashicons-star-filled"></span>';
		}

		return '<span class="serp-rating serp-desktop-rating"><div class="serp-star-rating">' . $html . '</div></div></span>';
	}

	/**
	 * Get Snippet Data for SERP preview.
	 *
	 * @return array
	 */
	private function get_snippet_data() {
		global $post;
		setup_postdata( $post );

		$schemas = array_filter(
			DB::get_schemas( $post->ID ),
			function( $schema ) {
				return ! empty( $schema['metadata']['isPrimary'] );
			}
		);
		if ( empty( $schemas ) ) {
			return false;
		}

		// Get rich snippet.
		$schema          = current( $schemas );
		$snippet         = strtolower( $schema['@type'] );
		$wp_review_total = get_post_meta( $post->ID, 'wp_review_total', true );

		if ( ! in_array( $snippet, [ 'book', 'course', 'event', 'product', 'recipe', 'softwareapplication', 'woocommerceproduct' ], true ) && ! $wp_review_total ) {
			return false;
		}

		$method = in_array( $snippet, [ 'book', 'course', 'softwareapplication' ], true ) ? 'get_ratings_data' : "get_{$snippet}_data";

		return [
			'type' => $snippet,
			'data' => $this->$method( $schema ),
		];
	}

	/**
	 * Get WooCommerce Product schema data.
	 *
	 * @param  array $schema Schema Data.
	 * @return array Preview Schema Data.
	 */
	private function get_woocommerceproduct_data( $schema ) {
		global $post;
		$product = wc_get_product( $post->ID );
		return [
			'price'        => $product->get_price(),
			'currency'     => get_woocommerce_currency_symbol(),
			'in_stock'     => 'outofstock' === $product->get_stock_status() ? esc_html__( 'Out of stock', 'rank-math' ) : __( 'In stock', 'rank-math' ),
			'rating'       => $product->get_average_rating(),
			'rating_count' => (string) $product->get_rating_count(),
		];
	}

	/**
	 * Get Product schema data.
	 *
	 * @param  array $schema Schema Data.
	 * @return array Preview Schema Data.
	 */
	private function get_product_data( $schema ) {
		$offers = [];
		if ( isset( $schema['offers'] ) ) {
			$offers = [
				'price'    => isset( $schema['offers']['price'] ) ? $schema['offers']['price'] : '',
				'currency' => isset( $schema['offers']['priceCurrency'] ) ? $schema['offers']['priceCurrency'] : '',
				'in_stock' => isset( $schema['offers']['availability'] ) && 'SoldOut' === $schema['offers']['availability'] ? esc_html__( 'Out of stock', 'rank-math' ) : esc_html__( 'In stock', 'rank-math' ),
			];
		}

		return \array_merge(
			$offers,
			$this->get_ratings_data( $schema )
		);
	}

	/**
	 * Get Event schema data.
	 *
	 * @param  array $schema Schema Data.
	 * @return array Preview Schema Data.
	 */
	private function get_event_data( $schema ) {
		global $post;
		$address = '';
		if ( ! empty( $schema['location']['address'] ) ) {
			unset( $schema['location']['address']['@type'] );
			$address = implode( ', ', array_filter( $schema['location']['address'] ) );
		}

		return \array_merge(
			[
				'event_name'  => Helper::replace_vars( $schema['name'], $post ),
				'event_date'  => ! empty( $schema['startDate'] ) ? date_i18n( 'j M Y', $schema['startDate'] ) : '',
				'event_place' => $address,
			],
			$this->get_ratings_data( $schema )
		);
	}

	/**
	 * Get Recipe schema data.
	 *
	 * @param  array $schema Schema Data.
	 * @return array Preview Schema Data.
	 */
	private function get_recipe_data( $schema ) {
		return \array_merge(
			[
				'time'     => ! empty( $schema['totalTime'] ) ? $schema['totalTime'] : '',
				'calories' => ! empty( $schema['nutrition'] ) && ! empty( $schema['nutrition']['calories'] ) ? $schema['nutrition']['calories'] : '',
			],
			$this->get_ratings_data( $schema )
		);
	}

	/**
	 * Get Ratings schema data.
	 *
	 * @param  array $schema Schema Data.
	 * @return array Preview Schema Data.
	 */
	private function get_ratings_data( $schema ) {
		$review = isset( $schema['review'] ) && isset( $schema['review']['reviewRating']['ratingValue'] ) ? $schema['review']['reviewRating'] : '';
		if ( empty( $review ) ) {
			return [];
		}

		return [
			'rating' => $review['ratingValue'],
			'min'    => ! empty( $review['worstRating'] ) ? $review['worstRating'] : 1,
			'max'    => ! empty( $review['bestRating'] ) ? $review['bestRating'] : 5,
		];
	}
}
