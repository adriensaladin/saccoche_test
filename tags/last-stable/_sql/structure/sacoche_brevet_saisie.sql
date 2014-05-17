DROP TABLE IF EXISTS sacoche_brevet_saisie;

CREATE TABLE sacoche_brevet_saisie (
  brevet_serie_ref    VARCHAR(6)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""      COMMENT "Série du brevet.",
  brevet_epreuve_code TINYINT(3)             UNSIGNED                NOT NULL DEFAULT 0       COMMENT "Code de l'épreuve ; 255 pour le total des points et l'appréciation du conseil de classe.",
  eleve_ou_classe_id  MEDIUMINT(8)           UNSIGNED                NOT NULL DEFAULT 0       COMMENT "id élève ou classe suivant le champ saisie_type",
  saisie_type         ENUM("eleve","classe") COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve" COMMENT "indique si la saisie concerne un élève ou une classe",
  prof_id             MEDIUMINT(8)           UNSIGNED                NOT NULL DEFAULT 0       COMMENT "dernier auteur de l'appréciation",
  matieres_id         VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT ""      COMMENT "Liste des matiere_id finalement retenus pour extraire la note, et donc des profs qui auront accès à l'appréciation.",
  saisie_note         VARCHAR(5)             COLLATE utf8_unicode_ci NOT NULL DEFAULT ""      COMMENT "note épreuve sur 20 arrondi à 0.5, ou total des points, ou code spécial",
  saisie_appreciation VARCHAR(255)           COLLATE utf8_unicode_ci NOT NULL DEFAULT ""      COMMENT "appréciation, unique par épreuve, ou avis du conseil de classe si brevet_epreuve_code=255",
  PRIMARY KEY ( brevet_serie_ref , brevet_epreuve_code , eleve_ou_classe_id , saisie_type ),
  KEY brevet_epreuve_code (brevet_epreuve_code),
  KEY eleve_ou_classe_id (eleve_ou_classe_id),
  KEY saisie_type (saisie_type),
  KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
