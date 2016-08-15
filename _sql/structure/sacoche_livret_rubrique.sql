DROP TABLE IF EXISTS sacoche_livret_rubrique;

CREATE TABLE sacoche_livret_rubrique (
  livret_rubrique_id          TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_type       VARCHAR(7)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_ordre      TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  livret_rubrique_titre      VARCHAR(70)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_sous_titre VARCHAR(130) COLLATE utf8_unicode_ci          DEFAULT NULL,
  PRIMARY KEY (livret_rubrique_id),
  UNIQUE KEY (livret_rubrique_type,livret_rubrique_ordre),
  KEY livret_rubrique_titre (livret_rubrique_titre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_rubrique DISABLE KEYS;

INSERT INTO sacoche_livret_rubrique (livret_rubrique_id, livret_rubrique_type, livret_rubrique_ordre, livret_rubrique_titre, livret_rubrique_sous_titre) VALUES
( 11, "theme",    11, "Mobiliser le langage dans toutes ses dimensions", "Langage oral : communication, expression"),
( 12, "theme",    12, "Mobiliser le langage dans toutes ses dimensions", "Compréhension d'un message oral ou d'un texte lu par l'adulte"),
( 13, "theme",    13, "Mobiliser le langage dans toutes ses dimensions", "Découverte de l'écrit ; relations entre l'oral et l'écrit"),
( 14, "theme",    14, "Mobiliser le langage dans toutes ses dimensions", "Geste graphique, écriture"),
( 15, "theme",    15, "Mobiliser le langage dans toutes ses dimensions", "Mémorisation, restitution de textes (comptines, poèmes…)"),
( 21, "theme",    21, "Agir, s'exprimer, comprendre à travers l'activité physique", "Engagement, aisance et inventivité dans les actions ou déplacements"),
( 22, "theme",    22, "Agir, s'exprimer, comprendre à travers l'activité physique", "Coopération, interactions avec respect des rôles de chacun"),
( 23, "theme",    23, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : dessin, compositions graphiques, compositions plastiques"),
( 24, "theme",    24, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : voix, chants, pratiques rythmiques et corporelles"),
( 31, "theme",    31, "Construire les premiers outils pour structurer sa pensée", "Utilisation des nombres"),
( 32, "theme",    32, "Construire les premiers outils pour structurer sa pensée", "Première compréhension du nombre"),
( 33, "theme",    33, "Construire les premiers outils pour structurer sa pensée", "Petits problèmes de composition et de décomposition de nombres (ex : 3 c'est 2 et encore 1 ; 1 et encore 2)"),
( 34, "theme",    34, "Construire les premiers outils pour structurer sa pensée", "Tris, classements, rangements, algorithmes "),
( 41, "theme",    41, "Explorer le monde", "Temps : repérage, représentations, utilisation de mots de liaison (puis, pendant, avant, après,…)"),
( 42, "theme",    42, "Explorer le monde", "Espace : repérage, représentations, utilisation des termes de position (devant, derrière, loin, près,…)"),
( 43, "theme",    43, "Explorer le monde", "Premières connaissances sur le vivant (développement ; besoins…)"),
( 44, "theme",    44, "Explorer le monde", "Utilisation, fabrication et manipulation d'objets"),
( 45, "theme",    45, "Explorer le monde", "Compréhension de règles de sécurité et d'hygiène"),
( 51, "domaine",  11, "Français", "Langage oral"),
( 52, "domaine",  12, "Français", "Lecture et compréhension de l'écrit"),
( 53, "domaine",  13, "Français", "Écriture"),
( 54, "domaine",  14, "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
( 61, "domaine",  21, "Mathématiques", "Nombres et calcul"),
( 62, "domaine",  22, "Mathématiques", "Grandeurs et mesures"),
( 63, "domaine",  23, "Mathématiques", "Espace et géométrie"),
( 70, "domaine",  30, "Éducation physique et sportive", NULL),
( 80, "domaine",  40, "Sciences et technologie", NULL),
( 91, "domaine",  51, "Enseignements artistiques", "Arts plastiques et visuels"),
( 92, "domaine",  52, "Enseignements artistiques", "Éducation musicale"),
( 93, "domaine",  53, "Enseignements artistiques", "Histoire des arts"),
(101, "domaine",  61, "Histoire géographie", "Histoire"),
(102, "domaine",  62, "Histoire géographie", "Géographie"),
(110, "domaine",  70, "Enseignement moral et civique", NULL),
(121, "domaine",  81, "Langue vivante", "Écouter et parler"),
(122, "domaine",  82, "Langue vivante", "Lire et écrire"),
(130, "matiere",  10, "Français", NULL),
(140, "matiere",  20, "Mathématiques", NULL),
(150, "matiere",  30, "Histoire-Géographie / Enseignement moral et civique", NULL),
(160, "matiere",  40, "Langue vivante 1", NULL),
(161, "matiere",  41, "Langue vivante 2", NULL),
(170, "matiere",  50, "Éducation physique et sportive", NULL),
(180, "matiere",  60, "Arts plastiques", NULL),
(190, "matiere",  70, "Éducation musicale", NULL),
(200, "matiere",  80, "Sciences de la Vie et de la Terre", NULL),
(210, "matiere",  90, "Technologie", NULL),
(220, "matiere", 100, "Physique-Chimie", NULL),
(231, "matiere", 111, "Enseignement(s) de complément", "Latin / Langue et culture régionales");

ALTER TABLE sacoche_livret_rubrique ENABLE KEYS;
