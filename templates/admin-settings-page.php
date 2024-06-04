<div class="wrap">
  <h1>Nafeza Dynamic Discounts</h1>
  <form method="post" action="options.php">
    <?php
    settings_fields('nafeza-discount-settings-group');
    do_settings_sections('nafeza-discount-settings-group');
    $discount_rules = get_option('nafeza_discount_rules', []);
    ?>
    <div id="discount-rules-container">
      <?php if (!empty($discount_rules)) : ?>
      <?php foreach ($discount_rules as $index => $rule) : ?>
      <div class="discount-rule">
        <input type="text" name="nafeza_discount_rules[<?php echo $index; ?>][rule_name]" placeholder="Rule Name"
          value="<?php echo esc_attr($rule['rule_name']); ?>" />
        <input type="text" name="nafeza_discount_rules[<?php echo $index; ?>][discount_label]"
          placeholder="Discount Label" value="<?php echo esc_attr($rule['discount_label']); ?>" />
        <input type="number" name="nafeza_discount_rules[<?php echo $index; ?>][priority]" placeholder="Priority"
          value="<?php echo esc_attr($rule['priority']); ?>" />
        <select class="nafeza-product-select" name="nafeza_discount_rules[<?php echo $index; ?>][product_id]"
          style="width: 100%;">
          <option value="<?php echo esc_attr($rule['product_id']); ?>"><?php echo get_the_title($rule['product_id']); ?>
          </option>
        </select>
        <input type="number" name="nafeza_discount_rules[<?php echo $index; ?>][quantity_from]"
          placeholder="Quantity From" value="<?php echo esc_attr($rule['quantity_from']); ?>" />
        <input type="number" name="nafeza_discount_rules[<?php echo $index; ?>][quantity_to]" placeholder="Quantity To"
          value="<?php echo esc_attr($rule['quantity_to']); ?>" />
        <select name="nafeza_discount_rules[<?php echo $index; ?>][discount_type]">
          <option value="percentage" <?php selected($rule['discount_type'], 'percentage'); ?>>Percentage</option>
          <option value="fixed" <?php selected($rule['discount_type'], 'fixed'); ?>>
            <?php esc_html_e('Fixed', 'nafeza-woocommerce-dynamic-discount'); ?></option>
        </select>
        <input type="number" step="0.01" name="nafeza_discount_rules[<?php echo $index; ?>][discount_value]"
          placeholder="Discount Value" value="<?php echo esc_attr($rule['discount_value']); ?>" />
        <button type="button"
          class="button remove-rule"><?php esc_html_e('Remove', 'nafeza-woocommerce-dynamic-discount'); ?></button>
      </div>
      <hr>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <p><button type="button" class="button button-primary"
        id="add-rule"><?php esc_html_e('Add Rule', 'nafeza-woocommerce-dynamic-discount'); ?></button></p>
    <?php submit_button(); ?>
  </form>
</div>