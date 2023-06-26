<?php
/**
 * The Choices helpers.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Choices class.
 */
trait Choices {

	/**
	 * Gets list of overlay images for the social thumbnail.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $output Output type.
	 * @return array
	 */
	public static function choices_overlay_images( $output = 'object' ) {
		$uri = rank_math()->plugin_url() . 'assets/admin/img/';
		$dir = rank_math()->plugin_dir() . 'assets/admin/img/';

		/**
		 * Allow developers to add/remove overlay images.
		 *
		 * @param array $images Image data as array of arrays.
		 */
		$list = apply_filters(
			'rank_math/social/overlay_images',
			[
				'play' => [
					'name' => esc_html__( 'Play icon', 'rank-math' ),
					'url'  => $uri . 'icon-play.png',
					'path' => $dir . 'icon-play.png',
				],
				'gif'  => [
					'name' => esc_html__( 'GIF icon', 'rank-math' ),
					'url'  => $uri . 'icon-gif.png',
					'path' => $dir . 'icon-gif.png',
				],
			]
		);

		// Allow custom positions.
		foreach ( $list as $name => $data ) {
			$list[ $name ]['position'] = apply_filters( 'rank_math/social/overlay_image_position', 'middle_center', $name );
		}

		return 'names' === $output ? wp_list_pluck( $list, 'name' ) : $list;
	}

	/**
	 * Get robot choices.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_robots() {
		return [
			'index'        => esc_html__( 'Index', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Instructs search engines to index and show these pages in the search results.', 'rank-math' ) ),
			'noindex'      => esc_html__( 'No Index', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Prevents pages from being indexed and displayed in search engine result pages', 'rank-math' ) ),
			'nofollow'     => esc_html__( 'No Follow', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Prevents search engines from following links on the pages', 'rank-math' ) ),
			'noarchive'    => esc_html__( 'No Archive', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Prevents search engines from showing Cached links for pages', 'rank-math' ) ),
			'noimageindex' => esc_html__( 'No Image Index', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Prevents images on a page from being indexed by Google and other search engines', 'rank-math' ) ),
			'nosnippet'    => esc_html__( 'No Snippet', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Prevents a snippet from being shown in the search results', 'rank-math' ) ),
		];
	}

	/**
	 * Get separator choices.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $current Currently saved separator if any.
	 * @return array
	 */
	public static function choices_separator( $current = '' ) {
		$defaults = [ '-', '&ndash;', '&mdash;', '&raquo;', '|', '&bull;' ];
		if ( ! $current || in_array( $current, $defaults, true ) ) {
			$current = '';
		}

		return [
			'-'       => '-',
			'&ndash;' => '&ndash;',
			'&mdash;' => '&mdash;',
			'&raquo;' => '&raquo;',
			'|'       => '|',
			'&bull;'  => '&bull;',
			$current  => '<span class="custom-sep" contenteditable>' . $current . '</span>',
		];
	}

