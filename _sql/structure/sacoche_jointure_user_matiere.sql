DROP TABLE IF EXISTS sacoche_jointure_user_matiere;

CREATE TABLE sacoche_jointure_user_matiere (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	matiere_id SMALLINT(5) UNSIGNED NOT NULL,
	jointure_coord TINYINT(1) NOT NULL,
	UNIQUE KEY user_matiere_key (user_id,matiere_id),
	KEY user_id (user_id),
	KEY matiere_id (matiere_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
