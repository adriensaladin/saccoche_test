DROP TABLE IF EXISTS sacoche_jointure_devoir_eleve;

CREATE TABLE sacoche_jointure_devoir_eleve (
  devoir_id      MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  eleve_id       MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  jointure_texte VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  jointure_audio VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY ( devoir_id , eleve_id ),
  KEY eleve_id (eleve_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
