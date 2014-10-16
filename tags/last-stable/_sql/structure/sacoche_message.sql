DROP TABLE IF EXISTS sacoche_message;

-- Attention : pas d`apostrophes dans les lignes commentées sinon on peut obtenir un bug d`analyse dans la classe pdo de SebR : "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound ..."
-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB

CREATE TABLE IF NOT EXISTS sacoche_message (
  message_id            MEDIUMINT(8) UNSIGNED            NOT NULL AUTO_INCREMENT,
  user_id               MEDIUMINT(8) UNSIGNED            NOT NULL DEFAULT 0,
  message_debut_date    DATE                             NOT NULL DEFAULT "0000-00-00",
  message_fin_date      DATE                             NOT NULL DEFAULT "0000-00-00",
  message_destinataires TEXT COLLATE utf8_unicode_ci     NOT NULL,
  message_contenu       TEXT COLLATE utf8_unicode_ci     NOT NULL,
  message_dests_cache   TEXT COLLATE utf8_unicode_ci     NOT NULL, 
  PRIMARY KEY (message_id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
