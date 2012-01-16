CREATE TABLE IF NOT EXISTS `m_mysqlnotif_mod_msg` (
 `msg_id` int(11) NOT NULL AUTO_INCREMENT,
 `msg_data` mediumblob NOT NULL,
 `insert_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
 `send_count` int(11) NOT NULL DEFAULT '0',
 `send_date` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_bin;