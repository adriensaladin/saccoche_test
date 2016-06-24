DROP TABLE IF EXISTS sacoche_livret_jointure_ap_prof;

CREATE TABLE sacoche_livret_jointure_ap_prof (
  livret_ap_id SMALLINT(5)  UNSIGNED NOT NULL DEFAULT 0,
  matiere_id   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  prof_id      MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY ( livret_ap_id , matiere_id , prof_id ),
  KEY matiere_id (matiere_id),
  KEY prof_id (prof_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
