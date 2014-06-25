<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start();

if (isset($_SESSION['login'])) {
    header('Location: index.php');
}

require_once './config/connection.php';
require_once './config/constants.php';
require_once './class/function.class.php';
require_once './class/mysql.php';

require_once 'lib/goauth/Google_Client.php'; // include the required calss files for google login
require_once 'lib/goauth/contrib/Google_PlusService.php';
require_once 'lib/goauth/contrib/Google_Oauth2Service.php';

$fn_obj = new functions();
$db = new DataTransaction();

$client = new Google_Client();
$client->setApplicationName("RSS READER"); // Set your applicatio name
$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me')); // set scope during user login
$client->setClientId('492306952880-udnup3mbf8lsgnh03a9p5700da51gfuu.apps.googleusercontent.com'); // paste the client id which you get from google API Console
$client->setClientSecret('T66ufsU2IXuCC5CBLXYmMu44'); // set the client secret
$client->setRedirectUri('http://sanket.host22.com/rss/login.php'); // paste the redirect URI where you given in APi Console. You will get the Access Token here during login success
$client->setDeveloperKey('492306952880-udnup3mbf8lsgnh03a9p5700da51gfuu@developer.gserviceaccount.com'); // Developer key
$plus = new Google_PlusService($client);
$oauth2 = new Google_Oauth2Service($client); // Call the OAuth2 class for get email address
if (isset($_GET['code'])) {
    $client->authenticate(); // Authenticate
    $user = $oauth2->userinfo->get();
    $me = $plus->people->get('me');

    $_SESSION['user_details'] = $me;
    $_SESSION['access_token'] = $client->getAccessToken(); // get the access token here
    $_SESSION['login'] = 'Y';

    $user_data = $db->selectdata('rss_users', "user_email = '" . $me['emails'][0]['value'] . "'");

    if (sizeof($user_data) == 0) {
        $insert_array = array(
            'user_full_name' => $me['displayName'],
            'user_email' => $me['emails'][0]['value'],
            'created_date' => date('Y-m-d H:i:s')
        );

        $_SESSION['user_details']['user_id'] = $db->insertData($insert_array, 'rss_users');
    } else {
        $_SESSION['user_details']['user_id'] = $user_data[0]['user_id'];
    }

    header('Location: index.php');
}
?>
<html>
    <head>
        <title>Sign-in with Facebook</title>
        <?php echo $fn_obj->styles('bootstrap/bootstrap.css'); ?>
        <style>
            .bg-image a img:hover {
                -webkit-transform: scale(1.09,1.11);
                -webkit-transition-timing-function: ease-out;
                -webkit-transition-duration: 250ms;
                -moz-transform: scale(1.09,1.11);
                -moz-transition-timing-function: ease-out;
                -moz-transition-duration: 250ms;
            }
            .bg-image {
                text-align: center;
            }
            .bg-image a {
                position: absolute;
                text-align: center;
                top: 45%;
                left: 0;
                right: 0;
            }
        </style>
    </head>
    <body style="background-color: #EEE;">
        <div class="bg-image">
            <a href="<?php echo $client->createAuthUrl(); ?>">
                <img src="images/google-login-button.png" alt="Facebook Login" />
            </a>
        </div>
    </body>
</html>
<?php echo $fn_obj->js('jquery/jquery-1.11.1.js,bootstrap/bootstrap.js'); ?>