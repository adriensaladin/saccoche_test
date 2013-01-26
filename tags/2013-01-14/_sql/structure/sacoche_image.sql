DROP TABLE IF EXISTS sacoche_image;

CREATE TABLE sacoche_image (
	user_id       MEDIUMINT(8)                     UNSIGNED                NOT NULL DEFAULT 0 COMMENT "0 pour le tampon de l'établissement (objet signature) ou le logo de l'établissement",
	image_objet   ENUM("signature","photo","logo") COLLATE utf8_unicode_ci NOT NULL DEFAULT "photo",
	image_contenu MEDIUMBLOB                                               NOT NULL,
	image_format  CHAR(4)                          COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	image_largeur SMALLINT(5)                      UNSIGNED                NOT NULL DEFAULT 0,
	image_hauteur SMALLINT(5)                      UNSIGNED                NOT NULL DEFAULT 0,
	UNIQUE KEY user_id (user_id,image_objet)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Photos pour les élèves, signatures pour les professeurs et directeurs, logo pour l'établissement.";
