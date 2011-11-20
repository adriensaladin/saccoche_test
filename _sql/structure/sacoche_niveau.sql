DROP TABLE IF EXISTS sacoche_niveau;

CREATE TABLE sacoche_niveau (
	niveau_id    TINYINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
	niveau_cycle TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Indique un niveau 'longitudinal' nommé 'cycle'.",
	niveau_ordre TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_ref   VARCHAR(6)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	code_mef     CHAR(11)    COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Masque à comparer avec le code_mef d'une classe (nomenclature Sconet).",
	niveau_nom   VARCHAR(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (niveau_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau DISABLE KEYS;

-- Niveaux "longitudinaux" nommés "cycles"

INSERT INTO sacoche_niveau VALUES 
(   5, 1,   5,     "P0",            "", "Cycle 1 (PS-GS)"),
(   1, 1,   9,     "P1",            "", "Cycle 2 (GS-CE1)"),
(   2, 1,  29,     "P2",            "", "Cycle 3 (CE2-CM2)"),
(   3, 1,  69,     "P3",            "", "Cycle Collège"),
(   4, 1, 199,     "P4",            "", "Cycle Lycée");

-- Primaire

INSERT INTO sacoche_niveau VALUES 
(  11, 0,   1,     "PS", "0001000131.", "Maternelle, petite section"),
(  12, 0,   2,     "MS", "0001000132.", "Maternelle, moyenne section"),
(  13, 0,   3,     "GS", "0001000133.", "Maternelle, grande section"),
(  14, 0,   7,     "CP", "0011000211.", "Cours préparatoire"),
(  15, 0,   8,    "CE1", "0021000221.", "Cours élémentaire 1e année"),
(  16, 0,  11,    "CE2", "0021000222.", "Cours élémentaire 2e année"),
(  17, 0,  12,    "CM1", "0031000221.", "Cours moyen 1e année"),
(  18, 0,  13,    "CM2", "0031000222.", "Cours moyen 2e année"),
(  19, 0,  21,   "INIT", "0601000311.", "Initiation"),
(  20, 0,  22,   "ADAP", "0611000411.", "Adaptation"),
(  21, 0,  23,   "CLIS", "0621000511.", "Classe d'intégration scolaire");

-- Collège (pb pour différencier 4e et 4AES)

INSERT INTO sacoche_niveau VALUES 
(  31, 0,  31,      "6", "100100..11.", "Sixième"),
(  32, 0,  32,      "5", "101100..11.", "Cinquième"),
(  33, 0,  33,      "4", "102100..11.", "Quatrième"),
(  34, 0,  34,    "4AS", "102100..11.", "Quatrième d'aide et de soutien"),
(  35, 0,  35,      "3", "103100..11.", "Troisième"),
(  36, 0,  41,     "3I", "104100..11.", "Troisième d'insertion"),
(  37, 0,  42,    "REL", "105100..11.", "Classe / Atelier relais"),
(  38, 0,  43,    "UPI", "106100..11.", "Unité pédagogique d'intégration");

-- SEGPA & Pré apprentissage

INSERT INTO sacoche_niveau VALUES 
(  41, 0,  51,     "6S", "1641000211.", "Sixième SEGPA"),
(  42, 0,  52,     "5S", "1651000211.", "Cinquième SEGPA"),
(  43, 0,  53,     "4S", "1661000211.", "Quatrième SEGPA"),
(  44, 0,  54,     "3S", "167...9911.", "Troisième SEGPA"),
(  51, 0,  61,   "3PVP", "110.....22.", "Troisième préparatoire à la voie professionnelle"),
(  52, 0,  62,    "CPA", "112..99911.", "Classe préparatoire à l'apprentissage"),
(  53, 0,  63,  "CLIPA", "113..99911.", "Classe d'initiation pré-professionnelle en alternance"),
(  54, 0,  64,    "FAJ", "114..99911.", "Formation d'apprenti junior");

-- Lycée général

INSERT INTO sacoche_niveau VALUES 
(  61, 0,  71,      "2", "20010...11.", "Seconde de détermination"),
(  62, 0,  81,     "1S", "20111...11.", "Première S"),
(  63, 0,  82,    "1ES", "20112...11.", "Première ES"),
(  64, 0,  83,     "1L", "20113...11.", "Première L"),
(  65, 0,  91,     "TS", "20211...11.", "Terminale S"),
(  66, 0,  92,    "TES", "20212...11.", "Terminale ES"),
(  67, 0,  93,     "TL", "20213...11.", "Terminale L"),
(  68, 0,  84,      "1", "2011....11.", "Première générale"),
(  69, 0,  94,      "T", "2021....11.", "Terminale générale");

-- Lycée technologique

INSERT INTO sacoche_niveau VALUES 
(  71, 0, 101,     "2T", "210.....11.", "Seconde technologique / musique"),
(  72, 0, 102,    "2BT", "220.....11.", "Seconde BT"),
(  73, 0, 111,    "1ST", "211.....11.", "Première STI / STL / STG"),
(  74, 0, 112,     "1T", "213.....11.", "Première technologique"),
(  75, 0, 113,    "1BT", "221.....11.", "Première BT"),
(  76, 0, 114,   "1BTA", "223.....11.", "Première BTA"),
(  77, 0, 115,   "1ADN", "231.....11.", "Première d'adaptation BTN"),
(  78, 0, 116,    "1AD", "232.....11.", "Première d'adaptation BT"),
(  79, 0, 121,    "TST", "212.....11.", "Terminale STI / STL / STG"),
(  80, 0, 122,     "TT", "214.....11.", "Terminale technologique"),
(  81, 0, 123,    "TBT", "222.....11.", "Terminale BT"),
(  82, 0, 124,   "TBTA", "224.....11.", "Terminale BTA");

-- Lycée professionnel

INSERT INTO sacoche_niveau VALUES 
(  91, 0, 131,  "1CAP1", "240.....11.", "CAP 1 an"),
(  92, 0, 132,  "1CAP2", "241.....21.", "CAP 2 ans, 1e année"),
(  93, 0, 133,  "2CAP2", "241.....22.", "CAP 2 ans, 2e année"),
(  94, 0, 134,  "1CAP3", "242.....31.", "CAP 3 ans, 1e année"),
(  95, 0, 135,  "2CAP3", "242.....32.", "CAP 3 ans, 2e année"),
(  96, 0, 136,  "3CAP3", "242.....33.", "CAP 3 ans, 3e année"),
( 101, 0, 141,   "BEP1", "243.....11.", "BEP 1 an"),
( 102, 0, 142,   "2BEP", "244.....21.", "BEP 2 ans, 1e année (seconde)"),
( 103, 0, 143,   "TBEP", "244.....22.", "BEP 2 ans, 2e année (terminale)"),
( 111, 0, 151,  "1PRO1", "245.....11.", "Bac Pro 1 an"),
( 112, 0, 152,  "1PRO2", "246.....21.", "Bac Pro 2 ans, 1e année"),
( 113, 0, 153,  "2PRO2", "246.....22.", "Bac Pro 2 ans, 2e année (terminale)"),
( 114, 0, 154,  "1PRO3", "247.....31.", "Bac Pro 3 ans, 1e année (seconde pro)"),
( 115, 0, 155,  "2PRO3", "247.....32.", "Bac Pro 3 ans, 2e année (première pro)"),
( 116, 0, 156,  "3PRO3", "247.....33.", "Bac Pro 3 ans, 3e année (terminale pro)");

-- BTS

INSERT INTO sacoche_niveau VALUES 
( 121, 0, 161,  "1BTS1", "310.....11.", "BTS 1 an"),
( 122, 0, 162,  "1BTS2", "311.....21.", "BTS 2 ans, 1e année"),
( 123, 0, 163,  "2BTS2", "311.....22.", "BTS 2 ans, 2e année"),
( 124, 0, 164,  "1BTS3", "312.....31.", "BTS 3 ans, 1e année"),
( 125, 0, 165,  "2BTS3", "312.....32.", "BTS 3 ans, 2e année"),
( 126, 0, 166,  "3BTS3", "312.....33.", "BTS 3 ans, 3e année"),
( 131, 0, 171, "1BTS1A", "370.....11.", "BTS Agricole 1 an"),
( 132, 0, 172, "1BTS2A", "371.....21.", "BTS Agricole 2 ans, 1e année"),
( 133, 0, 173, "2BTS2A", "371.....22.", "BTS Agricole 2 ans, 2e année");

ALTER TABLE sacoche_niveau ENABLE KEYS;
