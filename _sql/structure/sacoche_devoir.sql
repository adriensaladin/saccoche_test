DROP TABLE IF EXISTS sacoche_devoir;

CREATE TABLE sacoche_devoir (
	devoir_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	prof_id MEDIUMINT(8) UNSIGNED NOT NULL,
	groupe_id MEDIUMINT(8) UNSIGNED NOT NULL,
	devoir_date DATE NOT NULL,
	devoir_info VARCHAR(60) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (devoir_id),
	KEY prof_id (prof_id),
	KEY groupe_id (groupe_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
