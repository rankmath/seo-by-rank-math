<?php
/**
 * Unit tests for Breadcrumbs Helper
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Tests\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests\Frontend;

use RankMath\Settings;
use RankMath\Frontend\Breadcrumbs;
use RankMath\Tests\UnitTestCase;

defined( 'ABSPATH' ) || exit;

class Breadcrumbs_Test extends UnitTestCase {

	/**
	 * Breadcrumb object.
	 *
	 * @var Breadcrumbs
	 */
	protected $object;

	public function setUp() {
		parent::setUp();
		rank_math()->settings = new Settings();
		$this->object         = new Breadcrumbs();
	}

	public function test_breadcrumb_settings() {
		$settings = $this->getPrivate( $this->object, 'settings' );
		$this->assertTrue( $settings['home'] );
		$this->assertEquals( '-', $settings['separator'] );
		$this->assertFalse( $settings['remove_title'] );
		$this->assertFalse( $settings['hide_tax_name'] );
		$this->assertFalse( $settings['show_ancestors'] );
	}

	public function test_get_breadcrumb() {
		$this->assertContains( '<nav class="rank-math-breadcrumb"><p>', $this->object->get_breadcrumb() );
	}
}
