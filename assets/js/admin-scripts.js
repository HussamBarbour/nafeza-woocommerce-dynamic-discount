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
    });
  }

  initializeSelect2();


});

