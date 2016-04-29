DROP TABLE IF EXISTS sacoche_socle_domaine;

CREATE TABLE sacoche_socle_domaine (
  socle_domaine_id         TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_ordre      TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  socle_domaine_ordre_lsun TINYINT(3)  UNSIGNED                         DEFAULT NULL,
  socle_domaine_nom        VARCHAR(44) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (socle_domaine_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_socle_domaine DISABLE KEYS;

INSERT INTO sacoche_socle_domaine (socle_domaine_id, socle_domaine_ordre, socle_domaine_ordre_lsun, socle_domaine_nom) VALUES
(1, 1, NULL, "Langages pour penser et communiquer"),
(2, 2, 8   , "Méthodes et outils pour apprendre"),
(3, 3, 7   , "Formation de la personne et du citoyen"),
(4, 4, 5   , "Systèmes naturels et systèmes techniques"),
(5, 5, 3   , "Représentations du monde et activité humaine");

ALTER TABLE sacoche_socle_domaine ENABLE KEYS;
