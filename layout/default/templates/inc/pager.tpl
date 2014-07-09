{assign var=pages value=$totalRows/$pageSize+1}
{assign var=pages value=$pages|intval}
{strip}
{if ($pages > 1)}
    {assign var=get value=$smarty.get}
    {assign var=predots value=true}
    {assign var=postdots value=true}
    <ol class="pager">
        {if $currentPage == 1}
            <li class="current">1</li>
        {else}
            <li><a href="?{$get|pager_url:1:lp}">1</a></li>
        {/if}
        {section name=pager start=2 loop=$pages}
            {assign var=index value=$smarty.section.pager.index}
            {if ($currentPage - $index) > 3}
                {if ($predots)}
                    {assign var=predots value=false}
                    <li class="dots">...</li>
                {/if}
                {continue}
            {/if}
            {if ($index - $currentPage) > 3}
                {if ($postdots)}
                    {assign var=postdots value=false}
                    <li class="dots">...</li>
                {/if}
                {continue}
            {/if}
            {if $smarty.section.pager.index eq $currentPage}
                <li class="current">{$currentPage}</li>
                {else}
                <li><a href="?{$get|pager_url:$index:lp}">{$index}</a></li>
            {/if}
        {/section}
        {if $currentPage == $pages}
            <li class="current">{$pages}</li>
        {else}
            <li><a href="?{$get|pager_url:$pages:lp}">{$pages}</a></li>
        {/if}
    </ol>
{/if}
{/strip}