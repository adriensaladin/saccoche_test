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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_bilan')){exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action'])  : '';
$eleve_id   = (isset($_POST['f_user']))   ? clean_entier($_POST['f_user'])   : 0;
$palier_id  = (isset($_POST['f_palier'])) ? clean_entier($_POST['f_palier']) : 0;
$pilier_id  = (isset($_POST['f_pilier'])) ? clean_entier($_POST['f_pilier']) : 0;
$tab_pilier = (isset($_POST['piliers']))  ? array_map('clean_entier',explode(',',$_POST['piliers'])) : array() ;
$tab_eleve  = (isset($_POST['eleves']))   ? array_map('clean_entier',explode(',',$_POST['eleves']))  : array() ;
$delete_id  = (isset($_POST['delete_id'])) ? clean_texte($_POST['delete_id']) : '';

$listing_eleve_id = implode(',',$tab_eleve);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher le tableau avec les états de validations ET NE CONSERVER QUE LES VALIDATIONS POSITIVES
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Afficher_bilan') && $palier_id && count($tab_pilier) && count($tab_eleve) )
{
	Formulaire::save_choix('palier');
	$affichage = '';
	// Tableau des langues
	$tfoot = '';
	require_once('./_inc/tableau_langues.php');
	// Récupérer les données des élèves
	$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($listing_eleve_id,$with_gepi=FALSE,$with_langue=TRUE);
	if(!is_array($tab_eleve))
	{
		exit('Aucun élève trouvé correspondant aux identifiants transmis !');
	}
	// Afficher la première ligne du tableau avec les étiquettes des élèves
	$tab_eleve_id = array(); // listing des ids des élèves mis à jour au cas où la récupération dans la base soit différente des ids transmis...
	$affichage .= '<thead><tr>';
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
		$affichage .= '<th><img id="I'.$eleve_id.'" alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'" /></th>';
		$tfoot .= '<td class="L'.$eleve_langue.'" title="'.$tab_langues[$eleve_langue]['texte'].'"></td>';
		$tab_eleve_id[] = $eleve_id;
	}
	$affichage .= '<th class="nu">&nbsp;&nbsp;&nbsp;</th>';
	$affichage .= '<th class="nu">';
	$affichage .=   '<p class="danger">Outil à utiliser avec parcimonie, uniquement pour rectifier des erreurs de saisie.</p>';
	$affichage .=   '<div id="confirmation" style="opacity:0">';
	$affichage .=     '<ul class="puce"><li id="report_nom"></li><li id="report_compet"></li></ul>';
	$affichage .=     '<input type="hidden" id="f_valid" name="f_valid" val="" /><button id="Enregistrer_validation" type="button" class="valider">Confirmer la suppression de cette validation</button><label id="ajax_msg_validation"></label>';
	$affichage .=   '</div>';
	$affichage .=   '<div><button id="fermer_zone_validation" type="button" class="retourner">Retour</button></div>';
	$affichage .=   '<div class="m1 b p">@PALIER@</div>';
	$affichage .= '</th>';
	$affichage .= '</tr></thead>';
	$affichage .= '<tbody>';
	// Récupérer l'arborescence des piliers du palier du socle (enfin... uniquement les piliers, ça suffit ici)
	$tab_pilier_id = array(); // listing des ids des piliers mis à jour au cas où la récupération dans la base soit différente des ids transmis...
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_recuperer_piliers($palier_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$pilier_id = $DB_ROW['pilier_id'];
		if(in_array($pilier_id,$tab_pilier))
		{
			$tab_pilier_id[] = $pilier_id;
			// Afficher la ligne du tableau avec les validations des piliers, puis le nom du pilier (officiellement compétence)
			$affichage .= '<tr>';
			foreach($tab_eleve_id as $eleve_id)
			{
				$affichage .= '<td id="U'.$eleve_id.'C'.$pilier_id.'" class="v3"></td>';
			}
			$affichage .= '<th class="nu" colspan="2"><div class="n1">'.html($DB_ROW['pilier_nom']).'</div></th>';
			$affichage .= '</tr>';
		}
	}
	$affichage .= '</tbody>';
	// Ligne avec le drapeau de la LV, si compétence concernée sélectionnée.
	$affichage .= count(array_intersect($tab_pilier_id,$tab_langue_piliers)) ? '<tfoot>'.$tfoot.'<th class="nu" colspan="3"></th></tfoot>' : '' ;
	// Récupérer la liste des jointures (validations)
	$listing_eleve_id  = implode(',',$tab_eleve_id);
	$listing_pilier_id = implode(',',$tab_pilier_id);
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_pilier($listing_eleve_id,$listing_pilier_id,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les piliers
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['validation_pilier_etat'])
		{
			$tab_bad[] = 'U'.$DB_ROW['user_id'].'C'.$DB_ROW['pilier_id'].'" class="v3">';
			$tab_bon[] = 'U'.$DB_ROW['user_id'].'C'.$DB_ROW['pilier_id'].'" class="v'.$DB_ROW['validation_pilier_etat'].'" title="Validé le '.convert_date_mysql_to_french($DB_ROW['validation_pilier_date']).' par '.html($DB_ROW['validation_pilier_info']).'" lang="lock">';
		}
	}
	$affichage = str_replace($tab_bad,$tab_bon,$affichage);
	// Afficher le résultat
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer une validation positive
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Enregistrer_validation') && ($delete_id) )
{
	// Récupérer le duo {eleve;pilier}
	$string_infos = str_replace( array('U','C') , '_' , $delete_id);
	list($rien,$eleve_id,$pilier_id) = explode('_',$string_infos);
	// Mettre à jour la base
	DB_STRUCTURE_SOCLE::DB_supprimer_validation('pilier',$eleve_id,$pilier_id);
	exit('OK');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
