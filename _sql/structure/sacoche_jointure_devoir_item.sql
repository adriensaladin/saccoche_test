DROP TABLE IF EXISTS sacoche_jointure_devoir_item;

CREATE TABLE sacoche_jointure_devoir_item (
	devoir_id      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	item_id        MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
	jointure_ordre TINYINT(3)   UNSIGNED NOT NULL DEFAULT 0,
	UNIQUE KEY devoir_item_key (devoir_id,item_id),
	KEY devoir_id (devoir_id),
	KEY item_id (item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
