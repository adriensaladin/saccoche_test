DROP TABLE IF EXISTS sacoche_referentiel_theme;

CREATE TABLE sacoche_referentiel_theme (
	theme_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	domaine_id SMALLINT(5) UNSIGNED NOT NULL,
	theme_ordre TINYINT(3) UNSIGNED NOT NULL COMMENT "Commence à 1.",
	theme_nom VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (theme_id),
	KEY domaine_id (domaine_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
