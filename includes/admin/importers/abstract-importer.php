<?php
/**
 * The abstract class for plugins import to inherit from
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use Exception;
use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Meta;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\DB;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin_Importer class.
 */
abstract class Plugin_Importer {

	use Hooker, Ajax, Meta;

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The plugin file.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key;

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys;

	/**
	 * Table names to drop while cleaning.
	 *
	 * @var array
	 */
	protected $table_names;

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices;

	/**
	 * Number of items to parse per page.
	 *
	 * @var int
	 */
	protected $items_per_page = 100;

	/**
	 * Pagination arguments.
	 *
	 * @var array
	 */
	protected $_pagination_args = [];

	/**
	 * Class constructor.
	 *
	 * @param string $plugin_file Plugins file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Get the name of the plugin we're importing from.
	 *
	 * @return string Plugin name.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the plugin file of the plugin we're importing from.
	 *
	 * @return string Plugin file
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * Get the actions which can be performed for the plugin.
	 *
	 * @return array
	 */
	public function get_choices() {
		if ( empty( $this->choices ) ) {
			return [];
		}

		return array_intersect_key(
			[
				'settings'     => esc_html__( 'Import Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import plugin settings, global meta, sitemap settings, etc.', 'rank-math' ) ),
				'postmeta'     => esc_html__( 'Import Post Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information of your posts/pages like the focus keyword, titles, descriptions, robots meta, OpenGraph info, etc.', 'rank-math' ) ),
				'termmeta'     => esc_html__( 'Import Term Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import data like category, tag, and CPT meta data from SEO.', 'rank-math' ) ),
				'usermeta'     => esc_html__( 'Import Author Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information like titles, descriptions, focus keyword, robots meta, etc., of your author archive pages.', 'rank-math' ) ),
				'redirections' => esc_html__( 'Import Redirections', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import all the redirections you have already set up in Yoast Premium.', 'rank-math' ) ),
				'blocks'       => esc_html__( 'Import Blocks', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import and convert all compatible blocks in post contents.', 'rank-math' ) ),
				'locations'    => esc_html__( 'Import Locations', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import Locations Settings from Yoast plugin.', 'rank-math' ) ),
				'news'         => esc_html__( 'Import News Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import News Settings from Yoast News Add-on.', 'rank-math' ) ),
				'video'        => esc_html__( 'Import Video Sitemap Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import Video Sitemap Settings from Yoast Video Add-on.', 'rank-math' ) ),
			],
			array_combine(
				$this->choices,
				$this->choices
			)
		);
	}

	/**
	 * Check if import is needed from this plugin.
	 *
	 * @return bool Whether there is something to import.
	 */
	public function run_detect() {
		return true === $this->has_options() ? true : $this->has_postmeta();
	}

	/**
	 * Delete all plugin data from the database.
	 *
	 * @return bool
	 */
	public function run_cleanup() {
		if ( ! $this->run_detect() ) {
			return false;
		}

		$result = $this->drop_custom_tables();
		$result = $this->clean_meta_table();
		$result = $this->clean_options();

		return $result;
	}

	/**
	 * Run importer.
	 *
	 * @throws Exception Throws error if no perform function founds.
	 *
	 * @param string $perform The action to perform when running import action.
	 */
	public function run_import( $perform ) {
		if ( ! method_exists( $this, $perform ) ) {
			throw new Exception( esc_html__( 'Unable to perform action this time.', 'rank-math' ) );
		}

		/**
		 * Number of items to import per run.
		 *
		 * @param int $items_per_page Default 100.
		 */
		$this->items_per_page = absint( $this->do_filter( 'importers/items_per_page', 100 ) );

		$status     = new Status();
		$result     = $this->$perform();
		$is_success = is_array( $result ) || true === $result;

		$status->set_action( $perform );
		$status->set_status( $is_success );
		$message = $this->format_message( $result, $perform, $status->get_message() );
		if ( is_scalar( $result ) ) {
			$result = [];
		}

		if ( $is_success ) {
			$result['message'] = $message;
			$this->success( $result );
		}

		$result['error'] = $message;
		$this->error( $result );
	}

	/**
	 * Get success message.
	 *
	 * @param array  $result  Array of result.
	 * @param string $action  Action performed.
	 * @param string $message Message to format.
	 *
	 * @return mixed
	 */
	private function format_message( $result, $action, $message ) {
		if ( 'blocks' === $action ) {
			return is_array( $result ) ? sprintf( $message, $result['start'], $result['end'], $result['total_items'] ) : $result;
		}

		if ( 'postmeta' === $action || 'usermeta' === $action ) {
			return sprintf( $message, $result['start'], $result['end'], $result['total_items'] );
		}

		if ( 'termmeta' === $action || 'redirections' === $action ) {
			return sprintf( $message, $result['count'] );
		}

		return $message;
	}

	/**
	 * Deactivate plugin action.
	 */
	protected function deactivate() {
		if ( is_plugin_active( $this->get_plugin_file() ) ) {
			deactivate_plugins( $this->get_plugin_file() );
		}

		return true;
	}

	/**
	 * Replace settings based on key/value hash.
	 *
	 * @param array $hash        Array of hash for search and replace.
	 * @param array $source      Array for source where to search.
	 * @param array $destination Array for destination where to save.
	 * @param bool  $convert     (Optional) Conversion type. Default: false.
	 */
	protected function replace( $hash, $source, &$destination, $convert = false ) {
		foreach ( $hash as $search => $replace ) {
			if ( ! isset( $source[ $search ] ) ) {
				continue;
			}

			$destination[ $replace ] = false === $convert ? $source[ $search ] : $this->$convert( $source[ $search ] );
		}
	}

	/**
	 * Replace meta based on key/value hash.
	 *
	 * @param array  $hash        Array of hash for search and replace.
	 * @param array  $source      Array for source where to search.
	 * @param int    $object_id   Object id for destination where to save.
	 * @param string $object_type Object type for destination where to save.
	 * @param bool   $convert     (Optional) Conversion type. Default: false.
	 */
	protected function replace_meta( $hash, $source, $object_id, $object_type, $convert = false ) {
		foreach ( $hash as $search => $replace ) {
			$value = ! empty( $source[ $search ] ) ? $source[ $search ] : $this->get_meta( $object_type, $object_id, $search );
			if ( empty( $value ) ) {
				continue;
			}

			$this->update_meta(
				$object_type,
				$object_id,
				$replace,
				false !== $convert ? $this->$convert( $value ) : $value
			);
		}
	}

	/**
	 * Replace an image to its URL and ID.
	 *
	 * @param string         $source      Source image url.
	 * @param array|callable $destination Destination array.
	 * @param string         $image       Image field key to save url.
	 * @param string         $image_id    Image id field key to save id.
	 * @param int            $object_id   Object ID either post ID, term ID or user ID.
	 */
	protected function replace_image( $source, $destination, $image, $image_id, $object_id = null ) {
		if ( empty( $source ) ) {
			return;
		}

		$attachment_id = Attachment::get_by_url( $source );
		if ( 1 > $attachment_id ) {
			return;
		}

		if ( is_null( $object_id ) ) {
			$destination[ $image ]    = $source;
			$destination[ $image_id ] = $attachment_id;
			return;
		}

		$this->update_meta( $destination, $object_id, $image, $source );
		$this->update_meta( $destination, $object_id, $image_id, $attachment_id );
	}

	/**
	 * Convert bool value to switch.
	 *
	 * @param mixed $value Value to convert.
	 * @return string
	 */
	protected function convert_bool( $value ) {
		if ( true === boolval( $value ) ) {
			return 'on';
		}

		if ( false === boolval( $value ) ) {
			return 'off';
		}

		return $value;
	}

	/**
	 * Set variable that twitter is using facebook data or not.
	 *
	 * @param string $object_type Object type for destination where to save.
	 * @param int    $object_id   Object id for destination where to save.
	 */
	protected function is_twitter_using_facebook( $object_type, $object_id ) {
		$keys = [
			'rank_math_twitter_title',
			'rank_math_twitter_description',
			'rank_math_twitter_image',
		];

		foreach ( $keys as $key ) {
			if ( ! empty( $this->get_meta( $object_type, $object_id, $key, true ) ) ) {
				$this->update_meta( $object_type, $object_id, 'rank_math_twitter_use_facebook', 'off' );
				break;
			}
		}
	}

	/**
	 * Convert Yoast / AIO SEO variables if needed.
	 *
	 * @param string $string Value to convert.
	 * @return string
	 */
	protected function convert_variables( $string ) {
		return str_replace( '%%', '%', $string );
	}

	/**
	 * Set pagination arguments.
	 *
	 * @param int $total_items Number of total items to set pagination.
	 */
	protected function set_pagination( $total_items = 0 ) {
		$args = [
			'total_pages' => 0,
			'total_items' => $total_items,
			'per_page'    => $this->items_per_page,
		];

		// Total Pages.
		if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
		}

		// Current Page.
		$pagenum = Param::request( 'paged', 0, FILTER_VALIDATE_INT );
		if ( isset( $args['total_pages'] ) && $pagenum > $args['total_pages'] ) {
			$pagenum = $args['total_pages'];
		}
		$args['page'] = max( 1, $pagenum );

		// Start n End.
		$args['start'] = ( ( $args['page'] - 1 ) * $this->items_per_page ) + 1;
		$args['end']   = min( $args['page'] * $this->items_per_page, $total_items );

		$this->_pagination_args = $args;
	}

	/**
	 * Get pagination arguments.
	 *
	 * @param bool $key If any specific data is required from arguments.
	 * @return mixed
	 */
	protected function get_pagination_arg( $key = false ) {
		if ( false === $key ) {
			return $this->_pagination_args;
		}

		return isset( $this->_pagination_args[ $key ] ) ? $this->_pagination_args[ $key ] : false;
	}

	/**
	 * Get all post IDs of all allowed post types only.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	protected function get_post_ids( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'posts' )->whereIn( 'post_type', Helper::get_accessible_post_types() );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select( 'ID' )->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Get all user IDs.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	protected function get_user_ids( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'users' );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select( 'ID' )->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Get system settings.
	 */
	protected function get_settings() {
		$all_opts       = rank_math()->settings->all_raw();
		$this->settings = $all_opts['general'];
		$this->titles   = $all_opts['titles'];
		$this->sitemap  = $all_opts['sitemap'];
	}

	/**
	 * Update system settings.
	 */
	protected function update_settings() {
		Helper::update_all_settings(
			$this->settings,
			$this->titles,
			$this->sitemap
		);
	}

	/**
	 * Clean meta table for post, term and user.
	 *
	 * @return bool
	 */
	private function clean_meta_table() {
		if ( empty( $this->meta_key ) ) {
			return false;
		}

		$result = false;
		$result = DB::query_builder( 'usermeta' )->whereLike( 'meta_key', $this->meta_key )->delete();
		$result = DB::query_builder( 'termmeta' )->whereLike( 'meta_key', $this->meta_key )->delete();
		$result = DB::query_builder( 'postmeta' )->whereLike( 'meta_key', $this->meta_key )->delete();

		return $result;
	}

	/**
	 * Clean options table.
	 *
	 * @return bool
	 */
	private function clean_options() {
		if ( empty( $this->option_keys ) ) {
			return false;
		}

		$table = DB::query_builder( 'options' );
		foreach ( $this->option_keys as $option_key ) {
			$table->orWhereLike( 'option_name', $option_key );
		}

		return $table->delete();
	}

	/**
	 * Drop custom tables for plugins.
	 *
	 * @return bool
	 */
	private function drop_custom_tables() {
		global $wpdb;
		if ( empty( $this->table_names ) ) {
			return false;
		}

		foreach ( $this->table_names as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
		}

		return true;
	}

	/**
	 * Check if plugin has options.
	 *
	 * @return bool
	 */
	private function has_options() {
		if ( empty( $this->option_keys ) ) {
			return false;
		}

		$table = DB::query_builder( 'options' )->select( 'option_id' );
		foreach ( $this->option_keys as $option_key ) {
			if ( '%' === substr( $option_key, -1 ) ) {
				$table->orWhereLike( 'option_name', substr( $option_key, 0, -1 ), '' );
			} else {
				$table->orWhere( 'option_name', $option_key );
			}
		}

		return absint( $table->getVar() ) > 0 ? true : false;
	}

	/**
	 * Check if plugin has postmeta.
	 *
	 * @return bool
	 */
	private function has_postmeta() {
		if ( empty( $this->meta_key ) ) {
			return false;
		}

		$result = DB::query_builder( 'postmeta' )->select( 'meta_id' )->whereLike( 'meta_key', $this->meta_key, '' )->getVar();
		return absint( $result ) > 0 ? true : false;
	}
}
