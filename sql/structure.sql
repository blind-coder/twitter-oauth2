--
-- Database: `twitter_oauth`
--

CREATE TABLE `tbl_member` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`oauth_id` varchar(255) NOT NULL,
	`oauth_provider` varchar(255) NOT NULL,
	`full_name` varchar(255) NOT NULL,
	`screen_name` varchar(255) NOT NULL,
	`create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`email` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `tbl_code` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL,
	`code` varchar(255) NOT NULL,
	`code_exp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`nonce` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `tbl_token` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`member_id` int(11) NOT NULL,
	`token` varchar(255) NOT NULL,
	`token_exp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
);
