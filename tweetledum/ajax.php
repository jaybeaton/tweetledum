<?php

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

header('Access-Control-Allow-Origin: *');

$num_per_page = 10;

$db = new mysqli($my_db['host'],
  $my_db['username'],
  $my_db['password'],
  $my_db['database']);

if($db->connect_errno > 0){
  die('Unable to connect to database [' . $db->connect_error . ']');
}

$id = (!empty($_GET['id'])) ? $_GET['id'] : '';

$utc = new DateTimeZone('UTC');
$tz = new DateTimeZone('America/New_York');

$sql = "SELECT id, `user`, body, `data`, `timestamp` 
  FROM tweetledum_tweets 
  WHERE id > ? 
  AND `read` = 0
  ORDER BY id ASC 
  LIMIT {$num_per_page} ";
$query = $db->prepare($sql);


$query->bind_param('s', $id);
$query->execute();
$result = $query->get_result();

$first_class = 'first';
while ($row = $result->fetch_assoc()) {

  $date = new DateTime('@' . $row['timestamp'], $utc);
  $date->setTimezone($tz);

  $row['data'] = unserialize($row['data']);
  $tweet_time = $date->format('Y-m-d H:i:s');

  $tweet_url = 'https://twitter.com/' . $row['user'] . '/status/' . $row['id'];
  $user_url = 'https://twitter.com/' . $row['user'];

  $embed = <<<EOT
<blockquote class="twitter-tweet" data-lang="en">
  <p lang="en" dir="ltr">
    {$row['body']}
  </p>
  &mdash; {$row['data']['user_name']} (@{$row['user']})
  <a href="{$tweet_url}">{$tweet_time}</a>
</blockquote>
EOT;
  /**
   * Not needed because we're loading via Ajax and calling it there:
   *
   * <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
   */

  if (!empty($row['data']['retweeted'])) {
    $embed = '<div class="retweet">'
      . 'Retweeted by <a href="' . $user_url . '">' . htmlentities($row['data']['user_name'] . '(@' . $row['user'] . ')') . '</a>'
      . '</div>'
      . $embed;
  }

  print <<<EOT
<div class="tweetledum-tweet tweetledum-new {$first_class}" id="tweetledum-{$row['id']}" data-id="{$row['id']}" data-url="{$tweet_url}">
{$embed}
</div>


EOT;

  $first_class = '';

} // Loop thru items.

$query->close();

$db->close();
