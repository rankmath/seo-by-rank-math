<?php
/**
 * The Import wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Admin\Importers\Detector;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Import implements Wizard_Step {

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
			<h1><?php esc_html_e( 'Import SEO Settings', 'rank-math' ); ?></h1>
			<p><?php esc_html_e( 'You can import SEO settings from the following plugins:', 'rank-math' ); ?></p>
		</header>

		<?php $wizard->cmb->show_form(); ?>


		<div id="import-progress-bar">
			<div id="importProgress">
				<div id="importBar"></div>
			</div>
			<span class="left"><strong><?php echo esc_html__( 'Importing: ', 'rank-math' ); ?></strong><span class="plugin-from"></span></span>
			<span class="right"><span class="number">0</span>% <?php echo esc_html__( 'Completed', 'rank-math' ); ?></span>
		</div>
		<textarea id="import-progress" class="import-progress-area large-text" disabled="disabled" rows="8"></textarea>
		<footer class="form-footer wp-core-ui rank-math-ui">
			<button type="submit" class="button button-secondary button-deactivate-plugins" data-deactivate-message="<?php esc_html_e( 'Deactivating Plugins...', 'rank-math' ); ?>"><?php esc_html_e( 'Skip, Don\'t Import Now', 'rank-math' ); ?></button>
			<button type="submit" class="button button-primary button-continue" style="display:none"><?php esc_html_e( 'Continue', 'rank-math' ); ?></button>
			<button type="submit" class="button button-primary button-import"><?php esc_html_e( 'Start Import', 'rank-math' ); ?></button>
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
		$detector = new Detector;
		$plugins  = $detector->detect();
		$plugins  = $this->set_priority( $plugins );

		$count = 0;
		foreach ( $plugins as $slug => $plugin ) {
			$checked       = 'checked';
			$multi_checked = 'multicheck-checked';
			$choices       = array_keys( $plugin['choices'] );

			if ( isset( $plugin['checked'] ) && false === $plugin['checked'] ) {
				$checked       = '';
				$multi_checked = '';
				$choices       = [];
			}

			$field_args = [
				'id'           => 'import_from_' . $slug,
				'type'         => 'group',
				'description'  => '<input type="checkbox" class="import-data" name="import[]" value="' . $slug . '" ' . $checked . ' data-plugin="' . $plugin['name'] . '" />',
				'before_group' => 0 === $count ? '<h3 class="import-label">' . esc_html__( 'Input Data From:', 'rank-math' ) . '</h3>' : '',
				'repeatable'   => false,
				'options'      => [
					'group_title' => $plugin['name'],
					'sortable'    => false,
					'closed'      => true,
				],
			];

			$group_id  = $wizard->cmb->add_field( $field_args );
			$is_active = is_plugin_active( $plugin['file'] );
			$wizard->cmb->add_group_field( $group_id, [
				'id'         => $slug . '_meta',
				'type'       => 'multicheck',
				'repeatable' => false,
				'desc'       => $this->get_choice_description( $slug, $plugin, $is_active ),
				'options'    => $plugin['choices'],
				'default'    => $choices,
				'dep'        => [ [ 'import_from', $slug ] ],
				'classes'    => 'nob nopb cmb-multicheck-inline with-description ' . $multi_checked . ' ' . $is_active,
				'attributes' => [ 'data-active' => $is_active ],
			]);

			$count++;
		}
	}

	/**
	 * Set plugins priority.
	 *
	 * @param array $plugins Array of detected plgins.
	 *
	 * @return array
	 */
	private function set_priority( $plugins ) {
		$checked  = false;
		$priority = array_intersect( [ 'seopress', 'yoast', 'yoast-premium', 'aioseo' ], array_keys( $plugins ) );

		foreach ( $priority as $slug ) {
			if ( ! $checked ) {
				$checked                     = true;
				$plugins[ $slug ]['checked'] = true;
				continue;
			}

			$plugins[ $slug ]['checked'] = false;
		}

		return $plugins;
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
		delete_option( 'rank_math_yoast_block_posts' );
		return true;
	}

	/**
	 * Get description for choice field.
	 *
	 * @param string  $slug      Plugin slug.
	 * @param array   $plugin    Plugin info array.
	 * @param boolean $is_active Is plugin active.
	 *
	 * @return string
	 */
	private function get_choice_description( $slug, $plugin, $is_active ) {
		/* translators: 1 is plugin name */
		$desc = 'aio-rich-snippet' === $slug ? esc_html__( 'Import meta data from the %1$s plugin.', 'rank-math' ) : esc_html__( 'Import settings and meta data from the %1$s plugin.', 'rank-math' );

		/* translators: 2 is link to Knowledge Base article */
		$desc .= __( 'The process may take a few minutes if you have a large number of posts or pages <a href="%2$s" target="_blank">Learn more about the import process here.</a>', 'rank-math' );

		if ( $is_active ) {
			/* translators: 1 is plugin name */
			$desc .= '<br>' . __( ' %1$s plugin will be disabled automatically moving forward to avoid conflicts. <strong>It is thus recommended to import the data you need now.</strong>', 'rank-math' );
		}

		return sprintf( wp_kses_post( $desc ), $plugin['name'], KB::get( 'seo-import' ) );
	}
}
