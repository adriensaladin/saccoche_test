DROP TABLE IF EXISTS sacoche_jointure_parent_eleve;

CREATE TABLE sacoche_jointure_parent_eleve (
	parent_id        MEDIUMINT(8)      UNSIGNED                NOT NULL DEFAULT 0,
	eleve_id         MEDIUMINT(8)      UNSIGNED                NOT NULL DEFAULT 0,
	resp_legal_num   TINYINT(3)        UNSIGNED                NOT NULL DEFAULT 1,
	resp_legal_envoi TINYINT(1)        UNSIGNED                NOT NULL DEFAULT 1,
	UNIQUE KEY parent_eleve_key (parent_id,eleve_id),
	KEY parent_id (parent_id),
	KEY eleve_id (eleve_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
