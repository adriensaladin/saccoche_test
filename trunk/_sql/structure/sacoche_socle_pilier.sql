DROP TABLE IF EXISTS sacoche_socle_pilier;

CREATE TABLE sacoche_socle_pilier (
	pilier_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	palier_id TINYINT(3) UNSIGNED NOT NULL,
	pilier_ordre TINYINT(3) UNSIGNED NOT NULL COMMENT "Commence à 1.",
	pilier_ref VARCHAR(2) COLLATE utf8_unicode_ci NOT NULL,
	pilier_nom VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (pilier_id),
	KEY palier_id (palier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_pilier DISABLE KEYS;

INSERT INTO sacoche_socle_pilier VALUES 
(  1, 1, 1,  "1", "Pilier 1 – La maîtrise de la langue française"),
(  2, 1, 2,  "3", "Pilier 3 – Les principaux éléments de mathématiques"),
(  3, 1, 3,  "6", "Pilier 6 – Les compétences sociales et civiques"),
(  4, 2, 1,  "1", "Pilier 1 – La maîtrise de la langue française"),
(  5, 2, 2,  "2", "Pilier 2 – La pratique d'une langue vivante étrangère (niveau A1)"),
(  6, 2, 3, "3a", "Pilier 3a – Les principaux éléments de mathématiques"),
(  7, 2, 4, "3b", "Pilier 3b – La culture scientifique et technologique"),
(  8, 2, 5,  "4", "Pilier 4 – La maîtrise des techniques usuelles de l'information et de la communication (B2i niveau école)"),
(  9, 2, 6,  "5", "Pilier 5 – La culture humaniste"),
( 10, 2, 7,  "6", "Pilier 6 – Les compétences sociales et civiques"),
( 11, 2, 8,  "7", "Pilier 7 – L'autonomie et l’initiative"),
( 12, 3, 1,  "1", "Pilier 1 – La maîtrise de la langue française"),
( 13, 3, 2,  "2", "Pilier 2 – La pratique d'une langue vivante étrangère (niveau A2)"),
( 14, 3, 3,  "3", "Pilier 3 – Les principaux éléments de mathématiques et la culture scientifique et technologique"),
( 15, 3, 4,  "4", "Pilier 4 – La maîtrise des techniques usuelles de l'information et de la communication (B2i)"),
( 16, 3, 5,  "5", "Pilier 5 – La culture humaniste"),
( 17, 3, 6,  "6", "Pilier 6 – Les compétences sociales et civiques"),
( 18, 3, 7,  "7", "Pilier 7 – L'autonomie et l'initiative");

ALTER TABLE sacoche_socle_pilier ENABLE KEYS;
