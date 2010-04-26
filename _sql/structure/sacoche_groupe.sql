DROP TABLE IF EXISTS sacoche_groupe;

CREATE TABLE sacoche_groupe (
	groupe_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	groupe_type ENUM("classe","groupe","besoin","eval") COLLATE utf8_unicode_ci NOT NULL,
	groupe_prof_id MEDIUMINT(8) UNSIGNED NOT NULL COMMENT "Id du prof dans le cas d'un groupe de type 'eval' ; 0 sinon.",
	groupe_ref CHAR(8) COLLATE utf8_unicode_ci NOT NULL,
	groupe_nom VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
	niveau_id TINYINT(3) UNSIGNED NOT NULL,
	PRIMARY KEY (groupe_id),
	KEY niveau_id (niveau_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
