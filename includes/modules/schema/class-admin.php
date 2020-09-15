<?php
/**
 * The Schema Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Rest\Sanitize;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'rich-snippet',
				'directory' => $directory,
			]
		);
		parent::__construct();

		$this->action( 'cmb2_admin_init', 'add_kb_links', 50 );
		$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue' );
		$this->filter( 'rank_math/metabox/tabs', 'add_metabox_tab' );
		$this->action( 'rank_math/metabox/process_fields', 'save_schemas' );
		$this->action( 'rank_math/metabox/process_fields', 'delete_schemas' );
		$this->action( 'rank_math/post/column/seo_details', 'display_schema_type' );
	}

	/**
	 * Add rich snippet tab to the metabox.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array
	 */
	public function add_metabox_tab( $tabs ) {

		if ( Admin_Helper::is_term_profile_page() || Admin_Helper::is_posts_page() ) {
			return $tabs;
		}

		Arr::insert(
			$tabs,
			[
				'schema' => [
					'icon'       => 'dashicons-schema',
					'title'      => '',
					'desc'       => '',
					'file'       => $this->directory . '/views/metabox-options.php',
					'capability' => 'onpage_snippet',
				],
			],
			Helper::is_advanced_mode() ? 3 : 2
		);

		return $tabs;
	}

	/**
	 * Save handler for metadata.
	 *
	 * @param CMB2 $cmb CMB2 instance.
	 */
	public function save_schemas( $cmb ) {
		if ( empty( $cmb->data_to_save['rank-math-schemas'] ) ) {
			return;
		}

		$sanitizer = Sanitize::get();
		$schemas   = \json_decode( stripslashes( $cmb->data_to_save['rank-math-schemas'] ), true );

		foreach ( $schemas as $meta_id => $schema ) {
			$meta_key = 'rank_math_schema_' . $schema['@type'];

			// Add new.
			if ( Str::starts_with( 'new-', $meta_id ) ) {
				$new_ids[ $meta_id ] = add_post_meta( $cmb->object_id, $meta_key, $sanitizer->sanitize( $meta_key, $schema ) );
				continue;
			}

			// Update old.
			$db_id      = absint( str_replace( 'schema-', '', $meta_id ) );
			$prev_value = update_metadata_by_mid( 'post', $db_id, $schema, $meta_key );
		}
	}

	/**
	 * Delete handler for metadata.
	 *
	 * @param CMB2 $cmb CMB2 instance.
	 */
	public function delete_schemas( $cmb ) {
		if ( empty( $cmb->data_to_save['rank-math-schemas-delete'] ) ) {
			return;
		}

		$schemas = \json_decode( stripslashes( $cmb->data_to_save['rank-math-schemas-delete'] ), true );

		foreach ( $schemas as $meta_id ) {
			\delete_metadata_by_mid( 'post', absint( \str_replace( 'schema-', '', $meta_id ) ) );
		}
	}

	/**
	 * Display schema type for post
	 *
	 * @param int $post_id The current post ID.
	 */
	public function display_schema_type( $post_id ) {
		$schema = get_post_meta( $post_id, 'rank_math_rich_snippet', true );
		if ( ! $schema && Helper::can_use_default_schema( $post_id ) ) {
			$post_type = get_post_type( $post_id );
			$schema    = Helper::get_settings( "titles.pt_{$post_type}_default_rich_snippet" );
		}

		$schema = $schema ? $schema : esc_html__( 'Off', 'rank-math' );
		?>
			<span class="rank-math-column-display schema-type">
				<strong><?php esc_html_e( 'Schema', 'rank-math' ); ?>:</strong>
				<?php echo esc_html( ucfirst( $schema ) ); ?>
			</span>
		<?php
	}

	/**
	 * Enqueue Styles and Scripts required for metabox.
	 */
	public function enqueue() {
		if ( ! Helper::has_cap( 'onpage_snippet' ) || Admin_Helper::is_posts_page() ) {
			return;
		}

		$values = [];
		$cmb    = $this->get_metabox();
		if ( false === $cmb ) {
			return;
		}

		$screen       = get_current_screen();
		$schemas      = DB::get_schemas( $cmb->object_id() );
		$post_type    = get_post_type();
		$default_type = ucfirst( Helper::get_settings( "titles.pt_{$screen->post_type}_default_rich_snippet" ) );

		if ( ( class_exists( 'WooCommerce' ) && 'product' === $screen->post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $screen->post_type ) ) {
			$default_type = 'WooCommerceProduct';
		}

		if ( 'rank_math_schema' === $screen->post_type && 'add' === $screen->action ) {
			$schemas['new-9999'] = [
				'@type'    => 'Article',
				'metadata' => [
					'title' => 'Article',
					'type'  => 'custom',
				],
			];
		}

		Helper::add_json( 'schemas', $schemas );
		Helper::add_json( 'customSchemaImage', esc_url( rank_math()->plugin_url() . 'includes/modules/schema/assets/img/custom-schema-builder.jpg' ) );

		wp_enqueue_style( 'rank-math-schema', rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema.css', [ 'wp-components', 'rank-math-post-metabox' ], rank_math()->version );
	}

	/**
	 * KB Links for gutenberg
	 */
	public function add_kb_links() {
		Helper::add_json(
			'assessor',
			[
				'articleKBLink'       => KB::get( 'article' ),
				'reviewConverterLink' => Helper::get_admin_url( 'status', 'view=tools' ),
				'richSnippetsKBLink'  => KB::get( 'rich-snippets' ),
			]
		);
	}

	/**
	 * Get metabox
	 *
	 * @return bool|CMB2
	 */
	private function get_metabox() {
		if ( Admin_Helper::is_term_profile_page() ) {
			return false;
		}

		return cmb2_get_metabox( 'rank_math_metabox' );
	}

	/**
	 * Can exclude field.
	 *
	 * @param string $id   Field id.
	 * @param string $type Field type.
	 *
	 * @return bool
	 */
	private function can_exclude( $id, $type ) {
		$exclude = [ 'meta_tab_container_open', 'tab_container_open', 'tab_container_close', 'tab', 'raw', 'notice' ];
		return in_array( $type, $exclude, true ) || ! Str::starts_with( 'rank_math_snippet_', $id );
	}

	/**
	 * Convert string to camel case.
	 *
	 * @param string $str String to convert.
	 *
	 * @return string
	 */
	private function camelize( $str ) {
		$sep = '_';
		$str = str_replace( 'rank_math_snippet_', '', $str );
		$str = str_replace( $sep, '', ucwords( $str, $sep ) );

		return lcfirst( $str );
	}
}
