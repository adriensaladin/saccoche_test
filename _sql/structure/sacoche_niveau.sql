DROP TABLE IF EXISTS sacoche_niveau;

CREATE TABLE sacoche_niveau (
	niveau_id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	palier_id TINYINT(3) UNSIGNED NOT NULL,
	niveau_ordre TINYINT(3) UNSIGNED NOT NULL,
	niveau_ref VARCHAR(5) COLLATE utf8_unicode_ci NOT NULL,
	niveau_sigle VARCHAR(6) COLLATE utf8_unicode_ci NOT NULL,
	niveau_nom VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (niveau_id),
	KEY palier_id (palier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau DISABLE KEYS;

INSERT INTO sacoche_niveau VALUES 
(  1, 0, 20,     "6",   "6EME", "Sixième"),
(  2, 0, 30,     "5",     "5G", "Cinquième"),
(  3, 0, 40,     "4",     "4G", "Quatrième"),
(  4, 0, 50,     "3",   "3EME", "Troisième"),
(  5, 0, 60,     "2",    "2GT", "Seconde de détermination"),
(  6, 0, 72,    "1S",     "1S", "Première S"),
(  7, 0, 82,    "TS",     "TS", "Terminale S"),
(  8, 0, 21,    "6S", "6SEGPA", "Sixième SEGPA"),
(  9, 0, 15,   "UPI",    "UPI", "Unité pédagogique d'intégration (UPI)"),
( 10, 0, 52,    "3I", "3INSER", "Troisième d'insertion"),
( 11, 0, 70,   "1ES",    "1ES", "Première ES"),
( 12, 0, 71,    "1L",     "1L", "Première L"),
( 13, 0, 80,   "TES",    "TES", "Terminale ES"),
( 14, 0, 81,    "TL",     "TL", "Terminale L"),
( 15, 0,  1,    "PS",       "", "Petite section de maternelle"),
( 16, 0,  2,    "MS",       "", "Moyenne section de maternelle"),
( 17, 0,  3,    "GS",       "", "Grande section de maternelle"),
( 18, 0,  4,    "CP",       "", "Cours préparatoire"),
( 19, 0,  5,   "CE1",       "", "Cours élémentaire 1ère année"),
( 20, 0, 11,   "CE2",       "", "Cours élémentaire 2ème année"),
( 21, 0, 12,   "CM1",       "", "Cours moyen 1ère année"),
( 22, 0, 13,   "CM2",       "", "Cours moyen 2ème année"),
( 24, 0, 14,  "CLIS",       "", "Classe d'intégration scolaire (CLIS)"),
( 33, 0, 42,  "4AES",    "4AS", "Quatrième aide et soutien (AES)"),
( 34, 0, 55,   "REL",       "", "Dispositif relais"),
( 35, 0, 57, "CLIPA",       "", "Classe pré-professionnelle en alternance"),
( 36, 0, 31,    "5S", "5SEGPA", "Cinquième SEGPA"),
( 37, 0, 41,    "4S", "4SEGPA", "Quatrième SEGPA"),
( 38, 0, 51,    "3S", "3SEGPA", "Troisième SEGPA"),
( 40, 0, 56,    "AR", "AT-REL", "Atelier relais"),
( 46, 1,  9,    "P1",       "", "Palier 1 (PS - CE1)"),
( 47, 2, 19,    "P2",       "", "Palier 2 ( CE2 - CM2)"),
( 48, 3, 59,    "P3",       "", "Palier 3 (6e - 3e)"),
( 49, 4, 89,    "P4",       "", "Palier 4 (2nde - Tle)");

ALTER TABLE sacoche_niveau ENABLE KEYS;
