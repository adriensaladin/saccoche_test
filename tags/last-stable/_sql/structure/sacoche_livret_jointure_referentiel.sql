DROP TABLE IF EXISTS sacoche_livret_jointure_referentiel;

CREATE TABLE sacoche_livret_jointure_referentiel (
  rubrique_type  VARCHAR(7)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  rubrique_ordre TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
  matiere_id     SMALLINT(5) UNSIGNED                         DEFAULT NULL,
  domaine_id     SMALLINT(5) UNSIGNED                         DEFAULT NULL,
  theme_id       SMALLINT(5) UNSIGNED                         DEFAULT NULL,
  KEY rubrique (rubrique_type,rubrique_ordre),
  KEY rubrique_ordre (rubrique_ordre),
  KEY matiere_id (matiere_id),
  KEY domaine_id (domaine_id),
  KEY theme_id (theme_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
