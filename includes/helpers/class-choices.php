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
			'noimageindex' => esc_html__( 'No Image Index', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Lets you specify that you do not want your pages to appear as the referring page for images that appear in image search results', 'rank-math' ) ),
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
										[ 'label' => 'Attorney' ],
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
	 * @param  bool $none Add none option to the list.
	 * @return array
	 */
	public static function choices_rich_snippet_types( $none = false ) {
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
		 * @param array $types Schema types.
		 */
		return apply_filters( 'rank_math/settings/snippet/types', $types );
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
	 * Country.
	 *
	 * @return array
	 */
	public static function choices_countries() {
		return [
			'all' => __( 'Worldwide', 'rank-math' ),
			'AR'  => __( 'Argentina', 'rank-math' ),
			'AU'  => __( 'Australia', 'rank-math' ),
			'AT'  => __( 'Austria', 'rank-math' ),
			'BE'  => __( 'Belgium', 'rank-math' ),
			'BR'  => __( 'Brazil', 'rank-math' ),
			'CA'  => __( 'Canada', 'rank-math' ),
			'CL'  => __( 'Chile', 'rank-math' ),
			'CO'  => __( 'Colombia', 'rank-math' ),
			'CZ'  => __( 'Czechia', 'rank-math' ),
			'DK'  => __( 'Denmark', 'rank-math' ),
			'EG'  => __( 'Egypt', 'rank-math' ),
			'FI'  => __( 'Finland', 'rank-math' ),
			'FR'  => __( 'France', 'rank-math' ),
			'DE'  => __( 'Germany', 'rank-math' ),
			'GR'  => __( 'Greece', 'rank-math' ),
			'HK'  => __( 'Hong Kong', 'rank-math' ),
			'HU'  => __( 'Hungary', 'rank-math' ),
			'IN'  => __( 'India', 'rank-math' ),
			'ID'  => __( 'Indonesia', 'rank-math' ),
			'IE'  => __( 'Ireland', 'rank-math' ),
			'IL'  => __( 'Israel', 'rank-math' ),
			'IT'  => __( 'Italy', 'rank-math' ),
			'JP'  => __( 'Japan', 'rank-math' ),
			'KE'  => __( 'Kenya', 'rank-math' ),
			'MY'  => __( 'Malaysia', 'rank-math' ),
			'MX'  => __( 'Mexico', 'rank-math' ),
			'NL'  => __( 'Netherlands', 'rank-math' ),
			'NZ'  => __( 'New Zealand', 'rank-math' ),
			'NG'  => __( 'Nigeria', 'rank-math' ),
			'NO'  => __( 'Norway', 'rank-math' ),
			'PH'  => __( 'Philippines', 'rank-math' ),
			'PL'  => __( 'Poland', 'rank-math' ),
			'PT'  => __( 'Portugal', 'rank-math' ),
			'RO'  => __( 'Romania', 'rank-math' ),
			'RU'  => __( 'Russia', 'rank-math' ),
			'SA'  => __( 'Saudi Arabia', 'rank-math' ),
			'SG'  => __( 'Singapore', 'rank-math' ),
			'ZA'  => __( 'South Africa', 'rank-math' ),
			'KR'  => __( 'South Korea', 'rank-math' ),
			'ES'  => __( 'Spain', 'rank-math' ),
			'SE'  => __( 'Sweden', 'rank-math' ),
			'CH'  => __( 'Switzerland', 'rank-math' ),
			'TW'  => __( 'Taiwan', 'rank-math' ),
			'TH'  => __( 'Thailand', 'rank-math' ),
			'TR'  => __( 'Turkey', 'rank-math' ),
			'UA'  => __( 'Ukraine', 'rank-math' ),
			'GB'  => __( 'United Kingdom', 'rank-math' ),
			'US'  => __( 'United States', 'rank-math' ),
			'VN'  => __( 'Vietnam', 'rank-math' ),
		];
	}

	/**
	 * Country.
	 *
	 * @return array
	 */
	public static function choices_countries_3() {
		return [
			'all' => __( 'Worldwide', 'rank-math' ),
			'ARG' => __( 'Argentina', 'rank-math' ),
			'AUS' => __( 'Australia', 'rank-math' ),
			'AUT' => __( 'Austria', 'rank-math' ),
			'BEL' => __( 'Belgium', 'rank-math' ),
			'BRA' => __( 'Brazil', 'rank-math' ),
			'CAN' => __( 'Canada', 'rank-math' ),
			'CHL' => __( 'Chile', 'rank-math' ),
			'COL' => __( 'Colombia', 'rank-math' ),
			'CZE' => __( 'Czechia', 'rank-math' ),
			'DNK' => __( 'Denmark', 'rank-math' ),
			'EGY' => __( 'Egypt', 'rank-math' ),
			'FIN' => __( 'Finland', 'rank-math' ),
			'FRA' => __( 'France', 'rank-math' ),
			'DEU' => __( 'Germany', 'rank-math' ),
			'GRC' => __( 'Greece', 'rank-math' ),
			'HKG' => __( 'Hong Kong', 'rank-math' ),
			'HUN' => __( 'Hungary', 'rank-math' ),
			'IND' => __( 'India', 'rank-math' ),
			'IDN' => __( 'Indonesia', 'rank-math' ),
			'IRL' => __( 'Ireland', 'rank-math' ),
			'ISR' => __( 'Israel', 'rank-math' ),
			'ITA' => __( 'Italy', 'rank-math' ),
			'JPN' => __( 'Japan', 'rank-math' ),
			'KEN' => __( 'Kenya', 'rank-math' ),
			'MYS' => __( 'Malaysia', 'rank-math' ),
			'MEX' => __( 'Mexico', 'rank-math' ),
			'NLD' => __( 'Netherlands', 'rank-math' ),
			'NZL' => __( 'New Zealand', 'rank-math' ),
			'NGA' => __( 'Nigeria', 'rank-math' ),
			'NOR' => __( 'Norway', 'rank-math' ),
			'PHL' => __( 'Philippines', 'rank-math' ),
			'POL' => __( 'Poland', 'rank-math' ),
			'PRT' => __( 'Portugal', 'rank-math' ),
			'ROU' => __( 'Romania', 'rank-math' ),
			'RUS' => __( 'Russia', 'rank-math' ),
			'SAU' => __( 'Saudi Arabia', 'rank-math' ),
			'SGP' => __( 'Singapore', 'rank-math' ),
			'ZAF' => __( 'South Africa', 'rank-math' ),
			'KOR' => __( 'South Korea', 'rank-math' ),
			'ESP' => __( 'Spain', 'rank-math' ),
			'SWE' => __( 'Sweden', 'rank-math' ),
			'CHE' => __( 'Switzerland', 'rank-math' ),
			'TWN' => __( 'Taiwan', 'rank-math' ),
			'THA' => __( 'Thailand', 'rank-math' ),
			'TUR' => __( 'Turkey', 'rank-math' ),
			'UKR' => __( 'Ukraine', 'rank-math' ),
			'GBR' => __( 'United Kingdom', 'rank-math' ),
			'USA' => __( 'United States', 'rank-math' ),
			'VNM' => __( 'Vietnam', 'rank-math' ),
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
}
