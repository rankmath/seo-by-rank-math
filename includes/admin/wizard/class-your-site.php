<?php
/**
 * The Your Site wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Your_Site implements Wizard_Step {

	/**
	 * Render step body.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function render( $wizard ) {
		include_once $wizard->get_view( 'your-site' );
	}

	/**
	 * Render form for step.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function form( $wizard ) {
		$displayname = $this->get_site_display_name();
		$wizard->cmb->add_field(
			[
				'id'      => 'site_type',
				'type'    => 'select',
				/* translators: sitename */
				'name'    => sprintf( esc_html__( '%1$s is a&hellip;', 'rank-math' ), $displayname ),
				'options' => $this->get_type_choices(),
				'default' => $this->get_default_site_type(),
			]
		);

		$wizard->cmb->add_field(
			[
				'id'         => 'business_type',
				'type'       => 'select',
				'name'       => esc_html__( 'Business Type', 'rank-math' ),
				'desc'       => esc_html__( 'Select the type that best describes your business. If you can\'t find one that applies exactly, use the generic "Organization" or "Local Business" types.', 'rank-math' ),
				'options'    => Helper::choices_business_types(),
				'attributes' => [
					'data-s2'      => '',
					'data-default' => Helper::get_settings( 'titles.local_business_type' ) ? '0' : '1',
				],
				'default'    => Helper::get_settings( 'titles.local_business_type' ),
				'dep'        => $this->get_type_dependency(),
			]
		);

		$wizard->cmb->add_field(
			[
				'id'      => 'company_name',
				'type'    => 'text',
				'name'    => esc_html__( 'Company Name', 'rank-math' ),
				'default' => Helper::get_settings( 'titles.knowledgegraph_name', $displayname ),
				'dep'     => $this->get_type_dependency(),
			]
		);

		$wizard->cmb->add_field(
			[
				'id'      => 'company_logo',
				'type'    => 'file',
				'name'    => esc_html__( 'Logo for Google', 'rank-math' ),
				'default' => $this->get_default_logo(),
				'desc'    => __( '<strong>Min Size: 160Χ90px, Max Size: 1920X1080px</strong>.<br />A squared image is preferred by the search engines.', 'rank-math' ),
				'options' => [ 'url' => false ],
			]
		);

		$wizard->cmb->add_field(
			[
				'id'      => 'open_graph_image',
				'type'    => 'file',
				'name'    => esc_html__( 'Default Social Share Image', 'rank-math' ),
				'desc'    => __( 'When a featured image or an OpenGraph Image is not set for individual posts/pages/CPTs, this image will be used as a fallback thumbnail when your post is shared on Facebook. <strong>The recommended image size is 1200 x 630 pixels.</strong>', 'rank-math' ),
				'options' => [ 'url' => false ],
				'default' => Helper::get_settings( 'titles.open_graph_image' ),
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
		$settings     = wp_parse_args(
			rank_math()->settings->all_raw(),
			[
				'titles'  => '',
				'sitemap' => '',
			]
		);
		$current_user = wp_get_current_user();
		$values       = wp_parse_args(
			$values,
			[
				'author_name'         => $current_user->display_name,
				'company_logo'        => '',
				'company_logo_id'     => '',
				'open_graph_image'    => '',
				'open_graph_image_id' => '',
			]
		);

		// Save these settings.
		$functions = [ 'save_local_seo', 'save_open_graph', 'save_post_types', 'save_taxonomies' ];
		foreach ( $functions as $function ) {
			$settings = $this->$function( $settings, $values );
		}

		$business_type = [ 'news', 'business', 'webshop', 'otherbusiness' ];
		$modules       = [ 'local-seo' => in_array( $values['site_type'], $business_type, true ) ? 'on' : 'off' ];
		$users         = get_users( [ 'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ] ] );

		if ( count( $users ) > 1 && ! is_plugin_active( 'members/members.php' ) ) {
			$modules['role-manager'] = 'on';
		}

		set_transient( '_rank_math_site_type', $values['site_type'] );
		Helper::update_modules( $modules );
		Helper::update_all_settings( null, $settings['titles'], null );

		return true;
	}

	/**
	 * Save Local Seo
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private function save_local_seo( $settings, $values ) {
		switch ( $values['site_type'] ) {
			case 'blog':
			case 'portfolio':
				$settings['titles']['knowledgegraph_type']    = 'person';
				$settings['titles']['knowledgegraph_name']    = $values['author_name'];
				$settings['titles']['knowledgegraph_logo']    = $values['company_logo'];
				$settings['titles']['knowledgegraph_logo_id'] = $values['company_logo_id'];
				break;

			case 'news':
			case 'webshop':
			case 'business':
			case 'otherbusiness':
				$settings['titles']['knowledgegraph_type']    = 'company';
				$settings['titles']['knowledgegraph_name']    = $values['company_name'];
				$settings['titles']['knowledgegraph_logo']    = $values['company_logo'];
				$settings['titles']['local_business_type']    = $values['business_type'];
				$settings['titles']['knowledgegraph_logo_id'] = $values['company_logo_id'];
				break;

			case 'otherpersonal':
				$settings['titles']['knowledgegraph_type'] = 'person';
				$settings['titles']['knowledgegraph_name'] = $values['author_name'];
				break;
		}

		return $settings;
	}

	/**
	 * Save Open Graph
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private function save_open_graph( $settings, $values ) {
		if ( ! empty( $values['open_graph_image_id'] ) ) {
			$settings['titles']['open_graph_image']    = $values['open_graph_image'];
			$settings['titles']['open_graph_image_id'] = $values['open_graph_image_id'];
		}

		if ( empty( $values['company_logo_id'] ) ) {
			unset( $settings['titles']['knowledgegraph_logo'] );
			unset( $settings['titles']['knowledgegraph_logo_id'] );
		}

		return $settings;
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
		foreach ( Helper::get_accessible_post_types() as $post_type => $label ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$settings['titles'][ "pt_{$post_type}_add_meta_box" ] = 'on';
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
		$taxonomies = Admin_Helper::get_taxonomies_options();
		array_shift( $taxonomies );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$settings['titles'][ "tax_{$taxonomy}_add_meta_box" ] = 'on';
		}

		return $settings;
	}

	/**
	 * Get site display name.
	 *
	 * @return string
	 */
	protected function get_site_display_name() {
		$siteurl  = get_bloginfo( 'url' );
		$sitename = get_bloginfo( 'title' );

		return $sitename ? $sitename : $siteurl;
	}

	/**
	 * Get default logo.
	 *
	 * @return string
	 */
	private function get_default_logo() {
		if ( defined( 'MTS_THEME_NAME' ) && MTS_THEME_NAME ) {
			$theme_options = get_option( MTS_THEME_NAME );
			if ( isset( $theme_options['mts_logo'] ) ) {
				return wp_get_attachment_url( $theme_options['mts_logo'] );
			}
		}

		if ( current_theme_supports( 'custom-logo' ) && ! empty( get_theme_mod( 'custom_logo' ) ) ) {
			return wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );
		}

		return Helper::get_settings( 'titles.knowledgegraph_logo' );
	}

	/**
	 * Get default site type.
	 *
	 * @return string
	 */
	private function get_default_site_type() {
		$default_type = get_transient( '_rank_math_site_type' );
		return $default_type ? $default_type : ( class_exists( 'Easy_Digital_Downloads' ) || class_exists( 'WooCommerce' ) ? 'webshop' : 'blog' );
	}

	/**
	 * Get type dependecy.
	 *
	 * @return array
	 */
	private function get_type_dependency() {
		return [
			[ 'site_type', 'news' ],
			[ 'site_type', 'business' ],
			[ 'site_type', 'webshop' ],
			[ 'site_type', 'otherbusiness' ],
		];
	}

	/**
	 * Get type choices.
	 *
	 * @return array
	 */
	private function get_type_choices() {
		return [
			'blog'          => esc_html__( 'Personal Blog', 'rank-math' ),
			'news'          => esc_html__( 'Community Blog/News Site', 'rank-math' ),
			'portfolio'     => esc_html__( 'Personal Portfolio', 'rank-math' ),
			'business'      => esc_html__( 'Small Business Site', 'rank-math' ),
			'webshop'       => esc_html__( 'Webshop', 'rank-math' ),
			'otherpersonal' => esc_html__( 'Other Personal Website', 'rank-math' ),
			'otherbusiness' => esc_html__( 'Other Business Website', 'rank-math' ),
		];
	}
}
