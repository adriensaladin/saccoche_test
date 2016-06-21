DROP TABLE IF EXISTS sacoche_socle_composante;

CREATE TABLE sacoche_socle_composante (
  socle_composante_id           TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_id              TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_composante_ordre        TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_composante_ordre_livret TINYINT(3)  UNSIGNED                         DEFAULT NULL,
  socle_composante_nom          VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (socle_composante_id),
  KEY socle_domaine_id (socle_domaine_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_composante DISABLE KEYS;

INSERT INTO sacoche_socle_composante VALUES 
(11, 1, 1, 1   , "Langue française à l'oral et à l'écrit"),
(12, 1, 2, 4   , "Langues étrangères et régionales"),
(13, 1, 3, 2   , "Langages mathématiques, scientifiques et informatiques"),
(14, 1, 4, 6   , "Langages des arts et du corps"),
(21, 2, 1, NULL, "Organisation du travail personnel"),
(22, 2, 2, NULL, "Coopération et réalisation de projets"),
(23, 2, 3, NULL, "Médias, démarches de recherche et de traitement de l'information"),
(24, 2, 4, NULL, "Outils numériques pour échanger et communiquer"),
(31, 3, 1, NULL, "Expression de la sensibilité et des opinions, respect des autres"),
(32, 3, 2, NULL, "La règle et le droit"),
(33, 3, 3, NULL, "Réflexion et discernement"),
(34, 3, 4, NULL, "Responsabilité, sens de l'engagement et de l'initiative"),
(41, 4, 1, NULL, "Démarches scientifiques"),
(42, 4, 2, NULL, "Conception, création, réalisation"),
(43, 4, 3, NULL, "Responsabilités individuelles et collectives"),
(44, 4, 4, NULL, "Connaissances à mobiliser"),
(51, 5, 1, NULL, "L'espace et le temps"),
(52, 5, 2, NULL, "Organisations et représentations du monde"),
(53, 5, 3, NULL, "Invention, élaboration, production"),
(54, 5, 4, NULL, "Connaissances à mobiliser");

ALTER TABLE sacoche_socle_composante ENABLE KEYS;
