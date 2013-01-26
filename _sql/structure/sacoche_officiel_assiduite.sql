DROP TABLE IF EXISTS sacoche_officiel_assiduite;

CREATE TABLE sacoche_officiel_assiduite (
	periode_id             MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	user_id                MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	assiduite_absence      TINYINT(3)   UNSIGNED          DEFAULT NULL COMMENT "nombre total d'absences",
	assiduite_non_justifie TINYINT(3)   UNSIGNED          DEFAULT NULL COMMENT "nombre d'absences non justifiées",
	assiduite_retard       TINYINT(3)   UNSIGNED          DEFAULT NULL COMMENT "nombre de retards",
	PRIMARY KEY ( user_id , periode_id ),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
