<?php

// Ensure WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  // Add filter to apply discount
  add_action('woocommerce_before_calculate_totals', 'nafeza_apply_dynamic_discounts', 10, 1);

  function nafeza_apply_dynamic_discounts($cart)
  {
    // Check if cart is valid
    if (did_action('woocommerce_before_calculate_totals') >= 2)
      return;

    // Get discount rules from options
    $discount_rules = nafeza_get_discount_rules();
    if (count($discount_rules) == 0) return;



    // Loop through cart items
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
      $product_id = $cart_item['product_id'];

      // Get the original product ID if WPML is active
      if (defined('ICL_SITEPRESS_VERSION')) {
        $original_product_id = apply_filters('wpml_object_id', $product_id, 'product', false, wpml_get_default_language());
      } else {
        $original_product_id = $product_id;
      }

      $quantity = $cart_item['quantity'];
      foreach ($discount_rules as $rule) {
        if ($original_product_id == $rule['product_id'] && $quantity >= $rule['quantity_from'] && $quantity <= $rule['quantity_to']) {
          $price = $cart_item['data']->get_regular_price();
          $discount = $rule['discount_type'] == 'percentage' ? ($rule['discount_value'] / 100) : $rule['discount_value'];

          // Apply discount
          if ($discount > 0) {
            $new_price = $rule['discount_type'] == 'percentage' ? ($price - ($price * $discount)) : ($price - $discount);
            $cart_item['data']->set_price($new_price);

            // Store discount amount in session to display in cart
            WC()->session->set('nafeza_discount_' . $product_id, $price - $new_price);
            break; // Apply only the first matched rule
          }
        } else {
          // Remove any existing discount if conditions are not met
          WC()->session->__unset('nafeza_discount_' . $product_id);
        }
      }
    }
  }

  // Add discount message next to the price in the cart
  add_filter('woocommerce_get_item_data', 'nafeza_display_discount_message', 10, 2);

  function nafeza_display_discount_message($item_data, $cart_item)
  {
    $product_id = $cart_item['product_id'];

    // Get the translated product ID if WPML is active
    if (defined('ICL_SITEPRESS_VERSION')) {
      $product_id = apply_filters('wpml_object_id', $product_id, 'product', false, wpml_get_default_language());
    }

    $discount = WC()->session->get('nafeza_discount_' . $product_id);
    if ($discount) {
      $item_data[] = array(
        'name' => __('Discount Applied', 'nafeza-woocommerce-dynamic-discount'),
        'value' => wc_price($discount),
      );
    }
    return $item_data;
  }




  // Add discount table to product pages
  add_action('woocommerce_single_product_summary', 'nafeza_display_discount_table', 25);

  function nafeza_display_discount_table()
  {
    wc_get_template('discount-table.php', array(), '', plugin_dir_path(__FILE__) . '../templates/');
  }
}


function nafeza_get_discount_rules()
{
  $discount_rules = get_option('nafeza_discount_rules', []);
  if (empty($discount_rules)) return [];

  // Sort discount rules by priority
  usort($discount_rules, function ($a, $b) {
    return (int)$a['priority'] - (int)$b['priority'];
  });

  return $discount_rules;
}



add_filter('woocommerce_product_price_class', function ($class) {
  $class .= ' nafeza-single-product-price';
  return $class;
});
