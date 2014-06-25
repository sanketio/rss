<?php

ob_clean();
ini_set('allow_url_fopen', 'On');

class functions {

    //styles
    public function styles($style_value) {

        $style_array = explode(',', $style_value);
        $css = '';

        foreach ($style_array as $style) {
            $css .= '<link rel="stylesheet" type="text/css" href="' . ASSETS_PATH . $style . '" media="all" />' . "\n\r";
        }
        return $css;
    }

    //js
    public function js($js_value) {
        $js_array = explode(',', $js_value);
        $javascripts = '';
        foreach ($js_array as $js) {
            $javascripts .= '<script type="text/javascript" src="' . ASSETS_PATH . $js . '"></script>' . "\n\r";
        }
        return $javascripts;
    }

    // Get domain of the website
    function get_domain($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    // Get rss location of the website if exists
    public function getRSSLocation($html, $location) {
        if (!$html or !$location) {
            return false;
        } else {
            #search through the HTML, save all <link> tags
            # and store each link's attributes in an associative array
            preg_match_all('/<link\s+(.*?)\s*\/?>/si', $html, $matches);
            $links = $matches[1];
            $final_links = array();
            $link_count = count($links);
            for ($n = 0; $n < $link_count; $n++) {
                $attributes = preg_split('/\s+/s', $links[$n]);
                foreach ($attributes as $attribute) {
                    $att = preg_split('/\s*=\s*/s', $attribute, 2);
                    if (isset($att[1])) {
                        $att[1] = preg_replace('/([\'"]?)(.*)\1/', '$2', $att[1]);
                        $final_link[strtolower($att[0])] = $att[1];
                    }
                }
                $final_links[$n] = $final_link;
            }
            #now figure out which one points to the RSS file
            for ($n = 0; $n < $link_count; $n++) {
                if (strtolower($final_links[$n]['rel']) == 'alternate') {
                    if (strtolower($final_links[$n]['type']) == 'application/rss+xml') {
                        $href = $final_links[$n]['href'];
                    }
                    if (!$href and strtolower($final_links[$n]['type']) == 'text/xml') {
                        #kludge to make the first version of this still work
                        $href = $final_links[$n]['href'];
                    }
                    if ($href) {
                        if (strstr($href, "http://") !== false) { #if it's absolute
                            $full_url = $href;
                        } else if (strstr($href, "https://") !== false) { #if it's absolute
                            $full_url = $href;
                        } else { #otherwise, 'absolutize' it
                            $url_parts = parse_url($location);
                            #only made it work for http:// links. Any problem with this?
                            $full_url = "http://$url_parts[host]";
                            if (isset($url_parts['port'])) {
                                $full_url .= ":$url_parts[port]";
                            }
                            if ($href{0} != '/') { #it's a relative link on the domain
                                $full_url .= dirname($url_parts['path']);
                                if (substr($full_url, -1) != '/') {
                                    #if the last character isn't a '/', add it
                                    $full_url .= '/';
                                }
                            }
                            $full_url .= $href;
                        }
                        return $full_url;
                    }
                }
            }
            return false;
        }
    }

    // Get contents using curl if allow_url_open is not allowed
    public function url_get_contents($Url) {
        if (!function_exists('curl_init')) {
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $Url);
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        if ($status['http_code'] == '301') {
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->get_domain($Url));
            $data = curl_exec($ch);
        }
        curl_close($ch);
        return $data;
    }

    function validateFeed($sFeedURL) {
        //Get contents using curl if allow_url_open is not allowed
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sFeedURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $string = curl_exec($ch);
        curl_close($ch);

        // Get contents if allow_url_open is allowed
        //$string = file_get_contents($sFeedURL);

        if (strpos($string, '<channel') > 0 && strpos($string, '</channel')) {
            return true;
        } else {
            return false;
        }
    }

