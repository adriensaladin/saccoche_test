DROP TABLE IF EXISTS sacoche_demande;

CREATE TABLE sacoche_demande (
	demande_id     MEDIUMINT(8)         UNSIGNED                NOT NULL AUTO_INCREMENT,
	user_id        MEDIUMINT(8)         UNSIGNED                NOT NULL DEFAULT 0,
	matiere_id     SMALLINT(5)          UNSIGNED                NOT NULL DEFAULT 0,
	item_id        MEDIUMINT(8)         UNSIGNED                NOT NULL DEFAULT 0,
	demande_date   DATE                                         NOT NULL DEFAULT "0000-00-00",
	demande_score  TINYINT(3)           UNSIGNED                         DEFAULT NULL    COMMENT "Sert à mémoriser le score avant réévaluation pour ne pas avoir à le recalculer ; valeur null si item non évalué.",
	demande_statut ENUM("eleve","prof") COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" COMMENT "[eleve] pour une demande d'élève ; [prof] pour une prévision d'évaluation par le prof ; une annulation de l'élève ou du prof efface l'enregistrement",
	PRIMARY KEY (demande_id),
	UNIQUE KEY demande_key (user_id,matiere_id,item_id),
	KEY matiere_id (matiere_id),
	KEY item_id (item_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
