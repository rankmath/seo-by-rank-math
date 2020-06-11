<?php
/**
 * The KML File
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Local_Seo;

use RankMath\Post;
use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use RankMath\Sitemap\Router;


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
		$this->filter( 'rank_math/sitemap/index', 'add_local_sitemap' );
		$this->filter( 'rank_math/sitemap/local/content', 'local_sitemap_content' );
		$this->filter( 'rank_math/sitemap/locations/content', 'kml_file_content' );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-titles_options', 'update_sitemap', 25, 2 );
	}

	/**
	 * Set up rewrite rules.
	 */
	public function init() {
		add_rewrite_rule( 'locations\.kml$', 'index.php?sitemap=locations', 'top' );
	}

	/**
	 * Add the Local SEO Sitemap to the sitemap index.
	 *
	 * @return string $xml The sitemap index with the Local SEO Sitemap added.
	 */
	public function add_local_sitemap() {
		$xml  = $this->newline( '<sitemap>' );
		$xml .= $this->newline( '<loc>' . Router::get_base_url( 'local-sitemap.xml' ) . '</loc>' );
		$xml .= $this->newline( '<lastmod>' . $this->get_modified_date() . '</lastmod>' );
		$xml .= $this->newline( '</sitemap>' );

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
				<loc>' . Router::get_base_url( 'locations.kml' ) . '</loc>
				<lastmod>' . $this->get_modified_date() . '</lastmod>
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
		$business      = $this->get_local_seo_data();
		$business_name = esc_html( $business['business_name'] );
		$business_url  = esc_url( $business['business_url'] );
		$address       = ! empty( $business['address'] ) ? implode( ', ', array_filter( $business['address'] ) ) : '';

		$kml  = $this->newline( '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">' );
		$kml .= $this->newline( '<Document>', 1 );
		$kml .= $this->newline( '<name>Locations for ' . $business_name . '</name>', 2 );
		$kml .= $this->newline( '<open>1</open>', 2 );

		if ( ! empty( $business['author'] ) ) {
			$kml .= $this->newline( '<atom:author>', 2 );
			$kml .= $this->newline( '<atom:name>' . $business['author'] . '</atom:name>', 3 );
			$kml .= $this->newline( '</atom:author>', 2 );
		}

		if ( ! empty( $business_url ) ) {
			$kml .= $this->newline( '<atom:link href="' . $business_url . '" />', 2 );
		}

		$kml .= $this->newline( '<Placemark>', 2 );
		$kml .= $this->newline( '<name><![CDATA[' . html_entity_decode( $business_name ) . ']]></name>', 3 );
		$kml .= $this->newline( '<description><![CDATA[' . html_entity_decode( $business['business_description'] ) . ']]></description>', 3 );
		$kml .= $this->newline( '<address><![CDATA[' . $address . ']]></address>', 3 );
		$kml .= $this->newline( '<phoneNumber><![CDATA[' . $business['business_phone'] . ']]></phoneNumber>', 3 );
		$kml .= $this->newline( '<atom:link href="' . $business_url . '"/>', 3 );
		$kml .= $this->newline( '<LookAt>', 3 );
		$kml .= $this->newline( '<latitude>' . $business['coords']['lat'] . '</latitude>', 4 );
		$kml .= $this->newline( '<longitude>' . $business['coords']['long'] . '</longitude>', 4 );
		$kml .= $this->newline( '<altitude>0</altitude>', 4 );
		$kml .= $this->newline( '<range></range>', 4 );
		$kml .= $this->newline( '<tilt>0</tilt>', 4 );
		$kml .= $this->newline( '</LookAt>', 3 );
		$kml .= $this->newline( '<Point>', 3 );
		$kml .= $this->newline( '<coordinates>' . $business['coords']['long'] . ',' . $business['coords']['lat'] . '</coordinates>', 4 );
		$kml .= $this->newline( '</Point>', 3 );
		$kml .= $this->newline( '</Placemark>', 2 );
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
	public function update_sitemap( $object_id, $updated ) {
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
			\RankMath\Sitemap\Sitemap::ping_search_engines( Router::get_base_url( 'local-sitemap.xml' ) );
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
			'lat'  => isset( $geo[0] ) ? $geo[0] : '',
			'long' => isset( $geo[1] ) ? $geo[1] : '',
		];

		$phone_numbers = Helper::get_settings( 'titles.phone_numbers' );
		$number        = ! empty( $phone_numbers ) && isset( $phone_numbers[0]['number'] ) ? $phone_numbers[0]['number'] : '';

		$locations = [
			'business_name'        => Helper::get_settings( 'titles.knowledgegraph_name' ),
			'business_description' => get_option( 'blogname' ) . ' - ' . get_option( 'blogdescription' ),
			'business_email'       => Helper::get_settings( 'titles.email' ),
			'business_phone'       => $number,
			'business_url'         => Helper::get_settings( 'titles.url' ),
			'address'              => Helper::get_settings( 'titles.local_address' ),
			'coords'               => $cords,
			'author'               => get_option( 'blogname' ),
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
