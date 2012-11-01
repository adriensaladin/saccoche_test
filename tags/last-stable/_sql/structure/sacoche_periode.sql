DROP TABLE IF EXISTS sacoche_periode;

CREATE TABLE sacoche_periode (
	periode_id    MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
	periode_ordre TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 1,
	periode_nom   VARCHAR(40)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
