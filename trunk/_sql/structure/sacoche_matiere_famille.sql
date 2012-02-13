DROP TABLE IF EXISTS sacoche_matiere_famille;

CREATE TABLE sacoche_matiere_famille (
	matiere_famille_id        TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  matiere_famille_categorie TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  matiere_famille_nom       VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (matiere_famille_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_matiere_famille DISABLE KEYS;

INSERT INTO sacoche_matiere_famille VALUES
(100, 2, "Activités non spécialisées"),
(  1, 2, "Philosophie sciences humaines"),
(  2, 2, "Lettres"),
(  3, 2, "Langues vivantes"),
(  4, 2, "Histoire géographie"),
(  5, 2, "Sciences économiques et sociales"),
(  6, 2, "Sciences"),
(  7, 2, "Technologie"),
(  8, 2, "Éducation musicale"),
(  9, 2, "Arts plastiques"),
( 10, 2, "Éducation physique et sportive"),
( 11, 3, "Génie industriel du bois"),
( 12, 3, "Génie industriel textile et cuir"),
( 13, 3, "Génie industriel verre céramique"),
( 14, 3, "Génie structures métalliques"),
( 15, 3, "Génie industriel plastiques composites"),
( 16, 3, "Génie chimique"),
( 17, 3, "Génie civil"),
( 18, 3, "Génie thermique"),
( 19, 3, "Génie mécanique de la construction"),
( 20, 3, "Génie mécanique de la productique"),
( 21, 3, "Génie mécanique de la maintenance"),
( 22, 3, "Génie électrique électronique"),
( 23, 3, "Génie électrique électrotechnique"),
( 24, 3, "Génie électrique informat.- télématique"),
( 25, 3, "Indust. graphiques (imprimerie - livre)"),
( 26, 3, "Conduite - navigation"),
( 27, 3, "Métiers des arts appliques"),
( 28, 3, "Métiers d'art"),
( 29, 3, "Métiers de l'artisanat et spécifiques"),
( 30, 3, "Biotechnologie génie biol.- biochimique"),
( 31, 3, "Biotechnologie santé environnement collectivités"),
( 32, 3, "Paramédical et médical"),
( 33, 3, "Soins personnels"),
( 34, 3, "Commerce"),
( 35, 3, "Bureautique et secrétariat"),
( 36, 3, "Droit et législation"),
( 37, 3, "Informatique de gestion"),
( 38, 3, "Économie"),
( 39, 3, "Comptabilité - finances"),
( 40, 3, "Hôtellerie - tourisme"),
( 41, 3, "Communication"),
( 42, 3, "Assurances"),
( 43, 3, "Gestion des entreprises"),
( 44, 3, "Publicité"),
( 50, 3, "Langue technique"),
( 60, 3, "Activités hippiques "),
( 61, 3, "Commercialisation"),
( 62, 3, "Élevage et soins aux animaux"),
( 63, 3, "Environnement - aménagement de l'espace"),
( 64, 3, "Équipements pour l'agriculture"),
( 66, 3, "Production"),
( 67, 3, "Services"),
( 68, 3, "Transformation"),
( 69, 3, "Disciplines générales enseignement agricole"),
( 90, 3, "Enseignement religieux"),
( 99, 1, "Matières principales");

ALTER TABLE sacoche_matiere_famille ENABLE KEYS;
