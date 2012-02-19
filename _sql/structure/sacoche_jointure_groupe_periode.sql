DROP TABLE IF EXISTS sacoche_jointure_groupe_periode;

CREATE TABLE sacoche_jointure_groupe_periode (
	groupe_id           MEDIUMINT(8)                                                          UNSIGNED                NOT NULL DEFAULT 0,
	periode_id          MEDIUMINT(8)                                                          UNSIGNED                NOT NULL DEFAULT 0,
	jointure_date_debut DATE                                                                                          NOT NULL DEFAULT "0000-00-00",
	jointure_date_fin   DATE                                                                                          NOT NULL DEFAULT "0000-00-00",
	bulletin_modele     ENUM("item_detail","item_synthese","socle_detail","socle_synthese")   COLLATE utf8_unicode_ci NOT NULL DEFAULT "item_synthese",
	bulletin_etat       ENUM("ferme_vierge","ouvert_profs","ouvert_synthese","ferme_complet") COLLATE utf8_unicode_ci NOT NULL DEFAULT "ferme_vierge",
	PRIMARY KEY ( groupe_id , periode_id ),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
