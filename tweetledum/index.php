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
    <link type="text/css" rel="stylesheet" href="css/styles.css" media="all" />
</head>
<body>

<div class="info-column">
<?php
  if (!empty($profile_img)) {
    print $profile_img;
  }
?>
    <div>
        <span id="current-view"></span>
    </div>
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
<script charset="utf-8" src="https://platform.twitter.com/widgets.js"></script>

</body>
</html>
