<?php
/**
 * The Schema Interface
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Schema interface.
 */
interface Snippet {

	/**
	 * Process schema data
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld Instance of JsonLD.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld );
}
