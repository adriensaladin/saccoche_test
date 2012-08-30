DROP TABLE IF EXISTS sacoche_socle_pilier;

CREATE TABLE sacoche_socle_pilier (
	pilier_id    TINYINT(3)   UNSIGNED                NOT NULL AUTO_INCREMENT,
	palier_id    TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
	pilier_ordre TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 1 COMMENT "Commence à 1.",
	pilier_ref   VARCHAR(2)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	pilier_nom   VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (pilier_id),
	KEY palier_id (palier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_pilier DISABLE KEYS;

INSERT INTO sacoche_socle_pilier VALUES 
(  1, 3, 1,  "1", "Compétence 1 – La maîtrise de la langue française"),
(  2, 3, 2,  "2", "Compétence 2 – La pratique d’une langue vivante étrangère (niveau A2)"),
(  3, 3, 3,  "3", "Compétence 3 – Les principaux éléments de mathématiques et la culture scientifique et technologique"),
(  4, 3, 4,  "4", "Compétence 4 – La maîtrise des techniques usuelles de l’information et de la communication (B2i)"),
(  5, 3, 5,  "5", "Compétence 5 – La culture humaniste"),
(  6, 3, 6,  "6", "Compétence 6 – Les compétences sociales et civiques"),
(  7, 3, 7,  "7", "Compétence 7 – L’autonomie et l’initiative"),
(  8, 2, 1,  "1", "Compétence 1 – La maîtrise de la langue française"),
(  9, 2, 2,  "2", "Compétence 2 – La pratique d’une langue vivante étrangère (niveau A1)"),
( 10, 2, 3, "3a", "Compétence 3a – Les principaux éléments de mathématiques"),
( 11, 2, 4, "3b", "Compétence 3b – La culture scientifique et technologique"),
( 12, 2, 5,  "4", "Compétence 4 – La maîtrise des techniques usuelles de l’information et de la communication (B2i niveau école)"),
( 13, 2, 6,  "5", "Compétence 5 – La culture humaniste"),
( 14, 2, 7,  "6", "Compétence 6 – Les compétences sociales et civiques"),
( 15, 2, 8,  "7", "Compétence 7 – L’autonomie et l’initiative"),
( 16, 1, 1,  "1", "Compétence 1 – La maîtrise de la langue française"),
( 17, 1, 2,  "3", "Compétence 3 – Les principaux éléments de mathématiques"),
( 18, 1, 3,  "6", "Compétence 6 – Les compétences sociales et civiques");

ALTER TABLE sacoche_socle_pilier ENABLE KEYS;
