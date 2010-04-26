DROP TABLE IF EXISTS sacoche_jointure_groupe_periode;

CREATE TABLE sacoche_jointure_groupe_periode (
	groupe_id MEDIUMINT(8) UNSIGNED NOT NULL,
	periode_id MEDIUMINT(8) UNSIGNED NOT NULL,
	jointure_date_debut DATE NOT NULL,
	jointure_date_fin DATE NOT NULL,
	UNIQUE KEY groupe_periode_key (groupe_id,periode_id),
	KEY groupe_id (groupe_id),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
