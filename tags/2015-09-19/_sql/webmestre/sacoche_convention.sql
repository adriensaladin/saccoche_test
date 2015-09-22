DROP TABLE IF EXISTS sacoche_convention;

-- Attention : pas de valeur par défaut possible pour les champs TEXT et BLOB

CREATE TABLE sacoche_convention (
  convention_id          SMALLINT(5)  UNSIGNED                NOT NULL AUTO_INCREMENT,
  sacoche_base           MEDIUMINT(8) UNSIGNED                NOT NULL DEFAULT 0,
  connexion_nom          VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '""',
  convention_date_debut  DATE                                 NOT NULL DEFAULT '0000-00-00',
  convention_date_fin    DATE                                 NOT NULL DEFAULT '0000-00-00',
  convention_creation    DATE                                          DEFAULT NULL,
  convention_signature   DATE                                          DEFAULT NULL,
  convention_paiement    DATE                                          DEFAULT NULL,
  convention_relance     DATE                                          DEFAULT NULL,
  convention_activation  TINYINT(1)   UNSIGNED                NOT NULL DEFAULT 0,
  convention_mail_renouv DATE                                          DEFAULT NULL,
  convention_commentaire TEXT         COLLATE utf8_unicode_ci,
  PRIMARY KEY (convention_id),
  UNIQUE KEY (sacoche_base,connexion_nom,convention_date_debut),
  KEY convention_date_fin (convention_date_fin),
  KEY convention_activation (convention_activation)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT "Pour les conventions ENT établissements (serveur Sésamath).";
