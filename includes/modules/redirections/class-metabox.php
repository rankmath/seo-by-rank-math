<?php
/**
 * The Redirections Metabox.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Metabox class.
 */
class Metabox {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/metabox/process_fields', 'save_advanced_meta' );
		$this->action( 'rank_math/metabox/values', 'add_json_data' );
	}

	/**
	 * Add settings in the Advanced tab of the metabox.
	 *
	 * @param array $values Localized data.
	 */
	public function add_json_data( $values ) {
		if ( ! $this->can_add_setting( $values ) ) {
			return $values;
		}

		$object_type = $values['objectType'];
		$object_id   = (int) $values['objectID'];
		$url         = 'term' === $object_type ? get_term_link( $object_id ) : get_permalink( $object_id );
		if ( is_wp_error( $url ) ) {
			return $values;
		}

		$url = wp_parse_url( $url, PHP_URL_PATH );
		$url = trim( $url, '/' );

		$redirection                       = Cache::get_by_object_id( $object_id, $object_type );
		$values['assessor']['redirection'] = $redirection ? DB::get_redirection_by_id( $redirection->redirection_id, 'active' ) : [
			'id'          => '',
			'url_to'      => '',
			'header_code' => Helper::get_settings( 'general.redirections_header_code' ),
		];

		$values['assessor']['autoCreateRedirection'] = Helper::get_settings( 'general.redirections_post_redirect' );

		return $values;
	}

	/**
	 * Save handler for metadata.
	 *
	 * @param CMB2 $cmb CMB2 instance.
	 */
	public function save_advanced_meta( $cmb ) {
		if ( $this->can_delete( $cmb->data_to_save ) ) {
			// Delete.
			if ( ! empty( $cmb->data_to_save['redirection_id'] ) ) {
				DB::delete( $cmb->data_to_save['redirection_id'] );
				Helper::add_notification( esc_html__( 'Redirection successfully deleted.', 'rank-math' ), [ 'type' => 'info' ] );
			}
			return [
				'action'  => 'delete',
				'message' => esc_html__( 'Redirection successfully deleted.', 'rank-math' ),
			];
		}

		// Check if no change bail!!
		if ( ! $this->can_update( $cmb->data_to_save ) ) {
			return [
				'action'  => 'cant_update',
				'message' => esc_html__( 'Can\'t update redirection.', 'rank-math' ),
			];
		}

		$redirection = Redirection::from(
			[
				'id'          => isset( $cmb->data_to_save['redirection_id'] ) ? $cmb->data_to_save['redirection_id'] : '',
				'url_to'      => $cmb->data_to_save['redirection_url_to'],
				'sources'     => [
					[
						'pattern'    => $cmb->data_to_save['redirection_sources'],
						'comparison' => 'exact',
					],
				],
				'header_code' => $cmb->data_to_save['redirection_header_code'],
			]
		);
		$redirection->set_nocache( true );
		$redirection->save();

		$response = [
			'action'  => 'update',
			'message' => esc_html__( 'Redirection updated successfully.', 'rank-math' ),
		];

		if ( $redirection->is_new() ) {
			Helper::add_notification( esc_html__( 'New redirection created.', 'rank-math' ) );
			$response = [
				'id'      => $redirection->get_id(),
				'action'  => 'new',
				'message' => esc_html__( 'New redirection created.', 'rank-math' ),
			];
		}

		Cache::add(
			[
				'from_url'       => $cmb->data_to_save['redirection_sources'],
				'redirection_id' => $redirection->get_id(),
				'object_id'      => $cmb->object_id,
				'object_type'    => \property_exists( $cmb, 'object_type' ) ? $cmb->object_type : 'post',
			]
		);

		return $response;
	}

	/**
	 * Whether to add Redirection Settings.
	 *
	 * @param array $values Localized data.
	 */
	private function can_add_setting( $values ) {
		if ( 'post' !== $values['objectType'] ) {
			return true;
		}

		$post = get_post( $values['objectID'] );
		if ( empty( $post ) || 'publish' !== $post->post_status ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if can delete.
	 *
	 * @param array $values Values.
	 *
	 * @return boolean
	 */
	private function can_delete( $values ) {
		if ( ! isset( $values['rank_math_enable_redirection'] ) || 'off' === $values['rank_math_enable_redirection'] ) {
			return true;
		}

		if ( isset( $values['has_redirect'] ) && empty( $values['has_redirect'] ) ) {
			return true;
		}

		if ( false === in_array( (int) $values['redirection_header_code'], [ 410, 451 ], true ) ) {
			if ( empty( $values['redirection_url_to'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if update is required.
	 *
	 * @param array $values Values.
	 *
	 * @return boolean
	 */
	private function can_update( $values ) {
		if ( did_action( 'rank_math/redirection/post_updated' ) ) {
			return false;
		}

		if ( empty( $values['redirection_id'] ) || in_array( (int) $values['redirection_header_code'], [ 410, 451 ], true ) ) {
			return true;
		}

		$redirection = DB::get_redirection_by_id( $values['redirection_id'] );

		return ! (
			$values['redirection_url_to'] === $redirection['url_to'] &&
			$values['redirection_header_code'] === $redirection['header_code']
		);
	}
}
