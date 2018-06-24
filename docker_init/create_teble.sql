CREATE TABLE `live` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(256) DEFAULT ' ',
  `live_id` varchar(128) NOT NULL DEFAULT '',
  `owner` varchar(256) NOT NULL DEFAULT '',
  `start` datetime NOT NULL,
  `image` varchar(256) DEFAULT NULL,
  `description` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `live_id` (`live_id`),
  KEY `start` (`start`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
