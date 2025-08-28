<?php
/**
 * Bulk Image Alt generation using the Content AI API.
 *
 * @since      1.0.218
 * @package    RankMath
 * @subpackage RankMath\ContentAI
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Bulk_Image_Alt class.
 */
class Bulk_Image_Alt extends \WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'bulk_image_alt';

	/**
	 * Main instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Bulk_Image_Alt
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Bulk_Image_Alt ) ) {
			$instance = new Bulk_Image_Alt();
		}

		return $instance;
	}

	/**
	 * Start creating batches.
	 *
	 * @param array $data Posts data.
	 */
	public function start( $data ) {
		Helper::add_notification(
			esc_html__( 'Bulk image alt generation started. It might take few minutes to complete the process.', 'rank-math' ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_content_ai_posts_started',
				'classes' => 'rank-math-notice',
			]
		);

		update_option( 'rank_math_content_ai_posts', array_keys( $data['posts'] ) );

		foreach ( $data['posts'] as $post_id => $images ) {
			$chunks = array_chunk( $images, 5, true );
			foreach ( $chunks as $chunk ) {
				$this->push_to_queue(
					[
						'post_id' => $post_id,
						'images'  => array_values( $chunk ),
					]
				);
			}
		}

		$this->save()->dispatch();
	}

	/**
	 * Task to perform.
	 *
	 * @param string $data Posts to process.
	 */
	public function wizard( $data ) {
		$this->task( $data );
	}

	/**
	 * Cancel the Bulk edit process.
	 */
	public function cancel() {
		delete_option( 'rank_math_content_ai_posts' );
		delete_option( 'rank_math_content_ai_posts_processed' );
		parent::clear_scheduled_event();
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$posts = get_option( 'rank_math_content_ai_posts' );
		delete_option( 'rank_math_content_ai_posts' );
		delete_option( 'rank_math_content_ai_posts_processed' );
		Helper::add_notification(
			// Translators: placeholder is the number of modified posts.
			sprintf( _n( 'Image alt attributes successfully updated in %d post.', 'Image alt attributes successfully updated in %d posts.', count( $posts ), 'rank-math' ), count( $posts ) ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_content_ai_posts',
				'classes' => 'rank-math-notice',
			]
		);

		parent::complete();
	}

	/**
	 * Task to perform.
	 *
	 * @param array $data Posts to process.
	 *
	 * @return bool
	 */
	protected function task( $data ) {
		try {
			if ( empty( $data['images'] ) ) {
				$this->update_content_ai_posts_count();
				return false;
			}

			$is_attachment = get_post_type( $data['post_id'] ) === 'attachment';
			$api_output    = json_decode( wp_remote_retrieve_body( $this->get_image_alts( $data, $is_attachment ) ), true );

			// Early bail if API returns and error.
			if ( ! empty( $api_output['error'] ) ) {
				$notice = ! empty( $api_output['message'] ) ? $api_output['message'] : esc_html__( 'Bulk image alt generation failed.', 'rank-math' );
				Helper::add_notification(
					$notice,
					[
						'type'    => 'error',
						'id'      => 'rank_math_content_ai_posts',
						'classes' => 'rank-math-notice',
					]
				);

				$this->cancel();

				return;
			}

			if ( empty( $api_output['altTexts'] ) ) {
				$this->update_content_ai_posts_count();
				return false;
			}

			$this->update_image_alt( $api_output['altTexts'], $data, $is_attachment );

			$this->update_content_ai_posts_count();

			$credits = ! empty( $api_output['credits'] ) ? $api_output['credits'] : [];
			if ( isset( $credits['available'] ) ) {
				$credits = $credits['available'] - $credits['taken'];
				Helper::update_credits( $credits );

				if ( $credits <= 0 ) {
					$posts_processed = get_option( 'rank_math_content_ai_posts_processed' );
					delete_option( 'rank_math_content_ai_posts' );
					delete_option( 'rank_math_content_ai_posts_processed' );
					Helper::add_notification(
						// Translators: placeholder is the number of modified posts.
						sprintf( esc_html__( 'Image alt attributes successfully updated in %d posts. The process was stopped as you have used all the credits on your site.', 'rank-math' ), $posts_processed ),
						[
							'type'    => 'success',
							'id'      => 'rank_math_content_ai_posts',
							'classes' => 'rank-math-notice',
						]
					);

					wp_clear_scheduled_hook( 'wp_bulk_image_alt_cron' );
				}
			}

			return false;
		} catch ( \Exception $error ) {
			return true;
		}
	}

	/**
	 * Get Posts to bulk update the data.
	 *
	 * @param array   $data          Data to process.
	 * @param boolean $is_attachment Whether the current post is attachment.
	 *
	 * @return array
	 */
	private function get_image_alts( $data, $is_attachment ) {
		$connect_data = Admin_Helper::get_registration_data();

		// Convert images to the new format with base64 data.
		$images     = [];
		$image_urls = $is_attachment ? $data['images'] : $this->extract_urls_from_tags( $data['images'] );

		foreach ( $image_urls as $image_url ) {
			$image_id    = $this->get_image_id_from_url( $image_url );
			$base64_data = $this->convert_image_to_base64( $image_url );

			if ( ! empty( $base64_data ) ) {
				$images[] = [
					'id'    => $image_id,
					'image' => $base64_data,
				];
			}
		}

		if ( empty( $images ) ) {
			return;
		}

		$request_data = [
			'images'         => $images,
			'username'       => $connect_data['username'],
			'api_key'        => $connect_data['api_key'],
			'site_url'       => $connect_data['site_url'],
			'plugin_version' => rank_math()->version,
			'language'       => Helper::get_settings( 'general.content_ai_language', Helper::content_ai_default_language() ),
		];

		return wp_remote_post(
			CONTENT_AI_URL . '/ai/generate_image_alt_v2',
			[
				'headers' => [
					'content-type' => 'application/json',
				],
				'timeout' => 60000,
				'body'    => wp_json_encode( $request_data ),
			]
		);
	}

	/**
	 * Extract URLs from image tags.
	 *
	 * @param array $image_tags Array of image HTML tags.
	 * @return array Array of image URLs.
	 */
	private function extract_urls_from_tags( $image_tags ) {
		$urls = [];
		foreach ( $image_tags as $image_tag ) {
			$url = $this->get_image_source( $image_tag );
			if ( ! empty( $url ) ) {
				$urls[] = $url;
			}
		}
		return $urls;
	}

	/**
	 * Convert image URL to base64 encoded data.
	 *
	 * @param string $image_url Image URL to convert.
	 * @return string Base64 encoded image data.
	 */
	private function convert_image_to_base64( $image_url ) {
		// Validate URL.
		if ( ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		// Get image content.
		$response = wp_remote_get( $image_url, [ 'timeout' => 30 ] );
		if ( is_wp_error( $response ) ) {
			return '';
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return '';
		}

		$image_content = wp_remote_retrieve_body( $response );
		if ( empty( $image_content ) ) {
			return '';
		}

		// Get image info to determine MIME type.
		$image_info = wp_check_filetype( $image_url );
		$mime_type  = ! empty( $image_info['type'] ) ? $image_info['type'] : 'image/jpeg';

		// Convert to base64 with proper data URL format.
		$base64 = base64_encode( $image_content ); // phpcs:ignore -- Verified as safe usage.
		return 'data:' . $mime_type . ';base64,' . $base64;
	}

	/**
	 * Extract filename from image URL.
	 *
	 * @param string $image_url Image URL.
	 * @return string Filename.
	 */
	private function get_image_id_from_url( $image_url ) {
		$url_parts = explode( '/', $image_url );
		$filename  = end( $url_parts );

		if ( empty( $filename ) ) {
			$filename = 'image.jpg';
		}

		// Remove query parameters if present.
		$filename = strtok( $filename, '?' );

		return $filename;
	}

	/**
	 * Keep count of the Content AI posts that were processed.
	 *
	 * @return void
	 */
	private function update_content_ai_posts_count() {
		$content_ai_posts_count = get_option( 'rank_math_content_ai_posts_processed', 0 ) + 1;
		update_option( 'rank_math_content_ai_posts_processed', $content_ai_posts_count, false );
	}

	/**
	 * Update Image alt value.
	 *
	 * @param array   $alt_texts     Alt texts returned by the API.
	 * @param array   $data          Data to process.
	 * @param boolean $is_attachment Whether the current post is attachment.
	 *
	 * @return void
	 */
	private function update_image_alt( $alt_texts, $data, $is_attachment ) {
		if ( $is_attachment ) {
			update_post_meta( $data['post_id'], '_wp_attachment_image_alt', sanitize_text_field( current( $alt_texts ) ) );

			return;
		}

		$post = get_post( $data['post_id'] );

		foreach ( $data['images'] as $image ) {
			$image_src = $this->get_image_source( $image );
			$image_id  = $this->get_image_id_from_url( $image_src );
			$image_alt = ! empty( $alt_texts[ $image_id ] ) ? $alt_texts[ $image_id ] : '';
			if ( ! $image_alt ) {
				continue;
			}

			// Remove any existing empty alt attributes.
			$img_tag = preg_replace( '/ alt=(""|\'\')/i', '', $image );
			// Add the new alt attribute.
			$img_tag = str_replace( '<img ', '<img alt="' . esc_attr( $image_alt ) . '" ', $img_tag );

			// Replace the old img tag with the new one in the post content.
			$post->post_content = str_replace( $image, $img_tag, $post->post_content );
		}

		wp_update_post( $post );
	}

	/**
	 * Get Image source from image tag.
	 *
	 * @param string $image Image tag.
	 *
	 * @return string
	 */
	private function get_image_source( $image ) {
		// The $data['images'] contains an array of img tags, so we need to extract the src attribute from each one.
		preg_match( '/src=[\'"]?([^\'" >]+)[\'" >]/i', $image, $matches );
		return $matches[1];
	}
}
