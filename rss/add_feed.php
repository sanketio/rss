<?php

require_once './config/connection.php';
require_once './config/constants.php';
require_once './class/function.class.php';
require_once './class/mysql.php';

$db = new DataTransaction();
$fn_obj = new functions();
ob_clean();
session_start();
error_reporting(0);

$feed_urls = $db->selectdata('rss_feed_urls', "user_id = '" . $_SESSION['user_details']['user_id'] . "'");
$feed_url_array = array();

// Get domain name for validation purpose.
$domain_name = $fn_obj->get_domain($_REQUEST['name']);

for ($f = 0; $f < sizeof($feed_urls); $f++) {
    array_push($feed_url_array, $feed_urls[$f]['domain_name']);
}

if (!in_array($domain_name, $feed_url_array)) {
    if (!file_exists(DOCUMENT_ROOT . "feed_images")) {
        $old = umask(0);
        mkdir(DOCUMENT_ROOT . "feed_images", 0777, true);
        umask($old);
    }

    if (!file_exists(DOCUMENT_ROOT . "feed_images/thumbs")) {
        $old = umask(0);
        mkdir(DOCUMENT_ROOT . "feed_images/thumbs", 0777, true);
        umask($old);
    }

    $feed_urls_array = array();
    $urls_array = array();

    //Get feed URL of the website
    $feed_url = $fn_obj->getFeedUrl($_REQUEST['name']);

    if ($feed_url) {
        array_push($feed_urls_array, $feed_url);
        array_push($urls_array, $_REQUEST['name']);

        $insert_array = array(
            'user_id' => $_SESSION['user_details']['user_id'],
            'domain_name' => $domain_name,
            'url' => $_REQUEST['name'],
            'feed_url' => $feed_url,
            'created_date' => date('Y-m-d H:i:s')
        );

        $feed_url_id = $db->insertData($insert_array, 'rss_feed_urls');

        $limit = ITEM_COUNT;

        // Parse the feed data
        $feeds = json_decode($fn_obj->parseFeeds($feed_url), true);

        if (sizeof($feeds) < $limit) {
            $limit = sizeof($feeds);
        }

        $feed_str = '<div id = "carousel-example-generic" class = "carousel slide" data-ride = "carousel">';
        $feed_str .= '<div class = "carousel-inner">';

        for ($f = 0; $f < $limit; $f++) {
            if ($f == 0) {
                $feed_str .= '<div class = "active item">';
            } else {
                $feed_str .= '<div class = "item">';
            }

            $feed_str .= '<div style = "position: relative; min-height: 51px; padding: 9px 12px;">';
            $feed_str .= '<div style = "margin-left:25px; margin-right:25px;">';
            $feed_str .= '<div class = "media">';

            if ($feeds[$f]['image_url'] != '') {
                $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img style = "border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align = "left" src = "feed_images/thumbs/' . $feeds[$f]['image_thumb'] . '" /></a>';
            } else {
                $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img style = "border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align = "left" src = "images/default_rss.png" /></a>';
            }
            $feed_str .= '<div class = "media-body">';
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
        $feed_str .= '<a class = "left carousel-control" href = "#carousel-example-generic" data-slide = "prev" style = "color: #aaa !important; background: none !important; margin-left: -40px !important; margin-top: -13px !important">';
        $feed_str .='<span class = "glyphicon glyphicon-chevron-left"></span>';
        $feed_str .='</a>';
        $feed_str .='<a class = "right carousel-control" href = "#carousel-example-generic" data-slide = "next" style = "color: #aaa !important; background: none !important; margin-right: -50px !important; margin-top: -13px !important;">';
        $feed_str .='<span class = "glyphicon glyphicon-chevron-right"></span>';
        $feed_str .='</a>';
        $feed_str .= '</div>';

        echo json_encode($urls_array) . "_sep_" . json_encode($feed_urls_array) . "_sep_" . $feed_str;
    } else {
        echo 'not_exist';
    }
} else {
    echo 'exist';
}
?>