<?php

error_reporting(0);

require_once './config/constants.php';
require_once './class/function.class.php';
require_once './lib/mpdf/mpdf.php';

$fn_obj = new functions();
header('Content-Type: text/html; charset=utf-8');
session_start();

$filepath = DOCUMENT_ROOT . "download";

//If directory is not created
if (!file_exists($filepath)) {
    $old = umask(0);
    mkdir($filepath, 0777, true);
    umask($old);
}
// path to save download file
$filename = "download/feeds_" . time() . ".pdf";
$title_pdf = 'Feeds';
ob_clean();

$domain_name = $fn_obj->get_domain($_REQUEST['feed_url']);
$limit = ITEM_COUNT;
$feeds = json_decode($fn_obj->parseFeeds($_REQUEST['feed_url']), true);

// check limit which is smaller
if (sizeof($feeds) < $limit) {
    $limit = sizeof($feeds);
}

$feed_str = '<body>';
$feed_str .= '<table cellspacing="40" cellpadding="10">';

for ($f = 0; $f < $limit; $f++) {
    $feed_str .= '<tr>';
    $feed_str .= '<td style="text-align: center">';
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

        $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img src="feed_images/thumbs/' . $fileName . '" width="100" /></a>';
    } else {
        $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank"><img src="images/default_rss.png" width="100" /></a>';
    }
    $feed_str .= '</td>';
    $feed_str .= '<td valign="top">';
    $feed_str .= '<label style="font-weight: bold;">';
    $feed_str .= '<a href="' . $feeds[$f]['link'] . '" target="_blank">' . $feeds[$f]['title'] . '</a>';
    $feed_str .= '</label>';
    $feed_str .= '<br />';
    $feed_str .= $feeds[$f]['description'];

    $feed_str .= '</td>';
    $feed_str .= '</tr>';
}
$feed_str .= '</table>';
$feed_str .= '</body>';

//Create new pdf file
$mpdf = new mPDF('utf-8', 'A4', '', '', 10, 10, 40, 20, 10, 10);

$mpdf->SetHTMLHeader('<div style="padding-bottom: 20px; border-bottom: 1px solid #000000;"><div style="text-align:left; width: 100px; float: left;"><img src="images/rss_logo.png" width="70" height="70" /></div><div style="text-align: right; font-weight: bold; font-size: 25; float: left; padding-top: 25px;">' . $title_pdf . '</div></div><br />');
$mpdf->SetHTMLFooter('<br /><div style="text-align: right; font-weight: bold; border-top: 1px solid #000000;">{PAGENO}/{nbpg}</div>');
//write data content into file
$mpdf->WriteHTML($feed_str);
//open new creted file into browser
$mpdf->Output($filename, "F");
echo $filename;
?>