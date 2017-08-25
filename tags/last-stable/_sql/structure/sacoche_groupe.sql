DROP TABLE IF EXISTS sacoche_groupe;

CREATE TABLE sacoche_groupe (
  groupe_id      MEDIUMINT(8)                            UNSIGNED                NOT NULL AUTO_INCREMENT,
  groupe_type    ENUM("classe","groupe","besoin","eval") COLLATE utf8_unicode_ci NOT NULL DEFAULT "classe",
  groupe_ref     VARCHAR(20)                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Passage de 8 à 20 caractères pour les groupes dans SIÈCLE BEE (mais pas pour les classes).",
  groupe_nom     VARCHAR(20)                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  niveau_id      MEDIUMINT(8)                            UNSIGNED                NOT NULL DEFAULT 0,
  groupe_chef_id MEDIUMINT(8)                            UNSIGNED                NOT NULL DEFAULT 0,
  PRIMARY KEY (groupe_id),
  KEY niveau_id (niveau_id),
  KEY groupe_type (groupe_type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
