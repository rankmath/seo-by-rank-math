<?php
/**
 * The admin-side code of the Schema module.
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
		$this->action( 'rank_math/admin/editor_scripts', 'enqueue' );
		$this->action( 'rank_math/post/column/seo_details', 'display_schema_type', 10, 2 );
	}

	/**
	 * Display schema type in the `seo_details` column on the posts.
	 *
	 * @param int   $post_id The current post ID.
	 * @param array $data    SEO data of current post.
	 */
	public function display_schema_type( $post_id, $data ) {
		$schema = absint( get_option( 'page_for_posts' ) ) !== $post_id ? $this->get_schema_types( $data, $post_id ) : 'CollectionPage';
		$schema = ! empty( $schema ) ? $schema : Helper::get_default_schema_type( $post_id, true, true );
		$schema = $schema ? $schema : esc_html__( 'Off', 'rank-math' );
		?>
			<span class="rank-math-column-display schema-type">
				<strong><?php esc_html_e( 'Schema', 'rank-math' ); ?>:</strong>
				<?php echo esc_html( Helper::sanitize_schema_title( $schema ) ); ?>
			</span>
		<?php
	}

	/**
	 * Enqueue Styles and Scripts required for the metabox on the post screen.
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

		Helper::add_json( 'schemas', $this->get_schema_data( $cmb->object_id() ) );
		Helper::add_json( 'customSchemaImage', esc_url( rank_math()->plugin_url() . 'includes/modules/schema/assets/img/custom-schema-builder.jpg' ) );

		wp_enqueue_style( 'rank-math-schema', rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema.css', [ 'wp-components', 'rank-math-editor' ], rank_math()->version );
		$this->enqueue_translation();

		$screen = get_current_screen();
		if ( 'rank_math_schema' !== $screen->post_type ) {
			wp_enqueue_script( 'rank-math-schema', rank_math()->plugin_url() . 'includes/modules/schema/assets/js/schema-gutenberg.js', [ 'rank-math-editor' ], rank_math()->version, true );
		}
	}

	/**
	 * KB Links for gutenberg
	 */
	public function add_kb_links() {
		Helper::add_json(
			'assessor',
			[
				'reviewConverterLink' => Helper::get_admin_url( 'status', 'view=tools' ),
			]
		);
	}

	/**
	 * Get Schema Data.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array $schemas Schema Data.
	 */
	private function get_schema_data( $post_id ) {
		$schemas = DB::get_schemas( $post_id );
		if ( ! empty( $schemas ) ) {
			return $schemas;
		}

		$default_type = $this->get_default_schema_type( $post_id );
		if ( ! $default_type ) {
			return [];
		}

		$schemas['new-9999'] = [
			'@type'    => $default_type,
			'metadata' => [
				'title'     => Helper::sanitize_schema_title( $default_type ),
				'type'      => 'template',
				'shortcode' => uniqid( 's-' ),
				'isPrimary' => true,
			],
		];

		if ( ! in_array( $default_type, [ 'Article', 'NewsArticle', 'BlogPosting' ], true ) ) {
			return $schemas;
		}

		$post_type   = get_post_type( $post_id );
		$name        = Helper::get_settings( "titles.pt_{$post_type}_default_snippet_name" );
		$description = Helper::get_settings( "titles.pt_{$post_type}_default_snippet_desc" );

		$schemas['new-9999']['headline']    = $name ? $name : '';
		$schemas['new-9999']['description'] = $description ? $description : '';
		$schemas['new-9999']['author']      = [
			'@type' => 'Person',
			'name'  => '%name%',
		];

		return $schemas;
	}

	/**
	 * Get default schema type.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string|bool Schema type.
	 */
	private function get_default_schema_type( $post_id ) {
		$default_type = ucfirst( Helper::get_default_schema_type( $post_id ) );
		if ( ! $default_type ) {
			return false;
		}

		if ( 'Video' === $default_type ) {
			return 'VideoObject';
		}

		if ( 'Software' === $default_type ) {
			return 'SoftwareApplication';
		}

		if ( 'Jobposting' === $default_type ) {
			return 'JobPosting';
		}

		return $default_type;
	}

	/**
	 * Enqueue translation.
	 */
	private function enqueue_translation() {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'rank-math-schema', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
		}
	}

	/**
	 * Get schema types for current post.
	 *
	 * @param array $data    Current post SEO data.
	 * @param int   $post_id Current post ID.
	 *
	 * @return string Comma separated schema types.
	 */
	private function get_schema_types( $data, $post_id ) {
		if ( empty( $data ) ) {
			return false;
		}

		$types = [];
		foreach ( $data as $key => $value ) {
			if ( ! Str::starts_with( 'rank_math_schema_', $key ) ) {
				continue;
			}

			$schema = maybe_unserialize( $value );
			if ( empty( $schema['@type'] ) ) {
				continue;
			}

			if ( ! is_array( $schema['@type'] ) ) {
				$types[] = Helper::sanitize_schema_title( $schema['@type'] );
				continue;
			}

			$types = array_merge(
				$types,
				array_map(
					function( $type ) {
						return Helper::sanitize_schema_title( $type );
					},
					$schema['@type']
				)
			);
		}

		if ( empty( $types ) && Helper::get_default_schema_type( $post_id ) ) {
			$types[] = ucfirst( Helper::get_default_schema_type( $post_id ) );
		}

		if ( has_block( 'rank-math/faq-block', $post_id ) ) {
			$types[] = 'FAQPage';
		}

		if ( has_block( 'rank-math/howto-block', $post_id ) ) {
			$types[] = 'HowTo';
		}

		return empty( $types ) ? false : implode( ', ', $types );
	}

	/**
	 * Get a CMB2 instance by the metabox ID.
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
