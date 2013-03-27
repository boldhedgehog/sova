{assign var=logItems value=$logItems|default:$nagiosObject.nagiosLog}
{*if $append}
<tr>
<th colspan="4" class="time-delimiter">{$fromTime|date_format:'%Y-%m-%d'}</th>
</tr>
{/if*}
{strip}
{foreach from=$logItems item=log name=log}
<tr class="log-row state{$log.state}">
    <td class="line-number">{$smarty.foreach.log.iteration}</td>
    <td class="time">{$log.time|date_format:'%Y-%m-%d %H:%M:%S'}</td>
    <td>{if $log.service_notes}{$log.service_notes|escape}{else}{$log.service_description|escape}{/if}</td>
    <td class="state">{$log.state_text}</td>
    <td>{$log.plugin_output|escape}</td>
</tr>
{foreachelse}
<tr>
<th colspan="5" class="time-delimiter">Записи відсутні</th>
</tr>
{/foreach}
{/strip}