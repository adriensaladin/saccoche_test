DROP TABLE IF EXISTS sacoche_livret_saisie;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB... sauf NULL !

-- saisie_valeur=NULL + prof_id>0 = saisie supprimée

CREATE TABLE sacoche_livret_saisie (
  livret_saisie_id        MEDIUMINT(8)                               UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_page_ref         VARCHAR(6)                                 COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_page_periodicite ENUM("periode","cycle","college")          COLLATE utf8_unicode_ci NOT NULL DEFAULT "periode",
  jointure_periode        ENUM("","T1","T2","T3","S1","S2")          COLLATE utf8_unicode_ci NOT NULL DEFAULT ""         COMMENT "renseigné si livret_page_periodicite = periode ; @see sacoche_periode.periode_livret",
  rubrique_type           VARCHAR(8)                                 COLLATE utf8_unicode_ci NOT NULL DEFAULT ""         COMMENT "eval | socle | epi | ap | parcours | synthese | viesco",
  rubrique_id             SMALLINT(5)                                UNSIGNED                NOT NULL DEFAULT 0          COMMENT "matiere_id | livret_rubrique_id | socle_composante (11;12;13;14;20;30;40;50) ; 0 pour viesco | synthese",
  cible_nature            ENUM("eleve","classe")                     COLLATE utf8_unicode_ci NOT NULL DEFAULT "eleve"    COMMENT "indique si la saisie concerne un élève ou une classe",
  cible_id                MEDIUMINT(8)                               UNSIGNED                NOT NULL DEFAULT 0          COMMENT "id élève ou classe suivant le champ cible_nature",
  saisie_objet            ENUM("position","appreciation","elements") COLLATE utf8_unicode_ci NOT NULL DEFAULT "position" COMMENT "indique si la saisie concerne un positionnement ou une appréciation ou des éléments de programmes travaillés",
  saisie_valeur           TEXT                                       COLLATE utf8_unicode_ci          DEFAULT NULL       COMMENT "valeur sur 100 pour un positionnement (avec 5 dixièmes possibles, à diviser par 5 pour l'avoir sur 20, à confronter aux seuils pour un positionnement de 1 à 4) | texte pour une appréciation | array json pour des éléments de programme",
  saisie_origine          ENUM("bulletin","calcul","saisie")         COLLATE utf8_unicode_ci NOT NULL DEFAULT "calcul"   COMMENT "recopie depuis le bulletin | calcul automatique par SACoche | saisie manuelle",
  prof_id                 MEDIUMINT(8)                               UNSIGNED                NOT NULL DEFAULT 0          COMMENT "pas dans la clef car plus simple (un seul positionnement et un seul élément de programme et une seule appréciation à remonter) ; pour l'historique des profs qui ont saisi qq chose pour pouvoir indiquer tous les profs ayant participé voir [sacoche_livret_saisie_jointure_prof]",
  PRIMARY KEY (livret_saisie_id),
  UNIQUE KEY ( livret_page_ref , livret_page_periodicite , jointure_periode , rubrique_type , rubrique_id , cible_nature , cible_id , saisie_objet ),
  KEY livret_page_periodicite (livret_page_periodicite),
  KEY jointure_periode (jointure_periode),
  KEY rubrique_type (rubrique_type),
  KEY rubrique_id (rubrique_id),
  KEY cible_nature (cible_nature),
  KEY cible_id (cible_id),
  KEY saisie_objet (saisie_objet),
  KEY saisie_origine (saisie_origine),
  KEY prof_id (prof_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
