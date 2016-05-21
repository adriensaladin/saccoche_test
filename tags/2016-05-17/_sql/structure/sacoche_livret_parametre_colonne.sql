DROP TABLE IF EXISTS sacoche_livret_parametre_colonne;

CREATE TABLE sacoche_livret_parametre_colonne (
  colonne_type      VARCHAR(8)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  colonne_ordre     TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "De 1 à 3 ou 4.",
  colonne_seuil_min TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 0 et 99 ; doit être cohérent avec l'ordre.",
  colonne_seuil_max TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 1 et 100 ; doit être cohérent avec l'ordre.",
  colonne_titre     VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  colonne_legende   VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (colonne_type,colonne_ordre),
  KEY colonne_ordre (colonne_ordre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_parametre_colonne DISABLE KEYS;

INSERT INTO sacoche_livret_parametre_colonne (colonne_type, colonne_ordre, colonne_seuil_min, colonne_seuil_max, colonne_titre, colonne_legende) VALUES
("reussite", 1,  0,  30, "Réussite",                          "ne réussit pas encore"),
("reussite", 2, 31,  69, "Réussite",                          "est en voie de réussite"),
("reussite", 3, 70, 100, "Réussite",                          "réussit souvent"),
("objectif", 1,  0,  34, "Objectifs d'apprentissage",         "Non atteints"),
("objectif", 2, 35,  64, "Objectifs d'apprentissage",         "Partiellement atteints"),
("objectif", 3, 65,  89, "Objectifs d'apprentissage",         "Atteints"),
("objectif", 4, 90, 100, "Objectifs d'apprentissage",         "Dépassés"),
("maitrise", 1,  0,  34, "Maîtrise des composantes du socle", "Maîtrise insuffisante"),
("maitrise", 2, 35,  59, "Maîtrise des composantes du socle", "Maîtrise fragile"),
("maitrise", 3, 60,  80, "Maîtrise des composantes du socle", "Maîtrise satisfaisante"),
("maitrise", 4, 81, 100, "Maîtrise des composantes du socle", "Très bonne maîtrise");

ALTER TABLE sacoche_livret_parametre_colonne ENABLE KEYS;
