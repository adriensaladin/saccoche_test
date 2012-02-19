DROP TABLE IF EXISTS sacoche_jointure_user_matiere;

CREATE TABLE sacoche_jointure_user_matiere (
	user_id        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	matiere_id     SMALLINT(5)  UNSIGNED NOT NULL DEFAULT 0,
	jointure_coord TINYINT(1)   UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY ( user_id , matiere_id ),
	KEY matiere_id (matiere_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
