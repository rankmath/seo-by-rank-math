<?php
/**
 * The Schema Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Module\Base;
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

		$this->action( 'cmb2_admin_init', 'enqueue', 50 );
		$this->action( 'cmb2_admin_init', 'add_kb_links', 50 );
		$this->filter( 'rank_math/metabox/tabs', 'add_metabox_tab' );
		$this->action( 'rank_math/metabox/process_fields', 'save_advanced_meta' );
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
				'richsnippet' => [
					'icon'       => 'dashicons',
					'title'      => esc_html__( 'Schema', 'rank-math' ),
					'desc'       => esc_html__( 'This tab contains snippet options.', 'rank-math' ),
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
	public function save_advanced_meta( $cmb ) {
		$instructions = $this->can_save_data( $cmb );
		if ( empty( $instructions ) ) {
			return;
		}

		foreach ( $instructions as $key => $instruction ) {
			if ( ! $instruction['name'] || ! $instruction['text'] || empty( trim( $instruction['name'] ) ) ) {
				unset( $instructions[ $key ] );
			}
		}
		$cmb->data_to_save['rank_math_snippet_recipe_instructions'] = $instructions;
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

		$schema = $schema ? $schema : __( 'Off', 'rank-math' );
		?>
			<span class="rank-math-column-display schema-type">
				<strong><?php _e( 'Schema', 'rank-math' ); ?>:</strong>
				<?php echo ucfirst( $schema ); ?>
			</span>
		<?php
	}

	/**
	 * Can save metadata.
	 *
	 * @param CMB2 $cmb CMB2 instance.
	 *
	 * @return boolean|array
	 */
	private function can_save_data( $cmb ) {
		if ( isset( $cmb->data_to_save['rank_math_snippet_recipe_instruction_type'] ) && 'HowToSection' !== $cmb->data_to_save['rank_math_snippet_recipe_instruction_type'] ) {
			return false;
		}

		return isset( $cmb->data_to_save['rank_math_snippet_recipe_instructions'] ) ? $cmb->data_to_save['rank_math_snippet_recipe_instructions'] : [];
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

		foreach ( $cmb->prop( 'fields' ) as $id => $field_args ) {
			$type = $field_args['type'];
			if ( $this->can_exclude( $id, $type ) ) {
				continue;
			}

			$values[ $this->camelize( $id ) ] = 'group' === $type ? $cmb->get_field( $id )->value :
				$cmb->get_field( $id )->escaped_value();
		}

		$values['snippetType'] = $cmb->get_field( 'rank_math_rich_snippet' )->escaped_value();

		// Default values.
		$post_type                    = \RankMath\CMB2::current_object_type();
		$values['defaultName']        = Helper::get_settings( "titles.pt_{$post_type}_default_snippet_name", '' );
		$values['defaultDescription'] = Helper::get_settings( "titles.pt_{$post_type}_default_snippet_desc", '' );
		$values['knowledgegraphType'] = Helper::get_settings( 'titles.knowledgegraph_type' );

		Helper::add_json( 'richSnippets', $values );
		Helper::add_json( 'hasReviewPosts', ! empty( Helper::get_review_posts() ) );
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
