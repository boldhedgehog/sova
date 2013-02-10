{assign var=nagiosServices value=$host.services_with_state}
{if $nagiosServices}
<ul>
{foreach name=services from=$nagiosServices item=service}
    <li id="service{$service.md5}"
        title="{if isset($service[0])}{$service[0]}{else}{$service.service_description}{/if}"
        class="service bgServiceState{if isset($service[1])}{$service[1]}{else}{$service.state}{/if}"
        service-key="{if isset($host['db_data']) && $host.db_data.host_id}{$host.db_data.host_id}{else}{$host.name}{/if}:{$service.service_description}">
    &nbsp;</li>
{/foreach}
</ul>
{/if}