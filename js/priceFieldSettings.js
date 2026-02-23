CRM.$(function ($) {
  // Moves "Allow other amounts" setting below is active option on the Price Field Edit/Create Form
  $('.crm-price-field-block-otheramount').insertAfter('.crm-price-field-form-block-is_active');
  $('.deleteme').remove();
});
