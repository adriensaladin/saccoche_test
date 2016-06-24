DROP TABLE IF EXISTS sacoche_livret_parametre_rubrique;

CREATE TABLE sacoche_livret_parametre_rubrique (
  rubrique_type       VARCHAR(7)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  rubrique_ordre      TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  rubrique_titre      VARCHAR(70)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  rubrique_sous_titre VARCHAR(130) COLLATE utf8_unicode_ci          DEFAULT NULL,
  PRIMARY KEY (rubrique_type,rubrique_ordre),
  KEY rubrique_ordre (rubrique_ordre)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_parametre_rubrique DISABLE KEYS;

INSERT INTO sacoche_livret_parametre_rubrique (rubrique_type, rubrique_ordre, rubrique_titre, rubrique_sous_titre) VALUES
("theme",    11, "Mobiliser le langage dans toutes ses dimensions", "Langage oral : communication, expression"),
("theme",    12, "Mobiliser le langage dans toutes ses dimensions", "Compréhension d'un message oral ou d'un texte lu par l'adulte"),
("theme",    13, "Mobiliser le langage dans toutes ses dimensions", "Découverte de l'écrit ; relations entre l'oral et l'écrit"),
("theme",    14, "Mobiliser le langage dans toutes ses dimensions", "Geste graphique, écriture"),
("theme",    15, "Mobiliser le langage dans toutes ses dimensions", "Mémorisation, restitution de textes (comptines, poèmes…)"),
("theme",    21, "Agir, s'exprimer, comprendre à travers l'activité physique", "Engagement, aisance et inventivité dans les actions ou déplacements"),
("theme",    22, "Agir, s'exprimer, comprendre à travers l'activité physique", "Coopération, interactions avec respect des rôles de chacun"),
("theme",    23, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : dessin, compositions graphiques, compositions plastiques"),
("theme",    24, "Agir, s'exprimer, comprendre à travers les activités artistiques", "Engagement dans les activités, réalisation de productions personnelles : voix, chants, pratiques rythmiques et corporelles"),
("theme",    31, "Construire les premiers outils pour structurer sa pensée", "Utilisation des nombres"),
("theme",    32, "Construire les premiers outils pour structurer sa pensée", "Première compréhension du nombre"),
("theme",    33, "Construire les premiers outils pour structurer sa pensée", "Petits problèmes de composition et de décomposition de nombres (ex : 3 c'est 2 et encore 1 ; 1 et encore 2)"),
("theme",    34, "Construire les premiers outils pour structurer sa pensée", "Tris, classements, rangements, algorithmes "),
("theme",    41, "Explorer le monde", "Temps : repérage, représentations, utilisation de mots de liaison (puis, pendant, avant, après,…)"),
("theme",    42, "Explorer le monde", "Espace : repérage, représentations, utilisation des termes de position (devant, derrière, loin, près,…)"),
("theme",    43, "Explorer le monde", "Premières connaissances sur le vivant (développement ; besoins…)"),
("theme",    44, "Explorer le monde", "Utilisation, fabrication et manipulation d'objets"),
("theme",    45, "Explorer le monde", "Compréhension de règles de sécurité et d'hygiène"),
("domaine",  11, "Français", "Langage oral"),
("domaine",  12, "Français", "Lecture et compréhension de l'écrit"),
("domaine",  13, "Français", "Écriture"),
("domaine",  14, "Français", "Étude de la langue (grammaire, orthographe, lexique)"),
("domaine",  21, "Mathématiques", "Nombres et calcul"),
("domaine",  22, "Mathématiques", "Grandeurs et mesures"),
("domaine",  23, "Mathématiques", "Espace et géométrie"),
("domaine",  30, "Éducation physique et sportive", NULL),
("domaine",  40, "Sciences et technologie", NULL),
("domaine",  51, "Enseignements artistiques", "Arts plastiques et visuels"),
("domaine",  52, "Enseignements artistiques", "Éducation musicale"),
("domaine",  53, "Enseignements artistiques", "Histoire des arts"),
("domaine",  61, "Histoire géographie", "Histoire"),
("domaine",  62, "Histoire géographie", "Géographie"),
("domaine",  70, "Enseignement moral et civique", NULL),
("domaine",  81, "Langue vivante", "Écouter et parler"),
("domaine",  82, "Langue vivante", "Lire et écrire"),
("matiere",  10, "Français", NULL),
("matiere",  20, "Mathématiques", NULL),
("matiere",  30, "Histoire-Géographie / Enseignement moral et civique", NULL),
("matiere",  40, "Langue vivante 1", NULL),
("matiere",  41, "Langue vivante 2", NULL),
("matiere",  50, "Éducation physique et sportive", NULL),
("matiere",  60, "Arts plastiques", NULL),
("matiere",  70, "Éducation musicale", NULL),
("matiere",  80, "Sciences de la Vie et de la Terre", NULL),
("matiere",  90, "Technologie", NULL),
("matiere", 100, "Physique-Chimie", NULL),
("matiere", 111, "Enseignement(s) de complément", "Latin / Langue et culture régionales");

ALTER TABLE sacoche_livret_parametre_rubrique ENABLE KEYS;
