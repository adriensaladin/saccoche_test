DROP TABLE IF EXISTS sacoche_jointure_user_pilier;

CREATE TABLE sacoche_jointure_user_pilier (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	pilier_id SMALLINT(5) UNSIGNED NOT NULL,
	validation_pilier_date DATE NOT NULL,
	validation_pilier_info TINYTEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",
	UNIQUE KEY validation_pilier_key (user_id,pilier_id),
	KEY user_id (user_id),
	KEY pilier_id (pilier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
