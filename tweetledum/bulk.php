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

$screen_name = NULL;
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
  $screen_name = $data['screen_name'];

} // Got an error?


$db = new mysqli($my_db['host'],
  $my_db['username'],
  $my_db['password'],
  $my_db['database']);

if($db->connect_errno > 0){
  die('Unable to connect to database [' . $db->connect_error . ']');
}

$errors = [];
$status_messages = [];
$current_list = $_GET['list'] ?? NULL;
$lists = [];
$tweeters = $_POST['tweeter'] ?? [];
$tweeters = array_filter($tweeters);
$mark_read = !empty($_POST['mark-read']);
$save_list = !empty($_POST['save-list']);
$list_name = $_POST['list-name'] ?? NULL;
$list = $_GET['list'] ?? NULL;

if ($mark_read) {

  if ($tweeters) {

    $placeholders = implode(',', array_fill(0, count($tweeters), '?'));
    $sql = "UPDATE tweetledum_tweets
      SET `read` = 1
      WHERE `read` = 0
      AND tweeter IN ({$placeholders}) ";
    $query = $db->prepare($sql);
    $query->bind_param(str_repeat('s', count($tweeters)), ...$tweeters);
    $query->execute();
    $message = '';
    if ($query->affected_rows == 1) {
      $message = '1 tweet ';
    }
    else {
      $message = "{$query->affected_rows} tweets ";
    }
    if (count($tweeters) == 1) {
      $message .= 'from 1 tweeter marked read.';
    }
    else {
      $message .= 'from ' . count($tweeters)  . ' tweeters marked read.';
    }
    $status_messages[] = $message;

    // @todo - Add an "undo" based on an 'updated' timestamp.

  } // Got tweeters to mark.

}
elseif ($save_list) {

  if (!$list_name) {
    $errors[] = 'List name is required.';
  }
  else {

    $list_data = serialize($tweeters);
    $timestamp = time();
    $sql = "REPLACE INTO tweetledum_lists
        (user, name, data, timestamp)
        VALUES
        (?, ?, ?, ?) ";
    $query = $db->prepare($sql);
    $query->bind_param('sssi', $screen_name, $list_name, $list_data, $timestamp);
    $done = $query->execute();
    if ($done) {
      $status_messages[] = 'List saved.';
    }
    else {
      $errors[] = 'List not saved.';
    }

  }

}

if (!$list_name && $list) {
  $list_name = $list;
}

// Get the list to show.
if ($list_name) {
  $sql = "SELECT data
  FROM tweetledum_lists
  WHERE user = ?
  AND name = ? ";
  $query = $db->prepare($sql);
  $query->bind_param('ss', $screen_name, $list_name);
  $query->execute();
  if ($list_data = $query->get_result()->fetch_object()->data) {
    $list_data = unserialize($list_data);
    if (is_array($list_data)) {
      $tweeters = $list_data;
      $status_messages[] = 'List "' . htmlentities($list_name) . '" loaded.';
    }
    else {
      $errors[] = 'List "' . htmlentities($list_name) . '" could not be retrieved.';
      $list_name = '';
    }
  }
}

$sql = "SELECT DISTINCT t.tweeter, IFNULL(counts.num_tweets, 0) AS num_tweets
  FROM tweetledum_tweets t
  LEFT JOIN (
    SELECT tc.tweeter, COUNT(tc.id) AS num_tweets
    FROM tweetledum_tweets AS tc
    WHERE tc.`read` = 0
    GROUP BY tc.tweeter ) AS counts ON t.tweeter = counts.tweeter
  WHERE 1=1
  ORDER BY counts.num_tweets DESC, t.tweeter ";
$result = $db->query($sql);

$counts = [];
while ($row = $result->fetch_assoc()) {

  $counts[$row['tweeter']] = $row;
}

// Get all lists.
$sql = "SELECT name
  FROM tweetledum_lists
  WHERE user = ?
  ORDER BY name ";
$query = $db->prepare($sql);
$query->bind_param('s', $screen_name);
$query->execute();
$result = $query->get_result();

$first_class = 'first';
while ($row = $result->fetch_assoc()) {
  $lists[] = $row['name'];
} // Loop thru lists.


$sql = "SELECT COUNT(id) as num_unread
  FROM tweetledum_tweets
  WHERE `read` = 0 ";
$num_unread = $db->query($sql)->fetch_object()->num_unread;

$db->close();

function get_messages($messages, $class) {
  if (!$messages) {
    return '';
  }
  $out = '<div class="messages ' . $class . '">'
    . '<ul>';
  foreach ($messages as $message) {
    $out .= '<li>' . htmlentities($message) . '</li>';
  }
  $out .= '</ul>'
    . "</div>\n";
  return $out;
}
?>
<!doctype html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Tweetledum Bulk Mark-Read</title>
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
    <span id="unread-count"><?php print $num_unread; ?></span>
  </div>
  <div>
    <div class="total-selected-label">Selected</div> <span id="total-selected">0</span>
  </div>
</div>
<div class="main">
  <div class="tweetledum-bulk">
    <?php
    if ($errors) {
      print get_messages($errors, 'errors');
    }
    if ($status_messages) {
      print get_messages($status_messages, 'status');
    }
    ?>
    <form id="bulk-mark-read" action="bulk.php" method="post">
      <table class="bulk-mark-read">
        <tr>
          <th><input type="checkbox" id="bulk-toggle" /></th>
          <th>Tweeter</th>
          <th>Count</th>
        </tr>
<?php
        foreach ($counts as $tweeter => $row) {
          $checked = (in_array($tweeter, $tweeters)) ? 'checked="checked"' : '';
          $id = 'tweeter__' . $tweeter;
          print '<tr>';
          print '<td class="checkbox"><input type="checkbox" name="tweeter[]" id="' . htmlentities($id) . '" value="' . htmlentities($tweeter) . '" ' . $checked . ' data-count=' . $row['num_tweets'] . '" /></td>';
          print '<td class="tweeter"><label for="' . htmlentities($id) . '">' . htmlentities($tweeter) . '</label></td>';
          print '<td class="count">' . $row['num_tweets'] . '</td>';
          print "</tr>\n";
        }
?>
      </table>
      <div class="actions">
        <div>
          <input class="bulk-save bulk-save--mark" type="submit" name="mark-read" value="Mark read" />
        </div>
        <div>
          <input class="bulk-input bulk-input--list" type="textfield" name="list-name" maxlength="255" value="<?php print htmlentities($list_name); ?>">
        </div>
        <div>
          <input class="bulk-save bulk-save--list" type="submit" name="save-list" value="Save list" />
        </div>
      </div>
    </form>


    <div class="lists-wrapper">
      <h3>Lists</h3>
      <div class="lists">
        <?php if ($lists) { ?>
          <ul>
            <?php foreach ($lists as $list) { ?>
              <li><a href="bulk.php?list=<?php print urlencode($list); ?>"><?php print htmlentities($list); ?></a></li>
            <?php } ?>
          </ul>
        <?php } else { ?>
          <div class="no-lists">
            You have no lists.
          </div>
        <?php } ?>
      </div>
    </div>

  </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="js/tweetledum-bulk-mark.js"></script>

</body>
</html>
