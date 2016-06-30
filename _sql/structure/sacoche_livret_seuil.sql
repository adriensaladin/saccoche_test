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

ALTER TABLE sacoche_livret_seuil DISABLE KEYS;

INSERT INTO sacoche_livret_seuil (livret_page_ref, livret_colonne_id, livret_seuil_min, livret_seuil_max) VALUES
-- reussite
('cycle1', 11,  0,  30),
('cycle1', 12, 31,  69),
('cycle1', 13, 70, 100),
-- objectif
('cp'    , 21,  0,  34),
('cp'    , 22, 35,  64),
('cp'    , 23, 65,  89),
('cp'    , 24, 90, 100),
('ce1'   , 21,  0,  34),
('ce1'   , 22, 35,  64),
('ce1'   , 23, 65,  89),
('ce1'   , 24, 90, 100),
('ce2'   , 21,  0,  34),
('ce2'   , 22, 35,  64),
('ce2'   , 23, 65,  89),
('ce2'   , 24, 90, 100),
('cm1'   , 21,  0,  34),
('cm1'   , 22, 35,  64),
('cm1'   , 23, 65,  89),
('cm1'   , 24, 90, 100),
('cm2'   , 21,  0,  34),
('cm2'   , 22, 35,  64),
('cm2'   , 23, 65,  89),
('cm2'   , 24, 90, 100),
-- maitrise
('cycle2', 31,  0,  34),
('cycle2', 32, 35,  59),
('cycle2', 33, 60,  81),
('cycle2', 34, 81, 100),
('cycle3', 31,  0,  34),
('cycle3', 32, 35,  59),
('cycle3', 33, 60,  81),
('cycle3', 34, 81, 100),
('cycle4', 31,  0,  34),
('cycle4', 32, 35,  59),
('cycle4', 33, 60,  81),
('cycle4', 34, 81, 100);
-- position    -> si choisi ultérieurement
-- moyenne     -> sans objet
-- pourcentage -> sans objet

ALTER TABLE sacoche_livret_seuil ENABLE KEYS;
