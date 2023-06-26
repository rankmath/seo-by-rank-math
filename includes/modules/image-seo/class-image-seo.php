<?php
/**
 * The Image SEO module.
 *
 * @since      1.0
 * @package    RankMath
 * @subpackage RankMath\Image_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Image_Seo;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Image_Seo class.
 *
 * @codeCoverageIgnore
 */
class Image_Seo {

	use Hooker;

	/**
	 * Admin page object.
	 *
	 * @var object
	 */
	public $admin;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->load_admin();

		new Add_Attributes();
	}

	/**
	 * Load admin functionality.
	 */
	private function load_admin() {
		if ( is_admin() ) {
			$this->admin = new Admin();
		}
	}

}
