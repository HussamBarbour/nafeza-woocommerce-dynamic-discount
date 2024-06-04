<?php

// Add admin menu for discount rules
add_action('admin_menu', 'nafeza_add_admin_menu');
add_action('admin_init', 'nafeza_register_settings');

function nafeza_add_admin_menu()
{
  add_submenu_page(
    'woocommerce',
    'Nafeza Dynamic Discounts',
    'Dynamic Discounts',
    'manage_options',
    'nafeza-dynamic-discounts',
    'nafeza_discount_rules_page'
  );
}

function nafeza_register_settings()
{
  register_setting('nafeza-discount-settings-group', 'nafeza_discount_rules');
}

function nafeza_discount_rules_page()
{
  include plugin_dir_path(__FILE__) . '../templates/admin-settings-page.php';
}


add_action('wp_ajax_nafeza_search_products', 'nafeza_search_products');

function nafeza_search_products()
{
  $term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

  $args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    's' => $term,
    'post_status' => 'publish'
  );

  $products = get_posts($args);
  $results = array();

  foreach ($products as $product) {
    $results[] = array(
      'id' => $product->ID,
      'title' => $product->post_title
    );
  }

  wp_send_json($results);
}
