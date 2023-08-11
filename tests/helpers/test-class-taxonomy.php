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

use WP_UnitTestCase;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

class TaxonomyHelper extends WP_UnitTestCase {

	/**
	 * Is post indexable.
	 */
	public function test_is_term_indexable() {
		$term_id = $this->factory()->category->create(array(
			'name' => 'Category Title',
		));
		$term    = get_term_by( 'id', $term_id, 'category' );
		$this->assertTrue( Helper::is_term_indexable( $term ) );

		// By Settings.
		rank_math()->settings->set( 'titles', 'tax_category_custom_robots', true );
		rank_math()->settings->set( 'titles', 'tax_category_robots', array( 'noindex' ) );
		$this->assertFalse( Helper::is_term_indexable( $term ) );

		// Noindex by Meta.
		update_term_meta( $term_id, 'rank_math_robots', array( 'noindex' ) );
		$this->assertFalse( Helper::is_term_indexable( $term ) );
	}
}
