DROP TABLE IF EXISTS sacoche_referentiel_item;

CREATE TABLE sacoche_referentiel_item (
	item_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	theme_id SMALLINT(5) UNSIGNED NOT NULL,
	entree_id SMALLINT(5) UNSIGNED NOT NULL,
	item_ordre TINYINT(3) UNSIGNED NOT NULL COMMENT "Commence à 0.",
	item_nom TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
	item_coef TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
	item_lien TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (item_id),
	KEY theme_id (theme_id),
	KEY entree_id (entree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
