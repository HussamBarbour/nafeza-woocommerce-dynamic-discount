<?php

// Ensure WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function nafeza_apply_dynamic_discounts($cart)
  {
    if (did_action('woocommerce_before_calculate_totals') >= 2)
      return;

    $discount_rules = nafeza_get_discount_rules();
    if (count($discount_rules) == 0) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
      $product_id = $cart_item['product_id'];
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

          if ($discount > 0) {
            $new_price = $rule['discount_type'] == 'percentage' ? ($price - ($price * $discount)) : ($price - $discount);
            $cart_item['data']->set_price($new_price);
            WC()->session->set('nafeza_discount_' . $product_id, $price - $new_price);
            break;
          }
        } else {
          WC()->session->__unset('nafeza_discount_' . $product_id);
        }
      }
    }
  }
  add_action('woocommerce_before_calculate_totals', 'nafeza_apply_dynamic_discounts', 10, 1);


  // Add discount message next to the price in the cart
  function nafeza_display_discount_message($item_data, $cart_item)
  {
    $product_id = $cart_item['product_id'];
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
  add_filter('woocommerce_get_item_data', 'nafeza_display_discount_message', 10, 2);




  // Add discount table to product pages
  function nafeza_display_discount_table()
  {
    wc_get_template('discount-table.php', array(), '', plugin_dir_path(__FILE__) . '../templates/');
  }
  add_action('woocommerce_single_product_summary', 'nafeza_display_discount_table', 25);
}


function nafeza_get_discount_rules()
{
  $args = array(
    'post_type' => 'nafeza_discount_rule',
    'posts_per_page' => -1,
    'post_status' => 'publish',
  );

  $discount_rules = get_posts($args);
  $rules = array();

  foreach ($discount_rules as $rule) {
    $meta = get_post_meta($rule->ID);
    $rules[] = array(
      'product_id' => $meta['product_id'][0],
      'quantity_from' => $meta['quantity_from'][0],
      'quantity_to' => $meta['quantity_to'][0],
      'discount_type' => $meta['discount_type'][0],
      'discount_value' => $meta['discount_value'][0],
      'priority' => $meta['priority'][0],
    );
  }

  // Sort discount rules by priority
  usort($rules, function ($a, $b) {
    return (int)$a['priority'] - (int)$b['priority'];
  });

  return $rules;
}




add_filter('woocommerce_product_price_class', function ($class) {
  $class .= ' nafeza-single-product-price';
  return $class;
});
