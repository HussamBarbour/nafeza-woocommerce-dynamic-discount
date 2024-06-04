jQuery(document).ready(function ($) {
  // Initialize select2 for existing product selects
  function initializeSelect2() {
    $('.nafeza-product-select').select2({
      ajax: {
        url: ajaxurl,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term, // search term
            action: 'nafeza_search_products'
          };
        },
        processResults: function (data) {
          return {
            results: data
          };
        },
        cache: true
      },
      minimumInputLength: 3,
      templateResult: formatProduct,
      templateSelection: formatProductSelection
    });
  }

  function formatProduct(product) {
    if (product.loading) return product.text;
    return product.title;
  }

  function formatProductSelection(product) {
    return product.title || product.text;
  }

  // Initialize select2 for existing rules
  initializeSelect2();

  $('#add-rule').on('click', function () {
    var container = $('#discount-rules-container');
    var index = container.find('.discount-rule').length;
    var newRule = $('<div class="discount-rule">' +
      '<input type="text" name="nafeza_discount_rules[' + index + '][rule_name]" placeholder="Rule Name" />' +
      '<input type="text" name="nafeza_discount_rules[' + index + '][discount_label]" placeholder="Discount Label" />' +
      '<input type="number" name="nafeza_discount_rules[' + index + '][priority]" placeholder="Priority" />' +
      '<select class="nafeza-product-select" name="nafeza_discount_rules[' + index + '][product_id]" style="width: 100%;"></select>' +
      '<input type="number" name="nafeza_discount_rules[' + index + '][quantity_from]" placeholder="Quantity From" />' +
      '<input type="number" name="nafeza_discount_rules[' + index + '][quantity_to]" placeholder="Quantity To" />' +
      '<select name="nafeza_discount_rules[' + index + '][discount_type]"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select>' +
      '<input type="number" step="0.01" name="nafeza_discount_rules[' + index + '][discount_value]" placeholder="Discount Value" />' +
      '<button type="button" class="button remove-rule">Remove</button>' +
      '</div><hr>');
    container.append(newRule);

    // Initialize select2 for the new rule
    initializeSelect2();
  });

  $(document).on('click', '.remove-rule', function () {
    $(this).closest('.discount-rule').remove();
  });
});
