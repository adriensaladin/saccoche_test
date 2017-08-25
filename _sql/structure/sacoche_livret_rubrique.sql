DROP TABLE IF EXISTS sacoche_livret_rubrique;

CREATE TABLE sacoche_livret_rubrique (
  livret_rubrique_id              TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_id_elements     TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Au 1er degré, en dehors de français / maths, les éléments de programme sont regroupés, mais il faut conserver des liaisons aux sous-domaines afin de savoir lesquels sont à afficher.",
  livret_rubrique_id_appreciation TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Au 1er degré, en dehors de français / maths, les appréciations sont pour l'ensemble du domaine enseigné.",
  livret_rubrique_id_position     TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0 COMMENT "Au 1er degré, en dehors de français / maths, les positionnements sont pour l'ensemble du domaine enseigné.",
  livret_rubrique_type            VARCHAR(10)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_ordre           TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_code_livret     CHAR(7)      COLLATE utf8_unicode_ci          DEFAULT NULL COMMENT "Dans SACoche on a une unique table avec domaine / sous-domaine, pas de table par domaine ; pour le code du domaine prendre ***-RAC.",
  livret_rubrique_domaine         VARCHAR(70)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_sous_domaine    VARCHAR(130) COLLATE utf8_unicode_ci          DEFAULT NULL,
  PRIMARY KEY (livret_rubrique_id),
  UNIQUE KEY (livret_rubrique_type,livret_rubrique_ordre),
  KEY livret_rubrique_ordre (livret_rubrique_ordre),
  KEY livret_rubrique_id_elements (livret_rubrique_id_elements),
  KEY livret_rubrique_id_appreciation (livret_rubrique_id_appreciation),
  KEY livret_rubrique_id_position (livret_rubrique_id_position)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_rubrique DISABLE KEYS;

INSERT INTO sacoche_livret_rubrique VALUES
( 11,  11,  11,  11, "c1_theme"  ,   1,      NULL, "Mobiliser le langage dans toutes ses dimensions", "Langage oral : communication, expression"),
( 12,  12,  11,  12, "c1_theme"  ,   2,      NULL, "Mobiliser le langage dans toutes ses dimensions", "Compréhension d'un message oral ou d'un texte lu par l'adulte"),
( 13,  13,  11,  13, "c1_theme"  ,   3,      NULL, "Mobiliser le langage dans toutes ses dimensions", "Découverte de l'écrit ; relations entre l'oral et l'écrit"),
( 14,  14,  11,  14, "c1_theme"  ,   4,      NULL, "Mobiliser le langage dans toutes ses dimensions", "Geste graphique, écriture"),
( 15,  15,  11,  15, "c1_theme"  ,   5,      NULL, "Mobiliser le langage dans toutes ses dimensions", "Mémorisation, restitution de textes (comptines, poèmes…)"),
( 21,  21,  21,  21, "c1_theme"  ,   6,      NULL, "Agir, s'exprimer, comprendre à travers l'activité physique", "Engagement, aisance et inventivité dans les actions ou déplacements"),
( 22,  22,  21,  22, "c1_theme"  ,   7,      NULL, "Agir, s'exprimer, comprendre à travers l'activité physique", "Coopération, interactions avec respect des rôles de chacun"),
( 31,  31,  31,  31, "c1_theme"  ,   8,      NULL, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : dessin, compositions graphiques, compositions plastiques"),
( 32,  32,  31,  32, "c1_theme"  ,   9,      NULL, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : voix, chants, pratiques rythmiques et corporelles"),
( 41,  41,  41,  41, "c1_theme"  ,  10,      NULL, "Construire les premiers outils pour structurer sa pensée", "Utilisation des nombres"),
( 42,  42,  41,  42, "c1_theme"  ,  11,      NULL, "Construire les premiers outils pour structurer sa pensée", "Première compréhension du nombre"),
( 43,  43,  41,  43, "c1_theme"  ,  12,      NULL, "Construire les premiers outils pour structurer sa pensée", "Petits problèmes de composition et de décomposition de nombres (ex : 3 c'est 2 et encore 1 ; 1 et encore 2)"),
( 44,  44,  41,  44, "c1_theme"  ,  13,      NULL, "Construire les premiers outils pour structurer sa pensée", "Tris, classements, rangements, algorithmes "),
( 51,  51,  51,  51, "c1_theme"  ,  14,      NULL, "Explorer le monde", "Temps : repérage, représentations, utilisation de mots de liaison (puis, pendant, avant, après,…)"),
( 52,  52,  51,  52, "c1_theme"  ,  15,      NULL, "Explorer le monde", "Espace : repérage, représentations, utilisation des termes de position (devant, derrière, loin, près,…)"),
( 53,  53,  51,  53, "c1_theme"  ,  16,      NULL, "Explorer le monde", "Premières connaissances sur le vivant (développement ; besoins…)"),
( 54,  54,  51,  54, "c1_theme"  ,  17,      NULL, "Explorer le monde", "Utilisation, fabrication et manipulation d'objets"),
( 55,  55,  51,  55, "c1_theme"  ,  18,      NULL, "Explorer le monde", "Compréhension de règles de sécurité et d'hygiène"),
( 61,  61,  61,  61, "c2_domaine",   1, "FRA-LGO", "Français", "Langage oral"),
( 62,  62,  61,  62, "c2_domaine",   2, "FRA-LEC", "Français", "Lecture et compréhension de l'écrit"),
( 63,  63,  61,  63, "c2_domaine",   3, "FRA-ECR", "Français", "Écriture"),
( 64,  64,  61,  64, "c2_domaine",   4, "FRA-ETL", "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
( 71,  71,  71,  71, "c2_domaine",   5, "MAT-NBC", "Mathématiques", "Nombres et calcul"),
( 72,  72,  71,  72, "c2_domaine",   6, "MAT-GDM", "Mathématiques", "Grandeurs et mesures"),
( 73,  73,  71,  73, "c2_domaine",   7, "MAT-GEO", "Mathématiques", "Espace et géométrie"),
( 81,  81,  81,  81, "c2_domaine",   8, "EPS-RAC", "Éducation physique et sportive", NULL),
( 91,  91,  91,  91, "c2_domaine",   9, "ART-PLA", "Enseignements artistiques", "Arts plastiques"),
( 92,  92,  91,  92, "c2_domaine",  10, "ART-MUS", "Enseignements artistiques", "Éducation musicale"),
(101, 101, 101, 101, "c2_domaine",  11, "QLM-VMO", "Questionner le monde", "Vivant, matière, objets"),
(102, 102, 101, 102, "c2_domaine",  12, "QLM-ETP", "Questionner le monde", "Espace, temps"),
(111, 111, 111, 111, "c2_domaine",  13, "EMC-RAC", "Enseignement moral et civique", NULL),
(121, 121, 121, 121, "c2_domaine",  14, "LGV-CPD", "Langue vivante", "Comprendre l'oral"),
(122, 122, 121, 122, "c2_domaine",  15, "LGV-EXP", "Langue vivante", "S'exprimer oralement en continu"),
(123, 123, 121, 123, "c2_domaine",  16, "LGV-CVS", "Langue vivante", "Prendre part à une conversation"),
(124, 124, 121, 124, "c2_domaine",  17, "LGV-DE1", "Langue vivante", "Découvrir quelques aspects culturels d'une langue"),
(131, 131, 131, 131, "c3_domaine",   1, "FRA-LGO", "Français", "Langage oral"),
(132, 132, 131, 132, "c3_domaine",   2, "FRA-LEC", "Français", "Lecture et compréhension de l'écrit"),
(133, 133, 131, 133, "c3_domaine",   3, "FRA-ECR", "Français", "Écriture"),
(134, 134, 131, 134, "c3_domaine",   4, "FRA-ETL", "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
(141, 141, 141, 141, "c3_domaine",   5, "MAT-NBC", "Mathématiques", "Nombres et calcul"),
(142, 142, 141, 142, "c3_domaine",   6, "MAT-GDM", "Mathématiques", "Grandeurs et mesures"),
(143, 143, 141, 143, "c3_domaine",   7, "MAT-GEO", "Mathématiques", "Espace et géométrie"),
(151, 151, 151, 151, "c3_domaine",   8, "EPS-RAC", "Éducation physique et sportive", NULL),
(161, 161, 161, 161, "c3_domaine",   9, "ART-PLA", "Enseignements artistiques", "Arts plastiques"),
(162, 162, 161, 162, "c3_domaine",  10, "ART-MUS", "Enseignements artistiques", "Éducation musicale"),
(163, 163, 161, 163, "c3_domaine",  11, "ART-HAR", "Enseignements artistiques", "Histoire des arts"),
(171, 171, 171, 171, "c3_domaine",  12, "STC-RAC", "Sciences et technologie", NULL),
(181, 181, 181, 181, "c3_domaine",  13, "HIG-RAC", "Histoire géographie", NULL),
(191, 191, 191, 191, "c3_domaine",  14, "EMC-RAC", "Enseignement moral et civique", NULL),
(201, 201, 201, 201, "c3_domaine",  15, "LGV-ECO", "Langue vivante", "Écouter et comprendre"),
(202, 202, 201, 202, "c3_domaine",  16, "LGV-LIR", "Langue vivante", "Lire et comprendre"),
(203, 203, 201, 203, "c3_domaine",  17, "LGV-PAR", "Langue vivante", "Parler en continu"),
(204, 204, 201, 204, "c3_domaine",  18, "LGV-ECR", "Langue vivante", "Écrire"),
(205, 205, 201, 205, "c3_domaine",  19, "LGV-DIA", "Langue vivante", "Réagir et dialoguer"),
(206, 206, 201, 206, "c3_domaine",  20, "LGV-DE2", "Langue vivante", "Découvrir les aspects culturels d'une langue");

ALTER TABLE sacoche_livret_rubrique ENABLE KEYS;
