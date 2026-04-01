<div class="panel">
  <div class="panel-heading">
    <i class="icon-list"></i>
    {l s='SMS Approvals' mod='ecsmsapproval'}
    <span class="badge">{$total}</span>
    <a href="{$export_url|escape:'html':'UTF-8'}" class="btn btn-default btn-sm pull-right">
      <i class="icon-download"></i> {l s='Export CSV' mod='ecsmsapproval'}
    </a>
  </div>

  <div class="panel-body">

    {if $rows}
      <table class="table">
        <thead>
          <tr>
            <th>{l s='ID' mod='ecsmsapproval'}</th>
            <th>{l s='Customer' mod='ecsmsapproval'}</th>
            <th>{l s='Email' mod='ecsmsapproval'}</th>
            <th>{l s='Guest' mod='ecsmsapproval'}</th>
            <th>{l s='Approval' mod='ecsmsapproval'}</th>
            <th>{l s='Date' mod='ecsmsapproval'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $rows as $row}
            <tr>
              <td>{$row.id_customer|intval}</td>
              <td>{$row.customer_name|escape:'html':'UTF-8'}</td>
              <td>{$row.email|escape:'html':'UTF-8'}</td>
              <td>
                {if $row.is_guest}
                  <span class="label label-warning">{l s='Yes' mod='ecsmsapproval'}</span>
                {else}
                  <span class="label label-default">{l s='No' mod='ecsmsapproval'}</span>
                {/if}
              </td>
              <td>
                {if $row.approval}
                  <span class="label label-success">{l s='Approved' mod='ecsmsapproval'}</span>
                {else}
                  <span class="label label-danger">{l s='Refused' mod='ecsmsapproval'}</span>
                {/if}
              </td>
              <td>{$row.date_upd|escape:'html':'UTF-8'}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      {if $total_pages > 1}
        <ul class="pagination">
          {for $p = 1 to $total_pages}
            <li{if $p == $page} class="active"{/if}>
              <a href="{$base_url|escape:'html':'UTF-8'}&page={$p}">{$p}</a>
            </li>
          {/for}
        </ul>
      {/if}

    {else}
      <div class="alert alert-info">
        {l s='No record found.' mod='ecsmsapproval'}
      </div>
    {/if}

  </div>
</div>
