DROP TABLE IF EXISTS sacoche_rss;

CREATE TABLE sacoche_rss (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL,
	rss_date DATETIME NOT NULL,
	rss_titre VARCHAR(60) COLLATE utf8_unicode_ci NOT NULL,
	rss_contenu TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
	KEY user_id (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
