{if $ecsms_approval === null}
  <span id="ecsms-approval-badge" class="badge badge-secondary rounded" style="display:none;">
    <i class="material-icons" style="font-size:1rem;vertical-align:middle;">sms</i>
    SMS : Aucune réponse
  </span>
{elseif $ecsms_approval}
  <span id="ecsms-approval-badge" class="badge badge-success rounded" style="display:none;">
    <i class="material-icons" style="font-size:1rem;vertical-align:middle;">sms</i>
    SMS : Approuvé
  </span>
{else}
  <span id="ecsms-approval-badge" class="badge badge-danger rounded" style="display:none;">
    <i class="material-icons" style="font-size:1rem;vertical-align:middle;">sms</i>
    SMS : Refusé
  </span>
{/if}
