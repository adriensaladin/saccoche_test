DROP TABLE IF EXISTS sacoche_jointure_user_pilier;

CREATE TABLE sacoche_jointure_user_pilier (
	user_id                MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	pilier_id              TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
	validation_pilier_etat TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1  COMMENT "1 si validation positive ; 0 si validation négative.",
	validation_pilier_date DATE                                 NOT NULL DEFAULT "0000-00-00",
	validation_pilier_info VARCHAR(25)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",
	UNIQUE KEY validation_pilier_key (user_id,pilier_id),
	KEY user_id (user_id),
	KEY pilier_id (pilier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
