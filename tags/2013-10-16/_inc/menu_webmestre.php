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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Menu [webmestre] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Le menu complet ; attention : en cas de changement du nom d'un menu, répercuter la modif dans tout le fichier (§ Adaptations).

$tab_menu = array
(
  "Informations" => array
  (
    "Accueil"                     => array( 'class' => 'compte_accueil' , 'href' => 'page=compte_accueil'      ),
    "Caractéristiques du serveur" => array( 'class' => 'serveur_info'   , 'href' => 'page=compte_info_serveur' ),
  ),
  "Administration du site" => array
  (
    "Mot de passe du webmestre"      => array( 'class' => 'compte_password'     , 'href' => 'page=compte_password'                    ),
    "Identité de l'installation"     => array( 'class' => 'serveur_identite'    , 'href' => 'page=webmestre_identite_installation'    ),
    "Fichiers déposés"               => array( 'class' => 'serveur_fichiers'    , 'href' => 'page=webmestre_fichiers_deposes'         ),
    "Zones géographiques"            => array( 'class' => 'serveur_geographie'  , 'href' => 'page=webmestre_geographie'               ), // multi-structures uniquement
    "Partenaires ENT conventionnés"  => array( 'class' => 'administrateur'      , 'href' => 'page=webmestre_partenariats'             ), // multi-structures et serveur Sésamath uniquement
    "Configuration d'un proxy"       => array( 'class' => 'serveur_proxy'       , 'href' => 'page=webmestre_configuration_proxy'      ),
    "Test des droits MySQL"          => array( 'class' => 'serveur_database'    , 'href' => 'page=webmestre_database_test'            ), // multi-structures uniquement
    "Droits du système de fichiers"  => array( 'class' => 'serveur_erreur'      , 'href' => 'page=webmestre_configuration_filesystem' ),
    "Maintenance &amp; mise à jour"  => array( 'class' => 'serveur_maintenance' , 'href' => 'page=webmestre_maintenance'              ),
    "Débogueur"                      => array( 'class' => 'serveur_debug'       , 'href' => 'page=webmestre_debug'                    ),
  ),
  "Gestion des inscriptions" => array
  ( // multi-structures uniquement
    "Gestion des établissements" => array( 'class' => 'structure_gestion'   , 'href' => 'page=webmestre_structure_gestion'   ),
    "Ajout CSV d'établissements" => array( 'class' => 'structure_ajout_csv' , 'href' => 'page=webmestre_structure_ajout_csv' ),
    "Transfert d'établissements" => array( 'class' => 'structure_transfert' , 'href' => 'page=webmestre_structure_transfert' ),
    "Statistiques d'utilisation" => array( 'class' => 'statistiques'        , 'href' => 'page=webmestre_statistiques'        ),
    "Lettre d'information"       => array( 'class' => 'newsletter'          , 'href' => 'page=webmestre_newsletter'          ),
  ),
  "Établissement" => array
  ( // mono-structure uniquement
    "Mot de passe administrateur" => array( 'class' => 'mdp_admin'    , 'href' => 'page=webmestre_mdp_admin'    ),
    "Statistiques d'utilisation"  => array( 'class' => 'statistiques' , 'href' => 'page=webmestre_statistiques' ),
    "Résilier l'inscription"      => array( 'class' => 'resilier'     , 'href' => 'page=webmestre_resilier'     ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Différences de menu [mono-structure] / [multi-structures]
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  unset(
    $tab_menu["Administration du site"]["Zones géographiques"] ,
    $tab_menu["Administration du site"]["Partenaires ENT conventionnés"] ,
    $tab_menu["Administration du site"]["Test des droits MySQL"] ,
    $tab_menu["Gestion des inscriptions"]
  );
}
else
{
  unset( $tab_menu["Établissement"] );
  if(!IS_HEBERGEMENT_SESAMATH)
  {
    unset( $tab_menu["Administration du site"]["Partenaires ENT conventionnés"] );
  }
}

?>