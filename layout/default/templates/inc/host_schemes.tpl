<ul class="schemes">
    <li>
        <h3>{$host.alias|escape}</h3>
        <div>
            <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$host.scheme_image_name}" target="_blank">
                {*<img src="{$smarty.const.SOVA_BASE_URL}media/scheme/{$host.scheme_image_name}" alt="Схема ПНО">*}
                {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$host.scheme_image_name}
                <img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                    img=$imagefile chain="scheme640" output="url"
                    outputformat="jpeg"
                    }" alt="{$host.name|escape}">
            </a>
        </div>
    </li>
{foreach from=$host.zones item=item}
    {if $item.scheme_image_name}
    <li>
        <h3>{$item.plas_zone_id|escape} - {$item.name|escape}</h3>
        <div>
            <a href="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}" target="_blank">
                {*<img src="{$smarty.const.SOVA_BASE_URL}media/scheme/{$item.scheme_image_name}" alt="{$item.name|escape}">*}
                {assign var="imagefile" value=$smarty.const.SITE_ROOT|cat:"media/scheme/"|cat:$item.scheme_image_name}
                <img src="{$smarty.const.SOVA_BASE_URL}{imagemodifier
                    img=$imagefile chain="scheme640" output="url"
                    outputformat="jpeg"
                    }" alt="{$item.name|escape}">
            </a>
        </div>
    </li>
    {/if}
{/foreach}
</ul>