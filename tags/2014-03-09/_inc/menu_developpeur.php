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
    "Accueil"                     => array( 'class' => 'compte_accueil' , 'href' => 'page=compte_accueil'         ),
    "Caractéristiques du serveur" => array( 'class' => 'serveur_info'   , 'href' => 'page=webmestre_info_serveur' ),
  ),
  "Administration du site" => array
  (
    "Configuration d'un proxy"          => array( 'class' => 'serveur_proxy'        , 'href' => 'page=webmestre_configuration_proxy'      ),
    "Test des droits MySQL"             => array( 'class' => 'serveur_database'     , 'href' => 'page=webmestre_database_test'            ), // multi-structures uniquement
    "Droits du système de fichiers"     => array( 'class' => 'serveur_erreur'       , 'href' => 'page=webmestre_configuration_filesystem' ),
    "Maintenance &amp; mise à jour"     => array( 'class' => 'serveur_maintenance'  , 'href' => 'page=webmestre_maintenance'              ),
    "Analyser / Réparer les bases"      => array( 'class' => 'structure_bdd_repair' , 'href' => 'page=webmestre_structure_bdd_repair'     ),
    "Vérification des certificats SSL"  => array( 'class' => 'serveur_security'     , 'href' => 'page=webmestre_certificats_ssl'          ),
    "Débogueur"                         => array( 'class' => 'serveur_debug'        , 'href' => 'page=webmestre_debug'                    ),
  ),
  "Gestion des inscriptions" => array
  ( // multi-structures uniquement
    "Statistiques d'utilisation"   => array( 'class' => 'statistiques'         , 'href' => 'page=webmestre_statistiques'         ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Différences de menu [mono-structure] / [multi-structures]
if(HEBERGEUR_INSTALLATION=='mono-structure')
{
  unset(
    $tab_menu["Administration du site"]["Test des droits MySQL"] ,
    $tab_menu["Gestion des inscriptions"]
  );
}

?>