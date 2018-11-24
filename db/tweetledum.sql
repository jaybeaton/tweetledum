CREATE TABLE `tweetledum_tweets` (
  `id` bigint(20) unsigned NOT NULL,
  `user` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tweeter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  `read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `read` (`read`),
  KEY `user` (`user`),
  KEY `tweeter` (`tweeter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
