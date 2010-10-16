DROP TABLE IF EXISTS sacoche_jointure_groupe_periode;

CREATE TABLE sacoche_jointure_groupe_periode (
	groupe_id           MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	periode_id          MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	jointure_date_debut DATE                  NOT NULL DEFAULT "0000-00-00",
	jointure_date_fin   DATE                  NOT NULL DEFAULT "0000-00-00",
	UNIQUE KEY groupe_periode_key (groupe_id,periode_id),
	KEY groupe_id (groupe_id),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
