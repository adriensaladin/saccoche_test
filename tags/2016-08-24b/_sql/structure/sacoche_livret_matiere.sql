DROP TABLE IF EXISTS sacoche_livret_matiere;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."

CREATE TABLE sacoche_livret_matiere (
  livret_matiere_id          TINYINT(3)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  livret_matiere_ordre       TINYINT(3)  UNSIGNED                NOT NULL DEFAULT 255,
  livret_siecle_code_matiere CHAR(6)     COLLATE utf8_unicode_ci          DEFAULT NULL COMMENT "Issu de SIECLE <MATIERE CODE> et requis pour l'export LSUN ; correspond à sacoche_matiere.matiere_code précédé de zéros (sauf cas particuliers).",
  livret_siecle_code_gestion VARCHAR(5)  COLLATE utf8_unicode_ci          DEFAULT NULL COMMENT "Issu de SIECLE <CODE_GESTION> ; correspond à sacoche_matiere.matiere_ref.",
  livret_siecle_libelle      VARCHAR(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_matiere_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_matiere DISABLE KEYS;

INSERT INTO sacoche_livret_matiere VALUES
(231,  1, NULL, NULL, "Français"),                           --  207, "020700", "FRANC"
(232,  2, NULL, NULL, "Mathématiques"),                      --  613, "061300", "MATHS"
(233,  3, NULL, NULL, "Histoire-géographie"),                --  437, "043700", "HI-GE"
(234,  4, NULL, NULL, "Enseignement moral et civique"),      --  438, "043800", "EMC"
(235,  5, NULL, NULL, "Langue vivante 1"),                   -- Anglais 030201 AGL1
(236,  6, NULL, NULL, "Langue vivante 2"),                   -- Espagnol lv2 030602 ESP2 | Allemand lv2 030102 ALL2
(237,  7, NULL, NULL, "Education physique et sportive"),     -- 1001, "100100", "EPS"
(238,  8, NULL, NULL, "Arts plastiques"),                    --  901, "090100", "A-PLA"
(239,  9, NULL, NULL, "Education musicale"),                 --  813, "081300", "EDMUS"
(240, 10, NULL, NULL, "Sciences de la vie et de la terre"),  --  629, "062900", "SVT"
(241, 11, NULL, NULL, "Technologie"),                        --  708, "070800", "TECHN"), '; // Technologie
(242, 12, NULL, NULL, "Physique-chimie"),                    --  623, "062300", "PH-CH"
(243, 13, NULL, NULL, "Langues et cultures de l'Antiquité"), -- Latin 020300 LCALA | Grec 020400 LCAGR
(244, 14, NULL, NULL, "Matière personnalisée n°1"),
(245, 15, NULL, NULL, "Matière personnalisée n°2"),
(246, 16, NULL, NULL, "Matière personnalisée n°3");

ALTER TABLE sacoche_livret_matiere ENABLE KEYS;
