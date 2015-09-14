<table class="grid registry">
    <thead>
    <tr>
        <th class="time">Дата/Час</th>
        <th>Виконавець</th>
        <th>Текст</th>
        <th>Зображення</th>
    </tr>
    </thead>
    {foreach from=$items item=item name=current}
        <tr class="{if $smarty.foreach.current.iteration % 2}odd{else}even{/if}{if $smarty.foreach.current.last} last{/if}{if $smarty.foreach.current.first} first{/if}">
            <td class="time">{$item.datetime|escape}</td>
            <td>{$item.person|escape}</td>
            <td>{$item.description}</td>
            <td>
                {if $item.image_name}
                    {assign var="registry_image" value=$smarty.const.SITE_ROOT|cat:"media/registry/"|cat:$item.image_name}
                <a href="{$smarty.const.SOVA_BASE_URL}media/registry/{$item.image_name}"
                   title="{$item.datetime|escape} - {$item.person|escape}"
                   target="_blank"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                    img=$registry_image chain="host-registry-thumb" output="url"
                    outputformat="jpeg"
                    }" alt="{$item.datetime|escape} - {$item.person|escape}"></a>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>
<script type="text/javascript">
    $(document).ready(function () {
        $(".registry a").attr('rel', 'registry-gallery').fancybox({
            prevEffect: 'none',
            nextEffect: 'none',
            closeBtn: true,
            helpers: {
                title: { type: 'outside' }
            }
        });
    });
</script>