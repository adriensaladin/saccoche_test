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

// Tableau avec les différents modes d'identification possibles

$tab_connexion_mode = array();
$tab_connexion_mode['normal'] = 'Normal';
$tab_connexion_mode['cas']    = 'Serveur CAS';
$tab_connexion_mode['gepi']   = 'GEPI';
/*
$tab_connexion_mode['ldap']   = '???';
*/

$tab_connexion_info = array();

$tab_connexion_info['normal']['sacoche']       = array( 'txt'=>"Connexion avec les identifiants enregistrés dans SACoche." );

$tab_connexion_info['cas']['ent_02']           = array( 'etat'=>1 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.scolastance.com'     , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent02'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de l'Aisne (02)." );
$tab_connexion_info['cas']['ent_04']           = array( 'etat'=>0 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.cg04.fr'             , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent04'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Alpes de Haute-Provence (04)." );
$tab_connexion_info['cas']['ent_alsace']       = array( 'etat'=>1 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.scolastance.com'     , 'serveur_port'=>443  , 'serveur_root'=>'cas-alsace'     , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Alsace (académie de Strasbourg)." );
$tab_connexion_info['cas']['argos']            = array( 'etat'=>1 , 'societe'=>'CATICE Bordeaux' , 'serveur_host'=>'ent-cas.ac-bordeaux.fr'  , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Argos (académie de Bordeaux, départements 24 33 40 47)." );
$tab_connexion_info['cas']['argos64']          = array( 'etat'=>1 , 'societe'=>'CATICE Bordeaux' , 'serveur_host'=>'ent-cas.ac-bordeaux.fr'  , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Argos64 (département des Pyrénées-Atlantiques)." );
$tab_connexion_info['cas']['ent_auvergne']     = array( 'etat'=>0 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.scolastance.com'     , 'serveur_port'=>443  , 'serveur_root'=>'cas-auvergne'   , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Auvergne (académie de Clermond-Ferrand)." );
$tab_connexion_info['cas']['cartabledesavoie'] = array( 'etat'=>0 , 'societe'=>'Pentila'         , 'serveur_host'=>'cartabledesavoie.com'    , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Cartable de Savoie (73)." );
$tab_connexion_info['cas']['celia']            = array( 'etat'=>1 , 'societe'=>'Logica'          , 'serveur_host'=>'www.ent-celia.fr'        , 'serveur_port'=>443  , 'serveur_root'=>'connexion'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>6 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>4    , 'txt'=>"ENT Celi@ (collèges de Seine-Saint-Denis)." );
$tab_connexion_info['cas']['cybercolleges42']  = array( 'etat'=>1 , 'societe'=>'Kosmos'          , 'serveur_host'=>'cas.cybercolleges42.fr'  , 'serveur_port'=>443  , 'serveur_root'=>''               , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>4 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Cybercollège 42 (département de la Loire)." );
$tab_connexion_info['cas']['elie']             = array( 'etat'=>1 , 'societe'=>'Logica'          , 'serveur_host'=>'ent.limousin.fr'         , 'serveur_port'=>443  , 'serveur_root'=>'connexion'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>6 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>4    , 'txt'=>"ENT Elie (lycées du Limousin et collèges de la Creuse)." );
$tab_connexion_info['cas']['e-lyco']           = array( 'etat'=>1 , 'societe'=>'Kosmos'          , 'serveur_host'=>'cas.e-lyco.fr'           , 'serveur_port'=>443  , 'serveur_root'=>''               , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>4 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT e-lyco (académie de Nantes)." );
$tab_connexion_info['cas']['ent_52']           = array( 'etat'=>0 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.scolastance.com'     , 'serveur_port'=>443  , 'serveur_root'=>'cas-hautemarne' , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de Haute-Marne (52)." );
$tab_connexion_info['cas']['ent_38']           = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'cas.enteduc.fr'          , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de l'Isère (38)." );
$tab_connexion_info['cas']['laclasse']         = array( 'etat'=>0 , 'societe'=>'Erasme'          , 'serveur_host'=>'www.laclasse.com'        , 'serveur_port'=>443  , 'serveur_root'=>'sso'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT laclasse.com (département du Rhône)." );
$tab_connexion_info['cas']['lilie']            = array( 'etat'=>1 , 'societe'=>'Logica'          , 'serveur_host'=>'ent.iledefrance.fr'      , 'serveur_port'=>443  , 'serveur_root'=>'connexion'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>5 , 'csv_prenom'=>6 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>4    , 'txt'=>"ENT Lilie (lycées d'Ile de France)." );
$tab_connexion_info['cas']['entmip']           = array( 'etat'=>1 , 'societe'=>'Kosmos'          , 'serveur_host'=>'cas.entmip.fr'           , 'serveur_port'=>443  , 'serveur_root'=>''               , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>4 , 'csv_prenom'=>3 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Midi-Pyrénées (académie de Toulouse)." );
$tab_connexion_info['cas']['mirabelle']        = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'cas.enteduc.fr'          , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Mirabelle (académie de Nancy-Metz)." );
$tab_connexion_info['cas']['ent_06']           = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'cas.enteduc.fr'          , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de Nice (06)." );
$tab_connexion_info['cas']['ent_60']           = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'ent.oise.fr'             , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de l'Oise (60)." );
$tab_connexion_info['cas']['place']            = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'www.placedulycee.fr'     , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Place (Lorraine - 54 55)." );
$tab_connexion_info['cas']['ent_reunion']      = array( 'etat'=>0 , 'societe'=>'DSI La Réunion'  , 'serveur_host'=>'seshat.ac-reunion.fr'    , 'serveur_port'=>8443 , 'serveur_root'=>'login'          , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de La Réunion." );
$tab_connexion_info['cas']['ent_77']           = array( 'etat'=>0 , 'societe'=>NULL              , 'serveur_host'=>'ent77.seine-et-marne.fr' , 'serveur_port'=>443  , 'serveur_root'=>'connexion'      , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de Seine et Marne (77)." );
$tab_connexion_info['cas']['ent_80']           = array( 'etat'=>0 , 'societe'=>'iTop'            , 'serveur_host'=>'cas.enteduc.fr'          , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>FALSE , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT de la Somme (80)." );
$tab_connexion_info['cas']['ent_90']           = array( 'etat'=>0 , 'societe'=>'Itslearning'     , 'serveur_host'=>'cas.scolastance.com'     , 'serveur_port'=>443  , 'serveur_root'=>'cas-ent90'      , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>2 , 'csv_prenom'=>3 , 'csv_id_ent'=>4 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Territoire de Belfort (90)." );
$tab_connexion_info['cas']['toutatice']        = array( 'etat'=>1 , 'societe'=>'SERIA Rennes'    , 'serveur_host'=>'www.toutatice.fr'        , 'serveur_port'=>443  , 'serveur_root'=>'cas'            , 'csv_infos'=>TRUE  , 'csv_entete'=>0 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Toutatice (académie de Rennes)." );
$tab_connexion_info['cas']['perso']            = array( 'etat'=>1 , 'societe'=>NULL              , 'serveur_host'=>''                        , 'serveur_port'=>443  , 'serveur_root'=>''               , 'csv_infos'=>TRUE  , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"Configuration CAS manuelle." );
/*
$tab_connexion_info['cas']['cartableenligne']  = array( 'serveur_host'=>'A-CHANGER.ac-creteil.fr'          , 'serveur_port'=>8443 , 'serveur_root'=>''                 , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Cartable en ligne de Créteil (EnvOLE Scribe)." );
$tab_connexion_info['cas']['place-test']       = array( 'serveur_host'=>'www.preprod.place.e-lorraine.net' , 'serveur_port'=>443  , 'serveur_root'=>'cas'              , 'csv_entete'=>0 , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Test Place (iTOP)." );
$tab_connexion_info['cas']['scolastance-test'] = array( 'serveur_host'=>'preprod-cas.scolastance.com'      , 'serveur_port'=>443  , 'serveur_root'=>'cas-recette1_616' , 'csv_entete'=>1 , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>NULL , 'txt'=>"ENT Test Scolastance." );
*/

$saml_rne = isset($_SESSION['WEBMESTRE_UAI']) ? $_SESSION['WEBMESTRE_UAI'] : '' ; // au moins à cause d'un appel de ce fichier depuis la doc
$tab_connexion_info['gepi']['saml']            = array( 'saml_url'=>'http://' , 'saml_rne'=>$saml_rne , 'saml_certif'=>'AA:FD:FF:98:48:18:A8:56:73:32:73:8F:33:53:04:8C:36:9B:E6:B2' , 'txt'=>"S'authentifier depuis GEPI (protocole SAML)." );

?>