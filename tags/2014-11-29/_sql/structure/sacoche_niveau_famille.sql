DROP TABLE IF EXISTS sacoche_niveau_famille;

CREATE TABLE sacoche_niveau_famille (
  niveau_famille_id        TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  niveau_famille_categorie TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "1 = Niveaux classes ; 2 = Niveaux particuliers",
  niveau_famille_ordre     TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  niveau_famille_nom       VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (niveau_famille_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau_famille DISABLE KEYS;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : en cas d`ajout, adapter DB_lister_niveaux_famille() dans requetes_structure_administrateur.php

INSERT INTO sacoche_niveau_famille VALUES
(  2, 1, 1, "Primaire"),
(  3, 1, 2, "Collège"),
(  4, 1, 3, "SEGPA - Pré-apprentissage"),
(  5, 1, 4, "Lycée général"),
(  6, 1, 5, "Lycée technologique"),
(  7, 1, 6, "Lycée professionnel"),
(  8, 1, 7, "BTS"),
( 11, 1, 8, "Métiers d'arts"),
(  1, 2, 1, "Cycles (primaire, collège, lycée)"),
(  9, 2, 2, "CECRL (cadre européen commun de référence pour les langues)"),
( 10, 2, 3, "APSA (activités physiques, sportives et artistiques)");

ALTER TABLE sacoche_niveau_famille ENABLE KEYS;
