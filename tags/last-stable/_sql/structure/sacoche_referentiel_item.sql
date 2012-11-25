DROP TABLE IF EXISTS sacoche_referentiel_item;

CREATE TABLE sacoche_referentiel_item (
	item_id    MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
	theme_id   SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	entree_id  SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	item_ordre TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Commence à 0.",
	item_nom   VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	item_coef  TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 1,
	item_cart  TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1 COMMENT "0 pour empêcher les élèves de demander une évaluation sur cet item.",
	item_lien  VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (item_id),
	KEY theme_id (theme_id),
	KEY entree_id (entree_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
