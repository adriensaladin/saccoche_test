DROP TABLE IF EXISTS sacoche_image;

CREATE TABLE sacoche_image (
	user_id        MEDIUMINT(8)              UNSIGNED                NOT NULL DEFAULT 0 COMMENT "0 pour le tampon de l'établissement",
	image_objet    ENUM("signature","photo") COLLATE utf8_unicode_ci NOT NULL DEFAULT "photo",
	image_contenu  MEDIUMBLOB                                        NOT NULL,
	image_format   CHAR(4)                   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	image_largeur  SMALLINT(5)               UNSIGNED                NOT NULL DEFAULT 0,
	image_hauteur  SMALLINT(5)               UNSIGNED                NOT NULL DEFAULT 0,
	PRIMARY KEY (user_id),
	KEY image_objet (image_objet)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Fichiers photo pour les élèves, fichiers de signatures pour les professeurs et directeurs.";
