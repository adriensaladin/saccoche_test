DROP TABLE IF EXISTS sacoche_brevet_epreuve;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_brevet_epreuve (
  brevet_serie_ref               VARCHAR(6)   COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Série du brevet.",
  brevet_epreuve_code            TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0    COMMENT "Code de l'épreuve.",
  brevet_epreuve_nom             VARCHAR(127) COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Nom de l'épreuve.",
  brevet_epreuve_obligatoire     TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1    COMMENT "Épreuve obligatoire ou facultative.",
  brevet_epreuve_note_chiffree   TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1    COMMENT "Épreuve avec notre chiffrée.",
  brevet_epreuve_point_sup_10    TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 0    COMMENT "Épreuve avec prise en compte des seuls points supérieurs à la moyenne.",
  brevet_epreuve_note_comptee    TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1    COMMENT "Épreuve comptabilisée dans le total des points.",
  brevet_epreuve_coefficient     TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 1    COMMENT "Coef 1 ou 2 ou 3 pour une note sur 20 ou 40 ou 60.",
  brevet_epreuve_code_speciaux   VARCHAR(8)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "AB" COMMENT "Liste des codes spéciaux autorisés à la saisie.",
  brevet_epreuve_matieres_cibles VARCHAR(127) COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Liste des matiere_id proposés par défaut pour le rapprochement.",
  brevet_epreuve_choix_recherche TINYINT(1)   UNSIGNED                         DEFAULT NULL COMMENT "1 = utiliser en priorité les moyennes des bulletins ; 0 = utiliser la moyenne annuelle des acquisitions ;  NULL = non paramétré",
  brevet_epreuve_choix_moyenne   TINYINT(1)   UNSIGNED                         DEFAULT NULL COMMENT "1 = utiliser la moyenne du premier référentiel trouvé ; 0 = utiliser la moyenne de tous les référentiels trouvés ;  NULL = non paramétré",
  brevet_epreuve_choix_matieres  VARCHAR(255) COLLATE utf8_unicode_ci          DEFAULT NULL COMMENT "Liste des matiere_id à examiner ; NULL = non paramétré.",
  PRIMARY KEY (brevet_serie_ref , brevet_epreuve_code)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_brevet_epreuve DISABLE KEYS;

-- Attention : sur certains LCS le module pdo_mysql bloque au dela de 40 instructions envoyées d`un coup (mais un INSERT multiple avec des milliers de lignes ne pose pas de pb).

INSERT INTO sacoche_brevet_epreuve VALUES

-- Série Générale

("G"     , 101, "Français"                         , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "207,6920"                                       , NULL, 1, NULL),
("G"     , 102, "Mathématiques"                    , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "613,6930"                                       , NULL, 1, NULL),
("G"     , 103, "Première langue vivante"          , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "315,316,317,318,319,320,321,322,323,324,325,326", NULL, 1, NULL),
("G"     , 104, "Sciences de la vie et de la terre", TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "629,6946"                                       , NULL, 1, NULL),
("G"     , 105, "Physique-chimie"                  , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "623,6936"                                       , NULL, 1, NULL),
("G"     , 106, "Éducation physique et sportive"   , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "1001,6914"                                      , NULL, 1, NULL),
("G"     , 107, "Arts plastiques"                  , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "901"                                            , NULL, 1, NULL),
("G"     , 108, "Éducation musicale"               , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "813"                                            , NULL, 1, NULL),
("G"     , 109, "Technologie"                      , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "708,738"                                        , NULL, 1, NULL),
("G"     , 110, "Deuxième langue vivante"          , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "327,328,329,330,331,332,333,334,335,336,337,338", NULL, 1, NULL),
("G"     , 113, "Option facultative"               , FALSE, TRUE , TRUE , TRUE , 1, "AB,DI"   , "201,6929,202,6923,50,51,74,399,230"             , NULL, 1, NULL),
("G"     , 121, "Histoire-géographie"              , TRUE , TRUE , FALSE, FALSE, 1, "AB"      , "406,6926,421"                                   , NULL, 1, NULL),
("G"     , 122, "Éducation civique"                , TRUE , TRUE , FALSE, FALSE, 1, "AB"      , "414,406,6926,421,6925"                          , NULL, 1, NULL),
("G"     , 130, "Niveau A2 de langue régionale"    , FALSE, FALSE, FALSE, TRUE , 0, "AB,VA,NV", "230,399"                                        , NULL, 1, NULL),

-- Série Professionnelle, sans option

("P"     , 101, "Français"                             , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "207,6920"                                                                                       , NULL, 1, NULL),
("P"     , 102, "Mathématiques"                        , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "613,6930"                                                                                       , NULL, 1, NULL),
("P"     , 103, "Langues vivantes"                     , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338", NULL, 0, NULL),
("P"     , 105, "Prévention santé environnement"       , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "3128,6939"                                                                                      , NULL, 1, NULL),
("P"     , 106, "Éducation physique et sportive"       , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "1001,6914"                                                                                      , NULL, 1, NULL),
("P"     , 107, "Éducation artistique"                 , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "905"                                                                                            , NULL, 0, NULL),
("P"     , 108, "Sciences et technologie"              , TRUE , TRUE , FALSE, TRUE , 2, "AB,DI"   , "708,738"                                                                                        , NULL, 0, NULL),
("P"     , 110, "Découverte professionnelle"           , TRUE , TRUE , FALSE, TRUE , 3, "AB,DI"   , "50,51,74"                                                                                       , NULL, 1, NULL),
("P"     , 121, "Histoire-géographie éducation civique", TRUE , TRUE , FALSE, FALSE, 1, "AB"      , "421,6925,406,6926,414"                                                                          , NULL, 0, NULL),
("P"     , 130, "Niveau A2 de langue régionale"        , FALSE, FALSE, FALSE, TRUE , 0, "AB,VA,NV", "230,399"                                                                                        , NULL, 1, NULL),

-- Série Professionnelle, option Agricole

("P-Agri", 101, "Français"                                                                    , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "207,6920"                                       , NULL, 1, NULL),
("P-Agri", 102, "Mathématiques"                                                               , TRUE , TRUE , FALSE, TRUE , 1, "AB"      , "613,6930"                                       , NULL, 1, NULL),
("P-Agri", 103, "Première langue vivante"                                                     , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "315,316,317,318,319,320,321,322,323,324,325,326", NULL, 1, NULL),
("P-Agri", 105, "Prévention santé environnement"                                              , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "3128,6939"                                      , NULL, 1, NULL),
("P-Agri", 106, "Éducation physique et sportive"                                              , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "1001,6914"                                      , NULL, 1, NULL),
("P-Agri", 107, "Éducation socioculturelle"                                                   , TRUE , TRUE , FALSE, TRUE , 1, "AB,DI"   , "5,6919"                                         , NULL, 1, NULL),
("P-Agri", 109, "Technologie, sciences et découverte de la vie professionnelle et des métiers", TRUE , TRUE , FALSE, TRUE , 3, "AB,DI"   , "708,738"                                        , NULL, 0, NULL),
("P-Agri", 121, "Histoire-géographie éducation civique"                                       , TRUE , TRUE , FALSE, FALSE, 1, "AB"      , "421,6925,406,6926,414"                          , NULL, 0, NULL),
("P-Agri", 130, "Niveau A2 de langue régionale"                                               , FALSE, FALSE, FALSE, TRUE , 0, "AB,VA,NV", "230,399"                                        , NULL, 1, NULL);

ALTER TABLE sacoche_brevet_epreuve ENABLE KEYS;
