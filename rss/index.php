<?php
session_start();
error_reporting(0);
require_once './config/constants.php';
require_once './class/function.class.php';
$fn_obj = new functions();
?>
<html>
    <head>
        <title>RSS Reader</title>
        <?php
        //load css files.
        echo $fn_obj->styles('bootstrap/css/bootstrap.css');
        ?>
        <?php header('Content-Type: text/html; charset=utf-8'); ?>
        <style>
            label.error {
                color: red;
            }
            div#spinner {
                display: none;
                position: fixed;
                top: 0;
                padding-top: 22%;
                left: 0;
                text-align:center;
                z-index:2;
                background:rgba(0,0,0,0.5);
                width: 100%;
                height: 100%;
            }
        </style>
    </head>
    <body>
        <div id="spinner">
            <img src="images/ajax-loader.gif" alt="Loading..."/>
        </div>
        <header style="background-color: #EEE; text-align: center;">
            <a href="index.php">
                <img src="images/rss_logo.png" class="image-responsive" />
            </a>
        </header>
        <div class="container" style="margin-top: 20px;">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-4">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <form id="form" name="form" method="post" enctype="multipart/form-data">
                                    <div id="feed_rows">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <input id="name" class="form-control input-sm required" type="url" required="" name="name">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary pull-right" name="submit_feed_url" id="submit_feed_url" onclick="submit_new_feed();">Submit</button>
                                </form>
                                <br/>
                                <div style="margin-top: 30px;" class="panel panel-default">
                                    <div class="panel-heading">Added Feed URLs</div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table id="master" class="table table-condensed">
                                                <tr>
                                                    <th>Sr. No.</th>
                                                    <th>Feed URL</th>
                                                </tr>
                                                <?php
                                                if (isset($_SESSION['org_urls'])) {
                                                    for ($f = 0; $f < sizeof($_SESSION['org_urls']); $f++) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo ($f + 1); ?></td>
                                                            <td><a style="cursor: pointer;" onclick="get_feeds('<?php echo $_SESSION['feeds'][$f]; ?>');"><?php echo $_SESSION['org_urls'][$f]; ?></a></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Feeds
                                <input type="button" class="btn btn-primary pull-right <?php if (!isset($_SESSION['feeds'])) { ?>hide<?php } ?>" name="download" id="download" value="Download" style="margin-top: -7px;" <?php if (isset($_SESSION['feeds'])) { ?>onclick="download_feeds('<?php echo $_SESSION['feeds'][0]; ?>');"<?php } ?> />
                            </div>
                            <div class="panel-body" id="feed_div">
                                <?php
                                if (isset($_SESSION['items_last'])) {
                                    ?>
                                    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php
                                            for ($i = 0; $i < sizeof($_SESSION['items_last']); $i++) {
                                                if ($i == 0) {
                                                    ?>
                                                    <div class="active item">
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <div class="item">
                                                            <?php
                                                        }
                                                        ?>
                                                        <div style="min-height: 51px; padding: 9px 12px;">
                                                            <div style="margin-left:25px; margin-right:25px;">
                                                                <div class="media">
                                                                    <?php if ($_SESSION['items_last'][$i]['image_url'] != '') { ?>
                                                                        <a href="<?php echo $_SESSION['items_last'][$i]['title']; ?>" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="feed_images/thumbs/<?php echo $_SESSION['items_last'][$i]['image_thumb']; ?>"/></a>
                                                                    <?php } else { ?>
                                                                        <a href="<?php echo $_SESSION['items_last'][$i]['title']; ?>" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="images/default_rss.png"/></a>
                                                                    <?php } ?>
                                                                    <div class="media-body">
                                                                        <label><a href="<?php echo $_SESSION['items_last'][$i]['title']; ?>" target="_blank"><?php echo $_SESSION['items_last'][$i]['title']; ?></a></label>
                                                                        <br />
                                                                        <?php echo $_SESSION['items_last'][$i]['description']; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev" style="color: #aaa !important; background: none !important; margin-left: -40px !important; margin-top: -13px;">
                                                <span class="glyphicon glyphicon-chevron-left"></span>
                                            </a>
                                            <a class="right carousel-control" href="#carousel-example-generic" data-slide="next" style="color: #aaa !important; background: none !important; margin-right: -50px !important; margin-top: -13px;">
                                                <span class="glyphicon glyphicon-chevron-right"></span>
                                            </a>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </body>
</html>
<?php
//load js files.
echo $fn_obj->js('jquery/jquery-1.11.1.js,bootstrap/js/bootstrap.js,jquery-validation/jquery.validate.min.js,jquery.mobile/jquery.mobile.custom.js');
?>
<script>
    $(document).ready(function() {
        jQuery('#form').validate({
            rules: {
                input: {
                    required: true,
                    url: true
                }
            }
        });

        // for swipe effect on mobile
        $("#carousel-example-generic").swiperight(function() {
            $("#carousel-example-generic").carousel('prev');
        });
        $("#carousel-example-generic").swipeleft(function() {
            $("#carousel-example-generic").carousel('next');
        });
    });

    // function for submitting the feed url
    function submit_new_feed() {
        if ($('#form').valid()) {
            $('#spinner').fadeIn('slow');
            $.ajax({
                url: 'add_feed.php',
                type: 'post',
                data: $('#form').serialize(),
                success: function(result) {
                    if (result.trim() == 'exist') {
                        alert('URL already exists. Please enter another.');
                    } else if (result.trim() == 'not_exist') {
                        alert('Feed URL does not exists. Please enter another.');
                    } else {
                        var split_data = result.split('_sep_');
                        var data = JSON.parse(split_data[0]);
                        var feed_url = JSON.parse(split_data[1]);
                        var new_tr = '';
                        var tr_length = $('#master tr').length;

                        for (f = 0; f < data.length; f++) {
                            new_tr += "<tr>";
                            new_tr += "<td>";
                            new_tr += tr_length;
                            new_tr += "</td>";
                            new_tr += "<td>";
                            new_tr += '<a style="cursor: pointer;" onclick="get_feeds(\'' + feed_url[f] + '\');">' + data[f] + '</a>';
                            new_tr += "</td>";
                            new_tr += "</tr>";
                            $('#download').attr('onclick', "download_feeds('" + feed_url[f] + "')");
                            $('#download').removeClass('hide');
                        }
                        $('#master tr:last').after(new_tr);
                        $('#feed_div').html(split_data[2]);
                    }
                    $("input[name='name']").val('');
                    $('#spinner').stop().fadeOut('slow');
                }
            });
        }
    }

    //function to download feeds
    function download_feeds(feed_url) {
        $('#spinner').fadeIn('slow');
        $.ajax({
            type: 'post',
            url: 'feeds_pdf.php',
            data: {'feed_url': feed_url},
            success: function(result) {
                $('#download').attr('onclick', "download_feeds('" + feed_url + "')");
                $('#download').removeClass('hide');
                document.location = 'file_download.php?filename=' + result.trim();
                $('#spinner').fadeOut('slow');
            }
        });
    }

    //function to get feeds of particular url
    function get_feeds(feed_url) {
        $('#spinner').fadeIn('slow');
        $.ajax({
            type: 'post',
            url: 'get_feeds_of_url.php',
            data: {'feed_url': feed_url},
            success: function(result) {
                $('#download').attr('onclick', "download_feeds('" + feed_url + "')");
                $('#download').removeClass('hide');
                $('#feed_div').html(result.trim());
                $('#spinner').fadeOut('slow');
            }
        });
    }
</script>