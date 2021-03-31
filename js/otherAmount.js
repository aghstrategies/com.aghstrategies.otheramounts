CRM.$(function ($) {
   $('input#group_10273').parent().hide();
   $('input#group_10163').parent().hide();
   $('input#group_10166').parent().hide();

   $('label[for="discountcode"]').text("Presenters, enter your discount code here"); 
  var otherFields = CRM.vars.otheramounts.otherFields;

  $.each(otherFields, function(priceFieldId, priceOptionId) {
    // id of the box being used for other amounts for this field
    var $otherBox = '#other_amount_' + priceFieldId;

    // shorcut for the price name
    var priceField = 'price_' + priceFieldId;
    var inputId = '#' + $('[name="price_' + priceFieldId + '"][value="' + priceOptionId + '"]').attr('id');

    // Put other amount fields under their related price fields
    $($otherBox).parent().parent().insertAfter($('[name^="price_' + priceFieldId + '"]').parent().parent().parent().last());


    // Hide other amount option just rely on the box
    // $(inputId).parent().hide();

    // Use the other amount option which is hidden
    var useOtherField = function () {
      $(inputId).prop('checked', true);
      $(inputId).trigger('change');
      $(inputId).trigger('click');
    };

   // useOtherField();
    $($otherBox).keyup(useOtherField);
    $($otherBox).click(useOtherField);

    // Update the Total Amount 
    var calculateTotalWithOtherAmount = function () {
      if ($('input[name=' + priceField + ']:checked').val() == $(inputId).val()) {
        var a = 0;
        if (parseFloat($('input' + $otherBox).val()) > 0) {
          var a = parseFloat($('input' + $otherBox).val());
        }
        $('input[name=' + priceField + ']:checked').attr('price', '["' + priceField + '", "' + a + '||"]');
        $('input[name=' + priceField + ']:checked').trigger('click');
      }
      else {
        $('input#other_amount_' + priceFieldId).val('');
      }
    };

    calculateTotalWithOtherAmount();
    $('input[name^=other_amount_' + priceFieldId + ']').keyup(calculateTotalWithOtherAmount);
    $('input[name^=' + priceField + ']').change(calculateTotalWithOtherAmount);
  });

});
