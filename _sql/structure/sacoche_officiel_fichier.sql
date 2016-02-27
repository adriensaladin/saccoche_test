DROP TABLE IF EXISTS sacoche_officiel_fichier;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, MySQL à partir de 5.7.8 est par défaut en mode strict, avec NO_ZERO_DATE qui interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_officiel_fichier (
  user_id                          MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0,
  officiel_type                    ENUM("releve","bulletin","palier1","palier2","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin",
  periode_id                       MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0,
  fichier_date_generation          DATE                                                                                     DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  fichier_date_consultation_eleve  DATE                                                                                     DEFAULT NULL ,
  fichier_date_consultation_parent DATE                                                                                     DEFAULT NULL ,
  UNIQUE KEY user_id (user_id,officiel_type,periode_id),
  KEY officiel_type (officiel_type),
  KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
