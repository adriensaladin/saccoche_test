DROP TABLE IF EXISTS sacoche_parametre;

CREATE TABLE sacoche_parametre (
	parametre_nom    VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	parametre_valeur VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (parametre_nom)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_parametre DISABLE KEYS;

INSERT INTO sacoche_parametre VALUES 
( "version_base"                                  , "" ),
( "sesamath_id"                                   , "0" ),
( "sesamath_uai"                                  , "" ),
( "sesamath_type_nom"                             , "" ),
( "sesamath_key"                                  , "" ),
( "webmestre_uai"                                 , "" ),
( "webmestre_denomination"                        , "" ),
( "etablissement_denomination"                    , "" ),
( "etablissement_adresse1"                        , "" ),
( "etablissement_adresse2"                        , "" ),
( "etablissement_adresse3"                        , "" ),
( "etablissement_telephone"                       , "" ),
( "etablissement_fax"                             , "" ),
( "etablissement_courriel"                        , "" ),
( "mois_bascule_annee_scolaire"                   , "8" ),
( "connexion_mode"                                , "normal" ),
( "connexion_nom"                                 , "sacoche" ),
( "modele_directeur"                              , "ppp.nnnnnnnn" ),
( "modele_professeur"                             , "ppp.nnnnnnnn" ),
( "modele_eleve"                                  , "ppp.nnnnnnnn" ),
( "modele_parent"                                 , "ppp.nnnnnnnn" ),
( "mdp_longueur_mini"                             , "6" ),
( "droit_eleve_demandes"                          , "0" ),
( "droit_modifier_mdp"                            , "directeur,professeur,parent,eleve" ),
( "droit_validation_entree"                       , "directeur,professeur" ),
( "droit_validation_pilier"                       , "directeur,profprincipal" ),
( "droit_annulation_pilier"                       , "directeur,aucunprof" ),
( "droit_gerer_referentiel"                       , "profcoordonnateur" ),
( "droit_gerer_ressource"                         , "professeur" ),
( "droit_voir_referentiels"                       , "directeur,professeur,parent,eleve" ),
( "droit_voir_grilles_items"                      , "directeur,professeur,parent,eleve" ),
( "droit_voir_score_bilan"                        , "directeur,professeur,parent,eleve" ),
( "droit_voir_algorithme"                         , "directeur,professeur,parent,eleve" ),
( "droit_voir_officiel_releve_archive"            , "directeur,professeur" ),
( "droit_voir_officiel_bulletin_archive"          , "directeur,professeur" ),
( "droit_voir_officiel_socle_archive"             , "directeur,professeur" ),
( "droit_bilan_moyenne_score"                     , "parent,eleve" ),
( "droit_bilan_pourcentage_acquis"                , "parent,eleve" ),
( "droit_bilan_note_sur_vingt"                    , "" ),
( "droit_socle_acces"                             , "parent,eleve" ),
( "droit_socle_pourcentage_acquis"                , "parent,eleve" ),
( "droit_socle_etat_validation"                   , "" ),
( "droit_officiel_releve_changer_etat"            , "directeur,aucunprof" ),
( "droit_officiel_releve_appreciation_generale"   , "directeur,profprincipal" ),
( "droit_officiel_releve_impression_pdf"          , "directeur,aucunprof" ),
( "droit_officiel_bulletin_changer_etat"          , "directeur,aucunprof" ),
( "droit_officiel_bulletin_appreciation_generale" , "directeur,profprincipal" ),
( "droit_officiel_bulletin_impression_pdf"        , "directeur,aucunprof" ),
( "droit_officiel_socle_changer_etat"             , "directeur,aucunprof" ),
( "droit_officiel_socle_appreciation_generale"    , "directeur,profprincipal" ),
( "droit_officiel_socle_impression_pdf"           , "directeur,aucunprof" ),
( "duree_inactivite"                              , "30" ),
( "calcul_valeur_RR"                              , "0" ),
( "calcul_valeur_R"                               , "33" ),
( "calcul_valeur_V"                               , "67" ),
( "calcul_valeur_VV"                              , "100" ),
( "calcul_seuil_R"                                , "40" ),
( "calcul_seuil_V"                                , "60" ),
( "calcul_methode"                                , "geometrique" ),
( "calcul_limite"                                 , "5" ),
( "cas_serveur_host"                              , "" ),
( "cas_serveur_port"                              , "" ),
( "cas_serveur_root"                              , "" ),
( "css_background-color_NA"                       , "#ff9999" ),
( "css_background-color_VA"                       , "#ffdd33" ),
( "css_background-color_A"                        , "#99ff99" ),
( "gepi_url"                                      , "" ),
( "gepi_rne"                                      , "" ),
( "gepi_certificat_empreinte"                     , "" ),
( "liste_paliers_actifs"                          , "" ),
( "note_image_style"                              , "Lomer" ),
( "note_texte_RR"                                 , "RR" ),
( "note_texte_R"                                  , "R" ),
( "note_texte_V"                                  , "V" ),
( "note_texte_VV"                                 , "VV" ),
( "note_legende_RR"                               , "Très insuffisant." ),
( "note_legende_R"                                , "Insuffisant." ),
( "note_legende_V"                                , "Satisfaisant." ),
( "note_legende_VV"                               , "Très satisfaisant." ),
( "acquis_texte_NA"                               , "NA" ),
( "acquis_texte_VA"                               , "VA" ),
( "acquis_texte_A"                                , "A" ),
( "acquis_legende_NA"                             , "Non acquis." ),
( "acquis_legende_VA"                             , "Partiellement acquis." ),
( "acquis_legende_A"                              , "Acquis." ),
( "enveloppe_horizontal_gauche"                   , "110" ),
( "enveloppe_horizontal_milieu"                   , "100" ),
( "enveloppe_horizontal_droite"                   , "20" ),
( "enveloppe_vertical_haut"                       , "50" ),
( "enveloppe_vertical_milieu"                     , "45" ),
( "enveloppe_vertical_bas"                        , "20" ),
( "officiel_infos_etablissement"                  , "adresse,telephone,fax,courriel" ),
( "officiel_infos_responsables"                   , "non" ),
( "officiel_nombre_exemplaires"                   , "un" ),
( "officiel_tampon_signature"                     , "remplacer" ),
( "officiel_marge_gauche"                         , "5" ),
( "officiel_marge_droite"                         , "5" ),
( "officiel_marge_haut"                           , "5" ),
( "officiel_marge_bas"                            , "10" ),
( "officiel_releve_appreciation_rubrique"         , "300" ),
( "officiel_releve_appreciation_generale"         , "400" ),
( "officiel_releve_moyenne_scores"                , "1" ),
( "officiel_releve_pourcentage_acquis"            , "1" ),
( "officiel_releve_cases_nb"                      , "4" ),
( "officiel_releve_aff_coef"                      , "0" ),
( "officiel_releve_aff_socle"                     , "1" ),
( "officiel_releve_aff_domaine"                   , "0" ),
( "officiel_releve_aff_theme"                     , "0" ),
( "officiel_releve_couleur"                       , "oui" ),
( "officiel_releve_legende"                       , "oui" ),
( "officiel_bulletin_appreciation_rubrique"       , "200" ),
( "officiel_bulletin_appreciation_generale"       , "400" ),
( "officiel_bulletin_moyenne_scores"              , "1" ),
( "officiel_bulletin_note_sur_20"                 , "1" ),
( "officiel_bulletin_moyenne_classe"              , "1" ),
( "officiel_bulletin_moyenne_generale"            , "0" ),
( "officiel_bulletin_couleur"                     , "oui" ),
( "officiel_bulletin_legende"                     , "oui" ),
( "officiel_socle_appreciation_rubrique"          , "0" ),
( "officiel_socle_appreciation_generale"          , "400" ),
( "officiel_socle_only_presence"                  , "0" ),
( "officiel_socle_pourcentage_acquis"             , "1" ),
( "officiel_socle_etat_validation"                , "1" ),
( "officiel_socle_couleur"                        , "oui" ),
( "officiel_socle_legende"                        , "oui" );

ALTER TABLE sacoche_parametre ENABLE KEYS;
