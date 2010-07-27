DROP TABLE IF EXISTS sacoche_referentiel;

CREATE TABLE sacoche_referentiel (
	matiere_id SMALLINT(5) UNSIGNED NOT NULL,
	niveau_id TINYINT(3) UNSIGNED NOT NULL,
	referentiel_partage_etat ENUM("bof","non","oui","hs") COLLATE utf8_unicode_ci NOT NULL COMMENT "'oui' = référentiel partagé sur le serveur communautaire ; 'non' = référentiel non partagé avec la communauté ; 'bof' = référentiel dont le partage est sans intérêt (pas novateur) ; 'hs' = référentiel dont le partage est sans objet (matière spécifique)",
	referentiel_partage_date DATE NOT NULL,
	referentiel_calcul_methode ENUM("geometrique","arithmetique","classique","bestof1","bestof2","bestof3") COLLATE utf8_unicode_ci NOT NULL DEFAULT "geometrique" COMMENT "Coefficients en progression géométrique, arithmetique, ou moyenne classique non pondérée, ou conservation des meilleurs scores. Valeur surclassant la configuration par défaut.",
	referentiel_calcul_limite TINYINT(3) UNSIGNED NOT NULL DEFAULT 5 COMMENT "Nombre maximum de dernières évaluations prises en comptes (0 pour les prendre toutes). Valeur surclassant la configuration par défaut.",
	UNIQUE KEY referentiel_id (matiere_id,niveau_id),
	KEY matiere_id (matiere_id),
	KEY niveau_id (niveau_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
