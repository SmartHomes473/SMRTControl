CREATE DATABASE `temperature` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `temperature`;

CREATE TABLE IF NOT EXISTS `Communication` (
  `Status` tinyint(8) NOT NULL,
  `ExStatusLength` smallint(16) NOT NULL,
  `ExtendedStatus` varchar(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `Communication` (`Status`, `ExStatusLength`, `ExtendedStatus`) VALUES
(0, 0, '0');
