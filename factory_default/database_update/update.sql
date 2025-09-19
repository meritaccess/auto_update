
-- rucne upraveno by jara 15.9.2025
-- add config value syslogserver
INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'syslogserver', '', '.*?', '192.168.10.201'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'syslogserver'
);

CREATE TABLE IF NOT EXISTS CasovePlany ( Id_CasovyPlan INT AUTO_INCREMENT PRIMARY KEY, Cislo INT NOT NULL, Nazev VARCHAR(50) NOT NULL, Popis VARCHAR(50) NULL, RezimOtevirani VARCHAR(20) NOT NULL,
    Po_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ut_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    St_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ct_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Pa_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    So_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ne_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Svatky_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00' );

DROP PROCEDURE IF EXISTS SetVal;
DROP PROCEDURE IF EXISTS CanAccess;

-- add config value SYSPLANREADER1
INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'SYSPLANREADER1', '0', '^(?:[0-9]|[1-5][0-9]|6[0-3])$', '0=noplan'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'SYSPLANREADER1'
);
-- add config value SYSPLANREADER2
INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'SYSPLANREADER2', '0', '^(?:[0-9]|[1-5][0-9]|6[0-3])$', '0=noplan'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'SYSPLANREADER2'
);

DELETE FROM running WHERE property = 'R1ReadError';
DELETE FROM running WHERE property = 'R2ReadError';

CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`ma`@`localhost` SQL SECURITY DEFINER VIEW `AccessDetails`  AS SELECT `Access`.`Kdy` AS `Kdy`, `Access`.`Karta` AS `Karta`, `Access`.`Ctecka` AS `Ctecka`, `Access`.`StavZpracovani` AS `StavZpracovani`, `Karty`.`Pozn` AS `Pozn`, `Karty`.`Povoleni` AS `Povoleni`, `Karty`.`Smazano` AS `Smazano`, `Karty`.`cardid` AS `cardid` FROM (`Access` left join `Karty` on(rtrim(`Access`.`Karta`) = rtrim(`Karty`.`Karta`))) ;

-- add config value ivar
INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'enable_ivar', 0, '^(?:[0-9]|[1-5][0-9]|6[0-3])$', '0 not used'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'enable_ivar'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'ivar_server', 'http://10.10.10.1', '.*', 'ipadress of ivar server'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'ivar_server'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'ivar_term_name1', 'term1', '.*', 'terminal name'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'ivar_term_name1'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'ivar_term_name2', 'term2', '.*', 'terminal name'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'ivar_term_name2'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'swap_wiegand_pins', '0', '[0-1]+', '0 - default state, 1 - use monitor and open for reader'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'swap_wiegand_pins'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'disable_web', '0', '[0-1]+', '0 - default, 1 - disable'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'disable_web'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'disable_ssh', '0', '[0-1]+', '0 - default, 1 - disable'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'disable_ssh'
);

INSERT INTO `ConfigDU` (`property`, `value`, `regex`, `sample`)
SELECT 'disable_ssh_password', '0', '[0-1]+', '0 - default, 1 - disable'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'disable_ssh_password'
);


CREATE TABLE IF NOT EXISTS tempCasovePlany ( Id_CasovyPlan INT AUTO_INCREMENT PRIMARY KEY, Cislo INT NOT NULL, Nazev VARCHAR(50) NOT NULL, Popis VARCHAR(50) NULL, RezimOtevirani VARCHAR(20) NOT NULL,
    Po_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Po_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ut_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ut_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    St_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', St_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ct_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ct_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Pa_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Pa_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    So_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', So_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Ne_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Ne_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00',
    Svatky_PrvniZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_PrvniKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyZacatek VARCHAR(8) NOT NULL DEFAULT '00:00:00', Svatky_DruhyKonec VARCHAR(8) NOT NULL DEFAULT '00:00:00' );


DROP PROCEDURE IF EXISTS ActivateTempCasovePlany;

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


UPDATE ConfigDU
SET value = 'meritaccess'
WHERE property = 'appupdate' AND value = 'meritaccess/merit_access';

