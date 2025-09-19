-- Rucne upravil Jara Bant
-- version 1.7c

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Databáze: `MeritAccessLocal`
DROP DATABASE IF EXISTS `MeritAccessLocal`;
CREATE DATABASE `MeritAccessLocal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `MeritAccessLocal`;

DELIMITER $$
--
-- Procedury
--
CREATE DEFINER=`ma`@`localhost` PROCEDURE `ActivateTempCard` ()  BEGIN
 START TRANSACTION;
  delete from Karty;
  INSERT INTO Karty select * from tempKarty;
 COMMIT;
END$$
DELIMITER ;

-- Struktura tabulky `Access`
CREATE TABLE `Access` (
  `Id_Access` int(11) NOT NULL AUTO_INCREMENT,
  `Adresa` varchar(15) DEFAULT NULL,
  `Karta` varchar(20) DEFAULT NULL,
  `Ctecka` int(11) DEFAULT NULL,
  `Tlacitko` int(11) DEFAULT NULL,
  `Kdy` datetime NOT NULL,
  `StavZpracovani` int(11) NOT NULL,
  PRIMARY KEY (`Id_Access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Struktura tabulky `ConfigDU`
CREATE TABLE `ConfigDU` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property` varchar(30) NOT NULL,
  `value` varchar(50) NOT NULL,
  `regex` varchar(255) NOT NULL,
  `sample` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Konfigurace dverni jednotky';

-- Vypisuji data pro tabulku `ConfigDU`
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'mode', '1', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'dhcp', '1', '[0-1]+', '1');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ip', '192.168.10.201', '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$', '192.168.1.10');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'mask', '255.255.255.0', '^(255)\\.(0|128|192|224|240|248|252|254|255)\\.(0|128|192|224|240|248|252|254|255)\\.(0|128|192|224|240|248|252|254|255)$', '255.255.255.0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'dg', '192.168.10.1', '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$', '192.168.1.1');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'dns1', '8.8.8.8', '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$', '8.8.8.8');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ws', 'https://ws.meritaccess.cz/service?WSDL', '^((http)|(https)|(rtp)|(rtsp)|(udp)):\\/\\/', 'https://ws.meritaccess.cz/service?WSDL');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'appupdate', 'meritaccess', '.*', 'http://update.meritaccess.cloud');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'enablewifi', '0', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ssid', 'myssid', '.*?', 'myTest');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'wifiuser', 'pepa', '.*?', 'Pepa');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'wifipass', 'pepapass', '.*?', 'pepapass');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'mqttenabled', '0', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'mqttserver', '192.168.10.1', '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$', '192.168.1.1');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'mqtttopic', '/ma/', '.*?', '/ma/');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'easy_add', '1', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'easy_remove', '1', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'update_mode', '0', '[0-2]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'syslogserver', '', '.*?', '192.168.10.201');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'enable_ivar', '0', '^(?:[0-9]|[1-5][0-9]|6[0-3])$', '0 not used');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ivar_server', 'http://10.10.10.1', '.*', 'ipadress of ivar server');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ivar_term_name1', 'term1', '.*', 'terminal name');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'ivar_term_name2', 'term2', '.*', 'terminal name');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'swap_wiegand_pins', '0', '[0-1]+', '0 - default state, 1 - use monitor and open for reader');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'disable_web', '0', '[0-1]+', '0 - default, 1 - disable');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'disable_ssh', '0', '[0-1]+', '0 - default, 1 - disable');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'disable_ssh_password', '0', '[0-1]+', '0 - default, 1 - disable');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'enable_osdp', '0', '[0-1]+', '0');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'use_secure_channel', '1', '[0-1]+', '0 - not use, 1 - use');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'log_level', 'info', '.*', 'debug, info, warn, error, critical');
INSERT INTO `ConfigDU` ( `property`, `value`, `regex`, `sample`) VALUES( 'maxRows', '1000', '^[1-9]\d{0,4}$', '1000 default');

-- --------------------------------------------------------

-- Struktura tabulky `running`
CREATE TABLE `running` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastchange` timestamp NOT NULL DEFAULT current_timestamp(),
  `property` varchar(20) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Vypisuji data pro tabulku `running`
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-09-01 00:00:00', 'MyID', 'MDUD83ADD06DB00');
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-09-01 00:00:00', 'LastStart', '2024-06-26 14:17:38.964259');
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-06-01 00:00:00', 'Version', 'v1.1.1');
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-06-01 00:00:00', 'MyIP', '192.168.10.201');
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-06-01 00:00:00', 'restart', '0');
INSERT INTO `running` (`lastchange`, `property`, `value`) VALUES('2025-06-01 00:00:00', 'change_time', '');

