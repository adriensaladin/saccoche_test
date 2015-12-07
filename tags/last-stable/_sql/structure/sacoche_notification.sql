DROP TABLE IF EXISTS sacoche_notification;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB

CREATE TABLE sacoche_notification (
  notification_id         INT(10)                                             UNSIGNED                NOT NULL AUTO_INCREMENT,
  user_id                 MEDIUMINT(8)                                        UNSIGNED                NOT NULL DEFAULT 0,
  abonnement_ref          VARCHAR(30)                                         COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  notification_attente_id MEDIUMINT(8)                                        UNSIGNED                         DEFAULT NULL   COMMENT "En cas de modification, pour retrouver une notification non encore envoyée ; passé à NULL une fois la notification envoyée.",
  notification_statut     ENUM("attente","consultable","consultée","envoyée") COLLATE utf8_unicode_ci NOT NULL DEFAULT "attente",
  notification_date       DATETIME                                                                             DEFAULT NULL ,
  notification_contenu    TEXT                                                COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (notification_id),
  KEY user_id (user_id),
  KEY abonnement_ref (abonnement_ref),
  KEY notification_attente_id (notification_attente_id),
  KEY notification_statut (notification_statut),
  KEY notification_date (notification_date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="En lien avec les tables sacoche_abonnement et sacoche_jointure_user_abonnement.";
