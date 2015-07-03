DROP TABLE IF EXISTS sacoche_groupe;

CREATE TABLE sacoche_groupe (
  groupe_id      MEDIUMINT(8)                                                 UNSIGNED                NOT NULL AUTO_INCREMENT,
  groupe_type    ENUM("classe","groupe","besoin","eval")                      COLLATE utf8_unicode_ci NOT NULL DEFAULT "classe",
  groupe_ref     CHAR(8)                                                      COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  groupe_nom     VARCHAR(20)                                                  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  niveau_id      SMALLINT(5)                                                  UNSIGNED                NOT NULL DEFAULT 0,
  fiche_brevet   ENUM("","1vide","2rubrique","3mixte","4synthese","5complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (groupe_id),
  KEY niveau_id (niveau_id),
  KEY groupe_type (groupe_type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