	/**
	 * Get all accessible post types as choices.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_post_types() {
		static $choices_post_types;

		if ( ! isset( $choices_post_types ) ) {
			$choices_post_types = Helper::get_accessible_post_types();
			$choices_post_types = \array_map(
				function( $post_type ) {
					$object = get_post_type_object( $post_type );
					return $object->label;
				},
				$choices_post_types
			);
		}

		return $choices_post_types;
	}

	/**
	 * Get all post types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_any_post_types() {

		$post_types = self::choices_post_types();
		unset( $post_types['attachment'] );

		return [ 'any' => esc_html__( 'Any', 'rank-math' ) ] + $post_types + [ 'comments' => esc_html( translate( 'Comments' ) ) ]; // phpcs:ignore
	}

	/**
	 * Get business types as choices.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  bool $none Add none option to list.
	 * @return array
	 */
	public static function choices_business_types( $none = false ) {
		$data = apply_filters(
			'rank_math/json_ld/business_types',
			[
				[
					'label' => 'Organization',
					'child' => [
						[ 'label' => 'Airline' ],
						[ 'label' => 'Consortium' ],
						[ 'label' => 'Corporation' ],
						[
							'label' => 'Educational Organization',
							'child' => [
								[ 'label' => 'College Or University' ],
								[ 'label' => 'Elementary School' ],
								[ 'label' => 'High School' ],
								[ 'label' => 'Middle School' ],
								[ 'label' => 'Preschool' ],
								[ 'label' => 'School' ],
							],
						],
						[ 'label' => 'Funding Scheme' ],
						[ 'label' => 'Government Organization' ],
						[ 'label' => 'Library System' ],
						[
							'label' => 'Local Business',
							'child' => [
								[ 'label' => 'Animal Shelter' ],
								[ 'label' => 'Archive Organization' ],
								[
									'label' => 'Automotive Business',
									'child' => [
										[ 'label' => 'Auto Body Shop' ],
										[ 'label' => 'Auto Dealer' ],
										[ 'label' => 'Auto Parts Store' ],
										[ 'label' => 'Auto Rental' ],
										[ 'label' => 'Auto Repair' ],
										[ 'label' => 'Auto Wash' ],
										[ 'label' => 'Gas Station' ],
										[ 'label' => 'Motorcycle Dealer' ],
										[ 'label' => 'Motorcycle Repair' ],
									],
								],
								[ 'label' => 'Child Care' ],
								[ 'label' => 'Dry Cleaning Or Laundry' ],
								[
									'label' => 'Emergency Service',
									'child' => [
										[ 'label' => 'Fire Station' ],
										[ 'label' => 'Hospital' ],
										[ 'label' => 'Police Station' ],
									],
								],
								[ 'label' => 'Employment Agency' ],
								[
									'label' => 'Entertainment Business',
									'child' => [
										[ 'label' => 'Adult Entertainment' ],
										[ 'label' => 'Amusement Park' ],
										[ 'label' => 'Art Gallery' ],
										[ 'label' => 'Casino' ],
										[ 'label' => 'Comedy Club' ],
										[ 'label' => 'Movie Theater' ],
										[ 'label' => 'Night Club' ],
									],
								],
								[
									'label' => 'Financial Service',
									'child' => [
										[ 'label' => 'Accounting Service' ],
										[ 'label' => 'Automated Teller' ],
										[ 'label' => 'Bank Or CreditUnion' ],
										[ 'label' => 'Insurance Agency' ],
									],
								],
								[
									'label' => 'Food Establishment',
									'child' => [
										[ 'label' => 'Bakery' ],
										[ 'label' => 'Bar Or Pub' ],
										[ 'label' => 'Brewery' ],
										[ 'label' => 'Cafe Or CoffeeShop' ],
										[ 'label' => 'Distillery' ],
										[ 'label' => 'Fast Food Restaurant' ],
										[ 'label' => 'IceCream Shop' ],
										[ 'label' => 'Restaurant' ],
										[ 'label' => 'Winery' ],
									],
								],
								[
									'label' => 'Government Office',
									'child' => [
										[ 'label' => 'Post Office' ],
									],
								],
								[
									'label' => 'Health And Beauty Business',
									'child' => [
										[ 'label' => 'Beauty Salon' ],
										[ 'label' => 'Day Spa' ],
										[ 'label' => 'Hair Salon' ],
										[ 'label' => 'Health Club' ],
										[ 'label' => 'Nail Salon' ],
										[ 'label' => 'Tattoo Parlor' ],
									],
								],
								[
									'label' => 'Home And Construction Business',
									'child' => [
										[ 'label' => 'Electrician' ],
										[ 'label' => 'General Contractor' ],
										[ 'label' => 'HVAC Business' ],
										[ 'label' => 'House Painter' ],
										[ 'label' => 'Locksmith' ],
										[ 'label' => 'Moving Company' ],
										[ 'label' => 'Plumber' ],
										[ 'label' => 'Roofing Contractor' ],
									],
								],
								[ 'label' => 'Internet Cafe' ],
								[
									'label' => 'Legal Service',
									'child' => [
										[ 'label' => 'Notary' ],
									],
								],
								[ 'label' => 'Library' ],
								[
									'label' => 'Lodging Business',
									'child' => [
										[ 'label' => 'Bed And Breakfast' ],
										[ 'label' => 'Campground' ],
										[ 'label' => 'Hostel' ],
										[ 'label' => 'Hotel' ],
										[ 'label' => 'Motel' ],
										[
											'label' => 'Resort',
											'child' => [
												[ 'label' => 'Ski Resort' ],
											],
										],
									],
								],
								[
									'label' => 'Medical Business',
									'child' => [
										[ 'label' => 'Community Health' ],
										[ 'label' => 'Dentist' ],
										[ 'label' => 'Dermatology' ],
										[ 'label' => 'Diet Nutrition' ],
										[ 'label' => 'Emergency' ],
										[ 'label' => 'Geriatric' ],
										[ 'label' => 'Gynecologic' ],
										[ 'label' => 'Medical Clinic' ],
										[ 'label' => 'Optician' ],
										[ 'label' => 'Pharmacy' ],
										[ 'label' => 'Physician' ],
									],
								],
								[ 'label' => 'Professional Service' ],
								[ 'label' => 'Radio Station' ],
								[ 'label' => 'Real Estate Agent' ],
								[ 'label' => 'Recycling Center' ],
								[ 'label' => 'Self Storage' ],
								[ 'label' => 'Shopping Center' ],
								[
									'label' => 'Sports Activity Location',
									'child' => [
										[ 'label' => 'Bowling Alley' ],
										[ 'label' => 'Exercise Gym' ],
										[ 'label' => 'Golf Course' ],
										[ 'label' => 'Health Club' ],
										[ 'label' => 'Public Swimming Pool' ],
										[ 'label' => 'Ski Resort' ],
										[ 'label' => 'Sports Club' ],
										[ 'label' => 'Stadium Or Arena' ],
										[ 'label' => 'Tennis Complex' ],
									],
								],
								[
									'label' => 'Store',
									'child' => [
										[ 'label' => 'Auto Parts Store' ],
										[ 'label' => 'Bike Store' ],
										[ 'label' => 'Book Store' ],
										[ 'label' => 'Clothing Store' ],
										[ 'label' => 'Computer Store' ],
										[ 'label' => 'Convenience Store' ],
										[ 'label' => 'Department Store' ],
										[ 'label' => 'Electronics Store' ],
										[ 'label' => 'Florist' ],
										[ 'label' => 'Furniture Store' ],
										[ 'label' => 'Garden Store' ],
										[ 'label' => 'Grocery Store' ],
										[ 'label' => 'Hardware Store' ],
										[ 'label' => 'Hobby Shop' ],
										[ 'label' => 'Home Goods Store' ],
										[ 'label' => 'Jewelry Store' ],
										[ 'label' => 'Liquor Store' ],
										[ 'label' => 'Mens Clothing Store' ],
										[ 'label' => 'Mobile Phone Store' ],
										[ 'label' => 'Movie Rental Store' ],
										[ 'label' => 'Music Store' ],
										[ 'label' => 'Office Equipment Store' ],
										[ 'label' => 'Outlet Store' ],
										[ 'label' => 'Pawn Shop' ],
										[ 'label' => 'Pet Store' ],
										[ 'label' => 'Shoe Store' ],
										[ 'label' => 'Sporting GoodsStore' ],
										[ 'label' => 'Tire Shop' ],
										[ 'label' => 'Toy Store' ],
										[ 'label' => 'Wholesale Store' ],
									],
								],
								[ 'label' => 'Television Station' ],
								[ 'label' => 'Tourist Information Center' ],
								[ 'label' => 'Travel Agency' ],
							],
						],
						[
							'label' => 'Medical Organization',
							'child' => [
								[ 'label' => 'Diagnostic Lab' ],
								[ 'label' => 'Veterinary Care' ],
							],
						],
						[ 'label' => 'NGO' ],
						[ 'label' => 'News Media Organization' ],
						[
							'label' => 'Performing Group',
							'child' => [
								[ 'label' => 'Dance Group' ],
								[ 'label' => 'Music Group' ],
								[ 'label' => 'Theater Group' ],
							],
						],
						[
							'label' => 'Project',
							'child' => [
								[ 'label' => 'Funding Agency' ],
								[ 'label' => 'Research Project' ],
							],
						],
						[
							'label' => 'Sports Organization',
							'child' => [
								[ 'label' => 'Sports Team' ],
							],
						],
						[ 'label' => 'Workers Union' ],
					],
				],
			]
		);

		$business = [];
		if ( $none ) {
			$business['off'] = 'None';
		}

		foreach ( $data as $item ) {
			$business[ str_replace( ' ', '', $item['label'] ) ] = $item['label'];

			if ( isset( $item['child'] ) ) {
				self::indent_child_elements( $business, $item['child'] );
			}
		}

		return $business;
	}

