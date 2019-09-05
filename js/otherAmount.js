CRM.$(function ($) {
  var otherFields = CRM.vars.otheramounts.otherFields;

  $.each(otherFields, function(priceFieldId, priceOptionId) {
    var $fieldName = '#other_amount_' + priceFieldId;
    var $otherAmountDiv = $('#other_amount_' + priceFieldId).parent().parent();

    // Put other amount fields under their related price fields
    $($otherAmountDiv).insertAfter($('[name^="price_' + priceFieldId + '"]').parent().parent().parent().last());

    var inputId = '#' + $('[name="price_' + priceFieldId + '"][value="' + priceOptionId + '"]').attr('id');
    var priceField = 'price_' + priceFieldId;

    var showOtherAmountBox = function () {
      if ($('input[name=' + priceField + ']:checked').val() == $(inputId).val()) {
        $otherAmountDiv.css('display', 'block');
      } else {
        $otherAmountDiv.css('display', 'none');
        $('input#other_amount_' + priceFieldId).val('');
      }
    };

    showOtherAmountBox();
    $('input[name=' + priceField + ']').change(showOtherAmountBox);

    var calculateTotalWithOtherAmount = function () {
      if ($('input[name=' + priceField + ']:checked').val() == $(inputId).val()) {
        var a = 0;
        if (parseFloat($('input' + $fieldName).val()) > 0) {
          var a = parseFloat($('input' + $fieldName).val());
        }
        $('input[name=' + priceField + ']:checked').attr('price', '["' + priceField + '", "' + a + '||"]');
        $('input[name=' + priceField + ']:checked').trigger('click');
      }
    };

    calculateTotalWithOtherAmount();
    $('input[name^=other_amount_' + priceFieldId + ']').keyup(calculateTotalWithOtherAmount);
    $('input[name^="price_' + priceFieldId + '"]').change(calculateTotalWithOtherAmount);
  });
});
