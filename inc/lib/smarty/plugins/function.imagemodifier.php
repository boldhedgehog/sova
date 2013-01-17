<?php
/*
 * --------------------------------------------------------------------------
 *     E Y E F I                                         http://www.eyefi.nl/
 * --------------------------------------------------------------------------
 *     O P E N  S O U R C E                       http://opensource.eyefi.nl/
 * --------------------------------------------------------------------------
 *
 * Project: ImageModifier
 *
 * Version: 0.4
 * Release date: April 21, 2006
 *
 * Library homepage:
 * http://opensource.eyefi.nl/eyefi-imgfilter/
 *
 * Source available at:
 * http://www.sourceforge.net/projects/eyefi-imgfilter
 *
 * Copyright 2003-2006 by Eyefi Interactive,
 * http://www.eyefi.nl
 *
 * Author:
 *   Arjan Scherpenisse
 *
 * Contributors:
 *   Tny Wizzkid
 *   Mikan Huppertz
 *   Gijs Kunze
 *   Joost Lubach
 *
 * --------------------------------------------------------------------------
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------------
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     assign
 * Purpose:  assign a value to a template variable
 * -------------------------------------------------------------
 */
/**
 * @param $params
 * @param $smarty SovaSmarty
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_imagemodifier($params, &$smarty)
{
    foreach (array("chain", "img", "prefix", "format", "text", "extraparams", "outputformat", "over_chain", "output", "alt") as $param) {
        if (isset($params[$param])) {
            $$param = $params[$param];
        } else {
            $$param = null;
        }

        unset($params[$param]);
    }

    $opts = $params;

    // params:
    // chain, img (require()
    // prefix (optional)
    // extraparams (optional, format k=v,k=v)    
    if (empty($chain)) {
        throw new SmartyException("imagemodifier: missing 'chain' parameter");
    }

    if (empty($img) && empty($text)) {
        throw new SmartyException("imagemodifier: missing 'img' or 'text' parameter");
    }

    $xtra = false;
    if (!empty($extraparams)) {
        $xtra = array();
        foreach (explode(",", $extraparams) as $d) {
            list($k, $v) = explode("=", $d);
            $xtra[$k] = $v;
        }
    }

    if (empty($output)) {
        $output = "tag";
    }

    require_once(LIBRARY_PATH . "imgfilter.inc.php");
    $modifier = new Imagemodifier($chain, $format, $xtra);

    if (isset($over_chain))
        $overmod = new Imagemodifier($over_chain, $format, $xtra);


    if (isset($img)) {
        // if we have a prefix, prepend it to $img.
        if (!empty($prefix))
            $img = $prefix . $img;


        // if image is relative, and not an url, put DATA_PATH before it
        if ($img{0} != "/" && !preg_match("/^\w+:\/\//", $img) && defined("DATA_PATH")) {
            $img = DATA_PATH . $img;
        }

        $modifier->load($img);
        if (isset($over_chain)) $overmod->load($img);
    } else {
        // text
        $modifier->loadText($text);
        if (isset($over_chain)) $overmod->loadText($text);
    }

    if (isset($over_chain))
        $opts["eb__src_mouseover"] = "/imgcache/" . $overmod->getRelativeFileName();

    if (!empty($outputformat))
        $modifier->_outputformat = $outputformat;

    switch ($output) {
        case "url":
            return IMGCACHE_PATH . $modifier->getRelativeFileName();

        case "tag":
            return $modifier->getTag($opts);
    }

    return '';
}
