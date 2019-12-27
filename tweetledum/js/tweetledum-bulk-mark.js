(function ($) {

  $('#bulk-toggle').change(function(e) {
    let $checkboxes = $('#bulk-mark-read .checkbox input');
    if (this.checked) {
      $checkboxes.prop('checked', true);
    } else {
      $checkboxes.prop('checked', false);
    }
  });

})(jQuery);
