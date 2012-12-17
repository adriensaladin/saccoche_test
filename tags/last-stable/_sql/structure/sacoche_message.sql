DROP TABLE IF EXISTS sacoche_message;

CREATE TABLE IF NOT EXISTS sacoche_message (
	message_id            MEDIUMINT(8) UNSIGNED            NOT NULL AUTO_INCREMENT,
	user_id               MEDIUMINT(8) UNSIGNED            NOT NULL DEFAULT 0,
	message_debut_date    DATE                             NOT NULL DEFAULT "0000-00-00",
	message_fin_date      DATE                             NOT NULL DEFAULT "0000-00-00",
	message_destinataires TEXT COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
	message_contenu       TEXT COLLATE utf8_unicode_ci     NOT NULL DEFAULT "",
	PRIMARY KEY (message_id),
	KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
