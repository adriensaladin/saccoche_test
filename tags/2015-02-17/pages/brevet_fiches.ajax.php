<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir    : affichage des données d'un élève | enregistrement/suppression d'une appréciation
// Examiner  : recherche des saisies manquantes (appréciations)
// Consulter : affichage des données d'un élève (HTML)
// Imprimer  : affichage de la liste des élèves | étape d'impression PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array( $section , array('brevet_fiches_saisir','brevet_fiches_examiner','brevet_fiches_consulter','brevet_fiches_imprimer') ) )
{
  if( ($section=='brevet_fiches_consulter') && ($action=='imprimer') )
  {
    // Il s'agit d'un test d'impression d'un bilan non encore clos (on vérifiera quand même par la suite que les conditions sont respectées (état du bilan, droit de l'utilisateur)
    $section = 'brevet_fiches_imprimer';
    $_POST['f_objet'] = 'imprimer';
    $is_test_impression = TRUE;
  }
  require(CHEMIN_DOSSIER_INCLUDE.'code_'.$section.'.php');
  exit(); // Normalement, on n'arrive pas jusque là.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Signaler une faute
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='signaler_faute') || ($action=='corriger_faute') )
{
  $_POST['f_action']='ajouter';
  require(CHEMIN_DOSSIER_PAGES.'compte_message.ajax.php');
  exit(); // Normalement, on n'arrive pas jusque là.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un archivage des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_actions = array
(
  'imprimer_donnees_eleves_epreuves'  => 'Appréciations par épreuve pour chaque élève',
  'imprimer_donnees_eleves_syntheses' => 'Avis de synthèse pour chaque élève',
  'imprimer_donnees_eleves_moyennes'  => 'Tableau des notes pour chaque élève',
);

if( isset($tab_actions[$action]) )
{
  require(CHEMIN_DOSSIER_INCLUDE.'code_brevet_fiches_archiver.php');
  exit(); // Normalement, on n'arrive pas jusque là.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
