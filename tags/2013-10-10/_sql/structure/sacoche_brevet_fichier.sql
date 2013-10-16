DROP TABLE IF EXISTS sacoche_brevet_fichier;

CREATE TABLE sacoche_brevet_fichier (
  user_id            MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
  fichier_date       DATE                  NOT NULL DEFAULT "0000-00-00",
  PRIMARY KEY (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
