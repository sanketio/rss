<?php

require_once './config/constants.php';
require_once './class/function.class.php';
$fn_obj = new functions();
ob_clean();
session_start();
error_reporting(0);

if (!isset($_SESSION['feeds'])) {
    $_SESSION['feeds'] = array();
    $_SESSION['urls'] = array();
    $_SESSION['org_urls'] = array();
    $_SESSION['feeds_md5'] = array();
}

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

// Get domain name for validation purpose.
$domain_name = $fn_obj->get_domain($_REQUEST['name']);

if (!in_array(md5($domain_name), $_SESSION['urls'])) {
    $feed_urls_array = array();
    $urls_array = array();

    //Get feed URL of the website
    $feed_url = $fn_obj->getFeedUrl($_REQUEST['name']);

    if ($feed_url) {
        array_push($feed_urls_array, $feed_url);
        array_push($urls_array, $_REQUEST['name']);
        array_push($_SESSION['feeds'], $feed_url);
        array_push($_SESSION['org_urls'], $_REQUEST['name']);
        array_push($_SESSION['feeds_md5'], md5($feed_url));
        array_push($_SESSION['urls'], md5($domain_name));

        $limit = ITEM_COUNT;

        // Parse the feed data
        $feeds = json_decode($fn_obj->parseFeeds($feed_url), true);

        $_SESSION['items_last'] = array();

        if (sizeof($feeds) < $limit) {
            $limit = sizeof($feeds);
        }

        $feed_str = '<div id = "carousel-example-generic" class = "carousel slide" data-ride = "carousel">';
        $feed_str .= '<div class = "carousel-inner">';

        for ($f = 0; $f < $limit; $f++) {
            $item_array = array();
            $item_array['title'] = $feeds[$f]['title'];
            $item_array['link'] = $feeds[$f]['link'];
            $item_array['description'] = $feeds[$f]['description'];
            $item_array['image_url'] = $feeds[$f]['image_url'];
            $item_array['feed_url'] = $feed_url;

            if ($f == 0) {
                $feed_str .= '<div class = "active item">';
            } else {
                $feed_str .= '<div class = "item">';
            }

            $feed_str .= '<div style = "position: relative; min-height: 51px; padding: 9px 12px;">';
            $feed_str .= '<div style = "margin-left:25px; margin-right:25px;">';
            $feed_str .= '<div class = "media">';
            if ($feeds[$f]['image_url'] != '') {
                $url = $feeds[$f]['image_url'];
                $fileName = md5($domain_name) . "_sepfile_" . basename($url);
                $img = DOCUMENT_ROOT . "feed_images/" . $fileName;

                if (!file_exists($img)) {
                    // if allow openssl and allow_url_fopen
                    //file_put_contents($img, fopen($url, 'r'));
                    //if not activate allow_url_fopen and curl is enable
                    $ch = curl_init($feeds[$f]['image_url']);
                    $fp = fopen("feed_images/" . $fileName, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    $fn_obj->imageResize($fileName, IMAGE_WIDTH, IMAGE_HEIGHT);
                }

                $item_array['image_thumb'] = $fileName;

                $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img style = "border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align = "left" src = "feed_images/thumbs/' . $fileName . '" /></a>';
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

            array_push($_SESSION['items_last'], $item_array);
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