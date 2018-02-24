(function($){

    var numToKeep = 30;
    var noNewTweets = false;

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
        xmlhttp.open('GET', url, true);
        xmlhttp.send();
    };

    var processLoadMoreButton = function(button) {

        $(button).addClass('loading');

        var lastID = $('.tweetledum-tweet').last().attr('data-id');
        if (typeof lastID === 'undefined') {
            lastID = 0;
        }

        var url = 'ajax.php?id=' + lastID;

        callAjax(url, function(content) {

            if (!content) {
                noNewTweets = true;
                $('.no-tweets-message').remove();
                var message = '<div class="no-tweets-message alert alert-warning" role="alert">No new tweets found.</div>';
                $('#load-more').before(message);
                setTimeout(function() {
                    $('.no-tweets-message').fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 3000);
                $(button).removeClass('loading');
                return;
            }

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
        $(tweet).addClass('active');
        var id = $(tweet).prev().attr('data-id');
        if (typeof id === 'undefined') {
            id = 0;
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
                $('#unread-count').text(unread);
            }
        });
    };

    var getTopItem = function() {

        var active = $('.active');
        if (active.length > 0 && active.visible(true)) {
            return;
        }

        $('.tweetledum-tweet').removeClass('active');
        var activeElement = document.elementFromPoint(200, 75);
        if (!$(activeElement).hasClass('tweetledum-tweet')) {
            activeElement = $(activeElement).parent('.tweetledum-tweet');
            if (!$(activeElement).hasClass('tweetledum-tweet')) {
                activeElement = $('.tweetledum-tweet').first();
            }
        }
        markActive(activeElement);

    };

    $('#load-more').not('.load-processed').click( function() {
        processLoadMoreButton(this);
    }).addClass('load-processed').click();

    $(document).keydown(function (event) {
        getTopItem();
        activeItem = $('.active');
        if (event.keyCode == 75) {
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
            // Pressing "v" will open tweet.
            event.preventDefault();
            var url = activeItem.attr('data-url');
            window.open(url, '_blank');
        } else if (event.keyCode == 84) {
            // Pressing "t" will open tweet.
            event.preventDefault();
            var url = activeItem.attr('data-tweet');
            window.open(url, '_blank');
        } else if (event.keyCode == 82) {
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
