DROP TABLE IF EXISTS sacoche_jointure_user_entree;

CREATE TABLE sacoche_jointure_user_entree (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	entree_id SMALLINT(5) UNSIGNED NOT NULL,
	validation_entree_etat TINYINT(1) NOT NULL COMMENT "1 si validation positive ; 0 si validation négative.",
	validation_entree_date DATE NOT NULL,
	validation_entree_info TINYTEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",
	UNIQUE KEY validation_entree_key (user_id,entree_id),
	KEY user_id (user_id),
	KEY entree_id (entree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
