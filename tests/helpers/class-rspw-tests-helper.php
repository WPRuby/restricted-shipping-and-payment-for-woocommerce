<?php


class RSPW_Tests_Helper {

	public static function add_product( array $args = array() ) {
		$args    = wp_parse_args(
			$args,
			array(
				'price'            => 1,
				'weight'           => 1,
				'downloadable'     => false,
				'virtual'          => false,
				'shipping_classes' => array(),
				'categories'       => array(),
				'tags'             => array(),
				'attribute'        => array(),
			)
		);
		$product = new WC_Product();
		$product->set_price( $args['price'] );
		$product->set_weight( $args['weight'] );
		$product->set_downloadable( $args['downloadable'] );
		$product->set_virtual( $args['virtual'] );
		$product->save();

		if ( isset( $args['categories'] ) ) {
			foreach ( $args['categories'] as $cat ) {
				$category = wp_insert_term( $cat, 'product_cat' );
				wp_add_object_terms( $product->get_id(), $category['term_id'], 'product_cat' );
			}
		}

		if ( isset( $args['shipping_classes'] ) ) {
			foreach ( $args['shipping_classes'] as $shipping_class ) {
				$shipping_class = wp_insert_term( $shipping_class, 'product_shipping_class' );
				$product->set_shipping_class_id( $shipping_class['term_id'] );
				$product->save();
			}
		}

		if ( isset( $args['tags'] ) ) {
			foreach ( $args['tags'] as $tag ) {
				$tag = wp_insert_term( $tag, 'product_tag' );
				wp_add_object_terms( $product->get_id(), $tag['term_id'], 'product_tag' );
			}
		}

		if ( count( $args['attribute'] ) > 0 ) {
			if ( term_exists( $args['attribute']['value'], 'pa_color' ) ) {
				// Save the possible option value for the attribute which will be used for variation later.
				wp_set_object_terms( $product->get_id(), $args['attribute']['value'], 'pa_color', true );
				// Get the term ID.
				$option_term_id         = get_term_by( 'name', $args['attribute']['value'], 'pa_color' )->term_id;
				$attributes             = array();
				$attributes['pa_color'] = array(
					'name'         => 'pa_color',
					'value'        => $option_term_id, // Need to be term IDs.
					'position'     => 1,
					'is_visible'   => '1',
					'is_variation' => '0',
					'is_taxonomy'  => '1',
				);
				update_post_meta( $product->get_id(), '_product_attributes', $attributes );
			}
		}

		return $product;
	}

	public static function prepare_package( WC_Product $product, array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'applied_coupons' => array( 'free-shipping' ),
				'customer_id'     => 1,
				'country'         => 'US',
				'city'            => 'london',
				'postcode'        => '5000',
				'quantity'        => 1,
			)
		);

		return array(
			'contents'        => array(
				$product->get_id() => array(
					'data'     => $product,
					'quantity' => $args['quantity'],
				),
			),
			'contents_cost'   => $product->get_price(),
			'cart_subtotal'   => $product->get_price(),
			'applied_coupons' => $args['applied_coupons'],
			'user'            => array(
				'ID' => $args['customer_id'],
			),
			'destination'     => array(
				'country'  => $args['country'],
				'city'     => $args['city'],
				'postcode' => $args['postcode'],
			),
		);
	}

	public static function prepare_rule( $rule_type, $operator, $value, $extra_value = null ) {
		return array(
			'rule_type'                      => $rule_type,
			'operator'                       => $operator,
			'value_' . $rule_type            => $value,
			'value_' . $rule_type . '_value' => $extra_value,
		);
	}
}
