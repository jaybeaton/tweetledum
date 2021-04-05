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
$user = (!empty($_GET['user'])) ? $_GET['user'] : '';
$list = (!empty($_GET['list'])) ? $_GET['list'] : '';

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
if ($user) {
  $sql .= "AND tweeter = '" . $db->real_escape_string($user) . "' ";
}
if ($list) {
  // Get the tweeters in list.
  $tweeters = [];
  $sql2 = "SELECT data
    FROM tweetledum_lists
    WHERE name = ? ";
  // @todo - We need to actually filter on the tweetledum user!
  // AND user = ? ";
  $query2 = $db->prepare($sql2);
  $query2->bind_param('s', $list);
  $query2->execute();
  if ($list_data = $query2->get_result()->fetch_object()->data) {
    $list_data = unserialize($list_data);
    if (is_array($list_data)) {
      foreach ($list_data as $tweeter) {
        $tweeters[] = "'" . $db->real_escape_string($tweeter) . "'";
      } // Loop thru tweeters.
    }
  }
  $sql .= "AND tweeter IN (" . implode(',', $tweeters) . ") ";
}

$num_unread = $db->query($sql)->fetch_object()->num_unread;

$db->close();

$out = array(
  'success' => $success,
  'unread' => $num_unread,
);

print json_encode($out);