DROP PROCEDURE IF EXISTS cleandb;
DROP EVENT IF EXISTS clean_event;

CREATE DEFINER=`ma`@`localhost` EVENT clean_event ON SCHEDULE EVERY 1 DAY STARTS '2024-09-19 12:01:20' ON COMPLETION NOT PRESERVE ENABLE DO CALL MeritAccessLocal.cleandb();

DELIMITER $$
CREATE DEFINER=`ma`@`localhost` PROCEDURE cleandb()
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

DELETE FROM running WHERE property = 'R1ReadCount';
DELETE FROM running WHERE property = 'R2ReadCount';

INSERT INTO running (`property`, `value`)
SELECT 'change_time', ''
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM running WHERE property = 'change_time');

INSERT INTO ConfigDU (`property`, `value`, `regex`, `sample`)
SELECT 'enable_osdp', 0, '[0-1]+', 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'enable_osdp'
);

INSERT INTO ConfigDU (`property`, `value`, `regex`, `sample`)
SELECT 'use_secure_channel', 1, '[0-1]+', '0 - not use, 1 - use'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `ConfigDU`
    WHERE `property` = 'use_secure_channel'
);

CREATE TABLE IF NOT EXISTS `Groups` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `groupname` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT IGNORE INTO `Groups` (`id`, `groupname`) VALUES
(1, 'Common'),
(2, 'IP'),
(3, 'WiFi'),
(4, 'MQTT'),
(5, 'IVAR'),
(6, 'OSDP');

-- 1. Zjistíme, zda tabulka už existuje
SET @table_exists := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'config_groups'
);

