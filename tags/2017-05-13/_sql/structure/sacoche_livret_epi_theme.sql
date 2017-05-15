DROP TABLE IF EXISTS sacoche_livret_epi_theme;

CREATE TABLE sacoche_livret_epi_theme (
  livret_epi_theme_code VARCHAR(7)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  livret_epi_theme_nom  VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  PRIMARY KEY (livret_epi_theme_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_livret_epi_theme DISABLE KEYS;

INSERT INTO sacoche_livret_epi_theme (livret_epi_theme_code, livret_epi_theme_nom) VALUES
("EPI_SAN", "Corps, santé, bien-être et sécurité"),
("EPI_ART", "Culture et création artistiques"),
("EPI_EDD", "Transition écologique et développement durable"),
("EPI_ICC", "Information, communication, citoyenneté"),
("EPI_LGA", "Langues et cultures de l'Antiquité"),
("EPI_LGE", "Langues et cultures étrangères ou régionales"),
("EPI_PRO", "Monde économique et professionnel"),
("EPI_STS", "Sciences, technologie et société");

ALTER TABLE sacoche_livret_epi_theme ENABLE KEYS;
