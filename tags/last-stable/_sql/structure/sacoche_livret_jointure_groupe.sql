DROP TABLE IF EXISTS sacoche_livret_jointure_groupe;

-- Attention : pour un champ DATE ou DATETIME, DEFAULT NOW() ne fonctionne qu`à partir de MySQL 5.6.5
-- Attention : pour un champ DATE ou DATETIME, la configuration NO_ZERO_DATE (incluse dans le mode strict de MySQL 5.7.4 à 5.7.7), interdit les valeurs en dehors de 1000-01-01 00:00:00 à 9999-12-31 23:59:59

CREATE TABLE sacoche_livret_jointure_groupe (
  groupe_id               MEDIUMINT(8)                                              UNSIGNED                NOT NULL DEFAULT 0,
  livret_page_ref         VARCHAR(6)                                                COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_periodicite ENUM("periode","cycle","college")                         COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode",
  jointure_periode        ENUM("","T1","T2","T3","S1","S2")                         COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret",
  jointure_etat           ENUM("1vide","2rubrique","3mixte","4synthese","5complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "1vide",
  jointure_date_verrou    DATE                                                                                       DEFAULT NULL ,
  PRIMARY KEY ( groupe_id , livret_page_ref , livret_page_periodicite , jointure_periode ),
  KEY livret_page_ref (livret_page_ref),
  KEY livret_page_periodicite (livret_page_periodicite),
  KEY jointure_periode (jointure_periode)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
