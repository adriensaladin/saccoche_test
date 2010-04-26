DROP TABLE IF EXISTS sacoche_user;

CREATE TABLE sacoche_user (
	user_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	user_num_sconet MEDIUMINT(8) UNSIGNED NOT NULL COMMENT "ELENOET pour un élève (entre 2000 et 5000 ; parfois appelé n° GEP avec un 0 devant) ou INDIVIDU_ID pour un prof (dépasse parfois une capacité SMALLINT UNSIGNED)",
	user_reference CHAR(11) COLLATE utf8_unicode_ci NOT NULL COMMENT "Dans Sconet, ID_NATIONAL pour un élève (pour un prof ce pourrait être le NUMEN mais il n'est pas renseigné et il faudrait deux caractères de plus). Ce champ sert aussi pour un import tableur.",
	user_profil ENUM("eleve","professeur","directeur","administrateur") COLLATE utf8_unicode_ci NOT NULL,
	user_nom VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
	user_prenom VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
	user_login VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
	user_password CHAR(32) COLLATE utf8_unicode_ci NOT NULL,
	user_statut TINYINT(1) NOT NULL DEFAULT 1,
	eleve_classe_id MEDIUMINT(8) UNSIGNED NOT NULL,
	user_id_ent VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL COMMENT "Paramètre renvoyé après une identification CAS depuis un ENT (ça peut être le login, mais ça peut aussi être un numéro interne à l'ENT...).",
	user_id_gepi VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL COMMENT "Login de l'utilisateur dans Gepi utilisé pour un transfert note/moyenne vers un bulletin.",
	PRIMARY KEY (user_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
