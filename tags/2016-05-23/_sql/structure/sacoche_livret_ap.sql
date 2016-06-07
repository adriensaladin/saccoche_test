DROP TABLE IF EXISTS sacoche_livret_ap;

CREATE TABLE sacoche_livret_ap (
  livret_ap_id    SMALLINT(5)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_page_ref VARCHAR(6)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  groupe_id       MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  matiere_id      SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
  prof_id         MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  livret_ap_titre VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_ap_id),
  UNIQUE KEY livret_ap (livret_page_ref, groupe_id, matiere_id, prof_id),
  KEY groupe_id (groupe_id),
  KEY matiere_id (matiere_id),
  KEY prof_id (prof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
