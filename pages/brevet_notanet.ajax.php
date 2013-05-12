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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$tab_eleve = (isset($_POST['f_eleve'])) ? explode(',',$_POST['f_eleve']) : array() ;
$tab_eleve = array_filter( Clean::map_entier($tab_eleve) , 'positif' );

if( !count($tab_eleve) )
{
  exit('Erreur avec les données transmises !');
}

$listing_eleve_id   = implode(',',$tab_eleve);
$epreuve_code_total = '255';

// Récupérer les données élèves

$tab_eleves = array(); // [user_id] => array(nom,prenom,sconet_id) Ordonné par INE.
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_eleves_cibles_actuels_avec_INE($listing_eleve_id);
if(empty($DB_TAB))
{
  exit('Erreur : les élèves trouvés n\'ont pas d\'Identifiant National Élève (INE) ou sont anciens !');
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_eleves[$DB_ROW['user_id']] = $DB_ROW['user_reference'];
}

// Récupérer les notes enregistrées

$tab_notes_enregistrees = array();
$DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_notes_eleves($listing_eleve_id);
if(count($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_notes_enregistrees[$DB_ROW['eleve_id']][$DB_ROW['brevet_epreuve_code']] = $DB_ROW['saisie_note'];
  }
}

// Fabriquer le fichier csv

$csv_contenu    = '';
$csv_separateur = '|';
$csv_eof        = "\r\n";

foreach($tab_eleves as $eleve_id => $user_reference)
{
  foreach($tab_notes_enregistrees[$eleve_id] as $epreuve_code => $saisie_note)
  {
    $csv_code = ($epreuve_code!=$epreuve_code_total) ? (string)$epreuve_code : 'TOT' ;
    $format   = ($epreuve_code!=$epreuve_code_total) ? "%05.2f" : "%06.2f" ;
    $csv_note = is_numeric($saisie_note) ? sprintf($format,$saisie_note) : (string)$saisie_note ;
    $csv_contenu .= $user_reference.$csv_separateur.$csv_code.$csv_separateur.$csv_note.$csv_separateur.$csv_eof;
  }
}

// Enregistrer le fichier csv / Retour

$fichier_nom = 'export_notanet'.'_'.Clean::fichier($_SESSION['WEBMESTRE_UAI']).'_'.fabriquer_fin_nom_fichier__date_et_alea().'.txt';
FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_nom , To::csv($csv_contenu) );

exit($fichier_nom);

?>
