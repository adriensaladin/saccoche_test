DROP TABLE IF EXISTS sacoche_user;

CREATE TABLE sacoche_user (
	user_id             MEDIUMINT(8)                                            UNSIGNED                NOT NULL AUTO_INCREMENT,
	user_sconet_id      MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0  COMMENT "ELEVE.ELEVE.ID pour un élève ; INDIVIDU_ID pour un prof",
	user_sconet_elenoet SMALLINT(5)                                             UNSIGNED                NOT NULL DEFAULT 0  COMMENT "ELENOET pour un élève (entre 2000 et 5000 ; parfois appelé n° GEP avec un 0 devant)",
	user_reference      CHAR(11)                                                COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Dans Sconet, ID_NATIONAL pour un élève (pour un prof ce pourrait être le NUMEN mais il n'est pas renseigné et il faudrait deux caractères de plus). Ce champ sert aussi pour un import tableur.",
	user_profil         ENUM("eleve","professeur","directeur","administrateur") COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve",
	user_nom            VARCHAR(20)                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	user_prenom         VARCHAR(20)                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	user_login          VARCHAR(20)                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	user_password       CHAR(32)                                                COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	user_statut         TINYINT(1)                                              UNSIGNED                NOT NULL DEFAULT 1,
	user_daltonisme     TINYINT(1)                                              UNSIGNED                NOT NULL DEFAULT 0,
	user_tentative_date DATETIME                                                                        NOT NULL DEFAULT "0000-00-00 00:00:00",
	user_connexion_date DATETIME                                                                        NOT NULL DEFAULT "0000-00-00 00:00:00",
	user_statut_date    DATE                                                                            NOT NULL DEFAULT "0000-00-00",
	eleve_classe_id     MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0,
	user_id_ent         VARCHAR(32)                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l'ENT...).",
	user_id_gepi        VARCHAR(32)                                             COLLATE utf8_unicode_ci NOT NULL DEFAULT "" COMMENT "Login de l'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin.",
	PRIMARY KEY (user_id),
	UNIQUE KEY user_login (user_login),
	KEY user_profil (user_profil),
	KEY user_statut (user_statut),
	KEY user_id_ent (user_id_ent)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
