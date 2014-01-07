DROP TABLE IF EXISTS sacoche_officiel_fichier;

CREATE TABLE sacoche_officiel_fichier (
  user_id                          MEDIUMINT(8)                                           UNSIGNED                NOT NULL DEFAULT 0,
  officiel_type                    ENUM("releve","bulletin","palier1","palier","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin",
  periode_id                       MEDIUMINT(8)                                           UNSIGNED                NOT NULL DEFAULT 0,
  fichier_date_generation          DATE                                                                           NOT NULL DEFAULT "0000-00-00",
  fichier_date_consultation_eleve  DATE                                                                                    DEFAULT NULL ,
  fichier_date_consultation_parent DATE                                                                                    DEFAULT NULL ,
  UNIQUE KEY user_id (user_id,officiel_type,periode_id),
  KEY officiel_type (officiel_type),
  KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
