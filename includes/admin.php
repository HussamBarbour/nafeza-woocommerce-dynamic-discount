<?php


function nafeza_register_discount_post_type()
{
  register_post_type(
    'nafeza_discount_rule',
    array(
      'labels' => array(
        'name' => __('Discount Rules'),
        'singular_name' => __('Discount Rule')
      ),
      'public' => false,
      'publicly_queryable' => false,
      'show_ui' => true,
      'show_in_menu' => 'woocommerce',
      'has_archive' => false, // No archive page
      'rewrite' => false, // No URL rewriting
      'supports' => array('title'),
    )
  );
}
add_action('init', 'nafeza_register_discount_post_type');





function nafeza_add_discount_rule_meta_boxes()
{
  add_meta_box(
    'nafeza_discount_rule_meta_box', // $id
    __('Discount Rule Details'), // $title
    'nafeza_display_discount_rule_meta_box', // $callback
    'nafeza_discount_rule', // $screen
    'normal', // $context
    'high' // $priority
  );
}
add_action('add_meta_boxes', 'nafeza_add_discount_rule_meta_boxes');

function nafeza_display_discount_rule_meta_box($post)
{
  $meta = get_post_meta($post->ID);
  $product_ids = get_post_meta($post->ID, 'product_ids', true);

  $selected_products = isset($product_ids) ? maybe_unserialize($product_ids) : [];
  $selected_products = is_array($selected_products) ? $selected_products : [];
?>
<div class="nafeza-rule-options">
  <p>
    <label for="discount_label"><?php _e('Discount Label:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <input type="text" name="discount_label" value="<?php echo esc_attr($meta['discount_label'][0] ?? ''); ?>" />
  </p>
  <p>
    <label for="priority"><?php _e('Priority:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <input type="number" name="priority" value="<?php echo esc_attr($meta['priority'][0] ?? ''); ?>" />
  </p>
  <p>
    <label for="product_ids"><?php _e('Products:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <select name="product_ids[]" class="nafeza-product-select" style="width: 100%;" multiple>
      <?php
      foreach ($selected_products as $product_id) {
        $product = get_post($product_id);
        if ($product) {
          echo '<option value="' . $product->ID . '" selected>' . get_the_title($product->ID) . '</option>';
        }
      }
      ?>
    </select>
  </p>
  <p>
    <label for="quantity_from"><?php _e('Quantity From:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <input type="number" name="quantity_from" value="<?php echo esc_attr($meta['quantity_from'][0] ?? ''); ?>" />
  </p>
  <p>
    <label for="quantity_to"><?php _e('Quantity To:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <input type="number" name="quantity_to" value="<?php echo esc_attr($meta['quantity_to'][0] ?? ''); ?>" />
  </p>
  <p>
    <label for="discount_type"><?php _e('Discount Type:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <select name="discount_type">
      <option value="percentage" <?php selected($meta['discount_type'][0] ?? '', 'percentage'); ?>>Percentage</option>
      <option value="fixed" <?php selected($meta['discount_type'][0] ?? '', 'fixed'); ?>>Fixed</option>
    </select>
  </p>
  <p>
    <label for="discount_value"><?php _e('Discount Value:', 'nafeza-woocommerce-dynamic-discount'); ?></label>
    <input type="number" step="0.01" name="discount_value"
      value="<?php echo esc_attr($meta['discount_value'][0] ?? ''); ?>" />
  </p>
</div>
<?php
}




function nafeza_save_discount_rule_meta($post_id)
{
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!isset($_POST['discount_label']) || !isset($_POST['priority']) || !isset($_POST['product_ids']) || !isset($_POST['quantity_from']) || !isset($_POST['quantity_to']) || !isset($_POST['discount_type']) || !isset($_POST['discount_value'])) return;

  update_post_meta($post_id, 'discount_label', sanitize_text_field($_POST['discount_label']));
  update_post_meta($post_id, 'priority', intval($_POST['priority']));
  update_post_meta($post_id, 'product_ids', maybe_serialize(array_map('intval', $_POST['product_ids'])));
  update_post_meta($post_id, 'quantity_from', intval($_POST['quantity_from']));
  update_post_meta($post_id, 'quantity_to', intval($_POST['quantity_to']));
  update_post_meta($post_id, 'discount_type', sanitize_text_field($_POST['discount_type']));
  update_post_meta($post_id, 'discount_value', floatval($_POST['discount_value']));
}
add_action('save_post', 'nafeza_save_discount_rule_meta');




function nafeza_search_products()
{
  $term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

  $args = array(
    'post_type' => 'product',
    'posts_per_page' => 10,
    's' => $term,
    'post_status' => 'publish'
  );

  $products = get_posts($args);
  $results = array();

  foreach ($products as $product) {
    $results[] = array(
      'id' => $product->ID,
      'text' => $product->post_title
    );
  }

  wp_send_json($results);
}
add_action('wp_ajax_nafeza_search_products', 'nafeza_search_products');