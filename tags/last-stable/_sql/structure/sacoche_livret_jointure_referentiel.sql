DROP TABLE IF EXISTS sacoche_livret_jointure_referentiel;

CREATE TABLE sacoche_livret_jointure_referentiel (
  livret_page_ref     VARCHAR(7) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_rubrique_id  TINYINT(3) UNSIGNED                NOT NULL DEFAULT 0,
  element_id         SMALLINT(5) UNSIGNED                         DEFAULT 0 COMMENT "matiere_id | domaine_id | theme_id",
  KEY rubrique (livret_page_ref,livret_rubrique_id),
  KEY livret_rubrique_id (livret_rubrique_id),
  KEY element_id (element_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
