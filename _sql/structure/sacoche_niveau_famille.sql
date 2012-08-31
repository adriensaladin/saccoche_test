DROP TABLE IF EXISTS sacoche_niveau_famille;

CREATE TABLE sacoche_niveau_famille (
	niveau_famille_id        TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_famille_categorie TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0 COMMENT "1 = Niveaux classes ; 2 = Niveaux spécifiques",
	niveau_famille_ordre     TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 0,
	niveau_famille_nom       VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (niveau_famille_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_niveau_famille DISABLE KEYS;

INSERT INTO sacoche_niveau_famille VALUES
( 2, 1, 1, "Primaire"),
( 3, 1, 2, "Collège"),
( 4, 1, 3, "SEGPA - Pré-apprentissage"),
( 5, 1, 4, "Lycée général"),
( 6, 1, 5, "Lycée technologique"),
( 7, 1, 6, "Lycée professionnel"),
( 8, 1, 7, "BTS"),
( 1, 2, 1, "Cycles (niveaux 'longitudinaux')"),
( 9, 2, 2, "CECRL (cadre européen commun de référence pour les langues)");

ALTER TABLE sacoche_niveau_famille ENABLE KEYS;
