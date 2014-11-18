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

$tab_base_id = (isset($_POST['f_listing_id'])) ? array_filter( Clean::map_entier( explode(',',$_POST['f_listing_id']) ) , 'positif' ) : array() ;
$nb_bases    = count($tab_base_id);

$num    = (isset($_POST['num']))      ? Clean::entier($_POST['num'])     : 0 ;  // Numéro de l'étape en cours
$max    = (isset($_POST['max']))      ? Clean::entier($_POST['max'])     : 0 ;  // Nombre d'étapes à effectuer

require(CHEMIN_DOSSIER_INCLUDE.'fonction_dump.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des structures avant recherche des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $nb_bases )
{
  // Mémoriser les données des structures
  $_SESSION['tmp'] = array();
  $_SESSION['tmp']['base_id']                = array();
  $_SESSION['tmp']['structure_denomination'] = array();
  $_SESSION['tmp']['niveau_alerte']          = array();
  $_SESSION['tmp']['messages']               = array();
  $DB_TAB = DB_WEBMESTRE_WEBMESTRE::DB_lister_structures( implode(',',$tab_base_id) );
  foreach($DB_TAB as $DB_ROW)
  {
    $_SESSION['tmp']['infos']['base_id'][]                = $DB_ROW['sacoche_base'];
    $_SESSION['tmp']['infos']['structure_denomination'][] = $DB_ROW['structure_denomination'];
  }
  // Retour
  $max = $nb_bases + 1 ; // La dernière étape consistera à vider la session temporaire et à renvoyer les totaux
  exit('ok-'.$max);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etape de récupération des recherches des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $num && $max && ($num<$max) )
{
  // Récupérer les infos
  $base_id                = $_SESSION['tmp']['infos']['base_id'               ][$num-1];
  $structure_denomination = $_SESSION['tmp']['infos']['structure_denomination'][$num-1];
  // Charger les paramètres de connexion à cette base afin de pouvoir y effectuer des requêtes
  charger_parametres_mysql_supplementaires($base_id);
  // Lancer analyse et réparation si besoin
  list( $niveau_alerte , $messages ) = analyser_et_reparer_tables_base_etablissement();
  // Retenir le résultat
  $tab_couleurs = array( 0=>'v' , 1=>'b' , 2=>'r' );
  $_SESSION['tmp']['infos']['niveau_alerte'][$num-1] = $niveau_alerte;
  $_SESSION['tmp']['infos']['messages'     ][$num-1] = '<tr class="'.$tab_couleurs[$niveau_alerte].'"><td>n°'.$base_id.' - '.html($structure_denomination).'</td><td>'.$messages.'</td></tr>';
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement du rapport de récupération des recherches des anomalies éventuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $num && $max && ($num==$max) )
{
  // Trier les lignes
  array_multisort(
    $_SESSION['tmp']['infos']['niveau_alerte'], SORT_DESC,SORT_NUMERIC,
    $_SESSION['tmp']['infos']['base_id']      , SORT_ASC,SORT_NUMERIC,
    $_SESSION['tmp']['infos']['messages']
  );
  // Enregistrement du rapport
  $fichier_nom = 'rapport_analyser_et_reparer_tables_'.fabriquer_fin_nom_fichier__date_et_alea().'.html';
  $thead = '<tr><td colspan="2">Analyse et réparation éventuelle des tables de bases de données par établissement - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = implode('',$_SESSION['tmp']['infos']['messages']);
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody );
  unset($_SESSION['tmp']);
  exit('ok-'.URL_DIR_EXPORT.$fichier_nom);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
