-- Quick syntax test for problematic tables
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- Test a few key tables that were problematic before
CREATE TABLE IF NOT EXISTS `test_class_timetable_weekdays` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `weekday` enum('Monday','Tuesday') NOT NULL
) ENGINE=InnoDB;

-- Try adding primary key
ALTER TABLE `test_class_timetable_weekdays` ADD PRIMARY KEY (`id`);

-- Try adding it again (this should cause error on second run)
ALTER TABLE `test_class_timetable_weekdays` ADD PRIMARY KEY (`id`);

DROP TABLE `test_class_timetable_weekdays`;
