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
$tab_connexion_mode['itop']   = 'Machine virtuelle itop';
*/

$tab_connexion_info = array();

$tab_connexion_info['normal']['sacoche']       = array( 'txt'=>'Connexion avec les identifiants enregistrés dans SACoche.' );

$tab_connexion_info['cas']['argos']            = array( 'serveur_host'=>'ent-cas.ac-bordeaux.fr'  , 'serveur_port'=>443  , 'serveur_root'=>'cas'       , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>'ENT Argos (académie de Bordeaux, départements 24 33 40 47).' ); // ex- ent-auth.ac-bordeaux.fr sans root
$tab_connexion_info['cas']['argos64']          = array( 'serveur_host'=>'ent-cas.ac-bordeaux.fr'  , 'serveur_port'=>443  , 'serveur_root'=>'cas'       , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'csv_id_sconet'=>NULL , 'txt'=>'ENT Argos64 (département des Pyrénées-Atlantiques).' ); // ex- cas.argos64.fr sans root
$tab_connexion_info['cas']['celia']            = array( 'serveur_host'=>'www.ent-celia.fr'        , 'serveur_port'=>443  , 'serveur_root'=>'connexion' , 'csv_nom'=>5 , 'csv_prenom'=>6 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>4    , 'txt'=>'ENT Celi@ (collèges de Seine-Saint-Denis).' );
$tab_connexion_info['cas']['cybercolleges42']  = array( 'serveur_host'=>'cas.cybercolleges42.fr'  , 'serveur_port'=>443  , 'serveur_root'=>''          , 'csv_nom'=>4 , 'csv_prenom'=>3 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>'ENT Cybercollège 42 (département de la Loire).' );
$tab_connexion_info['cas']['e-lyco']           = array( 'serveur_host'=>'cas.e-lyco.fr'           , 'serveur_port'=>443  , 'serveur_root'=>''          , 'csv_nom'=>4 , 'csv_prenom'=>3 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>'ENT e-lyco (académie de Nantes).' );
$tab_connexion_info['cas']['entmip']           = array( 'serveur_host'=>'cas.entmip.fr'           , 'serveur_port'=>443  , 'serveur_root'=>''          , 'csv_nom'=>4 , 'csv_prenom'=>3 , 'csv_id_ent'=>1 , 'csv_id_sconet'=>NULL , 'txt'=>'ENT Midi-Pyrénées K-d\'Ecole (académie de Toulouse).' );
$tab_connexion_info['cas']['lilie']            = array( 'serveur_host'=>'ent.iledefrance.fr'      , 'serveur_port'=>443  , 'serveur_root'=>'connexion' , 'csv_nom'=>5 , 'csv_prenom'=>6 , 'csv_id_ent'=>3 , 'csv_id_sconet'=>4    , 'txt'=>'ENT Lilie (lycées d\'Ile de France).' );
$tab_connexion_info['cas']['perso']            = array( 'serveur_host'=>''                        , 'serveur_port'=>443  , 'serveur_root'=>''          , 'csv_nom'=>1 , 'csv_prenom'=>2 , 'csv_id_ent'=>0 , 'txt'=>'Configuration CAS manuelle.' );

/*
$tab_connexion_info['cas']['laclasse']         = array( 'serveur_host'=>'sso.laclasse.com'        , 'serveur_port'=>443  , 'serveur_root'=>'cas' , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'txt'=>'ENT Laclasse.com du Rhône.' );
$tab_connexion_info['cas']['cartabledesavoie'] = array( 'serveur_host'=>'cartabledesavoie.com'    , 'serveur_port'=>443  , 'serveur_root'=>'cas' , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'txt'=>'ENT Cartable de Savoie.' );
$tab_connexion_info['cas']['cartableenligne']  = array( 'serveur_host'=>'A-CHANGER.ac-creteil.fr' , 'serveur_port'=>8443 , 'serveur_root'=>''    , 'csv_nom'=>0 , 'csv_prenom'=>0 , 'csv_id_ent'=>0 , 'txt'=>'ENT Cartable en ligne de Créteil (EnvOLE Scribe).' );
*/

$tab_connexion_info['gepi']['saml']            = array( 'saml_url'=>'http://' , 'saml_rne'=>$_SESSION['UAI'] , 'saml_certif'=>'AA:FD:FF:98:48:18:A8:56:73:32:73:8F:33:53:04:8C:36:9B:E6:B2' , 'txt'=>'S\'authentifier depuis GEPI (protocole SAML).' );

?>