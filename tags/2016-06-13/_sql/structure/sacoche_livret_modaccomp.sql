DROP TABLE IF EXISTS sacoche_livret_modaccomp;

CREATE TABLE sacoche_livret_modaccomp (
  livret_modaccomp_code VARCHAR(5)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_modaccomp_nom  VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_modaccomp_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_modaccomp DISABLE KEYS;

INSERT INTO sacoche_livret_modaccomp (livret_modaccomp_code, livret_modaccomp_nom) VALUES
("PAP",   "Projet d'accompagnement personnalisé"),
("PAI",   "Projet d'accueil individualisé"),
("PPRE",  "Projet personnalisé de réussite éducative"),
("PPS",   "Projet personnalisé de scolarisation"),
("ULIS",  "Unité localisée pour l'inclusion scolaire"),
("UPE2A", "Unité pédagogique pour élèves allophones arrivants"),
("SEGPA", "Section d'enseignement général adapté");

ALTER TABLE sacoche_livret_modaccomp ENABLE KEYS;
