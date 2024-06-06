<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;
$product_id = $product->get_id();

// Get the translated product ID if WPML is active
if (defined('ICL_SITEPRESS_VERSION')) {
    $product_id = apply_filters('wpml_object_id', $product_id, 'product', false, wpml_get_default_language());
}

$regular_price = floatval($product->get_price()); // Ensure regular price is a float
$discount_rules = nafeza_get_discount_rules(); // Fetch discount rules from custom post type

$rules_for_product = array_filter($discount_rules, function ($rule) use ($product_id) {
    return $rule['product_id'] == $product_id;
});

if (!empty($rules_for_product)) {
    usort($rules_for_product, function ($a, $b) {
        return (int)$a['priority'] - (int)$b['priority'];
    });

    echo '<div class="nafeza-discount-container">';
    echo '<table class="nafeza-discount-table">';
    echo '<thead><tr><th>' . esc_html__('Quantity', 'nafeza-woocommerce-dynamic-discount') . '</th><th>' . esc_html__('Discount', 'nafeza-woocommerce-dynamic-discount') . '</th><th>' . esc_html__('Price After Discount', 'nafeza-woocommerce-dynamic-discount') . '</th></tr></thead>';
    echo '<tbody>';
    foreach ($rules_for_product as $rule) {
        $quantity_range = esc_html($rule['quantity_from'] . ' - ' . $rule['quantity_to']);
        $discount_value = $rule['discount_type'] == 'percentage' ? esc_html($rule['discount_value'] . '%') : wc_price(floatval($rule['discount_value']));

        // Calculate price after discount
        if ($rule['discount_type'] == 'percentage') {
            $discount_amount = $regular_price * (floatval($rule['discount_value']) / 100);
        } else {
            $discount_amount = floatval($rule['discount_value']);
        }

        // Ensure discount amount does not exceed regular price
        $price_after_discount = $regular_price - $discount_amount;

        // Format price after discount
        $price_after_discount = wc_price($price_after_discount);

        echo '<tr><td>' . $quantity_range . '</td><td>' . $discount_value . '</td><td>' . $price_after_discount . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
