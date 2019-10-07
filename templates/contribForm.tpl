{foreach from=$otherAmounts item=otherAmount}
  <div class="crm-section otheramount">
    <div class="lab">{$form.$otherAmount.label}</div>
    <div class="content">{$form.$otherAmount.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}
