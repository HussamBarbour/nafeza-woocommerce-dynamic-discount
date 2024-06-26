<?php
/*
Plugin Name: Nafeza WooCommerce Dynamic Discount
Description: A plugin to apply dynamic discounts based on product quantity.
Version: 1.0
Author: Hussam Barbour
Text Domain: nafeza-woocommerce-dynamic-discount
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Load plugin textdomain for translations
function nafeza_load_textdomain()
{
  load_plugin_textdomain('nafeza-woocommerce-dynamic-discount', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'nafeza_load_textdomain');

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/discount-functions.php';

// Enqueue admin styles and scripts
function nafeza_admin_assets($hook)
{
  global $post;

  // Only load assets on specific admin pages
  if (($hook === 'post-new.php' || $hook === 'post.php') && $post->post_type === 'nafeza_discount_rule') {
    wp_enqueue_style('nafeza-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css', array(), time());
    wp_enqueue_script('nafeza-admin-scripts', plugin_dir_url(__FILE__) . 'assets/js/admin-scripts.js', array('jquery'), time(), true);

    // Enqueue Select2 CSS and JS from CDN
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);

    // Localize script for AJAX URL
    wp_localize_script('nafeza-admin-scripts', 'nafezaAdmin', array(
      'ajax_url' => admin_url('admin-ajax.php')
    ));
  }
}
add_action('admin_enqueue_scripts', 'nafeza_admin_assets');

// Enqueue front-end styles and scripts
function nafeza_frontend_assets()
{
  wp_register_script('nafeza-woocommerce-dynamic-discount', plugin_dir_url(__FILE__) . 'assets/js/scripts.js', array('jquery'), time(), true);

  if (is_product()) {
    $product = wc_get_product(get_the_ID());
    $product_id = $product->get_id();

    // Get the translated product ID if WPML is active
    if (defined('ICL_SITEPRESS_VERSION')) {
      $product_id = apply_filters('wpml_object_id', $product_id, 'product', false, wpml_get_default_language());
    }

    $discount_rules = nafeza_get_discount_rules();

    $product_discount_rules = array_filter($discount_rules, function ($rule) use ($product_id) {
      $product_ids = maybe_unserialize($rule['product_id']);
      if (!is_array($product_ids)) {
        $product_ids = array($product_ids); // Ensure $product_ids is always an array
      }
      return in_array($product_id, $product_ids);
    });

    wp_localize_script('nafeza-woocommerce-dynamic-discount', 'nafezaWooDynData', array(
      'discount_rules' => array_values($product_discount_rules), // Reindex array to avoid JS issues
      'original_price' => $product->get_price(),
      'currency_symbol' => get_woocommerce_currency_symbol(),
      'number_of_decimals' => wc_get_price_decimals(),
    ));

    wp_enqueue_script('nafeza-woocommerce-dynamic-discount');
  }

  wp_enqueue_style('nafeza-frontend-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time());
}
add_action('wp_enqueue_scripts', 'nafeza_frontend_assets');
