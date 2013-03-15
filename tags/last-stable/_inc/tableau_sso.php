<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

/*
 * Sous-tableau avec les différents formats de csv d'import
 */
$tab_csv_format = array();
$tab_csv_format['']            = array( 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL );
$tab_csv_format['elyco']       = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>4 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL );
$tab_csv_format['esup']        = array( 'csv_infos'=>TRUE  , 'csv_entete'=>2 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL );
$tab_csv_format['scolastance'] = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL );
$tab_csv_format['itop']        = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL );
$tab_csv_format['itslearning'] = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>NULL );
$tab_csv_format['kosmos']      = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>4 , 'csv_prenom'=>3 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL );
$tab_csv_format['liberscol']   = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>0 , 'csv_prenom'=>1 , 'csv_id_ent'=>2 , 'csv_id_sconet'=>NULL );
$tab_csv_format['logica']      = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>3 , 'csv_prenom'=>4 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>2    );
$tab_csv_format['pentila']     = array( 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>0 , 'csv_prenom'=>1 , 'csv_id_ent'=>5 , 'csv_id_sconet'=>NULL );
$tab_csv_format['toutatice']   = array( 'csv_infos'=>TRUE  , 'csv_entete'=>0 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL );

/*
 * Sous-tableau avec les différents paramétrages de serveurs CAS
 */
