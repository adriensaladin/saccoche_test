DROP TABLE IF EXISTS sacoche_parametre;

CREATE TABLE sacoche_parametre (
	parametre_nom    VARCHAR(30)  COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	parametre_valeur VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
	PRIMARY KEY (parametre_nom)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE sacoche_parametre DISABLE KEYS;

INSERT INTO sacoche_parametre VALUES 
( "version_base"                   , "" ),
( "sesamath_id"                    , "0" ),
( "sesamath_uai"                   , "" ),
( "sesamath_type_nom"              , "" ),
( "sesamath_key"                   , "" ),
( "uai"                            , "" ),
( "denomination"                   , "" ),
( "connexion_mode"                 , "normal" ),
( "connexion_nom"                  , "sacoche" ),
( "modele_directeur"               , "ppp.nnnnnnnn" ),
( "modele_professeur"              , "ppp.nnnnnnnn" ),
( "modele_eleve"                   , "ppp.nnnnnnnn" ),
( "modele_parent"                  , "ppp.nnnnnnnn" ),
( "matieres"                       , "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,99" ),
( "niveaux"                        , "31,32,33,35" ),
( "cycles"                         , "3" ),
( "paliers"                        , "3" ),
( "droit_eleve_demandes"           , "0" ),
( "droit_modifier_mdp"             , "directeur,professeur,parent,eleve" ),
( "droit_validation_entree"        , "directeur,professeur" ),
( "droit_validation_pilier"        , "directeur,profprincipal" ),
( "droit_annulation_pilier"        , "directeur,aucunprof" ),
( "droit_voir_referentiels"        , "directeur,professeur,parent,eleve" ),
( "droit_voir_score_bilan"         , "directeur,professeur,parent,eleve" ),
( "droit_voir_algorithme"          , "directeur,professeur,parent,eleve" ),
( "droit_bilan_moyenne_score"      , "parent,eleve" ),
( "droit_bilan_pourcentage_acquis" , "parent,eleve" ),
( "droit_bilan_note_sur_vingt"     , "" ),
( "droit_socle_acces"              , "parent,eleve" ),
( "droit_socle_pourcentage_acquis" , "parent,eleve" ),
( "droit_socle_etat_validation"    , "" ),
( "duree_inactivite"               , "30" ),
( "calcul_valeur_RR"               , "0" ),
( "calcul_valeur_R"                , "33" ),
( "calcul_valeur_V"                , "67" ),
( "calcul_valeur_VV"               , "100" ),
( "calcul_seuil_R"                 , "40" ),
( "calcul_seuil_V"                 , "60" ),
( "calcul_methode"                 , "geometrique" ),
( "calcul_limite"                  , "5" ),
( "cas_serveur_host"               , "" ),
( "cas_serveur_port"               , "" ),
( "cas_serveur_root"               , "" ),
( "css_background-color_NA"        , "#ff9999" ),
( "css_background-color_VA"        , "#ffdd33" ),
( "css_background-color_A"         , "#99ff99" ),
( "gepi_url"                       , "" ),
( "gepi_rne"                       , "" ),
( "gepi_certificat_empreinte"      , "" ),
( "note_image_style"               , "Lomer" ),
( "note_texte_RR"                  , "RR" ),
( "note_texte_R"                   , "R" ),
( "note_texte_V"                   , "V" ),
( "note_texte_VV"                  , "VV" ),
( "note_legende_RR"                , "Très insuffisant." ),
( "note_legende_R"                 , "Insuffisant." ),
( "note_legende_V"                 , "Satisfaisant." ),
( "note_legende_VV"                , "Très satisfaisant." ),
( "acquis_texte_NA"                , "NA" ),
( "acquis_texte_VA"                , "VA" ),
( "acquis_texte_A"                 , "A" ),
( "acquis_legende_NA"              , "Non acquis." ),
( "acquis_legende_VA"              , "Partiellement acquis." ),
( "acquis_legende_A"               , "Acquis." );

ALTER TABLE sacoche_parametre ENABLE KEYS;
