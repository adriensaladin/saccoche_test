DROP TABLE IF EXISTS sacoche_bulletin;

CREATE TABLE sacoche_bulletin (
	periode_id            MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	matiere_id            SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	prof_id               MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	eleve_id              MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	bulletin_note         TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
	bulletin_pourcentage  TINYINT(3)   UNSIGNED                NOT NULL DEFAULT 0,
	bulletin_appreciation VARCHAR(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY ( eleve_id , periode_id , matiere_id , prof_id ),
	KEY periode_id (periode_id),
	KEY matiere_id (matiere_id),
	KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
