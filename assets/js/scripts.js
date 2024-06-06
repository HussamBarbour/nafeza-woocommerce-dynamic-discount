jQuery(document).ready(function () {
  if (typeof nafezaWooDynData !== 'undefined') {
    var nafeza_discount_rules = nafezaWooDynData.discount_rules;
  }
  if (nafeza_discount_rules.length > 0) {
    jQuery(document).on('change', 'input.qty', function () {
      var newQuantity = parseInt(jQuery(this).val(), 10);

      var productID = jQuery('input[name="product_id"]').val(); // Get the product ID from the hidden input field
      var $priceContainer = jQuery('.nafeza-single-product-price .woocommerce-Price-amount bdi');
      var originalPrice = parseFloat(nafezaWooDynData.original_price);
      var numberOfDecimals = parseInt(nafezaWooDynData.number_of_decimals, 10);

      // Find the relevant discount rule for the current product and quantity
      var discount = null;
      nafeza_discount_rules.forEach(function (rule) {
        if (rule.product_id == productID && newQuantity >= rule.quantity_from && newQuantity <= rule.quantity_to) {
          discount = rule;
        }
      });

      if (discount) {
        var discountValue = parseFloat(discount.discount_value);
        var discountedPrice;

        if (discount.discount_type === 'fixed') {
          discountedPrice = originalPrice - discountValue;
        } else if (discount.discount_type === 'percentage') {
          discountedPrice = originalPrice - (originalPrice * (discountValue / 100));
        }

        // Update the price display
        $priceContainer.html(discountedPrice.toFixed(numberOfDecimals) + ` <span class="woocommerce-Price-currencySymbol"> ${nafezaWooDynData.currency_symbol}</span>`);
      } else {
        // Reset to original price if no discount applies
        $priceContainer.html(originalPrice.toFixed(numberOfDecimals) + ` <span class="woocommerce-Price-currencySymbol"> ${nafezaWooDynData.currency_symbol}</span>`);
      }
    });
  }

});
