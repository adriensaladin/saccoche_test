DROP TABLE IF EXISTS sacoche_jointure_user_pilier;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, MySQL à partir de 5.7.8 est par défaut en mode strict, avec NO_ZERO_DATE qui interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_jointure_user_pilier (
  user_id                MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  pilier_id              TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  validation_pilier_etat TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 1    COMMENT "1 si validation positive ; 0 si validation négative.",
  validation_pilier_date DATE                                          DEFAULT NULL COMMENT "Ne vaut normalement jamais NULL.",
  validation_pilier_info VARCHAR(25)  COLLATE utf8_unicode_ci NOT NULL DEFAULT ""   COMMENT "Enregistrement statique du nom du validateur, conservé les années suivantes.",
  PRIMARY KEY ( user_id , pilier_id ),
  KEY pilier_id (pilier_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