$tab_serveur_cas = array();
$tab_serveur_cas['']                       = array( 'serveur_host'=>''                                       , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['argos']                  = array( 'serveur_host'=>'ent-cas.ac-bordeaux.fr'                 , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['esup_montpellier']       = array( 'serveur_host'=>'www.environnementnumeriquedetravail.fr' , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_agora06']           = array( 'serveur_host'=>'www.agora06.fr'                         , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_alsace']            = array( 'serveur_host'=>'cas.scolastance.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-alsace'        );
$tab_serveur_cas['itop_enc92']             = array( 'serveur_host'=>'www.enc92.fr'                           , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_enteduc']           = array( 'serveur_host'=>'cas.enteduc.fr'                         , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_lille']             = array( 'serveur_host'=>'www.savoirsnumeriques5962.fr'           , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_oise']              = array( 'serveur_host'=>'ent.oise.fr'                            , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_place']             = array( 'serveur_host'=>'www.ent-place.fr'                       , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itop_valdoise']          = array( 'serveur_host'=>'ent95.valdoise.fr'                      , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['itslearning_02']         = array( 'serveur_host'=>'cas.itslearning.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent02'         );
$tab_serveur_cas['itslearning_04']         = array( 'serveur_host'=>'cas.itslearning.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent04'         );
$tab_serveur_cas['itslearning_52']         = array( 'serveur_host'=>'cas.itslearning.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-enthautemarne' );
$tab_serveur_cas['kosmos_cybercolleges42'] = array( 'serveur_host'=>'cas.cybercolleges42.fr'                 , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['kosmos_ecollege31']      = array( 'serveur_host'=>'cas.ecollege.haute-garonne.fr'          , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['kosmos_elyco']           = array( 'serveur_host'=>'cas.e-lyco.fr'                          , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['kosmos_entmip']          = array( 'serveur_host'=>'cas.entmip.fr'                          , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['laclasse']               = array( 'serveur_host'=>'www.laclasse.com'                       , 'serveur_port'=>443  , 'serveur_root'=>'sso'               );
$tab_serveur_cas['lareunion']              = array( 'serveur_host'=>'seshat.ac-reunion.fr'                   , 'serveur_port'=>8443 , 'serveur_root'=>''                  );
$tab_serveur_cas['liberscol']              = array( 'serveur_host'=>'cas.ent-liberscol.fr'                   , 'serveur_port'=>443  , 'serveur_root'=>''                  );
$tab_serveur_cas['logica_celia']           = array( 'serveur_host'=>'www.ent-celia.fr'                       , 'serveur_port'=>443  , 'serveur_root'=>'connexion'         );
$tab_serveur_cas['logica_elie']            = array( 'serveur_host'=>'ent.limousin.fr'                        , 'serveur_port'=>443  , 'serveur_root'=>'connexion'         );
$tab_serveur_cas['logica_ent77']           = array( 'serveur_host'=>'ent77.seine-et-marne.fr'                , 'serveur_port'=>443  , 'serveur_root'=>'connexion'         );
$tab_serveur_cas['logica_lilie']           = array( 'serveur_host'=>'ent.iledefrance.fr'                     , 'serveur_port'=>443  , 'serveur_root'=>'connexion'         );
$tab_serveur_cas['pentila']                = array( 'serveur_host'=>'cartabledesavoie.com'                   , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );
$tab_serveur_cas['scolastance_02']         = array( 'serveur_host'=>'cas.scolastance.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent02'         );
$tab_serveur_cas['scolastance_04']         = array( 'serveur_host'=>'cas.cg04.fr'                            , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent04'         );
$tab_serveur_cas['scolastance_52']         = array( 'serveur_host'=>'cas.scolastance.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-hautemarne'    );
$tab_serveur_cas['scolastance_90']         = array( 'serveur_host'=>'cas.scolastance.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent90'         );
$tab_serveur_cas['scolastance_auvergne']   = array( 'serveur_host'=>'cas.scolastance.com'                    , 'serveur_port'=>443  , 'serveur_root'=>'cas-auvergne'      );
$tab_serveur_cas['toutatice']              = array( 'serveur_host'=>'www.toutatice.fr'                       , 'serveur_port'=>443  , 'serveur_root'=>'cas'               );

/*
 * Sous-tableau avec les différents paramétrages SAML
 */
// Vérification de la définition de certaines variables de session car appel de ce fichier depuis la doc.
$saml_url = isset($_SESSION['GEPI_URL']) ? $_SESSION['GEPI_URL'] : 'http://' ; 
$saml_rne = isset($_SESSION['GEPI_RNE']) ? $_SESSION['GEPI_RNE'] : ( isset($_SESSION['WEBMESTRE_UAI']) ? $_SESSION['WEBMESTRE_UAI'] : '' ) ;
$tab_saml_param = array();
$tab_saml_param['gepi'] = array( 'saml_url'=>$saml_url , 'saml_rne'=>$saml_rne , 'saml_certif'=>'AA:FD:FF:98:48:18:A8:56:73:32:73:8F:33:53:04:8C:36:9B:E6:B2' );

/*
 * Tableau avec les modes d'identification possibles
 */
$tab_connexion_mode = array();
$tab_connexion_mode['normal']     = 'Normal';
$tab_connexion_mode['cas']        = 'Serveur CAS';
$tab_connexion_mode['shibboleth'] = 'Shibboleth';
$tab_connexion_mode['gepi']       = 'GEPI';

/*
 * Tableau avec les informations relatives à chaque connecteur
 */
$tab_connexion_info = array();
$tab_connexion_info['normal']['|sacoche']          = array( 'txt'=>'Connexion avec les identifiants enregistrés dans SACoche.' );
$tab_connexion_info['cas'][   '|perso']            = array( 'txt'=>'Configuration CAS manuelle.'                                  , 'etat'=>1 , 'societe'=>NULL                 ) + $tab_csv_format['itop']        + $tab_serveur_cas[''];
$tab_connexion_info['cas'][ '02|ent_02']           = array( 'txt'=>'ENT département de l\'Aisne sur Scolastance.'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_02'];
$tab_connexion_info['cas'][ '02|ent_02_v2']        = array( 'txt'=>'ENT département de l\'Aisne sur ItsLearning.'                 , 'etat'=>1 , 'societe'=>'ItsLearning'        ) + $tab_csv_format['itslearning'] + $tab_serveur_cas['itslearning_02'];
$tab_connexion_info['cas'][ '03|ent_auvergne']     = array( 'txt'=>'ENT Auvergne (académie de Clermond-Ferrand).'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_auvergne'];
$tab_connexion_info['cas'][ '04|ent_04']           = array( 'txt'=>'ENT département des Alpes de Haute-Provence sur Scolastance.' , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_04'];
$tab_connexion_info['cas'][ '04|ent_04_v2']        = array( 'txt'=>'ENT département des Alpes de Haute-Provence sur ItsLearning.' , 'etat'=>1 , 'societe'=>'ItsLearning'        ) + $tab_csv_format['itslearning'] + $tab_serveur_cas['itslearning_04'];
$tab_connexion_info['cas'][ '06|agora06']          = array( 'txt'=>'ENT Agora 06 (collèges des Alpes-Maritimes).'                 , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_agora06'];
$tab_connexion_info['cas'][ '06|ent_nice']         = array( 'txt'=>'ENT académie de Nice (hors Agora 06).'                        , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enteduc'];
$tab_connexion_info['cas'][ '09|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '11|ent_montpellier']  = array( 'txt'=>'ENT Languedoc-Roussillon (académie de Montpellier).'          , 'etat'=>1 , 'societe'=>'ESUP Portail'       ) + $tab_csv_format['esup']        + $tab_serveur_cas['esup_montpellier'];
$tab_connexion_info['cas'][ '12|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '15|ent_auvergne']     = array( 'txt'=>'ENT Auvergne (académie de Clermond-Ferrand).'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_auvergne'];
$tab_connexion_info['cas'][ '21|liberscol']        = array( 'txt'=>'ENT Liberscol (académie de Dijon).'                           , 'etat'=>1 , 'societe'=>'Tetra Informatique' ) + $tab_csv_format['liberscol']   + $tab_serveur_cas['liberscol'];
$tab_connexion_info['cas'][ '22|toutatice']        = array( 'txt'=>'ENT Toutatice (académie de Rennes).'                          , 'etat'=>1 , 'societe'=>'SERIA Rennes'       ) + $tab_csv_format['toutatice']   + $tab_serveur_cas['toutatice'];
$tab_connexion_info['cas'][ '23|elie']             = array( 'txt'=>'ENT Elie (lycées du Limousin et collèges de la Creuse).'      , 'etat'=>0 , 'societe'=>'Logica'             ) + $tab_csv_format['logica']      + $tab_serveur_cas['logica_elie'];
$tab_connexion_info['cas'][ '23|ent_auvergne']     = array( 'txt'=>'ENT Auvergne (académie de Clermond-Ferrand).'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_auvergne'];
$tab_connexion_info['cas'][ '24|argos']            = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'CATICE Bordeaux'    ) + $tab_csv_format['itop']        + $tab_serveur_cas['argos'];
$tab_connexion_info['cas'][ '27|ent_27']           = array( 'txt'=>'ENT département de l\'Eure.'                                  , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enteduc'];
$tab_connexion_info['cas'][ '29|toutatice']        = array( 'txt'=>'ENT Toutatice (académie de Rennes).'                          , 'etat'=>1 , 'societe'=>'SERIA Rennes'       ) + $tab_csv_format['toutatice']   + $tab_serveur_cas['toutatice'];
$tab_connexion_info['cas'][ '30|ent_montpellier']  = array( 'txt'=>'ENT Languedoc-Roussillon (académie de Montpellier).'          , 'etat'=>1 , 'societe'=>'ESUP Portail'       ) + $tab_csv_format['esup']        + $tab_serveur_cas['esup_montpellier'];
$tab_connexion_info['cas'][ '31|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '31|e-college31']      = array( 'txt'=>'ENT eCollège 31 (académie de Toulouse).'                      , 'etat'=>0 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_ecollege31'];
$tab_connexion_info['cas'][ '32|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '33|argos']            = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'CATICE Bordeaux'    ) + $tab_csv_format['itop']        + $tab_serveur_cas['argos'];
$tab_connexion_info['cas'][ '34|ent_montpellier']  = array( 'txt'=>'ENT Languedoc-Roussillon (académie de Montpellier).'          , 'etat'=>1 , 'societe'=>'ESUP Portail'       ) + $tab_csv_format['esup']        + $tab_serveur_cas['esup_montpellier'];
$tab_connexion_info['cas'][ '35|toutatice']        = array( 'txt'=>'ENT Toutatice (académie de Rennes).'                          , 'etat'=>1 , 'societe'=>'SERIA Rennes'       ) + $tab_csv_format['toutatice']   + $tab_serveur_cas['toutatice'];
$tab_connexion_info['cas'][ '38|ent_38']           = array( 'txt'=>'ENT département de l\'Isère.'                                 , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enteduc'];
$tab_connexion_info['cas'][ '40|argos']            = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'CATICE Bordeaux'    ) + $tab_csv_format['itop']        + $tab_serveur_cas['argos'];
$tab_connexion_info['cas'][ '42|cybercolleges42']  = array( 'txt'=>'ENT Cybercollèges 42 (département de la Loire).'              , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_cybercolleges42'];
$tab_connexion_info['cas'][ '43|ent_auvergne']     = array( 'txt'=>'ENT Auvergne (académie de Clermond-Ferrand).'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_auvergne'];
$tab_connexion_info['cas'][ '44|e-lyco']           = array( 'txt'=>'ENT e-lyco (académie de Nantes).'                             , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_elyco'];
$tab_connexion_info['cas'][ '46|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '47|argos']            = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'CATICE Bordeaux'    ) + $tab_csv_format['itop']        + $tab_serveur_cas['argos'];
$tab_connexion_info['cas'][ '48|ent_montpellier']  = array( 'txt'=>'ENT Languedoc-Roussillon (académie de Montpellier).'          , 'etat'=>1 , 'societe'=>'ESUP Portail'       ) + $tab_csv_format['esup']        + $tab_serveur_cas['esup_montpellier'];
$tab_connexion_info['cas'][ '49|e-lyco']           = array( 'txt'=>'ENT e-lyco (académie de Nantes).'                             , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_elyco'];
$tab_connexion_info['cas'][ '52|ent_52']           = array( 'txt'=>'ENT département de Haute-Marne sur Scolastance.'              , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_52'];
$tab_connexion_info['cas'][ '52|ent_52_v2']        = array( 'txt'=>'ENT département de Haute-Marne sur ItsLearning.'              , 'etat'=>1 , 'societe'=>'ItsLearning'        ) + $tab_csv_format['itslearning'] + $tab_serveur_cas['itslearning_52'];
$tab_connexion_info['cas'][ '53|e-lyco']           = array( 'txt'=>'ENT e-lyco (académie de Nantes).'                             , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_elyco'];
$tab_connexion_info['cas'][ '54|place']            = array( 'txt'=>'ENT Place (Lorraine).'                                        , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_place'];
$tab_connexion_info['cas'][ '55|place']            = array( 'txt'=>'ENT Place (Lorraine).'                                        , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_place'];
$tab_connexion_info['cas'][ '56|toutatice']        = array( 'txt'=>'ENT Toutatice (académie de Rennes).'                          , 'etat'=>1 , 'societe'=>'SERIA Rennes'       ) + $tab_csv_format['toutatice']   + $tab_serveur_cas['toutatice'];
$tab_connexion_info['cas'][ '57|mirabelle']        = array( 'txt'=>'ENT Mirabelle (département de la Moselle).'                   , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_place'];
$tab_connexion_info['cas'][ '58|liberscol']        = array( 'txt'=>'ENT Liberscol (académie de Dijon).'                           , 'etat'=>1 , 'societe'=>'Tetra Informatique' ) + $tab_csv_format['liberscol']   + $tab_serveur_cas['liberscol'];
$tab_connexion_info['cas'][ '59|ent_lille']        = array( 'txt'=>'ENT Savoirs numériques 5962 (académie de Lille).'             , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_lille'];
$tab_connexion_info['cas'][ '60|ent_60']           = array( 'txt'=>'ENT département de l\'Oise.'                                  , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_oise'];
$tab_connexion_info['cas'][ '62|ent_lille']        = array( 'txt'=>'ENT Savoirs numériques 5962 (académie de Lille).'             , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_lille'];
$tab_connexion_info['cas'][ '63|ent_auvergne']     = array( 'txt'=>'ENT Auvergne (académie de Clermond-Ferrand).'                 , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_auvergne'];
$tab_connexion_info['cas'][ '64|argos64']          = array( 'txt'=>'ENT Argos64 (département des Pyrénées-Atlantiques).'          , 'etat'=>1 , 'societe'=>'CATICE Bordeaux'    ) + $tab_csv_format['itop']        + $tab_serveur_cas['argos'];
$tab_connexion_info['cas'][ '65|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '66|ent_montpellier']  = array( 'txt'=>'ENT Languedoc-Roussillon (académie de Montpellier).'          , 'etat'=>1 , 'societe'=>'ESUP Portail'       ) + $tab_csv_format['esup']        + $tab_serveur_cas['esup_montpellier'];
$tab_connexion_info['cas'][ '67|ent_alsace']       = array( 'txt'=>'ENT Alsace (académie de Strasbourg).'                         , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['itop_alsace'];
$tab_connexion_info['cas'][ '68|ent_alsace']       = array( 'txt'=>'ENT Alsace (académie de Strasbourg).'                         , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['itop_alsace'];
$tab_connexion_info['cas'][ '69|laclasse']         = array( 'txt'=>'ENT laclasse.com (département du Rhône).'                     , 'etat'=>0 , 'societe'=>'Erasme'             ) + $tab_csv_format['']            + $tab_serveur_cas['laclasse'];
$tab_connexion_info['cas'][ '71|liberscol']        = array( 'txt'=>'ENT Liberscol (académie de Dijon).'                           , 'etat'=>1 , 'societe'=>'Tetra Informatique' ) + $tab_csv_format['liberscol']   + $tab_serveur_cas['liberscol'];
$tab_connexion_info['cas'][ '72|e-lyco']           = array( 'txt'=>'ENT e-lyco (académie de Nantes).'                             , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_elyco'];
$tab_connexion_info['cas'][ '73|cartabledesavoie'] = array( 'txt'=>'ENT Cartable de Savoie.'                                      , 'etat'=>1 , 'societe'=>'Pentila'            ) + $tab_csv_format['pentila']     + $tab_serveur_cas['pentila'];
$tab_connexion_info['cas'][ '77|ent_77']           = array( 'txt'=>'ENT département de Seine et Marne.'                           , 'etat'=>1 , 'societe'=>'Logica'             ) + $tab_csv_format['logica']      + $tab_serveur_cas['logica_ent77'];
$tab_connexion_info['cas'][ '80|ent_80']           = array( 'txt'=>'ENT département de la Somme.'                                 , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enteduc'];
$tab_connexion_info['cas'][ '81|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '82|entmip']           = array( 'txt'=>'ENT Midi-Pyrénées (académie de Toulouse).'                    , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['kosmos']      + $tab_serveur_cas['kosmos_entmip'];
$tab_connexion_info['cas'][ '83|ent_nice']         = array( 'txt'=>'ENT académie de Nice (hors Agora 06).'                        , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enteduc'];
$tab_connexion_info['cas'][ '85|e-lyco']           = array( 'txt'=>'ENT e-lyco (académie de Nantes).'                             , 'etat'=>1 , 'societe'=>'Kosmos'             ) + $tab_csv_format['elyco']       + $tab_serveur_cas['kosmos_elyco'];
$tab_connexion_info['cas'][ '89|liberscol']        = array( 'txt'=>'ENT Liberscol (académie de Dijon).'                           , 'etat'=>1 , 'societe'=>'Tetra Informatique' ) + $tab_csv_format['liberscol']   + $tab_serveur_cas['liberscol'];
$tab_connexion_info['cas'][ '90|ent_90']           = array( 'txt'=>'ENT Territoire de Belfort.'                                   , 'etat'=>1 , 'societe'=>'Infostance'         ) + $tab_csv_format['scolastance'] + $tab_serveur_cas['scolastance_90'];
$tab_connexion_info['cas'][ '92|ent_92']           = array( 'txt'=>'ENT collèges des Hauts-de-Seine.'                             , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_enc92'];
$tab_connexion_info['cas'][ '93|celia']            = array( 'txt'=>'ENT Celi@ (collèges de Seine-Saint-Denis).'                   , 'etat'=>1 , 'societe'=>'Logica'             ) + $tab_csv_format['logica']      + $tab_serveur_cas['logica_celia'];
$tab_connexion_info['cas'][ '93|lilie']            = array( 'txt'=>'ENT Lilie (lycées d\'Ile de France).'                         , 'etat'=>1 , 'societe'=>'Logica'             ) + $tab_csv_format['logica']      + $tab_serveur_cas['logica_lilie'];
$tab_connexion_info['cas'][ '95|ent_95']           = array( 'txt'=>'ENT Anper95 (département du Val d\'Oise).'                    , 'etat'=>1 , 'societe'=>'iTOP'               ) + $tab_csv_format['itop']        + $tab_serveur_cas['itop_valdoise'];
$tab_connexion_info['cas']['974|ent_reunion']      = array( 'txt'=>'ENT département de La Réunion.'                               , 'etat'=>1 , 'societe'=>'DSI La Réunion'     ) + $tab_csv_format['itslearning'] + $tab_serveur_cas['lareunion'];
$tab_connexion_info['shibboleth'][ '24|argos']     = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'DSI Bordeaux'       ) + $tab_csv_format['itop'];
$tab_connexion_info['shibboleth'][ '33|argos']     = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'DSI Bordeaux'       ) + $tab_csv_format['itop'];
$tab_connexion_info['shibboleth'][ '40|argos']     = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'DSI Bordeaux'       ) + $tab_csv_format['itop'];
$tab_connexion_info['shibboleth'][ '47|argos']     = array( 'txt'=>'ENT Argos (académie de Bordeaux).'                            , 'etat'=>1 , 'societe'=>'DSI Bordeaux'       ) + $tab_csv_format['itop'];
$tab_connexion_info['shibboleth'][ '64|argos64']   = array( 'txt'=>'ENT Argos64 (département des Pyrénées-Atlantiques).'          , 'etat'=>1 , 'societe'=>'DSI Bordeaux'       ) + $tab_csv_format['itop'];
$tab_connexion_info['gepi']['|saml']               = array( 'txt'=>'S\'authentifier depuis GEPI (protocole SAML).' ) + $tab_saml_param['gepi'];

/*
 * Les sous-tableaux ne sont plus utiles.
 */
 unset( $tab_csv_format , $tab_serveur_cas , $tab_saml_param );

/*
Orléans-Tours   http://www.ac-orleans-tours.fr/vie_numerique/ent/   https://envole-loiret.ac-orleans-tours.fr/   https://envole-indre.ac-orleans-tours.fr/
$tab_connexion_info['cas'][ '|ent_75']           = array('txt'=>'' , 'etat'=>1 , 'societe'=>'ItsLearning'         , 'serveur_host'=>'cas.paris.fr'                               , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent75'        , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Paris (département 75)." );
$tab_connexion_info['cas'][ '|cartableenligne']  = array( 'txt'=>'' , 'etat'=>1 , 'societe'=>NULL                 , 'serveur_host'=>'A-CHANGER.ac-creteil.fr'                    , 'serveur_port'=>8443 , 'serveur_root'=>''                 , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Cartable en ligne de Créteil (EnvOLE Scribe)." );
$tab_connexion_info['cas'][ '|place-test']       = array( 'txt'=>'' , 'etat'=>1 , 'societe'=>'iTOP'               , 'serveur_host'=>'www.preprod.place.e-lorraine.net'           , 'serveur_port'=>443  , 'serveur_root'=>'cas'              , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Test Place (iTOP)." );
$tab_connexion_info['cas'][ '|scolastance-test'] = array( 'txt'=>'' , 'etat'=>1 , 'societe'=>'ItsLearning'        , 'serveur_host'=>'preprod-cas.scolastance.com'                , 'serveur_port'=>443  , 'serveur_root'=>'cas-recette1_616' , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Test Scolastance." );
$tab_connexion_info['cas'][ '|logica-test']      = array( 'txt'=>'' , 'etat'=>1 , 'societe'=>'Logica'             , 'serveur_host'=>'projets2-eta.fr.logica.com'                 , 'serveur_port'=>443  , 'serveur_root'=>'connexion'        , 'csv_entete'=>1 , 'csv_nom'=>3 , 'csv_prenom'=>4 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>2    , 'txt'=>"ENT Test Logica (Celia, Lilie)." );
https://cas.scolastance.com/cas-asp
https://cas.scolastance.com/cas-ent74      http://ent74.scolastance.com/etablissements.aspx
https://cas.scolastance.com/cas-entrouen  http://entrouen.scolastance.com/etablissements.aspx
https://cas.scolastance.com/cas-ifsp      http://ifsp.scolastance.com/etablissements.aspx
https://cas.scolastance.com/cas-client    http://client.scolastance.com/etablissements.aspx
https://cas.scolastance.com/cas-sierra    http://sierra.scolastance.com/etablissements.aspx
https://cas.scolastance.com/cas-demo      http://demo.scolastance.com/etablissements.aspx
*/

?>