	/**
	 * Get Schema types as choices.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  bool   $none      Add none option to the list.
	 * @param  string $post_type Post type.
	 * @return array
	 */
	public static function choices_rich_snippet_types( $none = false, $post_type = '' ) {
		$types = [
			'article'    => esc_html__( 'Article', 'rank-math' ),
			'book'       => esc_html__( 'Book', 'rank-math' ),
			'course'     => esc_html__( 'Course', 'rank-math' ),
			'event'      => esc_html__( 'Event', 'rank-math' ),
			'jobposting' => esc_html__( 'Job Posting', 'rank-math' ),
			'music'      => esc_html__( 'Music', 'rank-math' ),
			'product'    => esc_html__( 'Product', 'rank-math' ),
			'recipe'     => esc_html__( 'Recipe', 'rank-math' ),
			'restaurant' => esc_html__( 'Restaurant', 'rank-math' ),
			'video'      => esc_html__( 'Video', 'rank-math' ),
			'person'     => esc_html__( 'Person', 'rank-math' ),
			'service'    => esc_html__( 'Service', 'rank-math' ),
			'software'   => esc_html__( 'Software Application', 'rank-math' ),
		];

		if ( ! empty( self::get_review_posts() ) ) {
			$types['review'] = esc_html__( 'Review (Unsupported)', 'rank-math' );
		}

		if ( is_string( $none ) ) {
			$types = [ 'off' => $none ] + $types;
		}

		/**
		 * Allow developers to add/remove Schema type choices.
		 *
		 * @param array  $types     Schema types.
		 * @param string $post_type Post type.
		 */
		return apply_filters( 'rank_math/settings/snippet/types', $types, $post_type );
	}

