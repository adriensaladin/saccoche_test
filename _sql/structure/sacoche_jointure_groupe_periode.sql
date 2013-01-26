DROP TABLE IF EXISTS sacoche_jointure_groupe_periode;

CREATE TABLE sacoche_jointure_groupe_periode (
	groupe_id           MEDIUMINT(8)                                        UNSIGNED                NOT NULL DEFAULT 0,
	periode_id          MEDIUMINT(8)                                        UNSIGNED                NOT NULL DEFAULT 0,
	jointure_date_debut DATE                                                                        NOT NULL DEFAULT "0000-00-00",
	jointure_date_fin   DATE                                                                        NOT NULL DEFAULT "0000-00-00",
	officiel_releve     ENUM("","1vide","2rubrique","3synthese","4complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	officiel_bulletin   ENUM("","1vide","2rubrique","3synthese","4complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	officiel_palier1    ENUM("","1vide","2rubrique","3synthese","4complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	officiel_palier2    ENUM("","1vide","2rubrique","3synthese","4complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	officiel_palier3    ENUM("","1vide","2rubrique","3synthese","4complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY ( groupe_id , periode_id ),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
