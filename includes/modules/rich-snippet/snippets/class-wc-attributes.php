<?php
/**
 * Helper class for handling WooComerce product attributes for rich snippets.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

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
	private $_attributes;

	/**
	 * Hold product object.
	 *
	 * @var WC_Product
	 */
	private $_product;

	/**
	 * The Constructor.
	 *
	 * @param WC_Product $product The Product.
	 */
	public function __construct( $product ) {
		$this->_product    = $product;
		$this->_attributes = $product->get_attributes();
	}

	/**
	 * Find attribute for property.
	 *
	 * @param array  $entity Entity to attach data to.
	 * @param string $needle Assign this property.
	 */
	public function assign_property( &$entity, $needle ) {
		foreach ( $this->_attributes as $key => $attrib ) {
			if ( stristr( $key, $needle ) ) {
				$entity[ $needle ] = $this->_product->get_attribute( $key );
				unset( $this->_attributes[ $key ] );
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
		foreach ( $this->_attributes as $key => $attrib ) {
			if ( $attrib['is_visible'] && ! $attrib['is_variation'] ) {
				$entity['additionalProperty'][] = [
					'@type' => 'PropertyValue',
					'name'  => $key,
					'value' => $this->_product->get_attribute( $key ),
				];
			}
		}
	}
}
