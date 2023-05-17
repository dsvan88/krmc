<?php

namespace app\core;

class Paginator
{
    public static function weekly($data)
    {
        $Indexes = $data['weeksIds'];
        $count = count($Indexes);

        $currentIndex = $data['currentIndex'];
        $selectedIndex = $data['selectedIndex'];
        $pagesLinks = '';

        $breakBefore = false;
        $breakAfter = false;

        $contoller = 'weeks';
        foreach ($Indexes as $index => $wId) {
            $active = false;
            if ($selectedIndex !== -1 && $wId == $Indexes[$selectedIndex])
                $active = true;
            if ($count > 8) {
                if ($index >= 1 && $wId < $Indexes[$selectedIndex] - 1) {
                    if (!$breakBefore) {
                        $breakBefore = true;
                        $pagesLinks .= '<span>...</span>';
                    }
                    continue;
                }
                if ($index > $Indexes[$selectedIndex] && $wId <= $count - 1) {
                    if (!$breakAfter) {
                        $breakAfter = true;
                        $pagesLinks .= '<span>...</span>';
                    }
                    continue;
                }
            }
            $pagesLinks .= "<a href='/$contoller/$wId'" . ($active ? ' class="active"' : '') . '>' . ($index + 1) . '</a>';
        }
        if ($selectedIndex > 0) {
            $pagesLinks = "<a href='/$contoller/{$Indexes[$selectedIndex - 1]}'><i class='fa fa-angle-left'></i></a>$pagesLinks";
        }
        /* 
        if ($selectedIndex > 5) {
            $pagesLinks = "<a href='/$contoller/1'><i class='fa fa-angle-double-left'></i></a>$pagesLinks";
        } */

        if (isset($Indexes[$selectedIndex + 1]) && $currentIndex !== -1) {
            $pagesLinks .= "<a href='/$contoller/{$Indexes[$selectedIndex + 1]}'><i class='fa fa-angle-right'></i></a>";
        }
        /*         if ($count - 1 - $selectedIndex > 5) {
            $pagesLinks .= '<a href="/' . $contoller . '/' . ($Indexes[$count - 1]) . '"><i class="fa fa-angle-double-right"></i></a>';
        } */
        return $pagesLinks;
    }
    public static function news($data)
    {
        $count = $data['count'];
        $page = $data['page'];
        $pagesCount = ceil($count / CFG_NEWS_PER_PAGE);

        $pagesLinks = '';
        $contoller = 'news';
        for ($x = 0; $x < $pagesCount; $x++) {
            $pagesLinks .= "<a href='/$contoller/$x'" . ($x == $page ? ' class="active"' : '') . '>' . ($x + 1) . '</a>';
        }
        if ($page > 0) {
            $pagesLinks = "<a href='/$contoller/" . ($page - 1) . "'><i class='fa fa-angle-left'></i></a>$pagesLinks";
        } else {
            $pagesLinks = '<a><i class="fa fa-angle-left"></i></a>' . $pagesLinks;
        }
        if ($page > 5) {
            $pagesLinks = "<a href='/$contoller/0'><i class='fa fa-angle-double-left'></i></a>$pagesLinks";
        }


        if ($page != ($pagesCount - 1)) {
            $pagesLinks .= "<a href='/$contoller/" . ($page + 1) . "'><i class='fa fa-angle-right'></i></a>";
        } else {
            $pagesLinks .= '<a><i class="fa fa-angle-right"></i></a>';
        }
        if ($pagesCount - 1 - $page > 5) {
            $pagesLinks .= "<a href='/$contoller/" . ($pagesCount - 1) . "'><i class='fa fa-angle-double-right'></i></a>";
        }
        return $pagesLinks;
    }
}
