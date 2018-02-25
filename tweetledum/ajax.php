<?php

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

header('Access-Control-Allow-Origin: *');

$num_per_page = 10;
$max_display_link_len = 60;

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

$sql = "SELECT id, `tweeter`, body, `data`, `timestamp`
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

  $tweet_url = 'https://twitter.com/' . $row['tweeter'] . '/status/' . $row['id'];
  $user_url = 'https://twitter.com/' . $row['tweeter'];

  $embed = <<<EOT
<blockquote class="twitter-tweet" data-lang="en">
  <p lang="en" dir="ltr">
    {$row['body']}
  </p>
  &mdash; {$row['data']['user_name']} (@{$row['tweeter']})
  <a href="{$tweet_url}">{$tweet_time}</a>
</blockquote>
EOT;
  /**
   * Not needed because we're loading via Ajax and calling it there:
   *
   * <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
   */

  $link_url = NULL;
  $link = NULL;
  if (!empty($row['data']['link_url_expanded'])) {
    $link_url = $row['data']['link_url_expanded'];
  }
  elseif (!empty($row['data']['link_url'])) {
    $link_url = $row['data']['link_url'];
  }
  if ($link_url && !preg_match('~^https?://twitter.com/~', $link_url)) {
    $display_url = preg_replace('~^https?://~', '', $link_url);
    if (strlen($display_url) > $max_display_link_len) {
      $display_url = htmlentities(substr($display_url, 0, ($max_display_link_len - 1))) . '&hellip;';
    }
    else {
      $display_url = htmlentities($display_url);
    }
    $link = '<a href="' . $link_url . '">' . $display_url . '</a>';
  }
  else {
    $link_url = $tweet_url;
  }

  if ($link) {
    $embed = '<div class="link-url">'
      . $link
      . '</div>'
      . $embed;
  }

  if (!empty($row['data']['retweeted'])) {
    $embed = '<div class="retweet">'
      . 'Retweeted by <a href="' . $user_url . '">' . htmlentities($row['data']['user_name'] . '(@' . $row['tweeter'] . ')') . '</a>'
      . '</div>'
      . $embed;
  }

  print <<<EOT
<div class="tweetledum-tweet tweetledum-new {$first_class}" id="tweetledum-{$row['id']}" data-id="{$row['id']}" data-url="{$link_url}" data-tweet="{$tweet_url}">
{$embed}
</div>


EOT;

  $first_class = '';

} // Loop thru items.

$query->close();

$db->close();
