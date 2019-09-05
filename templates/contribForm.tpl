{foreach from=$elementNames item=elementName}
  <div class="crm-section otheramount">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}