-- 2. Pokud neexistuje, vytvoříme ji
CREATE TABLE IF NOT EXISTS `config_groups` (
  `config_id` int(11) NOT NULL,
  `groups_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`config_id`, `groups_id`),
  KEY `fk_groups_id` (`groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Pokud tabulka předtím neexistovala, přidáme klíče
SET @add_constraints := IF(@table_exists = 0,
  'ALTER TABLE `config_groups`
     ADD CONSTRAINT `fk_config_id` FOREIGN KEY (`config_id`) REFERENCES `ConfigDU` (`id`),
     ADD CONSTRAINT `fk_groups_id` FOREIGN KEY (`groups_id`) REFERENCES `Groups` (`id`);',
  'SELECT "Tabulka již existovala, klíče se nepřidávají";'
);

-- 4. Provedeme příkaz pro přidání klíčů
PREPARE stmt FROM @add_constraints;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;




INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'mode%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'ws%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'appupdate%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'easy_add%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'easy_remove%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'update_mode%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'syslogserver%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'swap_wiegand_pins%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_web%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_ssh%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'disable_ssh_password%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 1, id from ConfigDU WHERE property like 'maxRows%';

INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dhcp%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'ip%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'mask%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dg%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 2, id from ConfigDU WHERE property like 'dns1%';

INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'enablewifi%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'ssid%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'wifiuser%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 3, id from ConfigDU WHERE property like 'wifipass%';

INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqttenabled%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqttserver%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 4, id from ConfigDU WHERE property like 'mqtttopic%';

INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'enable_ivar%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_server%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_term_name1%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 5, id from ConfigDU WHERE property like 'ivar_term_name2%';

INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 6, id from ConfigDU WHERE property like 'enable_osdp%';
INSERT ignore into `config_groups` (`groups_id`,`config_id` ) select 6, id from ConfigDU WHERE property like 'use_secure_channel%';


CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`ma`@`localhost` SQL SECURITY DEFINER VIEW ConfigByGroups AS SELECT Groups.groupname AS groupname, ConfigDU.id AS id, ConfigDU.property AS property, ConfigDU.value AS value, ConfigDU.regex AS regex, ConfigDU.sample AS sample FROM ((config_groups join Groups on(config_groups.groups_id = Groups.id)) join ConfigDU on(config_groups.config_id = ConfigDU.id));


DELIMITER $$

CREATE PROCEDURE update_readers()
BEGIN
    DECLARE table_exists INT DEFAULT 0;
    DECLARE has_column INT DEFAULT 0;
    DECLARE sysplan1 INT DEFAULT 0;
    DECLARE sysplan2 INT DEFAULT 0;

    -- Zjistíme, zda tabulka Readers existuje
    SELECT COUNT(*)
    INTO table_exists
    FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 'Readers';

    -- Pokud tabulka existuje, zjistíme, jestli má sloupec max_open_time
    IF table_exists = 1 THEN
        SELECT COUNT(*)
        INTO has_column
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'Readers'
          AND column_name = 'max_open_time';
    END IF;

    -- Načteme hodnoty SYSPLANREADER1 a SYSPLANREADER2 z ConfigDU
SELECT COALESCE((
  SELECT CAST(`value` AS UNSIGNED)
   FROM `ConfigDU`
   WHERE `property`='SYSPLANREADER1'
   LIMIT 1
  ), 0)
INTO sysplan1;

SELECT COALESCE((
  SELECT CAST(`value` AS UNSIGNED)
  FROM `ConfigDU`
  WHERE `property`='SYSPLANREADER2'
  LIMIT 1
 ), 0)
 INTO sysplan2;

    -- Vytvoření nové tabulky nebo přepsání staré
    IF table_exists = 0 OR has_column = 0 THEN
        IF table_exists = 1 AND has_column = 0 THEN
            DROP TABLE IF EXISTS `Readers`;
        END IF;
        CREATE TABLE `Readers` (`id` int(10) UNSIGNED NOT NULL, `protocol` varchar(20) DEFAULT NULL,`address` tinyint(3) UNSIGNED DEFAULT NULL,`secure_key` varchar(32) DEFAULT NULL,`active` tinyint(1) UNSIGNED DEFAULT NULL,`output` varchar(20) DEFAULT NULL,
                				`pulse_time` smallint(5) UNSIGNED DEFAULT NULL,`sys_plan` smallint(5) UNSIGNED DEFAULT NULL,`monitor` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,`monitor_default` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,`max_open_time` smallint(5) UNSIGNED NOT NULL DEFAULT 30000,
                                 PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
                                 
        INSERT IGNORE INTO Readers(`id`, `protocol`, `active`, `output`, `pulse_time`, `sys_plan`, `monitor`, `monitor_default`, `max_open_time`) VALUES
        (1, 'wiegand',1, 'relay', 3000, 0, 0, 0, 30000),
        (2, 'wiegand',1, 'relay', 3000, 0, 0, 0, 30000),
        (3, NULL,0, 'relay', 3000, 0, 0, 0, 30000),
        (4, NULL,0, 'relay', 3000, 0, 0, 0, 30000);
    END IF;
    UPDATE Readers SET sys_plan = sysplan1 WHERE id = 1;
    UPDATE Readers SET sys_plan = sysplan2 WHERE id = 2;
    DELETE FROM `ConfigDU` WHERE `property` IN ('SYSPLANREADER1','SYSPLANREADER2');
END$$
DELIMITER ;

CALL update_readers();

DROP PROCEDURE IF EXISTS update_readers;

INSERT INTO `ConfigDU` (`property`,`value`,`regex`,`sample`) SELECT 'log_level','info','.*','debug, info, warn, error, critical'
FROM DUAL
WHERE NOT EXISTS ( SELECT 1 FROM `ConfigDU` WHERE `property`='log_level' );

INSERT INTO `config_groups` (`groups_id`, `config_id`) SELECT 1, c.`id` FROM `ConfigDU` c
WHERE c.`property` LIKE 'log_level%'
  AND NOT EXISTS (
    SELECT 1
    FROM `config_groups` cg
    WHERE cg.`groups_id` = 1
      AND cg.`config_id` = c.`id`
);

ALTER TABLE `Readers` ADD COLUMN IF NOT EXISTS `has_monitor` TINYINT UNSIGNED NOT NULL DEFAULT 0;

