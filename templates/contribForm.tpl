{foreach from=$otherAmounts item=otherAmount}
  <div class="crm-section otheramount">
    {* TODO: Should this class be `label` rather than `lab`? *}
    <div class="content">{$form.$otherAmount.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}
