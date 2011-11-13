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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_bilan')&&($_POST['f_action']!='Afficher_information')){exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action'])  : '';
$eleve_id   = (isset($_POST['f_user']))   ? clean_entier($_POST['f_user'])   : 0;
$palier_id  = (isset($_POST['f_palier'])) ? clean_entier($_POST['f_palier']) : 0;
$pilier_id  = (isset($_POST['f_pilier'])) ? clean_entier($_POST['f_pilier']) : 0;
$tab_pilier = (isset($_POST['piliers']))  ? array_map('clean_entier',explode(',',$_POST['piliers'])) : array() ;
$tab_eleve  = (isset($_POST['eleves']))   ? array_map('clean_entier',explode(',',$_POST['eleves']))  : array() ;

$listing_eleve_id = implode(',',$tab_eleve);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher le tableau avec les états de validations
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
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_langue
		$affichage .= '<th><img id="I'.$eleve_id.'" alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'" /></th>';
		$tfoot .= '<td class="L'.$eleve_langue.'" title="'.$tab_langues[$eleve_langue]['texte'].'"></td>';
		$tab_eleve_id[] = $eleve_id;
	}
	$affichage .= '<th><img alt="Tous les élèves" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode('TOUS LES ÉLÈVES').'" /></th>';
	$affichage .= '<th class="nu">&nbsp;&nbsp;&nbsp;</th>';
	$affichage .= '<th class="nu">';
	$affichage .=   '<p class="danger">Rappel : la validation d\'une compétence est définitive (une invalidation peut être changée).</p>';
	$affichage .=   '<p><button id="Enregistrer_validation" type="button" class="valider">Enregistrer les validations</button> <button id="fermer_zone_validation" type="button" class="retourner">Retour</button><label id="ajax_msg_validation"></label></p>';
	$affichage .= '</th>';
	$affichage .= '</tr></thead>';
	$affichage .= '<tbody>';
	// Afficher la ligne du tableau avec les validations pour des piliers choisis
	$affichage .= '<tr>';
	foreach($tab_eleve_id as $eleve_id)
	{
		$affichage .= '<th id="U'.$eleve_id.'" class="down1" title="Modifier la validation de toutes les compétences pour cet élève."></th>';
	}
	$affichage .= '<th id="P'.$palier_id.'" class="diag1" title="Modifier la validation de toutes les compétences pour tous les élèves."></th>';
	$affichage .= '<th class="nu" colspan="2"><div class="m1 b">@PALIER@</div></th>';
	$affichage .= '</tr>';
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
				$affichage .= '<td id="U'.$eleve_id.'C'.$pilier_id.'" class="v2"></td>';
			}
			$affichage .= '<th id="C'.$pilier_id.'" class="left1" title="Modifier la validation de cette compétence pour tous les élèves."></th>';
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
		$etat = ($DB_ROW['validation_pilier_etat']) ? 'Validé' : 'Invalidé' ;
		$lang = ($DB_ROW['validation_pilier_etat']) ? ' lang="lock"' : '' ;
		$tab_bad[] = 'U'.$DB_ROW['user_id'].'C'.$DB_ROW['pilier_id'].'" class="v2">';
		$tab_bon[] = 'U'.$DB_ROW['user_id'].'C'.$DB_ROW['pilier_id'].'" class="v'.$DB_ROW['validation_pilier_etat'].'" title="'.$etat.' le '.convert_date_mysql_to_french($DB_ROW['validation_pilier_date']).' par '.html($DB_ROW['validation_pilier_info']).'"'.$lang.'>';
	}
	$affichage = str_replace($tab_bad,$tab_bon,$affichage);
	// $affichage = str_replace('class="v2"','class="v2" title="Cliquer pour valider ou invalider."',$affichage); // Retiré car embêtant si modifié ensuite.
	// Afficher le résultat
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher les informations pour aider à valider un pilier précis pour un élève donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='Afficher_information') && $eleve_id && $pilier_id )
{
	// Récupération de la liste des validations des items du palier
	$tab_item = array();	// [entree_id] => 0/1;
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_entree($eleve_id,$listing_entree_id='',$domaine_id=0,$pilier_id,$palier_id=0);
	if(!count($DB_TAB))
	{
		exit('Aucune validation d\'item n\'est renseignée pour cette compétence !');
	}
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_item[$DB_ROW['entree_id']] = $DB_ROW['validation_entree_etat'];
	}
	// Récupérer l'arborescence du pilier du socle ; préparer l'affichage et comptabiliser les différents états de validation
	$tab_texte_stats = array(1=>'validé',0=>'invalidé',2=>'non renseigné');
	$tab_texte_items = array(1=>'OUI',0=>'NON',2=>'???');
	$tab_validation_socle = array(1=>0,0=>0,2=>0);
	$affichage_socle = '';
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_recuperer_arborescence_pilier($pilier_id);
	$section_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			$affichage_socle .= '<div class="n2 i">'.html($DB_ROW['section_nom']).'</div>';
			$entree_id  = 0;
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
		{
			$entree_id = $DB_ROW['entree_id'];
			$etat = (isset($tab_item[$DB_ROW['entree_id']])) ? $tab_item[$DB_ROW['entree_id']] : 2 ;
			$affichage_socle .= '<div class="n3"><tt class="v'.$etat.'">'.$tab_texte_items[$etat].'</tt>'.html($DB_ROW['entree_nom']).'</div>';
			$tab_validation_socle[$etat]++;
		}
	}
	// Ligne de stats
	foreach($tab_validation_socle as $etat => $nb)
	{
		$s = ($nb>1) ? 's' : '' ;
		echo'<span class="v'.$etat.'">'.$nb.' '.$tab_texte_stats[$etat].$s.'</span>';
	}
	// Paragraphe des items
	echo'@'.$affichage_socle;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Enregistrer les états de validation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif($action=='Enregistrer_validation')
{
	// Récupérer les triplets {eleve;pilier;valid}
	$tab_valid = (isset($_POST['f_valid'])) ? explode(',',$_POST['f_valid']) : array() ;
	$tab_post = array();
	// Au passage, enregistrer la liste des items et des élèves
	$tab_eleve_id  = array();
	$tab_pilier_id = array();
	foreach($tab_valid as $string_infos)
	{
		$string_infos = str_replace( array('U','C','V') , '_' , $string_infos);
		list($rien,$eleve_id,$pilier_id,$valid) = explode('_',$string_infos);
		$tab_post[$pilier_id.'x'.$eleve_id] = (int)$valid;
		$tab_eleve_id[$eleve_id]   = $eleve_id;
		$tab_pilier_id[$pilier_id] = $pilier_id;
	}
	if( (!count($tab_post)) || (count($tab_eleve_id)*count($tab_pilier_id)!=count($tab_post)) )
	{
		exit('Erreur détectée avec les validations transmises !');
	}
	// On recupère le contenu de la base déjà enregistré pour le comparer
	$listing_eleve_id  = implode(',',$tab_eleve_id);
	$listing_pilier_id = implode(',',$tab_pilier_id);
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_pilier($listing_eleve_id,$listing_pilier_id,$palier_id=0);
	// On remplit au fur et à mesure $tab_nouveau_modifier et $tab_nouveau_supprimer
	$tab_nouveau_modifier = array();
	$tab_nouveau_supprimer = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$key = $DB_ROW['pilier_id'].'x'.$DB_ROW['user_id'];
		// Attention : on ne peut pas modifier un pilier déjà validé (verrouillage)
		if($DB_ROW['validation_pilier_etat']!=1)
		{
			if($tab_post[$key]==2)
			{
				// Validation présente dans la base mais annulée par le formulaire
				$tab_nouveau_supprimer[$key] = $key;
			}
			elseif($tab_post[$key]!=$DB_ROW['validation_pilier_etat'])
			{
				// Validation présente dans la base mais modifiée par le formulaire
				$tab_nouveau_modifier[$key] = $tab_post[$key];
			}
			// Sinon, validation présente dans la base et confirmée par le formulaire : RAS
		}
		unset($tab_post[$key]);
	}
	// Il reste dans $tab_post les validations à ajouter (mises dans $tab_nouveau_ajouter) et les validations à ignorer (non effectuées par le formulaire)
	// On remplit $tab_nouveau_ajouter
	// Validation absente dans la base mais effectuée par le formulaire
	$tab_nouveau_ajouter = array_filter($tab_post,'is_renseigne');
	// Sinon, validation absente dans la base et absente du formulaire : RAS

	// Il n'y a plus qu'à mettre à jour la base
	if( !count($tab_nouveau_ajouter) && !count($tab_nouveau_modifier) && !count($tab_nouveau_supprimer) )
	{
		exit('Aucune modification détectée !');
	}
	// L'information associée à la validation comporte le nom du validateur (c'est une information statique, conservée sur plusieurs années)
	$info = $_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.';
	$date_mysql = date("Y-m-d");	// date_mysql de la forme aaaa-mm-jj
	foreach($tab_nouveau_ajouter as $key => $etat)
	{
		list($pilier_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_SOCLE::DB_ajouter_validation('pilier',$eleve_id,$pilier_id,$etat,$date_mysql,$info);
	}
	foreach($tab_nouveau_modifier as $key => $etat)
	{
		list($pilier_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_SOCLE::DB_modifier_validation('pilier',$eleve_id,$pilier_id,$etat,$date_mysql,$info);
	}
	foreach($tab_nouveau_supprimer as $key)
	{
		list($pilier_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_SOCLE::DB_supprimer_validation('pilier',$eleve_id,$pilier_id);
	}
	exit('OK');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
