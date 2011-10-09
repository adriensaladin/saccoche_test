DROP TABLE IF EXISTS sacoche_jointure_user_entree;

CREATE TABLE sacoche_jointure_user_entree (
	user_id                MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	entree_id              SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	validation_entree_etat TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1  COMMENT "1 si validation positive ; 0 si validation négative.",
	validation_entree_date DATE                                 NOT NULL DEFAULT "0000-00-00",
	validation_entree_info VARCHAR(25)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",
	UNIQUE KEY validation_entree_key (user_id,entree_id),
	KEY user_id (user_id),
	KEY entree_id (entree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
