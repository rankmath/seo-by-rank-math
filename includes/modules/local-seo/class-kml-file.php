<?php
/**
 * The KML File.
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Local_Seo;

use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use RankMath\Sitemap\Router;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * KML_File class.
 */
class KML_File {

	use Ajax, Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init', 1 );
		$this->filter( 'rank_math/sitemap/http_headers', 'remove_x_robots_tag' );
		$this->filter( 'rank_math/sitemap/index', 'add_local_sitemap' );
		$this->filter( 'rank_math/sitemap/local/content', 'local_sitemap_content' );
		$this->filter( 'rank_math/sitemap/locations/content', 'kml_file_content' );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-titles_options', 'update_sitemap', 25, 2 );
	}

	/**
	 * Set up rewrite rules.
	 */
	public function init() {
		add_rewrite_rule( Router::get_sitemap_base() . 'locations\.kml$', 'index.php?sitemap=locations', 'top' );
	}

	/**
	 * Filter function to remove x-robots tag from Locations KML file.
	 *
	 * @param array $headers HTTP headers.
	 */
	public function remove_x_robots_tag( $headers ) {
		if ( ! isset( $headers['X-Robots-Tag'] ) ) {
			return $headers;
		}

		$url = array_filter( explode( '/', Param::server( 'REQUEST_URI' ) ) );
		if ( 'locations.kml' !== end( $url ) ) {
			return $headers;
		}

		unset( $headers['X-Robots-Tag'] );
		return $headers;
	}

	/**
	 * Add the Local SEO Sitemap to the sitemap index.
	 *
	 * @return string $xml The sitemap index with the Local SEO Sitemap added.
	 */
	public function add_local_sitemap() {
		$xml  = $this->newline( '<sitemap>', 1 );
		$xml .= $this->newline( '<loc>' . htmlspecialchars( Router::get_base_url( 'local-sitemap.xml' ) ) . '</loc>', 2 );
		$xml .= $this->newline( '<lastmod>' . htmlspecialchars( $this->get_modified_date() ) . '</lastmod>', 2 );
		$xml .= $this->newline( '</sitemap>', 1 );

		return $xml;
	}

	/**
	 * The content of the Local SEO Sitemap.
	 *
	 * @return string $urlset Local SEO Sitemap XML content.
	 */
	public function local_sitemap_content() {
		$urlset = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<url>
				<loc>' . htmlspecialchars( Router::get_base_url( 'locations.kml' ) ) . '</loc>
				<lastmod>' . htmlspecialchars( $this->get_modified_date() ) . '</lastmod>
			</url>
		</urlset>';

		return $urlset;
	}

	/**
	 * Generate the KML file contents.
	 *
	 * @return string $kml KML file content.
	 */
	public function kml_file_content() {
		$locations = $this->get_local_seo_data();
		if ( empty( $locations ) ) {
			return;
		}

		$business_name = Helper::get_settings( 'titles.knowledgegraph_name' );
		$business_url  = Helper::get_settings( 'titles.url' );

		$kml  = $this->newline( '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">' );
		$kml .= $this->newline( '<Document>', 1 );
		$kml .= $this->newline( '<name>Locations for ' . esc_html( $business_name ) . '</name>', 2 );
		$kml .= $this->newline( '<open>1</open>', 2 );
		$kml .= $this->newline( '<Folder>', 2 );

		if ( ! empty( $business_url ) ) {
			$kml .= $this->newline( '<atom:link href="' . $business_url . '" />', 3 );
		}

		foreach ( $locations as $location ) {
			$address   = ! empty( $location['address'] ) ? implode( ', ', array_filter( $location['address'] ) ) : '';
			$has_coord = ! empty( $location['coords']['latitude'] ) && ! empty( $location['coords']['longitude'] );

			$kml .= $this->newline( '<Placemark>', 3 );
			$kml .= $this->newline( '<name><![CDATA[' . html_entity_decode( $location['name'] ) . ']]></name>', 4 );
			$kml .= $this->newline( '<description><![CDATA[' . html_entity_decode( $location['description'] ) . ']]></description>', 4 );
			$kml .= $this->newline( '<address><![CDATA[' . $address . ']]></address>', 4 );
			$kml .= $this->newline( '<phoneNumber><![CDATA[' . $location['phone'] . ']]></phoneNumber>', 4 );
			$kml .= $this->newline( '<atom:link href="' . $location['url'] . '"/>', 4 );
			$kml .= $this->newline( '<LookAt>', 4 );

			if ( $has_coord ) {
				$kml .= $this->newline( '<latitude>' . $location['coords']['latitude'] . '</latitude>', 5 );
				$kml .= $this->newline( '<longitude>' . $location['coords']['longitude'] . '</longitude>', 5 );
			}

			$kml .= $this->newline( '<altitude>0</altitude>', 5 );
			$kml .= $this->newline( '<range></range>', 5 );
			$kml .= $this->newline( '<tilt>0</tilt>', 5 );
			$kml .= $this->newline( '</LookAt>', 4 );
			$kml .= $this->newline( '<Point>', 4 );
			if ( $has_coord ) {
				$kml .= $this->newline( '<coordinates>' . $location['coords']['longitude'] . ',' . $location['coords']['latitude'] . '</coordinates>', 5 );
			}
			$kml .= $this->newline( '</Point>', 4 );
			$kml .= $this->newline( '</Placemark>', 3 );
		}

		$kml .= $this->newline( '</Folder>', 2 );
		$kml .= $this->newline( '</Document>', 1 );
		$kml .= $this->newline( '</kml>' );

		return $kml;
	}

	/**
	 * Update the sitemap when the Local SEO settings are changed.
	 *
	 * @param int   $object_id The ID of the current object.
	 * @param array $updated   Array of field IDs that were updated.
	 *                         Will only include field IDs that had values change.
	 */
	public function update_sitemap( $object_id, $updated ) { // phpcs:ignore
		$local_seo_fields = [
			'knowledgegraph_name',
			'url',
			'email',
			'local_address',
			'local_business_type',
			'opening_hours',
			'phone_numbers',
			'price_range',
			'geo',
		];

		if ( count( array_intersect( $local_seo_fields, $updated ) ) ) {
			update_option( 'rank_math_local_seo_update', date_i18n( 'c' ) );
			\RankMath\Sitemap\Sitemap::ping_google( Router::get_base_url( 'local-sitemap.xml' ) );
		}
	}

	/**
	 * Get the Local SEO data.
	 *
	 * @return array
	 */
	private function get_local_seo_data() {
		$geo   = Str::to_arr( Helper::get_settings( 'titles.geo' ) );
		$cords = [
			'latitude'  => isset( $geo[0] ) ? $geo[0] : '',
			'longitude' => isset( $geo[1] ) ? $geo[1] : '',
		];

		$phone_numbers = Helper::get_settings( 'titles.phone_numbers' );
		$number        = ! empty( $phone_numbers ) && isset( $phone_numbers[0]['number'] ) ? $phone_numbers[0]['number'] : '';

		$locations = [
			[
				'name'        => Helper::get_settings( 'titles.knowledgegraph_name' ),
				'description' => get_option( 'blogname' ) . ' - ' . get_option( 'blogdescription' ),
				'email'       => Helper::get_settings( 'titles.email' ),
				'phone'       => $number,
				'url'         => Helper::get_settings( 'titles.url' ),
				'address'     => Helper::get_settings( 'titles.local_address' ),
				'coords'      => $cords,
				'author'      => get_option( 'blogname' ),
			],
		];

		return $this->do_filter( 'sitemap/locations/data', $locations );
	}

	/**
	 * Get the Modified Date.
	 *
	 * @return $date
	 */
	private function get_modified_date() {
		if ( ! $date = get_option( 'rank_math_local_seo_update' ) ) { // phpcs:ignore
			$date = date_i18n( 'c' );
		}

		return $date;
	}

	/**
	 * Write a newline with indent count.
	 *
	 * @param string  $content Content to write.
	 * @param integer $indent  Count of indent.
	 *
	 * @return string
	 */
	private function newline( $content, $indent = 0 ) {
		return str_repeat( "\t", $indent ) . $content . "\n";
	}
}
