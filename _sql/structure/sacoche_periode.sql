DROP TABLE IF EXISTS sacoche_periode;

CREATE TABLE sacoche_periode (
  periode_id    MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
  periode_ordre TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 1,
  periode_nom   VARCHAR(40)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  periode_lsun  VARCHAR(2)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "T1 | T2 | T3 | S1 | S2 ; période officielle utilisable pour le LSUN.",
  PRIMARY KEY (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
