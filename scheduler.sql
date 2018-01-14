
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tbl_task
-- ----------------------------
DROP TABLE IF EXISTS `tbl_task`;
CREATE TABLE `tbl_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `command` text,
  `repeat` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_count` int(11) NOT NULL DEFAULT '0',
  `interval` varchar(45) NOT NULL DEFAULT 'DAY',
  `interval_count` int(11) NOT NULL DEFAULT '1',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `last_datetime` datetime DEFAULT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tbl_task
-- ----------------------------
BEGIN;
INSERT INTO `tbl_task` VALUES (1, 'Test', 'Test descirption', 'ps ax', 1, 0, '\'MINUTE\'', 1, '2018-01-01 00:00:00', NULL, '2018-01-01 00:00:00', '2018-01-14 14:31:11');
COMMIT;

-- ----------------------------
-- Table structure for tbl_task_history
-- ----------------------------
DROP TABLE IF EXISTS `tbl_task_history`;
CREATE TABLE `tbl_task_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_task` int(11) NOT NULL,
  `result` text,
  `execute_time` decimal(11,6) NOT NULL DEFAULT '0.000000',
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Function structure for datetime_add_interval
-- ----------------------------
DROP FUNCTION IF EXISTS `datetime_add_interval`;
delimiter ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `datetime_add_interval`(input_datetime DATETIME, input_offset INT, input_interval VARCHAR(20)) RETURNS datetime
BEGIN

  IF input_offset = 0 OR input_offset IS NULL THEN
     RETURN input_datetime;
  END IF;
  
RETURN
  CASE input_interval
    WHEN 'YEAR' THEN input_datetime + INTERVAL input_offset YEAR
    WHEN 'MONTH' THEN input_datetime + INTERVAL input_offset MONTH
    WHEN 'DAY' THEN input_datetime + INTERVAL input_offset DAY
    WHEN 'HOUR' THEN input_datetime + INTERVAL input_offset HOUR
    WHEN 'MINUTE' THEN input_datetime + INTERVAL input_offset MINUTE
    WHEN 'SECOND' THEN input_datetime + INTERVAL input_offset SECOND
    ELSE NULL
  END;
END;
;;
delimiter ;

-- ----------------------------
-- Function structure for datetime_get_timestamp_diff
-- ----------------------------
DROP FUNCTION IF EXISTS `datetime_get_timestamp_diff`;
delimiter ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `datetime_get_timestamp_diff`(input_interval VARCHAR(20), input_start_datetime DATETIME, input_end_datetime DATETIME) RETURNS int(11)
BEGIN
RETURN CASE input_interval
        WHEN 'YEAR' THEN TIMESTAMPDIFF(YEAR,input_start_datetime,input_end_datetime)
        WHEN 'MONTH' THEN TIMESTAMPDIFF(MONTH,input_start_datetime,input_end_datetime)
        WHEN 'DAY' THEN  TIMESTAMPDIFF(DAY,input_start_datetime,input_end_datetime)
        WHEN 'HOUR' THEN TIMESTAMPDIFF(HOUR,input_start_datetime,input_end_datetime)
        WHEN 'MINUTE' THEN TIMESTAMPDIFF(MINUTE,input_start_datetime,input_end_datetime)
        WHEN 'SECOND' THEN TIMESTAMPDIFF(SECOND,input_start_datetime,input_end_datetime)
        else 0
       END;
END;
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