	/**
	 * Get the redirection types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_redirection_types() {
		return [
			'301' => esc_html__( '301 Permanent Move', 'rank-math' ),
			'302' => esc_html__( '302 Temporary Move', 'rank-math' ),
			'307' => esc_html__( '307 Temporary Redirect', 'rank-math' ),
			'410' => esc_html__( '410 Content Deleted', 'rank-math' ),
			'451' => esc_html__( '451 Content Unavailable for Legal Reasons', 'rank-math' ),
		];
	}

	/**
	 * Get comparison types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_comparison_types() {
		return [
			'exact'    => esc_html__( 'Exact', 'rank-math' ),
			'contains' => esc_html__( 'Contains', 'rank-math' ),
			'start'    => esc_html__( 'Starts With', 'rank-math' ),
			'end'      => esc_html__( 'End With', 'rank-math' ),
			'regex'    => esc_html__( 'Regex', 'rank-math' ),
		];
	}

	/**
	 * Get Post type icons.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_post_type_icons() {
		/**
		 * Allow developer to change post types icons.
		 *
		 * @param array $icons Array of available icons.
		 */
		return apply_filters(
			'rank_math/post_type_icons',
			[
				'default'    => 'rm-icon rm-icon-post',
				'post'       => 'rm-icon rm-icon-post',
				'page'       => 'rm-icon rm-icon-page',
				'attachment' => 'rm-icon rm-icon-attachment',
				'product'    => 'rm-icon rm-icon-cart',
				'web-story'  => 'rm-icon rm-icon-stories',
			]
		);
	}

	/**
	 * Get Taxonomy icons.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_taxonomy_icons() {
		/**
		 * Allow developer to change taxonomies icons.
		 *
		 * @param array $icons Array of available icons.
		 */
		return apply_filters(
			'rank_math/taxonomy_icons',
			[
				'default'     => 'rm-icon rm-icon-category',
				'category'    => 'rm-icon rm-icon-category',
				'post_tag'    => 'rm-icon rm-icon-tag',
				'product_cat' => 'rm-icon rm-icon-category',
				'product_tag' => 'rm-icon rm-icon-tag',
				'post_format' => 'rm-icon rm-icon-post-format',
			]
		);
	}

	/**
	 * Function to get posts having review schema type selected.
	 */
	public static function get_review_posts() {
		global $wpdb;

		static $posts = null;

		if ( true === boolval( get_option( 'rank_math_review_posts_converted' ) ) ) {
			return false;
		}

		if ( ! is_null( $posts ) ) {
			return $posts;
		}

		$posts = get_transient( 'rank_math_any_review_posts' );
		if ( false !== $posts ) {
			return $posts;
		}

		$meta_query = new \WP_Meta_Query(
			[
				'relation' => 'AND',
				[
					'key'   => 'rank_math_rich_snippet',
					'value' => 'review',
				],
			]
		);

		$meta_query = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM $wpdb->posts {$meta_query['join']} WHERE 1=1 {$meta_query['where']} AND ({$wpdb->posts}.post_status = 'publish')" ); // phpcs:ignore

		if ( 0 === count( $posts ) ) {
			update_option( 'rank_math_review_posts_converted', true );
			return false;
		}

		set_transient( 'rank_math_any_review_posts', $posts, DAY_IN_SECONDS );

		return $posts;
	}

	/**
	 * Phones types for schema.
	 *
	 * @return array
	 */
	public static function choices_phone_types() {
		return [
			'customer support'    => esc_html__( 'Customer Service', 'rank-math' ),
			'technical support'   => esc_html__( 'Technical Support', 'rank-math' ),
			'billing support'     => esc_html__( 'Billing Support', 'rank-math' ),
			'bill payment'        => esc_html__( 'Bill Payment', 'rank-math' ),
			'sales'               => esc_html__( 'Sales', 'rank-math' ),
			'reservations'        => esc_html__( 'Reservations', 'rank-math' ),
			'credit card support' => esc_html__( 'Credit Card Support', 'rank-math' ),
			'emergency'           => esc_html__( 'Emergency', 'rank-math' ),
			'baggage tracking'    => esc_html__( 'Baggage Tracking', 'rank-math' ),
			'roadside assistance' => esc_html__( 'Roadside Assistance', 'rank-math' ),
			'package tracking'    => esc_html__( 'Package Tracking', 'rank-math' ),
		];
	}

	/**
	 * Function to indent child business types..
	 *
	 * @param array $business Business types array.
	 * @param array $item     Array of child data.
	 * @param int   $level    Nesting level of the current iteration.
	 */
	private static function indent_child_elements( &$business, $item, $level = 1 ) {
		foreach ( $item as $child ) {
			$business[ str_replace( ' ', '', $child['label'] ) ] = str_repeat( '&mdash; ', $level ) . $child['label'];

			if ( isset( $child['child'] ) ) {
				self::indent_child_elements( $business, $child['child'], ( $level + 1 ) );
			}
		}
	}

	/**
	 * Country.
	 *
	 * @return array
	 */
	public static function choices_contentai_countries() {
		return [
			'all'    => esc_html__( 'Worldwide', 'rank-math' ),
			'ar_DZ'  => esc_html__( 'Algeria', 'rank-math' ),
			'es_AR'  => esc_html__( 'Argentina', 'rank-math' ),
			'hy_AM'  => esc_html__( 'Armenia', 'rank-math' ),
			'en_AU'  => esc_html__( 'Australia', 'rank-math' ),
			'de_AT'  => esc_html__( 'Austria', 'rank-math' ),
			'az_AZ'  => esc_html__( 'Azerbaijan', 'rank-math' ),
			'ar_BH'  => esc_html__( 'Bahrain', 'rank-math' ),
			'bn_BD'  => esc_html__( 'Bangladesh', 'rank-math' ),
			'be_BY'  => esc_html__( 'Belarus', 'rank-math' ),
			'de_BE'  => esc_html__( 'Belgium', 'rank-math' ),
			'es_BO'  => esc_html__( 'Bolivia, Plurinational State Of', 'rank-math' ),
			'pt_BR'  => esc_html__( 'Brazil', 'rank-math' ),
			'bg_BG'  => esc_html__( 'Bulgaria', 'rank-math' ),
			'km_KH'  => esc_html__( 'Cambodia', 'rank-math' ),
			'en_CA'  => esc_html__( 'Canada', 'rank-math' ),
			'es_CL'  => esc_html__( 'Chile', 'rank-math' ),
			'es_CO'  => esc_html__( 'Colombia', 'rank-math' ),
			'es_CR'  => esc_html__( 'Costa Rica', 'rank-math' ),
			'hr_HR'  => esc_html__( 'Croatia', 'rank-math' ),
			'el_CY'  => esc_html__( 'Cyprus', 'rank-math' ),
			'cs_CZ'  => esc_html__( 'Czechia', 'rank-math' ),
			'da_DK'  => esc_html__( 'Denmark', 'rank-math' ),
			'es_EC'  => esc_html__( 'Ecuador', 'rank-math' ),
			'ar_EG'  => esc_html__( 'Egypt', 'rank-math' ),
			'es_SV'  => esc_html__( 'El Salvador', 'rank-math' ),
			'et_EE'  => esc_html__( 'Estonia', 'rank-math' ),
			'fi_FI'  => esc_html__( 'Finland', 'rank-math' ),
			'fr_FR'  => esc_html__( 'France', 'rank-math' ),
			'de_DE'  => esc_html__( 'Germany', 'rank-math' ),
			'ak_GH'  => esc_html__( 'Ghana', 'rank-math' ),
			'el_GR'  => esc_html__( 'Greece', 'rank-math' ),
			'es_GT'  => esc_html__( 'Guatemala', 'rank-math' ),
			'en_HK'  => esc_html__( 'Hong Kong', 'rank-math' ),
			'hu_HU'  => esc_html__( 'Hungary', 'rank-math' ),
			'hi_IN'  => esc_html__( 'India', 'rank-math' ),
			'id_ID'  => esc_html__( 'Indonesia', 'rank-math' ),
			'en_IE'  => esc_html__( 'Ireland', 'rank-math' ),
			'he_IL'  => esc_html__( 'Israel', 'rank-math' ),
			'it_IT'  => esc_html__( 'Italy', 'rank-math' ),
			'ja_JP'  => esc_html__( 'Japan', 'rank-math' ),
			'ar_JO'  => esc_html__( 'Jordan', 'rank-math' ),
			'kk_KZ'  => esc_html__( 'Kazakhstan', 'rank-math' ),
			'om_KE'  => esc_html__( 'Kenya', 'rank-math' ),
			'ko_KR'  => esc_html__( 'Korea, Republic Of', 'rank-math' ),
			'lv_LV'  => esc_html__( 'Latvia', 'rank-math' ),
			'lt_LT'  => esc_html__( 'Lithuania', 'rank-math' ),
			'mk_MK'  => esc_html__( 'Macedonia, The Former Yugoslav Republic Of', 'rank-math' ),
			'ms_MY'  => esc_html__( 'Malaysia', 'rank-math' ),
			'mt_MT'  => esc_html__( 'Malta', 'rank-math' ),
			'es_MX'  => esc_html__( 'Mexico', 'rank-math' ),
			'ar_MA'  => esc_html__( 'Morocco', 'rank-math' ),
			'mnw_MM' => esc_html__( 'Myanmar', 'rank-math' ),
			'fy_NL'  => esc_html__( 'Netherlands', 'rank-math' ),
			'en_NZ'  => esc_html__( 'New Zealand', 'rank-math' ),
			'es_NI'  => esc_html__( 'Nicaragua', 'rank-math' ),
			'en_NG'  => esc_html__( 'Nigeria', 'rank-math' ),
			'nb_NO'  => esc_html__( 'Norway', 'rank-math' ),
			'pa_PK'  => esc_html__( 'Pakistan', 'rank-math' ),
			'es_PY'  => esc_html__( 'Paraguay', 'rank-math' ),
			'es_PE'  => esc_html__( 'Peru', 'rank-math' ),
			'en_PH'  => esc_html__( 'Philippines', 'rank-math' ),
			'pl_PL'  => esc_html__( 'Poland', 'rank-math' ),
			'pt_PT'  => esc_html__( 'Portugal', 'rank-math' ),
			'ro_RO'  => esc_html__( 'Romania', 'rank-math' ),
			'ce_RU'  => esc_html__( 'Russian Federation', 'rank-math' ),
			'ar_SA'  => esc_html__( 'Saudi Arabia', 'rank-math' ),
			'ff_SN'  => esc_html__( 'Senegal', 'rank-math' ),
			'sq_RS'  => esc_html__( 'Serbia', 'rank-math' ),
			'en_SG'  => esc_html__( 'Singapore', 'rank-math' ),
			'sk_SK'  => esc_html__( 'Slovakia', 'rank-math' ),
			'sl_SI'  => esc_html__( 'Slovenia', 'rank-math' ),
			'af_ZA'  => esc_html__( 'South Africa', 'rank-math' ),
			'an_ES'  => esc_html__( 'Spain', 'rank-math' ),
			'si_LK'  => esc_html__( 'Sri Lanka', 'rank-math' ),
			'sv_SE'  => esc_html__( 'Sweden', 'rank-math' ),
			'de_CH'  => esc_html__( 'Switzerland', 'rank-math' ),
			'zh_TW'  => esc_html__( 'Taiwan', 'rank-math' ),
			'th_TH'  => esc_html__( 'Thailand', 'rank-math' ),
			'ar_TN'  => esc_html__( 'Tunisia', 'rank-math' ),
			'az_TR'  => esc_html__( 'Turkey', 'rank-math' ),
			'ru_UA'  => esc_html__( 'Ukraine', 'rank-math' ),
			'ar_AE'  => esc_html__( 'United Arab Emirates', 'rank-math' ),
			'en_GB'  => esc_html__( 'United Kingdom', 'rank-math' ),
			'en_US'  => esc_html__( 'United States Of America', 'rank-math' ),
			'es_UY'  => esc_html__( 'Uruguay', 'rank-math' ),
			'es_VE'  => esc_html__( 'Venezuela, Bolivarian Republic Of', 'rank-math' ),
			'vi_VN'  => esc_html__( 'Viet Nam', 'rank-math' ),
		];
	}
}
