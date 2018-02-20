<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.css" crossorigin="anonymous">

    <title>Twitter Timeline</title>
    <style>
        body {
            margin: 10px 80px;
            background-color: #ddd;
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
        #unread-count {
            position: fixed;
            top: 10px;
            left: 10px;
            font-size: 1.2em;
            color: #666;
            background-color: #bbb;
            border-radius: 5px;
            padding: 5px;
        }
        .alert {
            width: 545px;
        }

    </style>
</head>
<body>
<h1>Twitter Timeline</h1>
<span id="unread-count">_</span>

<div class="tweetledum-feed"></div>

<button id="load-more" class="btn btn-primary">Load More Tweets</button>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="js/jquery.visible.min.js"></script>
<script src="js/tweetledum.js"></script>

</body>
</html>
