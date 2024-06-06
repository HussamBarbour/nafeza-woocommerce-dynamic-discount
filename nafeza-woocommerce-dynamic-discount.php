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
function nafeza_admin_assets()
{
  wp_enqueue_style('nafeza-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css');
  wp_enqueue_script('nafeza-admin-scripts', plugin_dir_url(__FILE__) . 'assets/js/admin-scripts.js', array('jquery'), time(), true);


  // Enqueue Select2 CSS and JS from CDN
  wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
  wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'nafeza_admin_assets');

// Enqueue front-end styles
function nafeza_frontend_assets()
{

  wp_register_script('nafeza-woocommerce-dynamic-discount', plugin_dir_url(__FILE__) . 'assets/js/scripts.js', array('jquery'), time(), true);

  if (is_product()) {
    $product = wc_get_product(get_the_ID());
    $discount_rules = nafeza_get_discount_rules();

    $product_discount_rules = array_filter($discount_rules, function ($product_discount_rule) {
      return isset($product_discount_rule['product_id']) && $product_discount_rule['product_id'] ==  get_the_ID();
    });

    wp_localize_script('nafeza-woocommerce-dynamic-discount', 'nafezaWooDynData', array(
      'discount_rules' => json_encode($product_discount_rules),
      'original_price' => $product->get_price(),
      'currency_symbol' => get_woocommerce_currency_symbol(),
      'number_of_decimals' => wc_get_price_decimals(),
    ));

    wp_enqueue_script('nafeza-woocommerce-dynamic-discount');
  }
  wp_enqueue_style('nafeza-frontend-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time());
}
add_action('wp_enqueue_scripts', 'nafeza_frontend_assets');
