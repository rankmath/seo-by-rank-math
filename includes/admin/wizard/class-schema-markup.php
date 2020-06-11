<?php
/**
 * The Schema_Markup wizard step
 *
 * @since      1.0.32
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
class Schema_Markup implements Wizard_Step {

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
			<h1><?php esc_html_e( 'Schema Markup ', 'rank-math' ); ?> </h1>
			<p><?php esc_html_e( 'Schema adds metadata to your website, resulting in rich search results and more traffic.', 'rank-math' ); ?></p>
		</header>

		<?php $wizard->cmb->show_form(); ?>

		<footer class="form-footer wp-core-ui rank-math-ui">
			<a href="<?php echo esc_url( Helper::get_admin_url() ); ?>" class="button button-secondary button-skip"><?php esc_html_e( 'Skip Step', 'rank-math' ); ?></a>
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
				'id'      => 'rich_snippet',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Schema Type', 'rank-math' ),
				'desc'    => esc_html__( 'Use automatic structured data to mark up content, to help Google better understand your content\'s context for display in Search. You can set different defaults for your posts here.', 'rank-math' ),
				'default' => Helper::is_module_active( 'rich-snippet' ) ? 'on' : 'off',
			]
		);

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$wizard->cmb->add_field( $this->get_field_args( $post_type ) );

			// Article fields.
			$article_dep   = [ 'relation' => 'and' ] + [ [ 'rich_snippet', 'on' ] ];
			$article_dep[] = [ 'pt_' . $post_type . '_default_rich_snippet', 'article' ];
			/* translators: Google article snippet doc link */
			$article_desc = 'person' === Helper::get_settings( 'titles.knowledgegraph_type' ) ? '<div class="notice notice-warning inline rank-math-notice" style="margin-left:0;"><p>' . sprintf( __( 'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.', 'rank-math' ), KB::get( 'article' ) ) . '</p></div>' : '';
			$wizard->cmb->add_field(
				[
					'id'      => 'pt_' . $post_type . '_default_article_type',
					'type'    => 'radio_inline',
					'name'    => esc_html__( 'Article Type', 'rank-math' ),
					'options' => [
						'Article'     => esc_html__( 'Article', 'rank-math' ),
						'BlogPosting' => esc_html__( 'Blog Post', 'rank-math' ),
						'NewsArticle' => esc_html__( 'News Article', 'rank-math' ),
					],
					'default' => Helper::get_settings( 'titles.pt_' . $post_type . '_default_article_type', 'post' === $post_type ? 'BlogPosting' : 'Article' ),
					'dep'     => $article_dep,
					'desc'    => $article_desc,
				]
			);
		}
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
		Helper::update_modules( [ 'rich-snippet' => $values['rich_snippet'] ] );

		// Schema.
		if ( 'on' === $values['rich_snippet'] ) {
			$this->save_rich_snippet( $settings, $values );
		}
		Helper::update_all_settings( $settings['general'], $settings['titles'], null );

		return Helper::get_admin_url();
	}

	/**
	 * Save rich snippet values for post type.
	 *
	 * @param array $settings Array of setting.
	 * @param array $values   Values to save.
	 */
	private function save_rich_snippet( &$settings, $values ) {
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$id           = 'pt_' . $post_type . '_default_rich_snippet';
			$article_type = 'pt_' . $post_type . '_default_article_type';

			$settings['titles'][ $id ]           = $values[ $id ];
			$settings['titles'][ $article_type ] = $values[ $article_type ];
		}
	}

	/**
	 * Get field arguments.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	private function get_field_args( $post_type ) {
		$object   = get_post_type_object( $post_type );
		$field_id = 'pt_' . $post_type . '_default_rich_snippet';

		/* translators: Post type name */
		$field_name = sprintf( esc_html__( 'Schema Type for %s', 'rank-math' ), $object->label );

		$richsnp_default = [
			'post'    => 'article',
			'product' => 'product',
		];

		if ( 'product' === $post_type ) {
			return [
				'id'      => $field_id,
				'type'    => 'radio_inline',
				'name'    => $field_name,
				'desc'    => __( 'Default rich snippet selected when creating a new product.', 'rank-math' ),
				'options' => [
					'off'     => esc_html__( 'None', 'rank-math' ),
					'product' => esc_html__( 'Product', 'rank-math' ),
				],
				'default' => Helper::get_settings( 'titles.pt_' . $post_type . '_default_rich_snippet', ( isset( $richsnp_default[ $post_type ] ) ? $richsnp_default[ $post_type ] : 'product' ) ),
			];

		}

		return [
			'id'         => $field_id,
			'type'       => 'select',
			'name'       => $field_name,
			'desc'       => esc_html__( 'Default rich snippet selected when creating a new post of this type. ', 'rank-math' ),
			'options'    => Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'rank-math' ) ),
			'dep'        => [ [ 'rich_snippet', 'on' ] ],
			'default'    => Helper::get_settings( 'titles.pt_' . $post_type . '_default_rich_snippet', ( isset( $richsnp_default[ $post_type ] ) ? $richsnp_default[ $post_type ] : 'none' ) ),
			'attributes' => [ 'data-s2' => '' ],
		];
	}
}
