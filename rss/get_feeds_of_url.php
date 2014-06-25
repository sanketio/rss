<?php

error_reporting(0);
require_once './config/constants.php';
require_once './class/function.class.php';
$fn_obj = new functions();

session_start();
$domain_name = $fn_obj->get_domain($_REQUEST['feed_url']);
$limit = ITEM_COUNT;
$feeds = json_decode($fn_obj->parseFeeds($_REQUEST['feed_url']), true);

$_SESSION['items_last'] = array();

if (sizeof($feeds) < $limit) {
    $limit = sizeof($feeds);
}

$feed_str = '<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">';
$feed_str .= '<div class="carousel-inner">';

for ($f = 0; $f < $limit; $f++) {
    if ($f == 0) {
        $feed_str .= '<div class="active item">';
    } else {
        $feed_str .= '<div class="item">';
    }

    $feed_str .= '<div style="position: relative; min-height: 51px; padding: 9px 12px;">';
    $feed_str .= '<div style="margin-left:25px; margin-right:25px;">';
    $feed_str .= '<div class="media">';

    if ($feeds[$f]['image_url'] != '') {
        $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="feed_images/thumbs/' . $feeds[$f]['image_thumb'] . '" /></a>';
    } else {
        $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="images/default_rss.png" /></a>';
    }
    $feed_str .= '<div class="media-body">';
    $feed_str .= '<label>';
    $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank">' . $feeds[$f]['title'] . '</a>';
    $feed_str .= '</label>';
    $feed_str .= '<br />';
    $feed_str .= $feeds[$f]['description'];
    $feed_str .= '</div>';
    $feed_str .= '</div>';
    $feed_str .= '</div>';
    $feed_str .= '</div>';
    $feed_str .= '</div>';
}

$feed_str .= '</div>';
$feed_str .= '<a class="left carousel-control" href="#carousel-example-generic" data-slide="prev" style="color: #aaa !important; background: none !important; margin-left: -40px !important; margin-top: -13px !important">';
$feed_str .='<span class="glyphicon glyphicon-chevron-left"></span>';
$feed_str .='</a>';
$feed_str .='<a class="right carousel-control" href="#carousel-example-generic" data-slide="next" style="color: #aaa !important; background: none !important; margin-right: -50px !important; margin-top: -13px !important;">';
$feed_str .='<span class="glyphicon glyphicon-chevron-right"></span>';
$feed_str .='</a>';
$feed_str .= '</div>';

echo $feed_str;
?>