DROP TABLE IF EXISTS sacoche_socle_palier;

CREATE TABLE sacoche_socle_palier (
	palier_id    TINYINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
	palier_actif TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
	palier_ordre TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	palier_nom   VARCHAR(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (palier_id),
	KEY palier_actif (palier_actif)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_palier DISABLE KEYS;

INSERT INTO sacoche_socle_palier VALUES 
( 1, 0, 1, "Palier 1 (fin CE1)"),
( 2, 0, 2, "Palier 2 (fin CM2)"),
( 3, 0, 3, "Palier 3 (fin troisième)");

ALTER TABLE sacoche_socle_palier ENABLE KEYS;
