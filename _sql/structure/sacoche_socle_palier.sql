DROP TABLE IF EXISTS sacoche_socle_palier;

CREATE TABLE sacoche_socle_palier (
	palier_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	palier_ordre TINYINT(3) UNSIGNED NOT NULL,
	palier_nom VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (palier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_palier DISABLE KEYS;

INSERT INTO sacoche_socle_palier VALUES 
( 1, 1, "Palier 1 (fin CE1)"),
( 2, 2, "Palier 2 (fin CM2)"),
( 3, 3, "Palier 3 (fin troisième)"),
( 4, 4, "Palier 4 (lycée, indéfini)");

ALTER TABLE sacoche_socle_palier ENABLE KEYS;
