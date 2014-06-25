<?php

session_start();
unset($_SESSION['access_token']);
unset($_SESSION['user_details']);
session_destroy();
//header('Location: login.php'); // it will simply destroy the current seesion which you started before
header('Location: https://www.google.com/accounts/Logout?continue=https://appengine.google.com/_ah/logout?continue=http://sanket.host22.com/rss/login.php');

/* NOTE: for logout and clear all the session direct goole jus un comment the above line an comment the first header function */
?>