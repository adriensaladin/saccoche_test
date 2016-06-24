DROP TABLE IF EXISTS sacoche_livret_colonne;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_colonne (
  livret_colonne_id      TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_colonne_type    VARCHAR(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_ordre   TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "De 1 à 3 ou 4.",
  livret_colonne_titre   VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_legende VARCHAR(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_colonne_id),
  UNIQUE KEY (livret_colonne_type,livret_colonne_ordre),
  KEY livret_colonne_ordre (livret_colonne_ordre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_colonne DISABLE KEYS;

INSERT INTO sacoche_livret_colonne (livret_colonne_id, livret_colonne_type, livret_colonne_ordre, livret_colonne_titre, livret_colonne_legende) VALUES
(11, "reussite"   , 1, "Réussite",                          "ne réussit pas encore"),   -- seuils par défaut  0 ~  30
(12, "reussite"   , 2, "Réussite",                          "est en voie de réussite"), -- seuils par défaut 31 ~  69
(13, "reussite"   , 3, "Réussite",                          "réussit souvent"),         -- seuils par défaut 70 ~ 100
(21, "objectif"   , 1, "Objectifs d'apprentissage",         "Non atteints"),            -- seuils par défaut  0 ~  34
(22, "objectif"   , 2, "Objectifs d'apprentissage",         "Partiellement atteints"),  -- seuils par défaut 35 ~  64
(23, "objectif"   , 3, "Objectifs d'apprentissage",         "Atteints"),                -- seuils par défaut 65 ~  89
(24, "objectif"   , 4, "Objectifs d'apprentissage",         "Dépassés"),                -- seuils par défaut 90 ~ 100
(31, "maitrise"   , 1, "Maîtrise des composantes du socle", "Maîtrise insuffisante"),   -- seuils par défaut  0 ~  34
(32, "maitrise"   , 2, "Maîtrise des composantes du socle", "Maîtrise fragile"),        -- seuils par défaut 35 ~  59
(33, "maitrise"   , 3, "Maîtrise des composantes du socle", "Maîtrise satisfaisante"),  -- seuils par défaut 60 ~  80
(34, "maitrise"   , 4, "Maîtrise des composantes du socle", "Très bonne maîtrise"),     -- seuils par défaut 81 ~ 100
(41, "position"   , 1, "Positionnement",                    "1 sur 4"),                 -- seuils par défaut  0 ~  24
(42, "position"   , 2, "Positionnement",                    "2 sur 4"),                 -- seuils par défaut 25 ~  49
(43, "position"   , 3, "Positionnement",                    "3 sur 4"),                 -- seuils par défaut 50 ~  74
(44, "position"   , 4, "Positionnement",                    "4 sur 4"),                 -- seuils par défaut 75 ~ 100
(51, "moyenne"    , 1, "Moyenne",                           "de l'élève"),
(52, "moyenne"    , 2, "Moyenne",                           "de la classe"),
(61, "pourcentage", 1, "Pourcentage",                       "de l'élève"),
(62, "pourcentage", 2, "Pourcentage",                       "de la classe");

ALTER TABLE sacoche_livret_colonne ENABLE KEYS;
