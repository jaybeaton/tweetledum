var twtldDebug = false;
var twtldUsername = '';

(function($){

    var numToKeep = 30;
    var noNewTweets = false;
    var lastTweetID = '';

    if (window.location.hash == '#debug') {
        twtldDebug = true;
    } else if (window.location.hash) {
      twtldUsername = window.location.hash.substring(1);
      if (twtldDebug) {
        console.log('Setting username to "' + twtldUsername + '".');
      }
    }

    console.log('window.location.hash:' + window.location.hash);
    console.log('twtldDebug:' + twtldDebug);


    var callAjax = function (url, callback) {
        var xmlhttp;
        xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == xmlhttp.DONE) {
                if (xmlhttp.status === 200) {
                    callback(xmlhttp.responseText);
                } else {
                    callback(embed_settings.error_msg);
                }
            }
        };
        if (twtldDebug) {
            console.log('callAjax() : url=' + url);
        }
        xmlhttp.open('GET', url, true);
        xmlhttp.send();
    };

    var processLoadMoreButton = function(button) {

        if (twtldDebug) {
            console.log('processLoadMoreButton() Called.');
        }

        $('.loading-message').remove();
        var message = '<div class="loading-message" role="alert">Loading...</div>';
        $('#load-more').before(message);

        $(button).addClass('loading');

        var lastID = $('.tweetledum-tweet').last().attr('data-id');
        if (typeof lastID === 'undefined') {
            if (twtldDebug) {
                console.log('processLoadMoreButton() No last tweet found.');
            }
            lastID = 0;
        }

        var url = 'ajax.php?id=' + lastID + '&t=' + Date.now();

        if (twtldDebug) {
            console.log('processLoadMoreButton() Will make Ajax call to url: ' + url);
        }
        callAjax(url, function(content) {

            if (!content) {
                if (twtldDebug) {
                    console.log('processLoadMoreButton() No new content found.');
                }
                noNewTweets = true;
                $('.loading-message').text('No new tweets.');
                setTimeout(function() {
                    $('.loading-message').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 3000);
                $(button).removeClass('loading');
                return;
            }

            if (twtldDebug) {
                console.log('processLoadMoreButton() Found new content.');
            }
            setTimeout(function() {
                $('.loading-message').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 1000);

            noNewTweets = false;

            $('.tweetledum-feed').append(content);
            $.getScript('https://platform.twitter.com/widgets.js');

            var tweets = $('.tweetledum-tweet');
            var totalTweets = tweets.length;
            var existing = tweets.not('.tweetledum-new');
            $('.tweetledum-tweet.tweetledum-new').click( function() {
                $('.active').removeClass('active');
                markActive($(this));
            }).removeClass('tweetledum-new');

            if (totalTweets > numToKeep) {
                var n = 0;
                var numToDelete = totalTweets - numToKeep;
                existing.each( function() {
                    if (n < numToDelete) {
                        $(this).remove();
                        n++;
                    }
                });
            }

            $(button).removeClass('loading');

            if ($('.active').length === 0) {
                markActive($('.tweetledum-tweet').first());
            }

        });

    };

    var markActive = function(tweet) {
        if (twtldDebug) {
            console.log('markActive() called on tweet:');
            console.log(tweet);
        }
        $(tweet).addClass('active');
        var id = $(tweet).prev().attr('data-id');
        if (id) {
            lastTweetID = id;
        }
        if (typeof id === 'undefined') {
            // No previous tweet.
            id = 0;
            if (twtldDebug) {
                console.log('markActive() No previous tweet found.');
            }
        }
        if (twtldDebug) {
            console.log('markActive() Mark previous tweet read, id (' + id + ').');
        }
        var url = 'mark-read.php?id=' + id;
        callAjax(url, function(content) {
            var results = JSON.parse(content);
            if (results['unread']) {
                var unread = parseInt(results['unread']);
                unread--;
                if (unread < 0) {
                    unread = 0;
                }
                if (twtldDebug) {
                    console.log('markActive() Setting unread count to (' + unread + ').');
                }
                $('#unread-count').text(unread);
            }
        });
    };

    var getTopItem = function() {

        if (twtldDebug) {
            console.log('getTopItem() Called.');
        }
        var active = $('.active');
        if (active.length > 0 && active.visible(true)) {
            if (twtldDebug) {
                console.log('getTopItem() Active item exists and is visible.');
            }
            return;
        }

        $('.tweetledum-tweet').removeClass('active');
        var activeElement = document.elementFromPoint(200, 75);
        if (twtldDebug) {
          console.log('getTopItem() activeElement is:');
          console.log(activeElement);
        }
        if (!$(activeElement).hasClass('tweetledum-tweet')) {
            if (twtldDebug) {
                console.log('getTopItem() activeElement is not one of our tweets.');
            }
            activeElement = $(activeElement).parent('.tweetledum-tweet');
            if (!$(activeElement).hasClass('tweetledum-tweet')) {
                if (twtldDebug) {
                    console.log('getTopItem() activeElement does not have a parent that is one of our tweets.');
                }
                if (lastTweetID) {
                    if (twtldDebug) {
                        console.log('getTopItem() Will use last active tweet.');
                    }
                    activeElement = $('#tweetledum-' + lastTweetID);
                } else {
                    activeElement = $('.tweetledum-tweet').first();
                }
            }
        }
        markActive(activeElement);

    };

    $('#load-more').not('.load-processed').click( function() {
        processLoadMoreButton(this);
    }).addClass('load-processed').click();

    $(document).keydown(function (event) {

        if (event.keyCode == 78) {
            // Pressing "n" will bring active tweet to top.
            event.preventDefault();
            if ($('.active').length) {
                if (twtldDebug) {
                    console.log('keydown("n"): Scrolling to active tweet (' + $('.active').first().attr('data-id') + ').');
                }
                $('.active')[0].scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
                });
            } else {
              if (twtldDebug) {
                console.log('keydown("n"): No active tweet found.');
              }
            }
            return;
        }

        getTopItem();
        activeItem = $('.active');
        if (event.keyCode == 75) {
            // Pressing "k" will scroll to previous item.
            event.preventDefault();
            var prev = activeItem.prev();
            markActive(prev);
            if (prev.length === 0) {
                return;
            }
            activeItem.removeClass('active');
            $(prev)[0].scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        } else if (event.keyCode == 74) {
          // Pressing "j" will scroll to next item.
            event.preventDefault();
            var next = activeItem.next();
            markActive(next);
            if (next.length === 0) {
                $('#load-more').click();
                return;
            }
            activeItem.removeClass('active');
            $(next)[0].scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        } else if (event.keyCode == 86) {
            // Pressing "v" will open url.
            event.preventDefault();
            var url = activeItem.attr('data-url');
            window.open(url, '_blank');
        } else if (event.keyCode == 84) {
            // Pressing "t" will open tweet.
            event.preventDefault();
            var url = activeItem.attr('data-tweet');
            window.open(url, '_blank');
        } else if (event.keyCode == 82) {
            // Pressing "r" will reload.
            location.reload();
        }
    });

    var checkingLoadMore = false;

    $(window).scroll(function(){
        if (!checkingLoadMore) {
            checkingLoadMore = true;
            checkLoadMore();
            setTimeout(function() {
                checkingLoadMore = false;
            }, 100);
        }
    });

    var checkLoadMore = function() {
        var button = $('#load-more').not('.loading');
        if (button.visible(true) && !noNewTweets) {
            button.addClass('loading');
            button.click();
        }
    }

})(jQuery);
