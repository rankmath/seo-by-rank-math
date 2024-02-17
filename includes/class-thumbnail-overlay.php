<?php
/**
 * Thumbnails with overlays.
 *
 * @since      1.0.82
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;
use RankMath\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * Thumbnail_Overlay class.
 */
class Thumbnail_Overlay {

	use Hooker;

	/**
	 * Image module to be used (gd or imagick).
	 *
	 * @var string
	 */
	private $image_module = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->image_module = extension_loaded( 'imagick' ) ? 'imagick' : 'gd';

		$this->action( 'wp_ajax_rank_math_overlay_thumb', 'generate_overlay_thumbnail' );
		$this->action( 'wp_ajax_nopriv_rank_math_overlay_thumb', 'generate_overlay_thumbnail' );
	}

	/**
	 * AJAX function to generate overlay image. Used in social thumbnails.
	 */
	public function generate_overlay_thumbnail() {
		$thumbnail_id = Param::request( 'id', 0, FILTER_VALIDATE_INT );
		$type         = Param::request( 'type', 'play' );
		$secret       = Param::request( 'hash', '' );
		if ( ! $secret ) {
			$secret = Param::request( 'secret', '' );
		}

		$choices = Helper::choices_overlay_images();
		if ( ! isset( $choices[ $type ] ) ) {
			die();
		}
		$overlay_image = $choices[ $type ]['path'];
		$image         = Attachment::get_scaled_image_path( $thumbnail_id, 'large' );

		if ( ! $this->is_secret_valid( $thumbnail_id, $type, $secret ) ) {
			die();
		}

		// If 'large' thumbnail is not found, fall back to full size.
		if ( empty( $image ) ) {
			$image = Attachment::get_scaled_image_path( $thumbnail_id, 'full' );
		}

		$position = $choices[ $type ]['position'];
		$this->create_overlay_image( $image, $overlay_image, $position );

		die();
	}

	/**
	 * Calculate margins for a GD resource based on position string.
	 *
	 * @param string   $position Position string.
	 * @param resource $image    GD image resource identifier.
	 * @param resource $stamp    GD image resource identifier.
	 *
	 * @return array
	 */
	private function get_position_margins_gd( $position, $image, $stamp ) {
		$margins = [
			'middle_center' => [],
		];

		$margins['middle_center']['top']  = round( abs( imagesy( $image ) - imagesy( $stamp ) ) / 2 );
		$margins['middle_center']['left'] = round( abs( imagesx( $image ) - imagesx( $stamp ) ) / 2 );

		$default_margins = $margins['middle_center'];
		$margins         = $this->do_filter( 'social/overlay_image_positions', $margins, $image, $stamp, 'gd' );

		if ( ! isset( $margins[ $position ] ) ) {
			return $default_margins;
		}

		return $margins[ $position ];
	}

	/**
	 * Calculate margins for an Imagick object based on position string.
	 *
	 * @param string $position Position string.
	 * @param object $image    Imagick object.
	 * @param object $stamp    Imagick object.
	 *
	 * @return array
	 */
	private function get_position_margins_imagick( $position, $image, $stamp ) {
		$margins = [
			'middle_center' => [],
		];

		$margins['middle_center']['top']  = round( abs( $image->getImageHeight() - $stamp->getImageHeight() ) / 2 );
		$margins['middle_center']['left'] = round( abs( $image->getImageWidth() - $stamp->getImageWidth() ) / 2 );

		$default_margins = $margins['middle_center'];
		$margins         = $this->do_filter( 'social/overlay_image_positions', $margins, $image, $stamp, 'imagick' );

		if ( ! isset( $margins[ $position ] ) ) {
			return $default_margins;
		}

		return $margins[ $position ];
	}

	/**
	 * Get correct imagecreatef based on image file.
	 *
	 * @param string $image_file Image file.
	 *
	 * @return string New generated image
	 */
	private function get_imagecreatefrom_method( $image_file ) {
		$image_format = pathinfo( $image_file, PATHINFO_EXTENSION );
		if ( ! in_array( $image_format, [ 'jpg', 'jpeg', 'gif', 'png' ], true ) ) {
			return '';
		}
		if ( 'jpg' === $image_format ) {
			$image_format = 'jpeg';
		}

		return 'imagecreatefrom' . $image_format;
	}

	/**
	 * Create Overlay Image.
	 *
	 * @param string $image_file    The permalink generated for this post by WordPress.
	 * @param string $overlay_image The ID of the post.
	 * @param string $position      Image position.
	 */
	private function create_overlay_image( $image_file, $overlay_image, $position ) {
		wp_raise_memory_limit( 'image' );

		/**
		 * Filter: 'rank_math/social/create_overlay_image' - Change the create_overlay_image arguments.
		 */
		$args = $this->do_filter( 'social/create_overlay_image', compact( 'image_file', 'overlay_image', 'position' ) );
		extract( $args ); // phpcs:ignore

		if ( empty( $image_file ) || empty( $overlay_image ) ) {
			return;
		}

		$method = 'generate_image_' . $this->image_module;
		$this->$method( $image_file, $overlay_image, $position );
		die();
	}

	/**
	 * Generate image using the GD module.
	 *
	 * @param string $image_file    The permalink generated for this post by WordPress.
	 * @param string $overlay_image The ID of the post.
	 * @param string $position      Image position.
	 */
	private function generate_image_gd( $image_file, $overlay_image, $position ) {
		$imagecreatefrom         = $this->get_imagecreatefrom_method( $image_file );
		$overlay_imagecreatefrom = $this->get_imagecreatefrom_method( $overlay_image );
		if ( ! $imagecreatefrom || ! $overlay_imagecreatefrom ) {
			return;
		}

		$stamp = $overlay_imagecreatefrom( $overlay_image );
		$image = $imagecreatefrom( $image_file );

		if ( ! $image || ! $stamp ) {
			return;
		}

		$stamp_width  = imagesx( $stamp );
		$stamp_height = imagesy( $stamp );

		$img_width  = imagesx( $image );

		if ( $stamp_width > $img_width ) {
			$stamp = imagescale( $stamp, $img_width );
		}

		$margins = $this->get_position_margins_gd( $position, $image, $stamp );

		// Copy the stamp image onto our photo using the margin offsets and the photo width to calculate positioning of the stamp.
		imagecopy( $image, $stamp, $margins['left'], $margins['top'], 0, 0, $stamp_width, $stamp_height );

		// Output and free memory.
		header( 'Content-type: image/png' );
		imagepng( $image );
		imagedestroy( $image );
	}

	/**
	 * Generate image using the Imagick module.
	 *
	 * @param string $image_file    The permalink generated for this post by WordPress.
	 * @param string $overlay_image The ID of the post.
	 * @param string $position      Image position.
	 *
	 * @return void
	 */
	private function generate_image_imagick( $image_file, $overlay_image, $position ) {
		try {
			$stamp = new \Imagick( $overlay_image );
			$image = new \Imagick( $image_file );

			if ( ! $image->valid() || ! $stamp->valid() || ! $image->getImageFormat() || ! $stamp->getImageFormat() ) {
				return;
			}

			// Select the first frame to handle animated images properly.
			if ( is_callable( [ $stamp, 'setIteratorIndex' ] ) ) {
				$stamp->setIteratorIndex( 0 );
			}
			if ( is_callable( [ $image, 'setIteratorIndex' ] ) ) {
				$image->setIteratorIndex( 0 );
			}
		} catch ( \Exception $e ) {
			return;
		}

		$stamp_width = $stamp->getImageWidth();
		$img_width   = $image->getImageWidth();

		if ( $stamp_width > $img_width ) {
			$stamp->resizeImage( $img_width, 0, \Imagick::FILTER_LANCZOS, 1 );
		}

		$margins = $this->get_position_margins_imagick( $position, $image, $stamp );

		// Copy the stamp image onto our photo using the margin offsets and the photo width to calculate positioning of the stamp.
		$image->compositeImage( $stamp, \Imagick::COMPOSITE_OVER, $margins['left'], $margins['top'] );

		// Output.
		header( 'Content-type: image/png' );
		echo $image->getImageBlob(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Free memory.
		$image->clear();
		$image->destroy();

		$stamp->clear();
		$stamp->destroy();
	}

	/**
	 * Check if secret key is valid.
	 *
	 * @param int    $id     The ID of the attachment.
	 * @param string $type   Overlay type.
	 * @param string $secret Secret key.
	 *
	 * @return boolean
	 */
	private function is_secret_valid( $id, $type, $secret ) {
		return md5( $id . $type . wp_salt( 'nonce' ) ) === $secret;
	}
}