-- --------------------------------------------------------

-- Struktura tabulky `Karty`
CREATE TABLE `Karty` (
  `cardid` int(11) NOT NULL AUTO_INCREMENT,
  `Karta` varchar(20) NOT NULL,
  `Ctecka` int(11) NOT NULL,
  `CasPlan` int(11) NOT NULL,
  `Povoleni` tinyint(1) NOT NULL,
  `Smazano` tinyint(1) NOT NULL,
  `Pozn` varchar(50) NOT NULL,
  PRIMARY KEY (`cardid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE ALGORITHM=UNDEFINED DEFINER=`ma`@`localhost` SQL SECURITY DEFINER VIEW `AccessDetails`  AS SELECT `Access`.`Kdy` AS `Kdy`, `Access`.`Karta` AS `Karta`, `Access`.`Ctecka` AS `Ctecka`, `Access`.`StavZpracovani` AS `StavZpracovani`, `Karty`.`Pozn` AS `Pozn`, `Karty`.`Povoleni` AS `Povoleni`, `Karty`.`Smazano` AS `Smazano`, `Karty`.`cardid` AS `cardid` FROM (`Access` left join `Karty` on(rtrim(`Access`.`Karta`) = rtrim(`Karty`.`Karta`))) ;

-- Struktura tabulky `logs`
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `severity` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Struktura tabulky `tempKarty`
CREATE TABLE `tempKarty` (
  `cardid` int(11) NOT NULL AUTO_INCREMENT,
  `Karta` varchar(20) NOT NULL,
  `Ctecka` int(11) NOT NULL,
  `CasPlan` int(11) NOT NULL,
  `Povoleni` tinyint(1) NOT NULL,
  `Smazano` tinyint(1) NOT NULL,
  `Pozn` varchar(50) NOT NULL,
  PRIMARY KEY (`cardid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Struktura tabulky `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `LogonName` varchar(20) NOT NULL,
  `MD5` varchar(50) NOT NULL,
  `rights` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Vypisuji data pro tabulku `users`
INSERT INTO `users` (`LogonName`, `MD5`, `rights`) VALUES('admin', '9df3b01c60df20d13843841ff0d4482c', 255);
INSERT INTO `users` (`LogonName`, `MD5`, `rights`) VALUES('bantj', '779af759f5e5847ade9f49d6edf757b5', 255);

-- --------------------------------------------------------

CREATE TABLE CasovePlany ( Id_CasovyPlan INT AUTO_INCREMENT PRIMARY KEY, Cislo INT NOT NULL, Nazev VARCHAR(50) NOT NULL, Popis VARCHAR(50) NULL, RezimOtevirani VARCHAR(20) NOT NULL,
    Po_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ut_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    St_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ct_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Pa_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    So_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ne_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Svatky_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00' );

CREATE TABLE tempCasovePlany ( Id_CasovyPlan INT AUTO_INCREMENT PRIMARY KEY, Cislo INT NOT NULL, Nazev VARCHAR(50) NOT NULL, Popis VARCHAR(50) NULL, RezimOtevirani VARCHAR(20) NOT NULL,
    Po_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ut_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    St_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ct_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Pa_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    So_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ne_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Svatky_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00' );

CREATE TABLE `config_groups` (
  `config_id` int(11) NOT NULL,
  `groups_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`config_id`, `groups_id`),
  KEY `fk_groups_id` (`groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `Groups` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `groupname` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Groups` (`id`, `groupname`) VALUES (1,'Common');
INSERT INTO `Groups` (`id`, `groupname`) VALUES (2, 'IP');
INSERT INTO `Groups` (`id`, `groupname`) VALUES (3, 'WiFi');
INSERT INTO `Groups` (`id`, `groupname`) VALUES (4, 'MQTT');
INSERT INTO `Groups` (`id`, `groupname`) VALUES (5, 'IVAR');
INSERT INTO `Groups` (`id`, `groupname`) VALUES (6, 'OSDP');

INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'mode%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'ws%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'appupdate%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'easy_add%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'easy_remove%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'update_mode%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'syslogserver%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'swap_wiegand_pins%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_web%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_ssh';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_ssh_password%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'maxRows%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'log_level%';

INSERT into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dhcp%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'ip%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'mask%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dg%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dns1%';

INSERT into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'enablewifi%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'ssid%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'wifiuser%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'wifipass%';

INSERT into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqttenabled%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqttserver%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqtttopic%';

INSERT into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'enable_ivar%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_server%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_term_name1%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_term_name2%';

INSERT into `config_groups` (`groups_id`,`config_id` ) select 6, id from ConfigDU WHERE property like 'enable_osdp%';
INSERT into `config_groups` (`groups_id`,`config_id` ) select 6, id from ConfigDU WHERE property like 'use_secure_channel%';

ALTER TABLE `config_groups`
  ADD CONSTRAINT `fk_config_id` FOREIGN KEY (`config_id`) REFERENCES `ConfigDU` (`id`),
  ADD CONSTRAINT `fk_groups_id` FOREIGN KEY (`groups_id`) REFERENCES `Groups` (`id`);

CREATE ALGORITHM=UNDEFINED DEFINER=`ma`@`localhost` SQL SECURITY DEFINER VIEW `ConfigByGroups` AS SELECT Groups.groupname AS groupname, ConfigDU.id AS id, ConfigDU.property AS property, ConfigDU.value AS value, ConfigDU.regex AS regex, ConfigDU.sample AS sample FROM ((config_groups join Groups on(config_groups.groups_id = Groups.id)) join ConfigDU on(config_groups.config_id = ConfigDU.id));

CREATE TABLE `Readers` (
  `id` int(10) UNSIGNED NOT NULL,
  `protocol` varchar(20) DEFAULT NULL,
  `address` tinyint(3) UNSIGNED DEFAULT NULL,
  `secure_key` varchar(32) DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `output` varchar(20) DEFAULT NULL,
  `pulse_time` smallint(5) UNSIGNED DEFAULT NULL,
  `sys_plan` smallint(5) UNSIGNED DEFAULT NULL,
  `monitor` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `monitor_default` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `max_open_time` smallint(5) UNSIGNED NOT NULL DEFAULT 30000,
  `has_monitor` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Readers(`id`, `protocol`, `active`, `output`, `pulse_time`, `sys_plan`, `monitor`, `monitor_default`, `max_open_time`,`has_monitor`) VALUES (1, 'wiegand',1, 'relay', 3000, 0, 0, 0, 30000,0);
INSERT INTO Readers(`id`, `protocol`, `active`, `output`, `pulse_time`, `sys_plan`, `monitor`, `monitor_default`, `max_open_time`,`has_monitor`) VALUES (2, 'wiegand',1, 'relay', 3000, 0, 0, 0, 30000,0);
INSERT INTO Readers(`id`, `protocol`, `active`, `output`, `pulse_time`, `sys_plan`, `monitor`, `monitor_default`, `max_open_time`,`has_monitor`) VALUES (3, NULL,0, 'relay', 3000, 0, 0, 0, 30000,0);
INSERT INTO Readers(`id`, `protocol`, `active`, `output`, `pulse_time`, `sys_plan`, `monitor`, `monitor_default`, `max_open_time`,`has_monitor`) VALUES (4, NULL,0, 'relay', 3000, 0, 0, 0, 30000,0);



DELIMITER $$
CREATE DEFINER=`ma`@`localhost`
PROCEDURE ActivateTempCasovePlany()
BEGIN
        START TRANSACTION;
        DELETE FROM CasovePlany;
        INSERT INTO CasovePlany SELECT * FROM tempCasovePlany;
  COMMIT;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=ma@localhost PROCEDURE cleandb()
BEGIN
    DECLARE rc INT;
    DECLARE max_lines INT;
    set max_lines=2000;
    SELECT COUNT(*) INTO rc FROM MeritAccessLocal.logs;
    if rc > max_lines THEN
        set rc=rc-max_lines;
        select rc;
        DELETE FROM MeritAccessLocal.logs
        WHERE id IN (
                SELECT id FROM (
                SELECT id FROM MeritAccessLocal.logs
                ORDER BY ts ASC
                LIMIT rc
                ) AS subquery
        );
    END IF;
    SELECT COUNT(*) INTO rc FROM MeritAccessLocal.Access;
    if rc > max_lines THEN
            set rc=rc-max_lines;
                select rc;
        DELETE FROM MeritAccessLocal.Access
                WHERE Id_Access IN (
                        SELECT Id_Access FROM (
                        SELECT Id_Access FROM MeritAccessLocal.Access
                        ORDER BY Kdy ASC
                        LIMIT rc
                        ) AS subquery
                );
      end if;
END$$
DELIMITER ;
CREATE DEFINER=`ma`@`localhost` EVENT `clean_event` ON SCHEDULE EVERY 1 DAY STARTS '2025-01-01 00:01:30' ON COMPLETION NOT PRESERVE ENABLE DO CALL MeritAccessLocal.cleandb();

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
