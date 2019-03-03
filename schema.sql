SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DELIMITER $$
--
-- Funktionen
--
CREATE  FUNCTION `DURATION_STRING` (`dur_sec` INT) RETURNS VARCHAR(12) CHARSET utf8 return CONCAT( IF( FLOOR( dur_sec / 3600 ) <= 99, RIGHT( CONCAT( '00', FLOOR( dur_sec / 3600 ) ), 2 ), FLOOR( dur_sec / 3600 ) ), ':', RIGHT( CONCAT( '00', FLOOR( MOD( dur_sec, 3600 ) / 60 ) ), 2 ), ':', RIGHT( CONCAT( '00', MOD( dur_sec, 60 ) ), 2 ) )$$

CREATE  FUNCTION `MAKE_MOVIE_SORTKEY` (`title` VARCHAR(255), `skey` VARCHAR(255)) RETURNS VARCHAR(255) CHARSET utf8 RETURN IF( skey IS NOT NULL, skey, IF( LEFT( title, 4 ) IN ('Der ', 'Die ', 'Das ', 'The '), MID( title, 5 ), title ) )$$

CREATE  FUNCTION `MAKE_MOVIE_TITLE` (`movie_title` VARCHAR(255), `comment` VARCHAR(255), `series_name` VARCHAR(255), `episode` VARCHAR(100), `prepend` BOOL, `omu` BOOL) RETURNS VARCHAR(255) CHARSET utf8 return concat(CONCAT(IF(prepend,
                    CONCAT(IF((series_name IS NOT NULL),
                                CONCAT(series_name,
                                        IF((episode IS NOT NULL),
                                            CONCAT(' ', episode),
                                            ''),
                                        ': '),
                                '')),
                    ''),
                movie_title,
                IF((comment IS NOT NULL),
                    CONCAT(' (', comment, ')'),
                    '')), if(omu, ' [Original mit Untertitel]', ''))$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE `categories` (
  `id` int(4) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(3, 'Dokumentation'),
(4, 'Konzert'),
(2, 'Märchen- oder Jugendfilm'),
(1, 'Spielfilm');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `disc`
--

CREATE TABLE `disc` (
  `ID` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `vdvd` bit(1) NOT NULL,
  `regular` bit(1) NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `disc_durations`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `disc_durations` (
`duration` decimal(32,0)
,`title` varchar(100)
);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `DVDAuswahl`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `DVDAuswahl` (
`ID` int(6)
,`Titel` varchar(255)
,`Länge` varchar(12)
,`Dateiname` varchar(255)
,`DVD` varchar(100)
,`Kategorie` varchar(100)
,`Sprachen` text
,`Reihe` varchar(100)
,`disc` int(10)
,`category` int(10)
,`series_id` int(4)
,`dur_sec` int(8)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `episode_series`
--

CREATE TABLE `episode_series` (
  `id` int(4) NOT NULL,
  `series_id` int(4) NOT NULL,
  `movie_id` int(10) NOT NULL,
  `episode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `languages`
--

CREATE TABLE `languages` (
  `id` char(3) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

--
-- Daten für Tabelle `languages`
--

INSERT INTO `languages` (`id`, `name`) VALUES
('ara', 'arabisch'),
('zho', 'chinesisch'),
('dan', 'dänisch'),
('ger', 'deutsch'),
('eng', 'englisch'),
('fra', 'französisch'),
('gre', 'griechisch'),
('hin', 'hindi'),
('ita', 'italienisch'),
('jpn', 'japanisch'),
('kor', 'koreanisch'),
('hrv', 'kroatisch'),
('per', 'persisch'),
('por', 'portugiesisch'),
('ron', 'rumänisch'),
('rus', 'russisch'),
('gsw', 'schweizerdeutsch'),
('spa', 'spanisch'),
('tha', 'thailändisch'),
('tur', 'türkisch'),
('und', 'undefiniert');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `movies`
--

CREATE TABLE `movies` (
  `ID` int(6) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration` int(8) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `disc` int(10) NOT NULL DEFAULT '263',
  `skey` varchar(100) DEFAULT NULL,
  `category` int(10) NOT NULL DEFAULT '1',
  `omu` bit(1) NOT NULL DEFAULT b'0',
  `top250` bit(1) NOT NULL DEFAULT b'0',
  `spooky` bit(1) DEFAULT NULL,
  `omdb_id` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `movie_languages`
--

CREATE TABLE `movie_languages` (
  `id` int(5) NOT NULL,
  `movie_id` int(6) NOT NULL,
  `lang_id` char(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `series`
--

CREATE TABLE `series` (
  `id` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `prepend` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 KEY_BLOCK_SIZE=8 ROW_FORMAT=COMPRESSED;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `statistics`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `statistics` (
`stat` varchar(32)
,`duration` varchar(53)
,`cid` bigint(20)
,`category` varchar(100)
,`mid` int(11)
,`title` varchar(255)
,`ord` bigint(20)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `admin` bit(1) NOT NULL DEFAULT b'0',
  `last_login` timestamp NULL DEFAULT NULL,
  `style` tinyint(4) DEFAULT '0',
  `fid` varchar(4096) DEFAULT NULL,
  `pagesize` tinyint(4) NOT NULL DEFAULT '24',
  `oauth_access_token` text,
  `oauth_access_token_secret` text,
  `consumer_key` text,
  `consumer_secret` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_plogins`
--

CREATE TABLE `users_plogins` (
  `uid` int(11) NOT NULL,
  `token` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_ratings`
--

CREATE TABLE `user_ratings` (
  `uid` int(11) NOT NULL,
  `movie_id` int(6) NOT NULL,
  `rating` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur des Views `disc_durations`
--
DROP TABLE IF EXISTS `disc_durations`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `disc_durations`  AS  select sum(`movies`.`duration`) AS `duration`,`disc`.`name` AS `title` from (`disc` left join `movies` on((`movies`.`disc` = `disc`.`ID`))) group by `disc`.`ID` ;

-- --------------------------------------------------------

--
-- Struktur des Views `DVDAuswahl`
--
DROP TABLE IF EXISTS `DVDAuswahl`;

CREATE ALGORITHM=TEMPTABLE  SQL SECURITY DEFINER VIEW `DVDAuswahl`  AS  select `movies`.`ID` AS `ID`,`MAKE_MOVIE_TITLE`(`movies`.`title`,`movies`.`comment`,`series`.`name`,`episode_series`.`episode`,`series`.`prepend`) AS `Titel`,`DURATION_STRING`(`movies`.`duration`) AS `Länge`,if((`movies`.`filename` is not null),`movies`.`filename`,'n.V.') AS `Dateiname`,`disc`.`name` AS `DVD`,`categories`.`name` AS `Kategorie`,group_concat(`languages`.`name` separator ', ') AS `Sprachen`,`series`.`name` AS `Reihe`,`movies`.`disc` AS `disc`,`movies`.`category` AS `category`,`episode_series`.`series_id` AS `series_id`,`movies`.`duration` AS `dur_sec` from ((((((`movies` left join `disc` on((`movies`.`disc` = `disc`.`ID`))) left join `movie_languages` on((`movies`.`ID` = `movie_languages`.`movie_id`))) left join `languages` on((`movie_languages`.`lang_id` = `languages`.`id`))) left join `categories` on((`movies`.`category` = `categories`.`id`))) left join `episode_series` on((`episode_series`.`movie_id` = `movies`.`ID`))) left join `series` on((`episode_series`.`series_id` = `series`.`id`))) group by `movies`.`ID` ;

-- --------------------------------------------------------

--
-- Struktur des Views `statistics`
--
DROP TABLE IF EXISTS `statistics`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `statistics`  AS  select 'Längste DVD' AS `stat`,`DURATION_STRING`(`lv`.`duration`) AS `duration`,1 AS `cid`,'über alle Kategorien' AS `category`,NULL AS `mid`,`lv`.`title` AS `title`,2 AS `ord` from `disc_durations` `lv` where `lv`.`duration` in (select max(`lv2`.`duration`) from `disc_durations` `lv2`) union all select 'Kürzeste DVD' AS `stat`,`DURATION_STRING`(`lv`.`duration`) AS `duration`,1 AS `cid`,'über alle Kategorien' AS `category`,NULL AS `mid`,`lv`.`title` AS `title`,2 AS `ord` from `disc_durations` `lv` where `lv`.`duration` in (select min(`lv2`.`duration`) from `disc_durations` `lv2`) union all select 'Durchschnittliche DVD-Spiellänge' AS `stat`,`DURATION_STRING`(avg(`lv`.`duration`)) AS `duration`,1 AS `cid`,'über alle Kategorien' AS `category`,NULL AS `mid`,NULL AS `title`,2 AS `ord` from `disc_durations` `lv` union all select 'Längstes Video' AS `stat`,`DURATION_STRING`(`movies`.`duration`) AS `duration`,`categories`.`id` AS `cid`,`categories`.`name` AS `category`,`movies`.`ID` AS `mid`,`movies`.`title` AS `title`,1 AS `ord` from (`movies` left join `categories` on((`categories`.`id` = `movies`.`category`))) where `movies`.`duration` in (select max(`movies`.`duration`) from (`movies` left join `categories` on((`movies`.`category` = `categories`.`id`))) group by `movies`.`category`) union all select 'Längstes Video' AS `stat`,`DURATION_STRING`(`movies`.`duration`) AS `duration`,-(1) AS `cid`,'über alle Kategorien' AS `category`,`movies`.`ID` AS `mid`,`movies`.`title` AS `title`,1 AS `ord` from `movies` where `movies`.`duration` in (select max(`movies`.`duration`) from `movies`) union all select 'Kürzestes Video' AS `stat`,`DURATION_STRING`(`movies`.`duration`) AS `duration`,`categories`.`id` AS `cid`,`categories`.`name` AS `category`,`movies`.`ID` AS `mid`,`movies`.`title` AS `title`,1 AS `ord` from (`movies` left join `categories` on((`categories`.`id` = `movies`.`category`))) where `movies`.`duration` in (select min(`movies`.`duration`) from (`movies` left join `categories` on((`movies`.`category` = `categories`.`id`))) group by `movies`.`category`) union all select 'Kürzestes Video' AS `stat`,`DURATION_STRING`(`movies`.`duration`) AS `duration`,-(1) AS `cid`,'über alle Kategorien' AS `category`,`movies`.`ID` AS `mid`,`movies`.`title` AS `title`,1 AS `ord` from `movies` where `movies`.`duration` in (select min(`movies`.`duration`) from `movies`) union all select 'Durchschnittliche Videolänge' AS `stat`,`DURATION_STRING`(avg(`movies`.`duration`)) AS `duration`,-(1) AS `cid`,'über alle Kategorien' AS `category`,NULL AS `mid`,NULL AS `title`,1 AS `ord` from `movies` union all select 'Durchschnittliche Videolänge' AS `stat`,`DURATION_STRING`(avg(`movies`.`duration`)) AS `val`,`categories`.`id` AS `cid`,`categories`.`name` AS `category`,NULL AS `mid`,NULL AS `title`,1 AS `ord` from (`movies` left join `categories` on((`categories`.`id` = `movies`.`category`))) group by `movies`.`category` union all select 'Qualität' AS `stat`,concat(round(((count(`movies`.`ID`) * 100) / 250),1),' %') AS `val`,1 AS `cid`,'von den imdb-Top250-Filmen' AS `category`,NULL AS `mid`,NULL AS `title`,0 AS `ord` from `movies` where (`movies`.`top250` is true) union all select 'Qualität' AS `stat`,concat(round(((count(`movies`.`ID`) * 100) / (select count(`movies`.`ID`) from `movies` where ((`movies`.`category` = 1) or (`movies`.`category` = 2)))),1),' %') AS `val`,1 AS `cid`,'Anteil an Schrott- & Rentnerfilmen' AS `category`,NULL AS `mid`,NULL AS `title`,0 AS `ord` from `movies` where (((`movies`.`category` = 1) or (`movies`.`category` = 2)) and (`movies`.`top250` is false)) union all (select 'Durchschnittliche Bewertung' AS `stat`,('' + avg(`user_ratings`.`rating`)) AS `val`,`movies`.`category` AS `cid`,`categories`.`name` AS `category`,NULL AS `mid`,NULL AS `title`,3 AS `ord` from ((`user_ratings` left join `movies` on((`movies`.`ID` = `user_ratings`.`movie_id`))) left join `categories` on((`movies`.`category` = `categories`.`id`))) group by `movies`.`category`) order by `ord`,`stat` desc,`cid` ;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indizes für die Tabelle `disc`
--
ALTER TABLE `disc`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`),
  ADD KEY `id_name` (`ID`,`name`);

--
-- Indizes für die Tabelle `episode_series`
--
ALTER TABLE `episode_series`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `movie_id` (`movie_id`),
  ADD KEY `series_id` (`series_id`);

--
-- Indizes für die Tabelle `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

--
-- Indizes für die Tabelle `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `id_skey` (`skey`),
  ADD KEY `id_disc` (`disc`),
  ADD KEY `id_category` (`category`),
  ADD KEY `id_title` (`title`),
  ADD KEY `id_top250` (`top250`),
  ADD KEY `id_spooky` (`spooky`);

--
-- Indizes für die Tabelle `movie_languages`
--
ALTER TABLE `movie_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_id` (`lang_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `la_mo_id` (`movie_id`,`lang_id`);

--
-- Indizes für die Tabelle `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `login_UNIQUE` (`login`);

--
-- Indizes für die Tabelle `users_plogins`
--
ALTER TABLE `users_plogins`
  ADD PRIMARY KEY (`uid`);

--
-- Indizes für die Tabelle `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`uid`,`movie_id`),
  ADD KEY `m_movie_id` (`movie_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `disc`
--
ALTER TABLE `disc`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `episode_series`
--
ALTER TABLE `episode_series`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `movies`
--
ALTER TABLE `movies`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `movie_languages`
--
ALTER TABLE `movie_languages`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `series`
--
ALTER TABLE `series`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `episode_series`
--
ALTER TABLE `episode_series`
  ADD CONSTRAINT `episode_series_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_episode_series_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`disc`) REFERENCES `disc` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `movies_ibfk_2` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `movie_languages`
--
ALTER TABLE `movie_languages`
  ADD CONSTRAINT `movie_languages_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;
