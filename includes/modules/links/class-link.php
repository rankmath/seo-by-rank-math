<?php
/**
 * The SEO Link class.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Links
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Links;

defined( 'ABSPATH' ) || exit;

/**
 * Link class.
 */
class Link {

	/**
	 * Link URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Link post ID.
	 *
	 * @var int
	 */
	protected $target_post_id;

	/**
	 * Link type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Sets the properties for the object.
	 *
	 * @param string $url            The URL.
	 * @param int    $target_post_id ID to the post where the link refers to.
	 * @param string $type           The URL type: internal or external.
	 */
	public function __construct( $url, $target_post_id, $type ) {
		$this->url            = $url;
		$this->target_post_id = $target_post_id;
		$this->type           = $type;
	}

	/**
	 * Returns the URL.
	 *
	 * @return string The URL.
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Returns the target post ID.
	 *
	 * @return int The target post ID.
	 */
	public function get_target_post_id() {
		return (int) $this->target_post_id;
	}

	/**
	 * Return the link type (internal/external).
	 *
	 * @return string The link type.
	 */
	public function get_type() {
		return $this->type;
	}
}
