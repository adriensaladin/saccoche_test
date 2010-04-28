<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$ids = (isset($_POST['ids'])) ? $_POST['ids'] : '';

if(mb_substr_count($ids,'_')!=2)
{
	exit('Erreur avec les données transmises !');
}

list($prefixe,$matiere_id,$niveau_id) = explode('_',$ids);
$matiere_id  = clean_entier($matiere_id);
$niveau_id   = clean_entier($niveau_id);

if( $matiere_id && $niveau_id )
{
	// Affichage du bilan de la liste des items pour la matière et le niveau sélectionnés
	$DB_TAB = DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_item=false,$socle_nom=true);
	echo afficher_arborescence($DB_TAB,$dynamique=false,$reference=false,$aff_coef='image',$aff_socle='image',$aff_lien='image',$aff_input=false);
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
