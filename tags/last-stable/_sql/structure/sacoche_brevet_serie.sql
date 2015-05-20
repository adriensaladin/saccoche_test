DROP TABLE IF EXISTS sacoche_brevet_serie;

CREATE TABLE sacoche_brevet_serie (
  brevet_serie_ref   VARCHAR(6)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "X" COMMENT "Série du brevet pour Notanet ( G = générale ; P = professionnelle ; options éventuelles LV2 DP6 AGRI ).",
  brevet_serie_ordre TINYINT(3)   UNSIGNED               NOT NULL DEFAULT 0,
  brevet_serie_nom   VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (brevet_serie_ref)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_brevet_serie DISABLE KEYS;

INSERT INTO sacoche_brevet_serie VALUES
("X"     , 1, "Série indéterminée"),
("G"     , 2, "Série Générale"),
("P"     , 3, "Série Professionnelle, sans option"),
("P-Agri", 4, "Série Professionnelle, option Agricole");

ALTER TABLE sacoche_brevet_serie ENABLE KEYS;
