SET NAMES utf8mb4;

DROP TABLE IF EXISTS `book`;

CREATE TABLE `book` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(128) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `book` (`id`, `user_id`, `title`, `created_at`, `updated_at`)
VALUES
	(1,1,'Hyperf Guide','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(2,1,'Hyperf Guide 2019','2018-01-02 00:00:00','2018-01-02 00:00:00'),
	(3,2,'Hyperf Component Guide','2018-01-02 00:00:00','2018-01-02 00:00:00');

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL DEFAULT '',
  `imageable_id` int(10) unsigned NOT NULL,
  `imageable_type` varchar(32) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `images` (`id`, `url`, `imageable_id`, `imageable_type`, `created_at`, `updated_at`)
VALUES
	(1,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',1,'user','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(2,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',2,'user','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(3,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',1,'book','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(4,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',2,'book','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(5,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',3,'book','2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(6,'https://avatars2.githubusercontent.com/u/44228082?s=200&v=4',0,'','2018-01-01 00:00:00','2018-01-01 00:00:00');

DROP TABLE IF EXISTS `role`;

CREATE TABLE `role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `role` (`id`, `name`, `created_at`, `updated_at`)
VALUES
	(1,'author','2018-01-01 00:00:00','2018-01-01 00:00:00');

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'user name',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:unknow 1:male 2:female',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id`, `name`, `gender`, `created_at`, `updated_at`)
VALUES
	(1,'Hyperf',1,'2018-01-01 00:00:00','2019-06-05 03:27:14'),
	(2,'Hyperflex',1,'2019-01-01 00:00:00','2019-02-16 09:59:36'),
	(3,'Hidden',0,'2019-01-01 00:00:00','2019-02-16 09:59:36'),
	(100,'John',0,NULL,NULL);

DROP TABLE IF EXISTS `user_ext`;

CREATE TABLE `user_ext` (
  `id` bigint(20) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `float_num` decimal(10,2) DEFAULT '0.00',
  `str` varchar(16) DEFAULT NULL,
  `json` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_ext` (`id`, `count`, `float_num`, `str`, `json`, `created_at`, `updated_at`)
VALUES
	(1,0,1.20,'','{"id": 1}','2019-03-13 02:38:04','2019-03-13 02:38:04'),
	(2,0,0.00,NULL,NULL,'2019-02-07 16:24:02','2019-02-17 04:44:41');

DROP TABLE IF EXISTS `user_role`;

CREATE TABLE `user_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_role` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`)
VALUES
	(1,1,1,'2018-01-01 00:00:00','2018-01-01 00:00:00'),
	(2,2,1,'2018-01-01 00:00:00','2018-01-01 00:00:00');

