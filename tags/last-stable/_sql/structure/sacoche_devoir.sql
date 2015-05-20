DROP TABLE IF EXISTS sacoche_devoir;

CREATE TABLE sacoche_devoir (
  devoir_id            MEDIUMINT(8)           UNSIGNED                NOT NULL AUTO_INCREMENT,
  proprio_id           MEDIUMINT(8)           UNSIGNED                NOT NULL DEFAULT 0,
  groupe_id            MEDIUMINT(8)           UNSIGNED                NOT NULL DEFAULT 0,
  devoir_date          DATE                                           NOT NULL DEFAULT "0000-00-00",
  devoir_info          VARCHAR(60)            COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_visible_date  DATE                                           NOT NULL DEFAULT "0000-00-00",
  devoir_autoeval_date DATE                                                    DEFAULT NULL ,
  devoir_doc_sujet     VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_doc_corrige   VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_fini          TINYINT(1)             UNSIGNED                NOT NULL DEFAULT 0,
  devoir_eleves_ordre  ENUM("alpha","classe") COLLATE utf8_unicode_ci NOT NULL DEFAULT "alpha",
  PRIMARY KEY (devoir_id),
  KEY proprio_id (proprio_id),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
