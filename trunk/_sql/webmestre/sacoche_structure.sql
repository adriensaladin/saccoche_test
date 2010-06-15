DROP TABLE IF EXISTS sacoche_structure;

CREATE TABLE sacoche_structure (
  sacoche_base MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  geo_id TINYINT(3) UNSIGNED NOT NULL,
  structure_uai CHAR(8) COLLATE utf8_unicode_ci NOT NULL,
  structure_localisation VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  structure_denomination VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  structure_contact_nom VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  structure_contact_prenom VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
  structure_contact_courriel VARCHAR(60) COLLATE utf8_unicode_ci NOT NULL,
  structure_inscription_date DATE NOT NULL,
  PRIMARY KEY (sacoche_base),
  KEY geo_id (geo_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
