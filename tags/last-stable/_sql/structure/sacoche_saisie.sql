DROP TABLE IF EXISTS sacoche_saisie;

CREATE TABLE sacoche_saisie (
  prof_id             MEDIUMINT(8)                                                   UNSIGNED                NOT NULL DEFAULT 0,
  eleve_id            MEDIUMINT(8)                                                   UNSIGNED                NOT NULL DEFAULT 0,
  devoir_id           MEDIUMINT(8)                                                   UNSIGNED                NOT NULL DEFAULT 0,
  item_id             MEDIUMINT(8)                                                   UNSIGNED                NOT NULL DEFAULT 0,
  saisie_date         DATE                                                                                   NOT NULL DEFAULT "0000-00-00",
  saisie_note         ENUM("VV","V","R","RR","ABS","DISP","NE","NF","NN","NR","REQ") COLLATE utf8_unicode_ci NOT NULL DEFAULT "NN",
  saisie_info         VARCHAR(100)                                                   COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Enregistrement statique du nom du devoir et du professeur, conservé les années suivantes.",
  saisie_visible_date DATE                                                                                   NOT NULL DEFAULT "0000-00-00",
  PRIMARY KEY ( devoir_id , eleve_id , item_id ),
  KEY prof_id (prof_id),
  KEY eleve_id (eleve_id),
  KEY item_id (item_id),
  KEY saisie_date (saisie_date),
  KEY saisie_visible_date (saisie_visible_date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
