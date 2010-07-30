DROP TABLE IF EXISTS sacoche_saisie;

CREATE TABLE sacoche_saisie (
	prof_id MEDIUMINT(8) UNSIGNED NOT NULL,
	eleve_id MEDIUMINT(8) UNSIGNED NOT NULL,
	devoir_id MEDIUMINT(8) UNSIGNED NOT NULL,
	item_id MEDIUMINT(8) UNSIGNED NOT NULL,
	saisie_date DATE NOT NULL,
	saisie_note ENUM("VV","V","R","RR","ABS","NN","DISP","REQ") COLLATE utf8_unicode_ci NOT NULL,
	saisie_info TINYTEXT COLLATE utf8_unicode_ci NOT NULL COMMENT "Enregistrement statique du nom du devoir et du professeur, conservé les années suivantes.",
	UNIQUE KEY saisie_key (eleve_id,devoir_id,item_id),
	KEY prof_id (prof_id),
	KEY eleve_id (eleve_id),
	KEY devoir_id (devoir_id),
	KEY item_id (item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
