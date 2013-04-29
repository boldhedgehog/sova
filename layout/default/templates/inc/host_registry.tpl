<table class="grid">
    <thead>
    <tr>
        <th class="time">Дата/Час</th>
        <th>Виконавець</th>
        <th>Текст</th>
    </tr>
    </thead>
    {foreach from=$items item=item name=current}
        <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
            <td class="time">{$item.datetime|escape}</td>
            <td>{$item.person|escape}</td>
            <td>{$item.description|escape}</td>
        </tr>
    {/foreach}
</table>