CREATE DATABASE `wwfSample` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `wwfSample`;

CREATE TABLE IF NOT EXISTS `commsPHP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TransmitCount` int(11) NOT NULL,
  `TransmitOverflow` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `Communication` (
  `Status` tinyint(8) NOT NULL,
  `ExStatusLength` smallint(16) NOT NULL,
  `ExtendedStatus` varchar(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `Communication` (`Status`, `ExStatusLength`, `ExtendedStatus`) VALUES
(0, 29, 'w;3;Dallas, Texas;43;24;60;0#');

CREATE TABLE IF NOT EXISTS `Settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `updateMode` int(11) NOT NULL DEFAULT '1',
  `updateDelay` int(11) NOT NULL DEFAULT '3600',
  `lastUpdate` int(11) NOT NULL,
  `degreeMode` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `Settings` (`id`, `updateMode`, `updateDelay`, `lastUpdate`, `degreeMode`) VALUES
(1, 1, 3600, 0, 0);

CREATE TABLE IF NOT EXISTS `Weather` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `WoeId` varchar(50) NOT NULL,
  `Location` varchar(100) NOT NULL DEFAULT '',
  `condition` varchar(20) NOT NULL DEFAULT 'Unknown',
  `HighTemp` int(3) NOT NULL DEFAULT '100',
  `LowTemp` int(3) NOT NULL DEFAULT '-30',
  `Humidity` int(3) unsigned NOT NULL,
  `PrecipChance` int(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

INSERT INTO `Weather` (`id`, `WoeId`, `Location`, `condition`, `HighTemp`, `LowTemp`, `Humidity`, `PrecipChance`) VALUES
(1, '48103.1.99999', 'Ann Arbor, Michigan', 'Overcast', 37, 26, 52, 0),
(2, '49230.1.99999', 'Brooklyn, Michigan', 'Overcast', 30, 25, 64, 0),
(3, '75201.1.99999', 'Dallas, Texas', 'Clear', 43, 24, 60, 0),
(4, '32099.1.99999', 'Jacksonville, Florida', 'Partly Cloudy', 72, 54, 76, 20),
(5, '00000.1.83755', 'Rio De Janeiro, Brazil', 'Chance of Rain', 77, 69, 79, 60);

