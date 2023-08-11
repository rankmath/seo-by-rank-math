<?php
/**
 * Class HelperTest
 *
 * Test methods in Helper class.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Tests
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tests;

use Exception;
use WP_UnitTestCase;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

class AttachmentHelper extends WP_UnitTestCase {

	/**
	 * Grabs an image alt text.
	 */
	public function test_attachment_alt_tag_and_sitemap_exclusion() {
		$attachment = $this->factory()->post->create( array( 'post_type' => 'attachment' ) );

		// Excluded from sitemap.
		update_post_meta( $attachment, 'rank_math_exclude_sitemap', true );
		$this->assertFalse( Helper::attachment_in_sitemap( $attachment ) );

		// Not Excluded from sitemap.
		delete_post_meta( $attachment, 'rank_math_exclude_sitemap' );
		$this->assertTrue( Helper::attachment_in_sitemap( $attachment ) );
	}
}
