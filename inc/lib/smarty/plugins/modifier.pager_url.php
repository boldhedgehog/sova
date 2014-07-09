<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.pager_url.php
 * Type:     modifier
 * Name:     pager_url
 * Purpose:  Set page query parameter in get and return string with GET query
 * Example:  {$var|@pager_url:$page:$paramName}
 * -------------------------------------------------------------
 */
function smarty_modifier_pager_url(&$params, $page, $paramName = 'p')
{
    $result = array_merge($params, array($paramName => $page));
    return http_build_query($result);
}
