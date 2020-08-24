<?php
/**
 * Image SEO module.
 *
 * @since      1.0
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Image_Seo;

use RankMath\KB;
use RankMath\Runner;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Image_Seo class.
 *
 * @codeCoverageIgnore
 */
class Image_Seo {

	use Hooker;

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
