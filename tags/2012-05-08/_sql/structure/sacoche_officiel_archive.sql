DROP TABLE IF EXISTS sacoche_officiel_archive;

CREATE TABLE sacoche_officiel_archive (
	user_id            MEDIUMINT(8)                                           UNSIGNED                NOT NULL DEFAULT 0,
	officiel_type      ENUM("releve","bulletin","palier1","palier","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin",
	periode_id         MEDIUMINT(8)                                           UNSIGNED                NOT NULL DEFAULT 0 COMMENT "mis à zéro lors d'un changement d'année",
	archive_date       DATE                                                                           NOT NULL DEFAULT "0000-00-00",
	archive_document   MEDIUMBLOB                                                                     NOT NULL,
	KEY user_id (user_id),
	KEY officiel_type (officiel_type),
	KEY periode_id (periode_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
