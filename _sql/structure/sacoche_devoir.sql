DROP TABLE IF EXISTS sacoche_devoir;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB

CREATE TABLE sacoche_devoir (
  devoir_id            MEDIUMINT(8) UNSIGNED                NOT NULL AUTO_INCREMENT,
  prof_id              MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  groupe_id            MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  devoir_date          DATE                                 NOT NULL DEFAULT "0000-00-00",
  devoir_info          VARCHAR(60)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_visible_date  DATE                                 NOT NULL DEFAULT "0000-00-00",
  devoir_autoeval_date DATE                                 NULL     DEFAULT NULL ,
  devoir_partage       TEXT         COLLATE utf8_unicode_ci NOT NULL,
  devoir_doc_sujet     VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  devoir_doc_corrige   VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (devoir_id),
  KEY prof_id (prof_id),
  KEY groupe_id (groupe_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
