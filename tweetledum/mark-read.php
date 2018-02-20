<?php

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

header('Access-Control-Allow-Origin: *');

$db = new mysqli($my_db['host'],
  $my_db['username'],
  $my_db['password'],
  $my_db['database']);

if($db->connect_errno > 0){
  die('Unable to connect to database [' . $db->connect_error . ']');
}

$id = (!empty($_GET['id'])) ? $_GET['id'] : '';

$success = 0;

if ($id) {
  $sql = "UPDATE tweetledum_tweets
  SET `read` = 1
  WHERE `read` = 0 
  AND id = ? ";
  $query = $db->prepare($sql);
  $query->bind_param('s', $id);
  $query->execute();
  $query->close();
  $success = 1;
}

$sql = "SELECT COUNT(id) as num_unread 
  FROM tweetledum_tweets 
  WHERE `read` = 0 ";
$num_unread = $db->query($sql)->fetch_object()->num_unread;

$db->close();

$out = array(
  'success' => $success,
  'unread' => $num_unread,
);

print json_encode($out);
