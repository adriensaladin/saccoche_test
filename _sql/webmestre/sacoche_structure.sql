DROP TABLE IF EXISTS sacoche_structure;

CREATE TABLE sacoche_structure (
  sacoche_base               MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
  geo_id                     TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
  structure_uai              CHAR(8)      COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_localisation     VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_denomination     VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_nom      VARCHAR(20)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_prenom   VARCHAR(20)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_contact_courriel VARCHAR(60)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  structure_inscription_date DATE                                 NOT NULL DEFAULT "0000-00-00",
  PRIMARY KEY (sacoche_base),
  KEY geo_id (geo_id),
  KEY structure_uai (structure_uai)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
