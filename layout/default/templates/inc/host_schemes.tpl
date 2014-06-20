{assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$host.scheme_image_name}
<ul class="schemes">
    <li>
        <h3>{$host.alias|escape}</h3>
        <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$host.scheme_image_name}" title="{$host.name|escape}"
           target="_blank"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$imagefile chain="host-scheme-thumb" output="url"
                outputformat="jpeg"
                }" alt="{$host.name|escape}"></a>
    </li>
{foreach from=$host.zones item=item}
    {if $item.scheme_image_name}
    {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$item.scheme_image_name}
    <li>
        <h3>{$item.plas_zone_id|escape} - {$item.name|escape}</h3>
        <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}" title="{$item.name|escape}"
           target="_blank"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$imagefile chain="host-scheme-thumb" output="url"
                outputformat="jpeg"
                }" alt="{$item.name|escape}"></a>
    </li>
    {/if}
{/foreach}
{foreach from=$host.notification_devices item=item}
    {if $item.scheme_image_name}
        {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$item.scheme_image_name}
        <li>
            <h3>{$item.plas_zone_id|escape} - {$item.name|escape}</h3>
            <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}"
               title="{$item.name|escape}"
               target="_blank"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$imagefile chain="host-scheme-thumb" output="url"
                outputformat="jpeg"
                }" alt="{$item.name|escape}"></a>
        </li>
    {/if}
{/foreach}

{foreach from=$host.services item=item}
    {if $item.scheme_image_name}
    {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$item.scheme_image_name}
    <li>
        <h3>{$item.nagios_name|escape} - {$item.alias|escape}</h3>
        <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}" title="{$item.zone.name|escape} - {if $item.communication_device.logical_number}{$item.communication_device.logical_number}/{/if}{$item.position}:{$item.sensor_type.name|escape}"
           target="_blank"><img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                img=$imagefile chain="host-scheme-thumb" output="url"
                outputformat="jpeg"
                }" alt="{$item.zone.name|escape} - {if $item.communication_device.logical_number}{$item.communication_device.logical_number}/{/if}{$item.position}:{$item.sensor_type.name|escape}"></a>
    </li>
    {/if}
{/foreach}
</ul>
<script type="text/javascript">
    $(document).ready(function() {
        $(".schemes a").attr('rel', 'gallery').fancybox({
            prevEffect		: 'none',
            nextEffect		: 'none',
            closeBtn		: true,
            helpers		: {
                title	: { type : 'outside' }
            }
        });
    });
</script>