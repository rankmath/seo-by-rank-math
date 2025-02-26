<?php
/**
 * Helper class for handling WooComerce product attributes for rich snippets.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Attributes class.
 */
class WC_Attributes {

	/**
	 * Hold product attributes.
	 *
	 * @var array
	 */
	private $attributes;

	/**
	 * Hold product object.
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 * The Constructor.
	 *
	 * @param WC_Product $product The Product.
	 */
	public function __construct( $product ) {
		$this->product    = $product;
		$this->attributes = $product->get_attributes();
	}

	/**
	 * Find attribute for property.
	 *
	 * @param array  $entity Entity to attach data to.
	 * @param string $needle Assign this property.
	 */
	public function assign_property( &$entity, $needle ) {
		foreach ( $this->attributes as $key => $attrib ) {
			if ( stristr( $key, $needle ) ) {
				$entity[ $needle ] = $this->product->get_attribute( $key );
				unset( $this->attributes[ $key ] );
				return;
			}
		}
	}

	/**
	 * Map remaining attributes as PropertyValue.
	 *
	 * @param array $entity Entity to attach data to.
	 */
	public function assign_remaining( &$entity ) {
		foreach ( $this->attributes as $key => $attrib ) {
			if ( $attrib['is_visible'] && ! $attrib['is_variation'] ) {
				$entity['additionalProperty'][] = [
					'@type' => 'PropertyValue',
					'name'  => $key,
					'value' => $this->product->get_attribute( $key ),
				];
			}
		}
	}
}
