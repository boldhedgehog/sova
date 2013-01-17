<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.human_interval.php
 * Type:     modifier
 * Name:     human_interval
 * Purpose:  Convert interval from UNIX timestamp to human readable string
 * Example:  {$var|human_interval:true}
 * -------------------------------------------------------------
 */
function smarty_modifier_human_interval($timeDiff, $short = false)
{
    $days = floor($timeDiff / 86400);
    $timeDiff = $timeDiff - ($days * 86400);
    $hours = floor($timeDiff / 3600);
    $timeDiff = $timeDiff - ($hours * 3600);
    $minutes = floor($timeDiff / 60);
    $timeDiff = $timeDiff - ($minutes * 60);

    $elapsedTime = '';
    $sign = '';

    if (!($short && ($hours || $days || $minutes))) {
        $elapsedTime .= sprintf('%.2f' . _('с'), $timeDiff);
    }

    if ($minutes && !($short && ($hours || $days))) {
        if ($short) {
            if ($timeDiff > 30) {
                $minutes++;
                $sign = '< ';
            } else {
                $sign = '> ';
            }
        }
        if ($minutes > 60) {
            $hours ++;
            $minutes = $minutes - 60;
        } else {
            $elapsedTime = $sign . $minutes . _('м') . ' ' . $elapsedTime;
        }
    }

    if ($hours && !($short && $days)) {
        if ($short) {
            if ($minutes > 30) {
                $hours++;
                $sign = '< ';
            } else {
                $sign = '> ';
            }
        }
        if ($hours > 24) {
            $days ++;
            $hours = $hours - 24;
        } else {
            $elapsedTime = $sign . $hours . _('г') . ' ' . $elapsedTime;
        }
    }

    if ($days) {
        if ($short) {
            if ($hours > 12) {
                $days++;
                $sign = '< ';
            } else {
                $sign = '> ';
            }
        }

        $elapsedTime = $sign . $days . _('д') . ' ' . $elapsedTime;
    }

    return htmlentities($elapsedTime, null, 'UTF-8');
}
