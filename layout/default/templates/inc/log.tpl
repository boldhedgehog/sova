{assign var=logItems value=$logItems|default:$nagiosObject.nagiosLog}
{*if $append}
<tr>
<th colspan="4" class="time-delimiter">{$fromTime|date_format:'%Y-%m-%d'}</th>
</tr>
{/if*}
{strip}
    <tr class="pager">
        <th colspan="5">{include file="inc/pager.tpl" totalRows=$nagiosObject.nagiosLogTotalRows
            currentPage=$nagiosObject.nagiosLogCurrentPage pageSize=$nagiosObject.nagiosLogPageSize}</th>
    </tr>
    {foreach from=$logItems item=log name=log}
        {assign var=lineNumber value=$smarty.foreach.log.iteration+($nagiosObject.nagiosLogCurrentPage-1)*$nagiosObject.nagiosLogPageSize}
        <tr class="log-row state{$log.state}">
            <td class="line-number">{$lineNumber}</td>
            <td class="time">{$log.time|date_format:'%Y-%m-%d %H:%M:%S'}</td>
            <td>{if $log.service_notes}{$log.service_notes|escape}{else}{$log.service_description|escape}{/if}</td>
            <td class="state">{$log.state_text}</td>
            <td>{$log.plugin_output|escape}</td>
            <td class="last">{if isset($log['duration'])}<span title="{$log.duration|human_interval}">{$log.duration|human_interval:true}</span>{else}
                    <span title="{(time() - $log.time)|human_interval}">{(time() - $log.time)|human_interval:true}</span>{/if}</td>
        </tr>
        {foreachelse}
        <tr>
            <th colspan="5" class="time-delimiter">Записи відсутні</th>
        </tr>
    {/foreach}
    <tr class="pager">
        <th colspan="5">{include file="inc/pager.tpl" totalRows=$nagiosObject.nagiosLogTotalRows
            currentPage=$nagiosObject.nagiosLogCurrentPage pageSize=$nagiosObject.nagiosLogPageSize}</th>
    </tr>
{/strip}
