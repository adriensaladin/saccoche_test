DROP TABLE IF EXISTS sacoche_livret_seuil;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_seuil (
  livret_page_ref   VARCHAR(6)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_colonne_id TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  livret_seuil_min  TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 0 et 99 ; doit être cohérent avec l'ordre.",
  livret_seuil_max  TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Entre 1 et 100 ; doit être cohérent avec l'ordre.",
  PRIMARY KEY (livret_page_ref,livret_colonne_id),
  KEY livret_colonne_id (livret_colonne_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
