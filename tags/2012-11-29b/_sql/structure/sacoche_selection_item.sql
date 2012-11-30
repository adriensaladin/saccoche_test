DROP TABLE IF EXISTS sacoche_selection_item;

CREATE TABLE sacoche_selection_item (
	selection_item_id    MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
	user_id              MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	selection_item_nom   VARCHAR(60)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	selection_item_liste TEXT         COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (selection_item_id),
	KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
