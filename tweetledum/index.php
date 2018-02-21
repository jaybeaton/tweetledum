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

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.css" crossorigin="anonymous">

    <title>Tweetledum Timeline</title>
    <style>
        body {
            margin: 10px 80px;
            background-color: #ddd;
        }
        .info-column {
            position: fixed;
            top: 0;
            left: 0;
            width: 120px;
            padding: 20px;
        }
        .info-column div {
            float: left;
            clear: both;
            width: 100px;
            padding-bottom: 20px;
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
            margin: 20px 0 100px 0;
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
        .alert {
            width: 545px;
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
</div>
<div class="main">
    <div class="tweetledum-feed"></div>
    <button id="load-more" class="btn btn-primary">Load More Tweets</button>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="js/jquery.visible.min.js"></script>
<script src="js/tweetledum.js"></script>

</body>
</html>
