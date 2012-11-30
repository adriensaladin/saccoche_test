DROP TABLE IF EXISTS sacoche_officiel_saisie;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

-- rubrique_id>0 + prof_id>0 = appréciation rubrique
-- rubrique_id=0 + prof_id>0 = appréciation synthèse
-- rubrique_id>0 + prof_id=0 = moyenne rubrique (bulletin)
-- les moyennes générales et moyennes de classe (bulletin) ne sont volontairement pas enregistrées dans la base, mais déduites des moyennes des rubriques

CREATE TABLE sacoche_officiel_saisie (
	officiel_type       ENUM("releve","bulletin","palier1","palier2","palier3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "bulletin",
	periode_id          MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0,
	eleve_id            MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0,
	rubrique_id         SMALLINT(5)                                             UNSIGNED                NOT NULL DEFAULT 0    COMMENT "matiere_id ou pilier_id ; 0 pour l'appréciation générale",
	prof_id             MEDIUMINT(8)                                            UNSIGNED                NOT NULL DEFAULT 0    COMMENT "0 pour la moyenne, avec commentaire dans saisie_appreciation si report non automatique",
	saisie_note         DECIMAL(3,1)                                            UNSIGNED                         DEFAULT NULL COMMENT "sur 20, à multiplier par 5 pour avoir le pourcentage",
	saisie_appreciation TEXT                                                    COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY ( eleve_id , officiel_type , periode_id , rubrique_id , prof_id ),
	KEY officiel_type (officiel_type),
	KEY periode_id (periode_id),
	KEY rubrique_id (rubrique_id),
	KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
