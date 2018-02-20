<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   homerss_nocache.php -- Home timeline results formatted as RSS feed
 *   Version: 0.4.1
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/

/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default home timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/homerss_nocache.php
==> User's Home Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/homerss_nocache.php?c=100
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/homerss_nocache.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/homerss_nocache.php?c=100&xrp=1
--------------------------------------------------------------------------------------------------*/
/*******************************************************************
 *  Debugging Flag
 ********************************************************************/
$TLD_DEBUG = 0;
if ($TLD_DEBUG == 1){
  ini_set('display_errors', 'On');
  error_reporting(E_ALL | E_STRICT);
}

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

// Display error response if do not receive 200 response code
if ($code <> 200) {
  if ($code == 429) {
    die("Exceeded Twitter API rate limit");
  }
  echo $tmhOAuth->response['error'];
  die("verify_credentials connection failure");
}

// Decode JSON
$data = json_decode($tmhOAuth->response['response'], true);

/*******************************************************************
 *  Defaults
 ********************************************************************/
$count = 200;  //default tweet number = 200
$exclude_replies = false;  //default to include replies
$screen_name = $data['screen_name'];


$db = new mysqli($my_db['host'],
  $my_db['username'],
  $my_db['password'],
  $my_db['database']);

if($db->connect_errno > 0){
  die('Unable to connect to database [' . $db->connect_error . ']');
}

$sql = "SELECT MAX(id) as max_id 
  FROM tweetledum_tweets 
  WHERE `read` = 0 ";
$max_id = $db->query($sql)->fetch_object()->max_id;


/*******************************************************************
 *  Request
 ********************************************************************/
$params = array(
  'include_entities' => true,
  'count' => $count,
  'exclude_replies' => $exclude_replies,
);
if ($max_id) {
  $params['since_id'] = $max_id;
}
$code = $tmhOAuth->user_request(array(
  'url' => $tmhOAuth->url('1.1/statuses/home_timeline'),
  'params' => $params
));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
  echo $tmhOAuth->response['error'];
  die("Home_timeline connection failure.");
}

$homeTimelineObj = json_decode($tmhOAuth->response['response'], true);


$sql = "REPLACE INTO tweetledum_tweets 
    (id, `user`, body, `data`, `timestamp`)
    VALUES
    (?, ?, ?, ?, ?) ";
$query = $db->prepare($sql);

$utc = new DateTimeZone('UTC');
$tz = new DateTimeZone('America/New_York');

$num = 0;
foreach ($homeTimelineObj as $item) {

  $dateObj = new DateTime('@' . strtotime($item['created_at']), $utc);
  $dateObj->setTimezone($tz);

  $data_field = array(
    'user_name' => $item['user']['name'],
    'retweeted' => (!empty($item['retweeted_status'])),
  );

  $data_field = serialize($data_field);
  $timestamp = $dateObj->format('U');

  $query->bind_param('ssssi',
    $item['id'],
    $item['user']['screen_name'],
    $item['text'],
    $data_field,
    $timestamp);
  $query->execute();
  $num++;

} // Loop thru items.

$query->close();

$db->close();

print $num;
