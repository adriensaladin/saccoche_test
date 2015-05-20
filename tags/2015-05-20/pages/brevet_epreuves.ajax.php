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

// Récupération et test de la série du DNB

$serie = (isset($_POST['f_serie'])) ? Clean::texte($_POST['f_serie']) : '' ;

if(!$serie)
{
  exit('Erreur : série non transmise !');
}

$DB_TAB_epreuves = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves($serie);

if(empty($DB_TAB_epreuves))
{
  exit('Erreur : série inconnue !');
}

// Test des paramètres des épreuves

$tab_choix_epreuve = array();
foreach($DB_TAB_epreuves as $DB_ROW)
{
  $epreuve = $DB_ROW['brevet_epreuve_code'];
  $recherche = (isset($_POST['f_'.$serie.'_'.$epreuve.'_recherche'])) ? Clean::entier($_POST['f_'.$serie.'_'.$epreuve.'_recherche']) : NULL ;
  $moyenne   = (isset($_POST['f_'.$serie.'_'.$epreuve.'_moyenne']))   ? Clean::entier($_POST['f_'.$serie.'_'.$epreuve.'_moyenne'])   : NULL ;
  $tab_matieres = (isset($_POST['f_'.$serie.'_'.$epreuve.'_matieres'])) ? explode(',',$_POST['f_'.$serie.'_'.$epreuve.'_matieres']) : array() ;
  $matieres = implode( ',' , array_filter( Clean::map_entier($tab_matieres) , 'positif' ) );
  if( ($recherche===NULL) || ($moyenne===NULL) || ( empty($matieres) && $DB_ROW['brevet_epreuve_obligatoire'] ) )
  {
    exit('Erreur : données manquante pour l\'épreuve "'.html($DB_ROW['brevet_epreuve_nom']).'" !');
  }
  $tab_choix_epreuve[$epreuve] = array( 'recherche'=>$recherche , 'moyenne'=>$moyenne , 'matieres'=>$matieres );
}

// Enregistrement
foreach($tab_choix_epreuve as $epreuve=>$tab_choix)
{
  DB_STRUCTURE_BREVET::DB_modifier_epreuve_choix( $serie , $epreuve , $tab_choix['recherche'] , $tab_choix['moyenne'] , $tab_choix['matieres'] );
}
exit('ok');

?>
