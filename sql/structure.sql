CREATE TABLE `tbl_code` (
	`id` int(11) NOT NULL,
	`member_id` int(11) NOT NULL,
	`code` varchar(255) NOT NULL,
	`code_exp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`nonce` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `tbl_member` (
	`id` int(11) NOT NULL,
	`oauth_id` varchar(255) NOT NULL,
	`oauth_provider` varchar(255) NOT NULL,
	`full_name` varchar(255) NOT NULL,
	`screen_name` varchar(255) NOT NULL,
	`create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `tbl_token` (
	`id` int(11) NOT NULL,
	`member_id` int(11) NOT NULL,
	`token` varchar(255) NOT NULL,
	`token_exp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `tbl_code`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `code` (`code`);

ALTER TABLE `tbl_member`
ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_token`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `token` (`token`);


ALTER TABLE `tbl_code`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_member`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_token`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
