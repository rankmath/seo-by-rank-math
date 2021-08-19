<?php
/**
 * The metabox functionality of the plugin.
 *
 * @since      1.0.25
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy metabox class.
 */
class Taxonomy_Screen implements IScreen {

	use Hooker;

	/**
	 * Class construct
	 */
	public function __construct() {
	}

	/**
	 * Get object ID.
	 *
	 * @return int
	 */
	public function get_object_id() {
		return Param::request( 'tag_ID', 0, FILTER_VALIDATE_INT );
	}

	/**
	 * Get object type
	 *
	 * @return string
	 */
	public function get_object_type() {
		return 'term';
	}

	/**
	 * Get object types to register metabox to
	 *
	 * @return array
	 */
	public function get_object_types() {
		$object_types = [];
		$taxonomies   = Helper::get_allowed_taxonomies();

		if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
			$object_types[] = 'term';
			$this->description_field_editor();
			remove_filter( 'pre_term_description', 'wp_filter_kses' );
			remove_filter( 'term_description', 'wp_kses_data' );
			add_filter( 'pre_term_description', 'wp_kses_post' );
			add_filter( 'term_description', 'wp_kses_post' );
		}

		return $object_types;
	}

	/**
	 * Enqueue Styles and Scripts required for screen.
	 */
	public function enqueue() {}

	/**
	 * Get analysis to run.
	 *
	 * @return array
	 */
	public function get_analysis() {
		return [
			'keywordInTitle'           => true,
			'keywordInMetaDescription' => true,
			'keywordInPermalink'       => true,
			'keywordNotUsed'           => true,
			'titleStartWithKeyword'    => true,
		];
	}

	/**
	 * Get values for localize.
	 *
	 * @return array
	 */
	public function get_values() {
		$url = '';
		if ( $this->get_object_id() ) {
			$url  = get_term_link( $this->get_object_id() );
			$data = array_filter( explode( '/', $url ) );
			$url  = ! empty( $data ) ? str_replace( array_pop( $data ), '%term%', $url ) : '';
		}

		return [
			'permalinkFormat' => $url ? $url : home_url(),
		];
	}

	/**
	 * Get object values for localize
	 *
	 * @return array
	 */
	public function get_object_values() {
		return [
			'titleTemplate'       => '%term% %sep% %sitename%',
			'descriptionTemplate' => '%term_description%',
		];
	}

	/**
	 * Adds custom category description editor.
	 *
	 * @return {void}
	 */
	private function description_field_editor() {
		$taxonomy        = filter_input( INPUT_GET, 'taxonomy', FILTER_DEFAULT, [ 'options' => [ 'default' => '' ] ] );
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( empty( $taxonomy_object ) || empty( $taxonomy_object->public ) ) {
			return;
		}

		if ( ! Helper::get_settings( 'titles.tax_' . $taxonomy . '_add_meta_box' ) ) {
			return;
		}
		add_action( "{$taxonomy}_edit_form_fields", [ $this, 'category_description_editor' ], 1 );
	}

	/**
	 * Output the WordPress editor.
	 *
	 * @param object $term Current taxonomy term object.
	 */
	public function category_description_editor( $term ) {
		?>
		<tr class="form-field term-description-wrap rank-math-term-description-wrap">
			<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'rank-math' ); ?></label></th>
			<td>
				<?php
				wp_editor(
					html_entity_decode( $term->description, ENT_QUOTES, 'UTF-8' ),
					'rank_math_description_editor',
					[
						'textarea_name' => 'description',
						'textarea_rows' => 5,
					]
				);
				?>
			</td>
			<script>
				// Remove the non-html field
				jQuery('textarea#description').closest('.form-field').remove();
			</script>
		</tr>
		<?php
	}
}
