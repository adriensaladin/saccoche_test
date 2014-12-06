<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Menu [partenaire] à mettre en session
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Le menu complet ; attention : en cas de changement du nom d'un menu, répercuter la modif dans tout le fichier (§ Adaptations).

$tab_menu = array
(
  "Informations" => array
  (
    "Accueil"                     => array( 'class' => 'compte_accueil' , 'href' => 'page=compte_accueil'          ),
    "Statistiques d'utilisation"  => array( 'class' => 'statistiques'   , 'href' => 'page=partenaire_statistiques' ),
  ),
  "Paramétrages" => array
  (
    "Mot de passe"          => array( 'class' => 'compte_password'  , 'href' => 'page=compte_password'         ),
    "Logo / Lien / Message" => array( 'class' => 'serveur_identite' , 'href' => 'page=partenaire_parametrages' ),
  ),
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Adaptations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// RAS !

?>