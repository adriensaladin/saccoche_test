DROP TABLE IF EXISTS sacoche_livret_rubrique;

CREATE TABLE sacoche_livret_rubrique (
  livret_rubrique_id         TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_type       VARCHAR(10)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_ordre      TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_titre      VARCHAR(70)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_sous_titre VARCHAR(130) COLLATE utf8_unicode_ci          DEFAULT NULL,
  PRIMARY KEY (livret_rubrique_id),
  UNIQUE KEY (livret_rubrique_type,livret_rubrique_ordre),
  KEY livret_rubrique_titre (livret_rubrique_titre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_rubrique DISABLE KEYS;

INSERT INTO sacoche_livret_rubrique (livret_rubrique_id, livret_rubrique_type, livret_rubrique_ordre, livret_rubrique_titre, livret_rubrique_sous_titre) VALUES
( 11, "c1_theme",     1, "Mobiliser le langage dans toutes ses dimensions", "Langage oral : communication, expression"),
( 12, "c1_theme",     2, "Mobiliser le langage dans toutes ses dimensions", "Compréhension d'un message oral ou d'un texte lu par l'adulte"),
( 13, "c1_theme",     3, "Mobiliser le langage dans toutes ses dimensions", "Découverte de l'écrit ; relations entre l'oral et l'écrit"),
( 14, "c1_theme",     4, "Mobiliser le langage dans toutes ses dimensions", "Geste graphique, écriture"),
( 15, "c1_theme",     5, "Mobiliser le langage dans toutes ses dimensions", "Mémorisation, restitution de textes (comptines, poèmes…)"),
( 21, "c1_theme",     6, "Agir, s'exprimer, comprendre à travers l'activité physique", "Engagement, aisance et inventivité dans les actions ou déplacements"),
( 22, "c1_theme",     7, "Agir, s'exprimer, comprendre à travers l'activité physique", "Coopération, interactions avec respect des rôles de chacun"),
( 23, "c1_theme",     8, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : dessin, compositions graphiques, compositions plastiques"),
( 24, "c1_theme",     9, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : voix, chants, pratiques rythmiques et corporelles"),
( 31, "c1_theme",    10, "Construire les premiers outils pour structurer sa pensée", "Utilisation des nombres"),
( 32, "c1_theme",    11, "Construire les premiers outils pour structurer sa pensée", "Première compréhension du nombre"),
( 33, "c1_theme",    12, "Construire les premiers outils pour structurer sa pensée", "Petits problèmes de composition et de décomposition de nombres (ex : 3 c'est 2 et encore 1 ; 1 et encore 2)"),
( 34, "c1_theme",    13, "Construire les premiers outils pour structurer sa pensée", "Tris, classements, rangements, algorithmes "),
( 41, "c1_theme",    14, "Explorer le monde", "Temps : repérage, représentations, utilisation de mots de liaison (puis, pendant, avant, après,…)"),
( 42, "c1_theme",    15, "Explorer le monde", "Espace : repérage, représentations, utilisation des termes de position (devant, derrière, loin, près,…)"),
( 43, "c1_theme",    16, "Explorer le monde", "Premières connaissances sur le vivant (développement ; besoins…)"),
( 44, "c1_theme",    17, "Explorer le monde", "Utilisation, fabrication et manipulation d'objets"),
( 45, "c1_theme",    18, "Explorer le monde", "Compréhension de règles de sécurité et d'hygiène"),
( 51, "c2_domaine",   1, "Français", "Langage oral"),
( 52, "c2_domaine",   2, "Français", "Lecture et compréhension de l'écrit"),
( 53, "c2_domaine",   3, "Français", "Écriture"),
( 54, "c2_domaine",   4, "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
( 61, "c2_domaine",   5, "Mathématiques", "Nombres et calcul"),
( 62, "c2_domaine",   6, "Mathématiques", "Grandeurs et mesures"),
( 63, "c2_domaine",   7, "Mathématiques", "Espace et géométrie"),
( 71, "c2_domaine",   8, "Éducation physique et sportive", NULL),
( 72, "c2_domaine",   9, "Enseignements artistiques", "Arts plastiques"),
( 73, "c2_domaine",  10, "Enseignements artistiques", "Éducation musicale"),
( 74, "c2_domaine",  11, "Questionner le monde", "Vivant, matière, objets"),
( 75, "c2_domaine",  12, "Questionner le monde", "Espace, temps"),
( 76, "c2_domaine",  13, "Enseignement moral et civique", NULL),
( 81, "c2_domaine",  14, "Langue vivante", "Comprendre l'oral"),
( 82, "c2_domaine",  15, "Langue vivante", "S'exprimer oralement en continu"),
( 83, "c2_domaine",  16, "Langue vivante", "Prendre part à une conversation"),
( 84, "c2_domaine",  17, "Langue vivante", "Découvrir quelques aspects culturels d'une langue"),
( 91, "c3_domaine",   1, "Français", "Langage oral"),
( 92, "c3_domaine",   2, "Français", "Lecture et compréhension de l'écrit"),
( 93, "c3_domaine",   3, "Français", "Écriture"),
( 94, "c3_domaine",   4, "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
(101, "c3_domaine",   5, "Mathématiques", "Nombres et calcul"),
(102, "c3_domaine",   6, "Mathématiques", "Grandeurs et mesures"),
(103, "c3_domaine",   7, "Mathématiques", "Espace et géométrie"),
(111, "c3_domaine",   8, "Éducation physique et sportive", NULL),
(121, "c3_domaine",   9, "Enseignements artistiques", "Arts plastiques"),
(122, "c3_domaine",  10, "Enseignements artistiques", "Éducation musicale"),
(123, "c3_domaine",  11, "Enseignements artistiques", "Histoire des arts"),
(131, "c3_domaine",  12, "Sciences et technologie", NULL),
(132, "c3_domaine",  13, "Histoire géographie", NULL),
(133, "c3_domaine",  14, "Enseignement moral et civique", NULL),
(141, "c3_domaine",  15, "Langue vivante", "Écouter et comprendre"),
(142, "c3_domaine",  16, "Langue vivante", "Lire et comprendre"),
(143, "c3_domaine",  17, "Langue vivante", "Parler en continu"),
(144, "c3_domaine",  18, "Langue vivante", "Écrire"),
(145, "c3_domaine",  19, "Langue vivante", "Réagir et dialoguer"),
(146, "c3_domaine",  20, "Langue vivante", "Découvrir les aspects culturels d'une langue");

ALTER TABLE sacoche_livret_rubrique ENABLE KEYS;
