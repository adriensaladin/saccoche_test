DROP TABLE IF EXISTS sacoche_jointure_user_groupe;

CREATE TABLE sacoche_jointure_user_groupe (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	groupe_id MEDIUMINT(8) UNSIGNED NOT NULL,
	jointure_pp TINYINT(1) NOT NULL,
	UNIQUE KEY user_groupe_key (user_id,groupe_id),
	KEY user_id (user_id),
	KEY groupe_id (groupe_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
