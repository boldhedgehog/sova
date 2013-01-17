{if $notifications}
<ul class="notifications">
{foreach from=$notifications item=notification}
<li class="notification {$notification.type}">{$notification.message}</li>
{/foreach}
</ul>
{/if}
