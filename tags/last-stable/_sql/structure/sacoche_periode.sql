DROP TABLE IF EXISTS sacoche_periode;

CREATE TABLE sacoche_periode (
  periode_id     MEDIUMINT(8)                                          UNSIGNED                NOT NULL AUTO_INCREMENT,
  periode_ordre  TINYINT(3)                                            UNSIGNED                NOT NULL DEFAULT 1,
  periode_nom    VARCHAR(40)                                           COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  periode_livret ENUM("","T1","T2","T3","S1","S2","B1","B2","B3","B4") COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Période officielle utilisable pour le livret scolaire.",
  PRIMARY KEY (periode_id),
  KEY periode_livret (periode_livret)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
