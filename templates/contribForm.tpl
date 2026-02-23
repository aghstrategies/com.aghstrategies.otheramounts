{foreach from=$otherAmounts item=otherAmount}
  <div class="crm-section otheramount" style="clear: both;">
    <div class="label">{$form.$otherAmount.label}</div>
    <div class="content">{$form.$otherAmount.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}
