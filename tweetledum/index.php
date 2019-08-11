<?php

/*******************************************************************
 *  Includes
 ********************************************************************/
// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';


/*******************************************************************
 *  OAuth
 ********************************************************************/
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'        => $my_consumer_key,
  'consumer_secret'     => $my_consumer_secret,
  'user_token'          => $my_access_token,
  'user_secret'         => $my_access_token_secret,
  'curl_ssl_verifypeer' => false
));

// request the user information
$code = $tmhOAuth->user_request(array(
    'url' => $tmhOAuth->url('1.1/account/verify_credentials')
  )
);


$error = NULL;
// Display error response if do not receive 200 response code
if ($code <> 200) {
  if ($code == 429) {
    $error = 'Exceeded Twitter API rate limit.';
  }
  else {
    $error = 'Error verifying credentials.';
  }
}

$profile_img = NULL;

if (!$error) {

  // Decode JSON
  $data = json_decode($tmhOAuth->response['response'], true);

  $user_url = 'https://twitter.com/' . $data['screen_name'];

  if (!empty($data['profile_image_url_https'])) {
    $profile_img = '<div class="profile-image"><a target="_blank" href="' . $user_url . '">'
        . '<img src="' . $data['profile_image_url_https'] . '" />'
        . '</a></div>';
  }
//  print '<pre>' . print_r($data, 1) . '</pre>'; die();

} // Got an error?

?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Tweetledum Timeline</title>
    <style>
        body {
            margin: 10px 80px;
            background-color: #ddd;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
        }
        .info-column {
            position: fixed;
            top: 0;
            left: 0;
            width: 120px;
            padding: 20px;
            height: 100%;
        }
        .info-column div {
            float: left;
            clear: both;
            width: 100px;
            padding-bottom: 20px;
            position: relative;
        }
        .main {
            float: left;
            width: 600px;
            margin-left: 20px;
        }
        #unread-count {
            font-size: 1.2em;
            color: #666;
            background-color: #bbb;
            border-radius: 5px;
            padding: 5px;
        }
        .tweetledum-feed {
            margin: 20px 0;
            width: 545px;
        }
        #load-more {
            width: 175px;
            margin: 20px 0 100px 0;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-appearance: button;
        }
        .tweetledum-tweet {
            margin-bottom: 30px;
            padding: 10px 20px;
            border: 2px solid transparent;
        }
        .tweetledum-tweet:hover {
            border-radius: 10px;
            border: 2px solid #999;
            background-color: #ccc;
        }
        .tweetledum-tweet.active {
            border-radius: 10px;
            border: 2px solid #999;
            background-color: #ccc;
        }
        .loading-message {
            width: 135px;
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-color: #333;
            border-radius: 0.25rem;
            color: #fff;
            background-color: #999;
        }
        .tweetledum-quoted {
            width: 475px;
            margin-left: 25px;
        }
        .info-column .tweetledum-controls {
            position: absolute;
            bottom: 0;
            height: 200px;
            margin: 0 0 20px 0;
        }
        .info-column .tweetledum-controls button {
            display: block;
            clear: both;
            padding: 10px 0 10px 0;
            margin: 10px 0 0 0;
            font-size: 20px;
            width: 45px;
            background-color: #ccc;
            border: 1px solid #999;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>

<div class="info-column">
<?php
  if (!empty($profile_img)) {
    print $profile_img;
  }
?>
    <div>
        <span id="unread-count">0</span>
    </div>
    <div class="tweetledum-controls" style="display: none;">
        <button class="tweetledum-controls-up" data-keycode="75">⬆️</button>
        <button class="tweetledum-controls-open" data-keycode="86">👓</button>
        <button class="tweetledum-controls-down" data-keycode="74">⬇️</button>
    </div>
</div>
<div class="main">
    <div class="tweetledum-feed"></div>
    <button id="load-more">Load More Tweets</button>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="js/jquery.visible.min.js"></script>
<script src="js/tweetledum.js"></script>

</body>
</html>
