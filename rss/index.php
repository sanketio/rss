<?php
session_start();
error_reporting(0);

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
}

require_once './config/connection.php';
require_once './config/constants.php';
require_once './class/function.class.php';
require_once './class/mysql.php';

$fn_obj = new functions();
$db = new DataTransaction();

$feed_urls = $db->selectdata('rss_feed_urls', "user_id = '" . $_SESSION['user_details']['user_id'] . "'");

$last_feed_url = $feed_urls[sizeof($feed_urls) - 1]['feed_url'];

// Get domain name for validation purpose.
$domain_name = $fn_obj->get_domain($last_feed_url);

if (sizeof($feed_urls) > 0) {
    $last_feed_data = json_decode($fn_obj->parseFeeds($last_feed_url), true);
}
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
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">RSS Reader</a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><label><?php echo $_SESSION['user_details']['displayName']; ?></label> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>

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
                                                if (sizeof($feed_urls) > 0) {
                                                    for ($f = 0; $f < sizeof($feed_urls); $f++) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo ($f + 1); ?></td>
                                                            <td><a style="cursor: pointer;" onclick="get_feeds('<?php echo $feed_urls[$f]['feed_url']; ?>');"><?php echo $feed_urls[$f]['url']; ?></a></td>
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
                                <input type="button" class="btn btn-primary pull-right <?php if (sizeof($feed_urls) == 0) { ?>hide<?php } ?>" name="download" id="download" value="Download" style="margin-top: -7px;" onclick="download_feeds('<?php echo $last_feed_url; ?>');" />
                            </div>
                            <div class="panel-body" id="feed_div">
                                <?php
                                if (sizeof($feed_urls) > 0) {
                                    ?>
                                    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php
                                            for ($i = 0; $i < sizeof($last_feed_data); $i++) {
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
                                                                    <?php if ($last_feed_data[$i]['image_url'] != '') { ?>
                                                                        <a href="<?php echo $last_feed_data[$i]['title']; ?>" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="feed_images/thumbs/<?php echo $last_feed_data[$i]['image_thumb']; ?>"/></a>
                                                                    <?php } else { ?>
                                                                        <a href="<?php echo $last_feed_data[$i]['title']; ?>" target="_blank"><img style="border: 3px solid #fff; border-radius: 10px; padding-right: 20px;" align="left" src="images/default_rss.png"/></a>
                                                                    <?php } ?>
                                                                    <div class="media-body">
                                                                        <label><a href="<?php echo $last_feed_data[$i]['title']; ?>" target="_blank"><?php echo $last_feed_data[$i]['title']; ?></a></label>
                                                                        <br />
                                                                        <?php echo $last_feed_data[$i]['description']; ?>
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