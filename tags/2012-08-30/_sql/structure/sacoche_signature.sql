DROP TABLE IF EXISTS sacoche_signature;

CREATE TABLE sacoche_signature (
	user_id            MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0 COMMENT "0 pour le tampon de l'établissement",
	signature_contenu  MEDIUMBLOB                           NOT NULL,
	signature_format   CHAR(4)      COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	signature_largeur  SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	signature_hauteur  SMALLINT(5)  UNSIGNED                NOT NULL DEFAULT 0,
	PRIMARY KEY (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
