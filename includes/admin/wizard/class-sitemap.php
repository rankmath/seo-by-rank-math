<?php
/**
 * The Sitemap wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Sitemap implements Wizard_Step {

	/**
	 * Render step body.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function render( $wizard ) {
		?>
		<header>
			<h1><?php esc_html_e( 'Sitemap', 'rank-math' ); ?> </h1>
			<p>
				<?php
				printf(
					/* translators: Link to How to Setup Sitemap KB article */
					esc_html__( 'Choose your Sitemap configuration and select which type of posts or pages you want to include in your Sitemaps. %s', 'rank-math' ),
					'<a href="' . esc_url( KB::get( 'configure-sitemaps', 'SW Sitemap Step' ) ) . '" target="_blank">' . esc_html__( 'Learn more.', 'rank-math' ) . '</a>'
				);
				?>
			</p>
		</header>

		<?php $wizard->cmb->show_form(); ?>

		<footer class="form-footer wp-core-ui rank-math-ui">
			<?php $wizard->get_skip_link(); ?>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save and Continue', 'rank-math' ); ?></button>
		</footer>
		<?php
	}

	/**
	 * Render form for step.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function form( $wizard ) {
		$wizard->cmb->add_field(
			[
				'id'      => 'sitemap',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Sitemaps', 'rank-math' ),
				'desc'    => esc_html__( 'XML Sitemaps help search engines index your website&#039;s content more effectively.', 'rank-math' ),
				'default' => Helper::is_module_active( 'sitemap' ) ? 'on' : 'off',
			]
		);

		$wizard->cmb->add_field(
			[
				'id'      => 'include_images',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Include Images', 'rank-math' ),
				'desc'    => esc_html__( 'Include reference to images from the post content in sitemaps. This helps search engines index your images better.', 'rank-math' ),
				'default' => Helper::get_settings( 'sitemap.include_images' ) ? 'on' : 'off',
				'classes' => 'features-child',
				'dep'     => [ [ 'sitemap', 'on' ] ],
			]
		);

		// Post Types.
		$post_types = $this->get_post_types();
		$wizard->cmb->add_field(
			[
				'id'      => 'sitemap_post_types',
				'type'    => 'multicheck',
				'name'    => esc_html__( 'Public Post Types', 'rank-math' ),
				'desc'    => esc_html__( 'Select post types to enable SEO options for them and include them in the sitemap.', 'rank-math' ),
				'options' => $post_types['post_types'],
				'default' => $post_types['defaults'],
				'classes' => 'features-child cmb-multicheck-inline' . ( count( $post_types['post_types'] ) === count( $post_types['defaults'] ) ? ' multicheck-checked' : '' ),
				'dep'     => [ [ 'sitemap', 'on' ] ],
			]
		);

		// Taxonomies.
		$taxonomies = $this->get_taxonomies();
		$wizard->cmb->add_field(
			[
				'id'      => 'sitemap_taxonomies',
				'type'    => 'multicheck',
				'name'    => esc_html__( 'Public Taxonomies', 'rank-math' ),
				'desc'    => esc_html__( 'Select taxonomies to enable SEO options for them and include them in the sitemap.', 'rank-math' ),
				'options' => $taxonomies['taxonomies'],
				'default' => $taxonomies['defaults'],
				'classes' => 'features-child cmb-multicheck-inline' . ( count( $taxonomies['taxonomies'] ) === count( $taxonomies['defaults'] ) ? ' multicheck-checked' : '' ),
				'dep'     => [ [ 'sitemap', 'on' ] ],
			]
		);
	}

	/**
	 * Save handler for step.
	 *
	 * @param array  $values Values to save.
	 * @param object $wizard Wizard class instance.
	 *
	 * @return bool
	 */
	public function save( $values, $wizard ) {
		$settings = rank_math()->settings->all_raw();
		Helper::update_modules( [ 'sitemap' => $values['sitemap'] ] );

		if ( 'on' === $values['sitemap'] ) {
			$settings['sitemap']['include_images'] = $values['include_images'];

			$settings = $this->save_post_types( $settings, $values );
			$settings = $this->save_taxonomies( $settings, $values );
			Helper::update_all_settings( null, null, $settings['sitemap'] );
		}

		Helper::schedule_flush_rewrite();
		return true;
	}

	/**
	 * Get post type data.
	 *
	 * @return array
	 */
	private function get_post_types() {
		$p_defaults = [];
		$post_types = Helper::choices_post_types();
		unset( $post_types['attachment'] );

		foreach ( $post_types as $post_type => $object ) {
			if ( true === Helper::get_settings( "sitemap.pt_{$post_type}_sitemap" ) ) {
				$p_defaults[] = $post_type;
			}
		}

		return [
			'defaults'   => $p_defaults,
			'post_types' => $post_types,
		];
	}

	/**
	 * Get taxonomies data.
	 *
	 * @return array
	 */
	private function get_taxonomies() {
		$t_defaults = [];
		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_tag'], $taxonomies['post_format'], $taxonomies['product_tag'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			if ( true === Helper::get_settings( "sitemap.tax_{$taxonomy}_sitemap" ) ) {
				$t_defaults[] = $taxonomy;
			}
		}

		return [
			'defaults'   => $t_defaults,
			'taxonomies' => $taxonomies,
		];
	}

	/**
	 * Save Post Types
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private function save_post_types( $settings, $values ) {
		$post_types = Helper::choices_post_types();
		if ( ! isset( $values['sitemap_post_types'] ) ) {
			$values['sitemap_post_types'] = [];
		}

		foreach ( $post_types as $post_type => $object ) {
			$settings['sitemap'][ "pt_{$post_type}_sitemap" ] = in_array( $post_type, $values['sitemap_post_types'], true ) ? 'on' : 'off';
		}

		return $settings;
	}

	/**
	 * Save Taxonomies
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private function save_taxonomies( $settings, $values ) {
		$taxonomies = Helper::get_accessible_taxonomies();
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		if ( ! isset( $values['sitemap_taxonomies'] ) ) {
			$values['sitemap_taxonomies'] = [];
		}

		foreach ( $taxonomies as $taxonomy => $label ) {
			$settings['sitemap'][ "tax_{$taxonomy}_sitemap" ] = in_array( $taxonomy, $values['sitemap_taxonomies'], true ) ? 'on' : 'off';
		}

		return $settings;
	}
}
