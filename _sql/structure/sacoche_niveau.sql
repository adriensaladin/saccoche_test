DROP TABLE IF EXISTS sacoche_niveau;

CREATE TABLE sacoche_niveau (
	niveau_id         TINYINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
	niveau_actif      TINYINT(1)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_famille_id TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_ordre      TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_ref        VARCHAR(6)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	code_mef          CHAR(11)    COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Masque à comparer avec le code_mef d'une classe (nomenclature Sconet).",
	niveau_nom        VARCHAR(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (niveau_id),
	KEY niveau_actif (niveau_actif),
	KEY niveau_famille_id (niveau_famille_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau DISABLE KEYS;

-- Cycles (niveaux 'longitudinaux')

INSERT INTO sacoche_niveau VALUES 
(   5, 0, 1,   5,     "P0",            "", "Cycle 1 (PS-GS)"),
(   1, 0, 1,   9,     "P1",            "", "Cycle 2 (GS-CE1)"),
(   2, 0, 1,  29,     "P2",            "", "Cycle 3 (CE2-CM2)"),
(   3, 0, 1,  69,     "P3",            "", "Cycle Collège"),
(   4, 0, 1, 199,     "P4",            "", "Cycle Lycée");

-- Primaire

INSERT INTO sacoche_niveau VALUES 
(  11, 0, 2,   1,     "PS", "0001000131.", "Maternelle, petite section"),
(  12, 0, 2,   2,     "MS", "0001000132.", "Maternelle, moyenne section"),
(  13, 0, 2,   3,     "GS", "0001000133.", "Maternelle, grande section"),
(  14, 0, 2,   7,     "CP", "0011000211.", "Cours préparatoire"),
(  15, 0, 2,   8,    "CE1", "0021000221.", "Cours élémentaire 1e année"),
(  16, 0, 2,  11,    "CE2", "0021000222.", "Cours élémentaire 2e année"),
(  17, 0, 2,  12,    "CM1", "0031000221.", "Cours moyen 1e année"),
(  18, 0, 2,  13,    "CM2", "0031000222.", "Cours moyen 2e année"),
(  19, 0, 2,  21,   "INIT", "0601000311.", "Initiation"),
(  20, 0, 2,  22,   "ADAP", "0611000411.", "Adaptation"),
(  21, 0, 2,  23,   "CLIS", "0621000511.", "Classe d'intégration scolaire");

-- Collège (4e et 4AES ont le même code_mef ...)

INSERT INTO sacoche_niveau VALUES 
(  31, 0, 3,  31,      "6", "100100..11.", "Sixième"),
(  32, 0, 3,  32,      "5", "101100..11.", "Cinquième"),
(  33, 0, 3,  33,      "4", "102100..11.", "Quatrième"),
(  34, 0, 3,  34,    "4AS", "102100..11.", "Quatrième d'aide et de soutien"),
(  35, 0, 3,  35,      "3", "103100..11.", "Troisième"),
(  36, 0, 3,  41,     "3I", "104100..11.", "Troisième d'insertion"),
(  37, 0, 3,  42,    "REL", "105100..11.", "Classe / Atelier relais"),
(  38, 0, 3,  43,    "UPI", "106100..11.", "Unité pédagogique d'intégration");

-- SEGPA - Pré apprentissage

INSERT INTO sacoche_niveau VALUES 
(  41, 0, 4,  51,     "6S", "1641000211.", "Sixième SEGPA"),
(  42, 0, 4,  52,     "5S", "1651000211.", "Cinquième SEGPA"),
(  43, 0, 4,  53,     "4S", "1661000211.", "Quatrième SEGPA"),
(  44, 0, 4,  54,     "3S", "167...9911.", "Troisième SEGPA"),
(  51, 0, 4,  61,   "3PVP", "110.....22.", "Troisième préparatoire à la voie professionnelle"),
(  52, 0, 4,  62,    "CPA", "112..99911.", "Classe préparatoire à l'apprentissage"),
(  53, 0, 4,  63,  "CLIPA", "113..99911.", "Classe d'initiation pré-professionnelle en alternance"),
(  54, 0, 4,  64,    "FAJ", "114..99911.", "Formation d'apprenti junior");

-- Lycée général

INSERT INTO sacoche_niveau VALUES 
(  61, 0, 5,  71,      "2", "20010...11.", "Seconde de détermination"),
(  62, 0, 5,  81,     "1S", "20111...11.", "Première S"),
(  63, 0, 5,  82,    "1ES", "20112...11.", "Première ES"),
(  64, 0, 5,  83,     "1L", "20113...11.", "Première L"),
(  65, 0, 5,  91,     "TS", "20211...11.", "Terminale S"),
(  66, 0, 5,  92,    "TES", "20212...11.", "Terminale ES"),
(  67, 0, 5,  93,     "TL", "20213...11.", "Terminale L"),
(  68, 0, 5,  84,      "1", "2011....11.", "Première générale"),
(  69, 0, 5,  94,      "T", "2021....11.", "Terminale générale");

-- Lycée technologique

INSERT INTO sacoche_niveau VALUES 
(  71, 0, 6, 101,     "2T", "210.....11.", "Seconde technologique / musique"),
(  72, 0, 6, 102,    "2BT", "220.....11.", "Seconde BT"),
(  73, 0, 6, 111,    "1ST", "211.....11.", "Première STI / STL / STG"),
(  74, 0, 6, 112,     "1T", "213.....11.", "Première technologique"),
(  75, 0, 6, 113,    "1BT", "221.....11.", "Première BT"),
(  76, 0, 6, 114,   "1BTA", "223.....11.", "Première BTA"),
(  77, 0, 6, 115,   "1ADN", "231.....11.", "Première d'adaptation BTN"),
(  78, 0, 6, 116,    "1AD", "232.....11.", "Première d'adaptation BT"),
(  79, 0, 6, 121,    "TST", "212.....11.", "Terminale STI / STL / STG"),
(  80, 0, 6, 122,     "TT", "214.....11.", "Terminale technologique"),
(  81, 0, 6, 123,    "TBT", "222.....11.", "Terminale BT"),
(  82, 0, 6, 124,   "TBTA", "224.....11.", "Terminale BTA");

-- Lycée professionnel

INSERT INTO sacoche_niveau VALUES 
(  91, 0, 7, 131,  "1CAP1", "240.....11.", "CAP 1 an"),
(  92, 0, 7, 132,  "1CAP2", "241.....21.", "CAP 2 ans, 1e année"),
(  93, 0, 7, 133,  "2CAP2", "241.....22.", "CAP 2 ans, 2e année"),
(  94, 0, 7, 134,  "1CAP3", "242.....31.", "CAP 3 ans, 1e année"),
(  95, 0, 7, 135,  "2CAP3", "242.....32.", "CAP 3 ans, 2e année"),
(  96, 0, 7, 136,  "3CAP3", "242.....33.", "CAP 3 ans, 3e année"),
( 101, 0, 7, 141,   "BEP1", "243.....11.", "BEP 1 an"),
( 102, 0, 7, 142,   "2BEP", "244.....21.", "BEP 2 ans, 1e année (seconde)"),
( 103, 0, 7, 143,   "TBEP", "244.....22.", "BEP 2 ans, 2e année (terminale)"),
( 111, 0, 7, 151,  "1PRO1", "245.....11.", "Bac Pro 1 an"),
( 112, 0, 7, 152,  "1PRO2", "246.....21.", "Bac Pro 2 ans, 1e année"),
( 113, 0, 7, 153,  "2PRO2", "246.....22.", "Bac Pro 2 ans, 2e année (terminale)"),
( 114, 0, 7, 154,  "1PRO3", "247.....31.", "Bac Pro 3 ans, 1e année (seconde pro)"),
( 115, 0, 7, 155,  "2PRO3", "247.....32.", "Bac Pro 3 ans, 2e année (première pro)"),
( 116, 0, 7, 156,  "3PRO3", "247.....33.", "Bac Pro 3 ans, 3e année (terminale pro)");

-- BTS

INSERT INTO sacoche_niveau VALUES 
( 121, 0, 8, 161,  "1BTS1", "310.....11.", "BTS 1 an"),
( 122, 0, 8, 162,  "1BTS2", "311.....21.", "BTS 2 ans, 1e année"),
( 123, 0, 8, 163,  "2BTS2", "311.....22.", "BTS 2 ans, 2e année"),
( 124, 0, 8, 164,  "1BTS3", "312.....31.", "BTS 3 ans, 1e année"),
( 125, 0, 8, 165,  "2BTS3", "312.....32.", "BTS 3 ans, 2e année"),
( 126, 0, 8, 166,  "3BTS3", "312.....33.", "BTS 3 ans, 3e année"),
( 131, 0, 8, 171, "1BTS1A", "370.....11.", "BTS Agricole 1 an"),
( 132, 0, 8, 172, "1BTS2A", "371.....21.", "BTS Agricole 2 ans, 1e année"),
( 133, 0, 8, 173, "2BTS2A", "371.....22.", "BTS Agricole 2 ans, 2e année");

-- CECRL (cadre européen commun de référence pour les langues)

INSERT INTO sacoche_niveau VALUES 
( 201, 0, 9,  30,     "A1",            "", "A1 : Introductif - Découverte"),
( 202, 0, 9,  32,     "A2",            "", "A2 : Intermédiaire - Usuel"),
( 203, 0, 9,  50,     "B1",            "", "B1 : Seuil"),
( 204, 0, 9, 175,     "B2",            "", "B2 : Avancé - Indépendant"),
( 205, 0, 9, 200,     "C1",            "", "C1 : Autonome"),
( 206, 0, 9, 250,     "C2",            "", "C2 : Maîtrise");

ALTER TABLE sacoche_niveau ENABLE KEYS;