    public function getFeedUrl($url) {
        if (strstr("$url", ".") === false) {
            if (substr("$url", -1) !== '/') {
                $url = $url . '/';
            }
        }

        //Get contents using curl if allow_url_open is not allowed
        if ($this->validateFeed($url)) {
            return $url;
        } else {
            return $this->getRSSLocation($this->url_get_contents($url), $url);
            // Get contents if allow_url_open is allowed
//            if (@file_get_contents($url)) {
//                preg_match_all('/<link\srel\=\"alternate\"\stype\=\"application\/(?:rss|atom)\+xml\"\stitle\=\".*href\=\"(.*)\"\s\/\>/', file_get_contents($url), $matches);
//                return $matches[1][0];
//            }
        }

        return false;
    }

//Parse the feeds
    public function parseFeeds($feedUrl) {
// Get contents using curl if allow_url_open is not allowed
        $rawFeed = $this->url_get_contents($feedUrl);
// Get contents if allow_url_open is allowed
        //$rawFeed = file_get_contents($feedUrl);
        $anobii = new SimpleXmlElement($rawFeed);

        $feeds = array();

        foreach ($anobii->channel->item as $anobiiinfo) {
            $feed_tem_array = array();

            $title = $anobiiinfo->title;
            $desc = preg_replace("/<img[^>]+\>/i", "", $anobiiinfo->description);
            $anobiiinfo->description;
            $link = $anobiiinfo->link;

            $feed_tem_array['title'] = "$title";
            $feed_tem_array['link'] = "$link";
            $feed_tem_array['description'] = "$desc";
            $feed_tem_array['image_url'] = $this->catch_that_image($anobiiinfo->children('content', true)->encoded);
            if ($feed_tem_array['image_url'] == '') {
                $feed_tem_array['image_url'] = $this->catch_that_image($anobiiinfo->description);
            }

            if ($feed_tem_array['image_url'] != '') {
                $url = $feed_tem_array['image_url'];
                $fileName = md5($this->get_domain($feedUrl)) . "_sepfile_" . basename($url);
                $img = DOCUMENT_ROOT . "feed_images/" . $fileName;

                if (!file_exists($img)) {
                    // if allow openssl and allow_url_fopen
                    //file_put_contents($img, fopen($url, 'r'));
                    //if not activate allow_url_fopen and curl is enable
                    $ch = curl_init($url);
                    $fp = fopen("feed_images/" . $fileName, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    $this->imageResize($fileName, IMAGE_WIDTH, IMAGE_HEIGHT);
                }

                $feed_tem_array['image_thumb'] = $fileName;
            }

            array_push($feeds, $feed_tem_array);
        }

        return json_encode($feeds);
    }

// Get first img tag
    public function catch_that_image($str) {
        $output = preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $str, $matches);
        $src = explode('?', $matches[1][0]);
        return $src[0];
        //return (isset($matches[1][0])) ? $matches[1][0] : '';
    }

//Image resize
    public function imageResize($name, $thumbheight, $thumbwidth) {  // function to resize the image
        $uploadedfile = "feed_images/" . $name;

        $ext_array = explode('.', $name);
        $mimeType = $ext_array[sizeof($ext_array) - 1];

        if ($mimeType == "jpeg" || $mimeType == "jpg") {
            $src = imagecreatefromjpeg($uploadedfile);
        } else if ($mimeType == "png") {
            $src = imagecreatefrompng($uploadedfile);
        } else if ($mimeType == "gif") {
            $src = imagecreatefromgif($uploadedfile);
        }

        list($width, $height) = getimagesize($uploadedfile);

        $newwidth = $thumbheight;
        $newheight = $thumbwidth;
        $tmp = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $filename = "feed_images/thumbs/" . $name;

        if ($mimeType == "jpeg" || $mimeType == "jpg") {
            imagejpeg($tmp, $filename, 100);
        } else if ($mimeType == "gif") {
            imagegif($tmp, $filename, 100);
        } else if ($mimeType == "png") {
            imagepng($tmp, $filename, 9);
        }
    }

}

?>