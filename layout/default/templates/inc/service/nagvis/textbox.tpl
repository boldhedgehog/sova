<div class="nagvisTextBox" style="top:{$service.y}px;left:{$service.x}px;width:{$service.w}px;height:{$service.h}px;
{if isset($service['z']) and $service['z'] neq ''}z-index:{$service.z|escape};{/if}
{if isset($service['background_color']) and $service['background_color'] neq ''}background-color:{$service.background_color|escape};{/if}
{if isset($service['border_color']) and $service['border_color'] neq ''}border-color:{$service.border_color|escape};{/if}
        {if isset($service['style']) and $service['style'] neq ''}border-color:{$service.style|nl2br|escape};{/if}">
    {$service.text|escape}
</div>