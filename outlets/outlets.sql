CREATE DATABASE `outlets` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `outlets`;

CREATE TABLE IF NOT EXISTS `Communication` (
  `Status` tinyint(8) NOT NULL,
  `ExStatusLength` smallint(16) NOT NULL,
  `ExtendedStatus` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `outlets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `state` tinyint(1) NOT NULL,
  `power` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
