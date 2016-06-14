DROP TABLE IF EXISTS sacoche_livret_parcours_type;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_parcours_type (
  livret_parcours_type_code            VARCHAR(5)   COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_parcours_type_nom             VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_parcours_type_url_sitegouv    VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_parcours_type_url_txtofficiel VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_parcours_type_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_parcours_type DISABLE KEYS;

INSERT INTO sacoche_livret_parcours_type (livret_parcours_type_code, livret_parcours_type_nom, livret_parcours_type_url_sitegouv, livret_parcours_type_url_txtofficiel) VALUES
("P_AVN", "Parcours avenir"                              , "http://www.education.gouv.fr/cid83948/le-parcours-avenir.html"                             , "http://www.education.gouv.fr/pid25535/bulletin_officiel.html?cid_bo=91137"),
("P_CIT", "Parcours citoyen"                             , "http://www.education.gouv.fr/cid100517/le-parcours-citoyen.html"                           , "http://cache.media.education.gouv.fr/file/CSP/70/5/parcours_citoyen_10-03-16_adopte_551705.pdf"),
("P_ART", "Parcours d'éducation artistique et culturelle", "http://eduscol.education.fr/cid74945/le-parcours-d-education-artistique-et-culturelle.html", "http://www.education.gouv.fr/pid25535/bulletin_officiel.html?cid_bo=91164"),
("P_SAN", "Parcours éducatif de santé"                   , "http://www.education.gouv.fr/cid50297/la-sante-des-eleves.html"                            , "http://www.education.gouv.fr/pid285/bulletin_officiel.html?cid_bo=97990");

ALTER TABLE sacoche_livret_parcours_type ENABLE KEYS;
