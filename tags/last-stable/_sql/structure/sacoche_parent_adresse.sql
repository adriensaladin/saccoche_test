DROP TABLE IF EXISTS sacoche_parent_adresse;

CREATE TABLE IF NOT EXISTS sacoche_parent_adresse (
	parent_id              MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	adresse_ligne1         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	adresse_ligne2         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	adresse_ligne3         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	adresse_ligne4         VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	adresse_postal_code    MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
	adresse_postal_libelle VARCHAR(45)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	adresse_pays_nom       VARCHAR(35)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (parent_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
