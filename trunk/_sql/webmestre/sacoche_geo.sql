DROP TABLE IF EXISTS sacoche_geo;

CREATE TABLE sacoche_geo (
	geo_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	geo_ordre TINYINT(3) UNSIGNED NOT NULL,
	geo_nom VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (geo_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO sacoche_geo VALUES (1, 1, '');
