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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_evaluations')&&($_POST['f_action']!='ordonner')&&($_POST['f_action']!='saisir')&&($_POST['f_action']!='voir')&&($_POST['f_action']!='voir_repart')){exit('Action désactivée pour la démo...');}

$action       = (isset($_POST['f_action']))          ? clean_texte($_POST['f_action'])       : '';
$date_debut   = (isset($_POST['f_date_debut']))      ? clean_texte($_POST['f_date_debut'])   : '';
$date_fin     = (isset($_POST['f_date_fin']))        ? clean_texte($_POST['f_date_fin'])     : '';
$ref          = (isset($_POST['f_ref']))             ? clean_texte($_POST['f_ref'])          : '';
$date         = (isset($_POST['f_date']))            ? clean_texte($_POST['f_date'])         : '';
$date_visible = (isset($_POST['f_date_visible']))    ? clean_texte($_POST['f_date_visible']) : ''; // Peut valoir une date (JJ/MM/AAAA) ou "identique"
$info         = (isset($_POST['f_info']))            ? clean_texte($_POST['f_info'])         : '';
$descriptif   = (isset($_POST['f_descriptif']))      ? clean_texte($_POST['f_descriptif'])   : '';
$cart_contenu = (isset($_POST['f_contenu']))         ? clean_texte($_POST['f_contenu'])      : '';
$cart_detail  = (isset($_POST['f_detail']))          ? clean_texte($_POST['f_detail'])       : '';
$orientation  = (isset($_POST['f_orientation']))     ? clean_texte($_POST['f_orientation'])  : '';
$marge_min    = (isset($_POST['f_marge_min']))       ? clean_texte($_POST['f_marge_min'])    : '';
$couleur      = (isset($_POST['f_couleur']))         ? clean_texte($_POST['f_couleur'])      : '';
$only_req     = (isset($_POST['f_restriction_req'])) ? true                                  : false;

$dossier_export = './__tmp/export/';
$fnom = 'saisie_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.$ref;

// Si "ref" est renseigné (pour Éditer ou Retirer ou Saisir ou ...), il contient l'id de l'évaluation + '_' + l'initiale du type de groupe + l'id du groupe
// Dans le cas d'une duplication, "ref" sert à retrouver l'évaluation d'origine pour évenuellement récupérer l'ordre des items
if(mb_strpos($ref,'_'))
{
	list($devoir_id,$groupe) = explode('_',$ref,2);
	$devoir_id = clean_entier($devoir_id);
	$groupe  = clean_texte($groupe);
}
else
{
	$devoir_id = 0;
	$groupe = '';
}

// Si "groupe" est transmis via "ref", il contient l'initiale du type de groupe + l'id du groupe
$groupe_type = 'eval';
$groupe_id   = ($groupe) ? clean_entier(mb_substr($groupe,1)) : 0 ;

// Contrôler la liste des items transmis
$tab_id = (isset($_POST['tab_id'])) ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
// Contrôler la liste des items transmis
$tab_items = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
$tab_items = array_map('clean_entier',$tab_items);
$tab_items = array_filter($tab_items,'positif');
$nb_items  = count($tab_items);
// Contrôler la liste des élèves transmis
$tab_eleves = (isset($_POST['f_eleve_liste']))  ? explode('_',$_POST['f_eleve_liste'])  : array() ;
$tab_eleves = array_map('clean_entier',$tab_eleves);
$tab_eleves = array_filter($tab_eleves,'positif');
$nb_eleves  = count($tab_eleves);

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Afficher une liste d'évaluations
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_evaluations') && $date_debut && $date_fin )
{
	// Formater les dates
	$date_debut_mysql = convert_date_french_to_mysql($date_debut);
	$date_fin_mysql   = convert_date_french_to_mysql($date_fin);
	// Vérifier que la date de début est antérieure à la date de fin
	if($date_debut_mysql>$date_fin_mysql)
	{
		exit('Erreur : la date de début est postérieure à la date de fin !');
	}
	$DB_TAB = DB_STRUCTURE_lister_devoirs_prof($_SESSION['USER_ID'],0,$date_debut_mysql,$date_fin_mysql);
	foreach($DB_TAB as $DB_ROW)
	{
		// Formater la date et la référence de l'évaluation
		$date_affich = convert_date_mysql_to_french($DB_ROW['devoir_date']);
		$date_visible = ($DB_ROW['devoir_date']==$DB_ROW['devoir_visible_date']) ? 'identique' : convert_date_mysql_to_french($DB_ROW['devoir_visible_date']);
		$ref = $DB_ROW['devoir_id'].'_'.strtoupper($DB_ROW['groupe_type']{0}).$DB_ROW['groupe_id'];
		$cs = ($DB_ROW['items_nombre']>1) ? 's' : '';
		$us = ($DB_ROW['users_nombre']>1) ? 's' : '';
		// Afficher une ligne du tableau
		echo'<tr>';
		echo	'<td><i>'.html($DB_ROW['devoir_date']).'</i>'.html($date_affich).'</td>';
		echo	'<td>'.html($date_visible).'</td>';
		echo	'<td lang="'.html($DB_ROW['users_listing']).'">'.html($DB_ROW['users_nombre']).' élève'.$us.'</td>';
		echo	'<td>'.html($DB_ROW['devoir_info']).'</td>';
		echo	'<td lang="'.html($DB_ROW['items_listing']).'">'.html($DB_ROW['items_nombre']).' item'.$cs.'</td>';
		echo	'<td class="nu" lang="'.$ref.'">';
		echo		'<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>';
		echo		'<q class="ordonner" title="Réordonner les items de cette évaluation."></q>';
		echo		'<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
		echo		'<q class="supprimer" title="Supprimer cette évaluation."></q>';
		echo		'<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
		echo		'<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
		echo		'<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
		echo		'<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
		echo	'</td>';
		echo'</tr>';
	}
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Ajouter une nouvelle évaluation
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='ajouter')||(($action=='dupliquer')&&($devoir_id))) && $date && $date_visible && $nb_eleves && $nb_items )
{
	$date_mysql         = convert_date_french_to_mysql($date);
	$date_visible_mysql = convert_date_french_to_mysql($date_visible);
	// Tester les dates
	$date_stamp         = strtotime($date_mysql);
	$date_visible_stamp = strtotime($date_visible_mysql);
	$mini_stamp         = strtotime("-3 month");
	$maxi_stamp         = strtotime("+3 month");
	$maxi_visible_stamp = strtotime("+10 month");
	if( ($date_stamp<$mini_stamp) || ($date_visible_stamp<$mini_stamp) || ($date_stamp>$maxi_stamp) || ($date_visible_stamp>$maxi_visible_stamp) )
	{
		exit('Erreur : date trop éloignée !');
	}
	// Commencer par créer un nouveau groupe de type "eval", utilisé uniquement pour cette évaluation (c'est transparent pour le professeur)
	$groupe_id = DB_STRUCTURE_ajouter_groupe($groupe_type,$_SESSION['USER_ID'],'','',0);
	// Insèrer l'enregistrement de l'évaluation
	$devoir_id2 = DB_STRUCTURE_ajouter_devoir($_SESSION['USER_ID'],$groupe_id,$date_mysql,$info,$date_visible_mysql);
	// Affecter tous les élèves choisis
	DB_STRUCTURE_modifier_liaison_devoir_user($devoir_id2,$groupe_id,$tab_eleves,'creer');
	// Insérer les enregistrements des items de l'évaluation
	DB_STRUCTURE_modifier_liaison_devoir_item($devoir_id2,$tab_items,'dupliquer',$devoir_id);
	// Afficher le retour
	$date_visible = ($date==$date_visible) ? 'identique' : $date_visible;
	$ref = $devoir_id2.'_'.strtoupper($groupe_type{0}).$groupe_id;
	$cs = ($nb_items>1) ? 's' : '';
	$us = ($nb_eleves>1)      ? 's' : '';
	echo'<td><i>'.html($date_mysql).'</i>'.html($date).'</td>';
	echo'<td>'.html($date_visible).'</td>';
	echo'<td lang="'.implode('_',$tab_eleves).'">'.$nb_eleves.' élève'.$us.'</td>';
	echo'<td>'.html($info).'</td>';
	echo'<td lang="'.implode('_',$tab_items).'">'.$nb_items.' item'.$cs.'</td>';
	echo'<td class="nu" lang="'.$ref.'">';
	echo	'<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>';
	echo	'<q class="ordonner" title="Réordonner les items de cette évaluation."></q>';
	echo	'<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
	echo	'<q class="supprimer" title="Supprimer cette évaluation."></q>';
	echo	'<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
	echo	'<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
	echo	'<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
	echo	'<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
	echo'</td>';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Modifier une évaluation existante
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $devoir_id && $groupe_id && $date && $date_visible && $nb_eleves && $nb_items )
{
	$date_mysql         = convert_date_french_to_mysql($date);
	$date_visible_mysql = convert_date_french_to_mysql($date_visible);
	// Tester les dates
	$date_stamp         = strtotime($date_mysql);
	$date_visible_stamp = strtotime($date_visible_mysql);
	$mini_stamp         = strtotime("-10 month");
	$maxi_stamp         = strtotime("+10 month");
	if( ($date_stamp<$mini_stamp) || ($date_visible_stamp<$mini_stamp) || ($date_stamp>$maxi_stamp) || ($date_visible_stamp>$maxi_stamp) )
	{
		exit('Erreur : date trop éloignée !');
	}
	// sacoche_devoir (maj des paramètres date & info)
	DB_STRUCTURE_modifier_devoir($devoir_id,$_SESSION['USER_ID'],$date_mysql,$info,$date_visible_mysql,$tab_items);
	// sacoche_jointure_user_groupe + sacoche_saisie pour les users supprimés
	DB_STRUCTURE_modifier_liaison_devoir_user($devoir_id,$groupe_id,$tab_eleves,'substituer');
	// sacoche_jointure_devoir_item + sacoche_saisie pour les items supprimés
	DB_STRUCTURE_modifier_liaison_devoir_item($devoir_id,$tab_items,'substituer');
	// ************************ dans sacoche_saisie faut-il aussi virer certains scores élèves en cas de changement de groupe ... ???
	// Afficher le retour
	$date_visible = ($date==$date_visible) ? 'identique' : $date_visible;
	$ref = $devoir_id.'_'.strtoupper($groupe_type{0}).$groupe_id;
	$cs = ($nb_items>1)  ? 's' : '';
	$us = ($nb_eleves>1) ? 's' : '';
	echo'<td><i>'.html($date_mysql).'</i>'.html($date).'</td>';
	echo'<td>'.html($date_visible).'</td>';
	echo'<td lang="'.implode('_',$tab_eleves).'">'.$nb_eleves.' élève'.$us.'</td>';
	echo'<td>'.html($info).'</td>';
	echo'<td lang="'.implode('_',$tab_items).'">'.$nb_items.' item'.$cs.'</td>';
	echo'<td class="nu" lang="'.$ref.'">';
	echo	'<q class="modifier" title="Modifier cette évaluation (date, description, ...)."></q>';
	echo	'<q class="ordonner" title="Réordonner les items de cette évaluation."></q>';
	echo	'<q class="dupliquer" title="Dupliquer cette évaluation."></q>';
	echo	'<q class="supprimer" title="Supprimer cette évaluation."></q>';
	echo	'<q class="imprimer" title="Imprimer un cartouche pour cette évaluation."></q>';
	echo	'<q class="saisir" title="Saisir les acquisitions des élèves à cette évaluation."></q>';
	echo	'<q class="voir" title="Voir les acquisitions des élèves à cette évaluation."></q>';
	echo	'<q class="voir_repart" title="Voir les répartitions des élèves à cette évaluation."></q>';
	echo'</td>';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Supprimer une évaluation existante
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $devoir_id && $groupe_id )
{
	// supprimer le groupe spécialement associé (invisible à l'utilisateur) et les entrées dans sacoche_jointure_user_groupe pour une évaluation avec des élèves piochés en dehors de tout groupe prédéfini
	DB_STRUCTURE_supprimer_groupe($groupe_id,$groupe_type,$with_devoir=false);
	// la suite est commune aux évals sur une classe ou un groupe
	DB_STRUCTURE_supprimer_devoir_et_saisies($devoir_id,$_SESSION['USER_ID']);
	// Afficher le retour
	exit('<td>ok</td>');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Afficher le formulaire pour réordonner les items d'une évaluation
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ordonner') && $devoir_id )
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_lister_items_devoir($devoir_id);
	if(!count($DB_TAB_COMP))
	{
		exit('Aucun item n\'est associé à cette évaluation !');
	}
	$tab_affich  = array();
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$tab_affich[] = '<div id="i'.$DB_ROW['item_id'].'"><b>'.html($item_ref.$texte_socle).'</b> - '.html($DB_ROW['item_nom']).'</div>';
	}
	echo implode('<div class="ti"><input type="image" src="./_img/action_ordonner.png" /></div>',$tab_affich);
	echo'<p>';
	echo	'<button id="Enregistrer_ordre" type="button" value="'.$ref.'"><img alt="" src="./_img/bouton/valider.png" /> Enregistrer cet ordre</button>&nbsp;&nbsp;&nbsp;';
	echo	'<button id="fermer_zone_ordonner" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>&nbsp;&nbsp;&nbsp;';
	echo	'<label id="ajax_msg">&nbsp;</label>';
	echo'</p>';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Afficher le formulaire pour saisir les items acquis par les élèves à une évaluation
//	Générer en même temps un csv à récupérer pour une saisie déportée
//	Générer en même temps un pdf contenant un tableau de saisie vide
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='saisir') && $devoir_id && $groupe_id && $date && $date_visible && $descriptif ) // $date au format MySQL ; $descriptif séparé par ::: ; $info (facultative) reportées dans input hidden
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_lister_items_devoir($devoir_id);
	// liste des élèves
	$DB_TAB_USER = DB_STRUCTURE_lister_eleves_actifs_regroupement($groupe_type,$groupe_id);
	// Let's go
	$item_nb = count($DB_TAB_COMP);
	if(!$item_nb)
	{
		exit('Aucun item n\'est associé à cette évaluation !');
	}
	$eleve_nb = count($DB_TAB_USER);
	if(!$eleve_nb)
	{
		exit('Aucun élève n\'est associé à cette évaluation !');
	}
	$separateur = ';';
	$tab_affich  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
	$tab_user_id = array(); // pas indispensable, mais plus lisible
	$tab_comp_id = array(); // pas indispensable, mais plus lisible
	$tab_affich[0][0] = '<td>';
	$tab_affich[0][0].= '<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_saisie_resultats">DOC : Saisie des résultats.</a></span><p />';
	$tab_affich[0][0].= '<label for="radio_clavier"><input type="radio" id="radio_clavier" name="mode_saisie" value="clavier" /> <img alt="" src="./_img/pilot_keyboard.png" /> Piloter au clavier</label> <img alt="" src="./_img/bulle_aide.png" title="Sélectionner un rectangle blanc<br />au clavier (flèches) ou à la souris<br />puis utiliser les touches suivantes :<br />&nbsp;1 ; 2 ; 3 ; 4 ; A ; N ; D ; suppr" /><br />';
	$tab_affich[0][0].= '<label for="radio_souris"><input type="radio" id="radio_souris" name="mode_saisie" value="souris" /> <img alt="" src="./_img/pilot_mouse.png" /> Piloter à la souris</label> <img alt="" src="./_img/bulle_aide.png" title="Survoler une case du tableau avec la souris<br />puis cliquer sur une des images proposées." /><p />';
	$tab_affich[0][0].= '<label for="check_largeur"><input type="checkbox" id="check_largeur" name="check_largeur" value="retrecir_largeur" /> <img alt="" src="./_img/retrecir_largeur.gif" /> Largeur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la largeur des colonnes<br />si les élèves sont nombreux." /><br />';
	$tab_affich[0][0].= '<label for="check_hauteur"><input type="checkbox" id="check_hauteur" name="check_hauteur" value="retrecir_hauteur" /> <img alt="" src="./_img/retrecir_hauteur.gif" /> Hauteur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la hauteur des lignes<br />si les items sont nombreux." /><p />';
	$tab_affich[0][0].= '<button id="Enregistrer_saisie" type="button"><img alt="" src="./_img/bouton/valider.png" /> Enregistrer les saisies</button><input type="hidden" name="f_ref" id="f_ref" value="'.$ref.'" /><input id="f_date" name="f_date" type="hidden" value="'.$date.'" /><input id="f_date_visible" name="f_date_visible" type="hidden" value="'.$date_visible.'" /><input id="f_info" name="f_info" type="hidden" value="'.html($info).'" /><br />';
	$tab_affich[0][0].= '<button id="fermer_zone_saisir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>';
	$tab_affich[0][0].= '</td>';
	// première ligne (noms prénoms des élèves)
	$csv_ligne_eleve_nom = $separateur;
	$csv_ligne_eleve_id  = $separateur;
	$csv_nb_colonnes = 1;
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$tab_affich[0][$DB_ROW['user_id']] = '<th><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'&amp;br" /></th>';
		$tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_prenom'].' '.$DB_ROW['user_nom']);
		$csv_ligne_eleve_nom .= '"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
		$csv_ligne_eleve_id  .= $DB_ROW['user_id'].$separateur;
		$csv_nb_colonnes++;
	}
	$export_csv = $csv_ligne_eleve_id."\r\n";
	// première colonne (noms items)
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$tab_affich[$DB_ROW['item_id']][0] = '<th><b>'.html($item_ref.$texte_socle).'</b> <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['item_nom']).'" /><div>'.html($DB_ROW['item_nom']).'</div></th>';
		$tab_comp_id[$DB_ROW['item_id']] = $item_ref;
		$export_csv .= $DB_ROW['item_id'].str_repeat($separateur,$csv_nb_colonnes).$item_ref.$texte_socle.' '.$DB_ROW['item_nom']."\r\n";
	}
	$export_csv .= $csv_ligne_eleve_nom."\r\n\r\n";
	// cases centrales avec un champ input de base
	$num_colonne = 0;
	foreach($tab_user_id as $user_id=>$val_user)
	{
		$num_colonne++;
		$num_ligne=0;
		foreach($tab_comp_id as $comp_id=>$val_comp)
		{
			$num_ligne++;
			$tab_affich[$comp_id][$user_id] = '<td class="td_clavier" lang="C'.$num_colonne.'L'.$num_ligne.'"><input type="text" class="X" value="X" id="C'.$num_colonne.'L'.$num_ligne.'" name="'.$comp_id.'x'.$user_id.'" readonly /></td>';
		}
	}
	// configurer le champ input
	$DB_TAB = DB_STRUCTURE_lister_saisies_devoir($devoir_id,$with_REQ=true);
	$bad = 'class="X" value="X"';
	foreach($DB_TAB as $DB_ROW)
	{
		// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
		if(isset($tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
		{
			$bon = 'class="'.$DB_ROW['saisie_note'].'" value="'.$DB_ROW['saisie_note'].'"';
			$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = str_replace($bad,$bon,$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]);
		}
	}
	// Enregistrer le csv
	$export_csv .= str_replace(':::',"\r\n",$descriptif)."\r\n\r\n";
	$export_csv .= 'CODAGES AUTORISÉS : 1 2 3 4 A N D'."\r\n";
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	//
	// pdf contenant un tableau de saisie vide ; on a besoin de tourner du texte à 90°
	//
	require('./_lib/FPDF/fpdf.php');
	require('./_inc/class.PDF.php');
	$sacoche_pdf = new PDF($orientation='landscape',$marge_min=10,$couleur='non');
	$sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
	// 1ère ligne : référence devoir, noms élèves
	$sacoche_pdf->tableau_saisie_reference_devoir($descriptif);
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
	}
	// ligne suivantes : référence item, cases vides
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW['item_nom']);
		for($i=0 ; $i<$eleve_nb ; $i++)
		{
			$sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		}
		$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
	}
	$sacoche_pdf->Output($dossier_export.$fnom.'_sans_notes.pdf','F');
	//
	// c'est fini ; affichage du retour
	//
	foreach($tab_affich as $comp_id => $tab_user)
	{
		if(!$comp_id)
		{
			echo'<thead>';
		}
		echo'<tr>';
		foreach($tab_user as $user_id => $val)
		{
			echo $val;
		}
		echo'</tr>';
		if(!$comp_id)
		{
			echo'</thead><tbody class="h">';
		}
	}
	echo'</tbody>';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Voir les items acquis par les élèves à une évaluation
//	Générer en même temps un csv à récupérer pour une saisie déportée
//	Générer en même temps un pdf contenant un tableau de saisie vide
//	Générer en même temps un pdf contenant un tableau de saisie plein
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir') && $devoir_id && $groupe_id && $date && $descriptif ) // $date française pour le csv ; $descriptif séparé par :::
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_lister_items_devoir($devoir_id);
	// liste des élèves
	$DB_TAB_USER = DB_STRUCTURE_lister_eleves_actifs_regroupement($groupe_type,$groupe_id);
	// Let's go
	$item_nb = count($DB_TAB_COMP);
	if(!$item_nb)
	{
		exit('Aucun item n\'est associé à cette évaluation !');
	}
	$eleve_nb = count($DB_TAB_USER);
	if(!$eleve_nb)
	{
		exit('Aucun élève n\'est associé à cette évaluation !');
	}
	$separateur = ';';
	$tab_affich  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
	$tab_user_id = array(); // pas indispensable, mais plus lisible
	$tab_comp_id = array(); // pas indispensable, mais plus lisible
	$tab_affich[0][0] = '<td>';
	$tab_affich[0][0].= '<label for="check_largeur"><input type="checkbox" id="check_largeur" name="check_largeur" value="retrecir_largeur" /> <img alt="" src="./_img/retrecir_largeur.gif" /> Largeur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la largeur des colonnes<br />si les élèves sont nombreux." /><br />';
	$tab_affich[0][0].= '<label for="check_hauteur"><input type="checkbox" id="check_hauteur" name="check_hauteur" value="retrecir_hauteur" /> <img alt="" src="./_img/retrecir_hauteur.gif" /> Hauteur optimale</label> <img alt="" src="./_img/bulle_aide.png" title="Diminuer la hauteur des lignes<br />si les items sont nombreux." /><p />';
	$tab_affich[0][0].= '<button id="fermer_zone_voir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>';
	$tab_affich[0][0].= '</td>';
	// première ligne (noms prénoms des élèves)
	$csv_ligne_eleve_nom = $separateur;
	$csv_ligne_eleve_id  = $separateur;
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$tab_affich[0][$DB_ROW['user_id']] = '<th><img alt="'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($DB_ROW['user_nom']).'&amp;prenom='.urlencode($DB_ROW['user_prenom']).'&amp;br" /></th>';
		$tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
		$csv_ligne_eleve_nom .= '"'.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].'"'.$separateur;
		$csv_ligne_eleve_id  .= $DB_ROW['user_id'].$separateur;
	}
	$export_csv = $csv_ligne_eleve_id."\r\n";
	$csv_lignes_scores = array();
	$csv_colonne_texte = array();
	// première colonne (noms items)
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$tab_affich[$DB_ROW['item_id']][0] = '<th><b>'.html($item_ref.$texte_socle).'</b> <img alt="" src="./_img/bulle_aide.png" title="'.html($DB_ROW['item_nom']).'" /><div>'.html($DB_ROW['item_nom']).'</div></th>';
		$tab_comp_id[$DB_ROW['item_id']] = $item_ref;
		$csv_lignes_scores[$DB_ROW['item_id']][0] = $DB_ROW['item_id'];
		$csv_colonne_texte[$DB_ROW['item_id']]    = $item_ref.$texte_socle.' '.$DB_ROW['item_nom'];
	}
	// cases centrales vierges
	foreach($tab_user_id as $user_id=>$val_user)
	{
		foreach($tab_comp_id as $comp_id=>$val_comp)
		{
			$tab_affich[$comp_id][$user_id] = '<td title="'.$val_user.'<br />'.$val_comp.'">-</td>';
			$csv_lignes_scores[$comp_id][$user_id] = '';
		}
	}
	// ajouter le contenu
	$tab_dossier = array( ''=>'' , 'RR'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'R'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'V'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'VV'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'ABS'=>'commun/h/' , 'NN'=>'commun/h/' , 'DISP'=>'commun/h/' , 'REQ'=>'' );
	$DB_TAB = DB_STRUCTURE_lister_saisies_devoir($devoir_id,$with_REQ=true);
	foreach($DB_TAB as $DB_ROW)
	{
		// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
		if(isset($tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
		{
			$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = str_replace('>-<','><img alt="'.$DB_ROW['saisie_note'].'" src="./_img/note/'.$tab_dossier[$DB_ROW['saisie_note']].$DB_ROW['saisie_note'].'.gif" /><',$tab_affich[$DB_ROW['item_id']][$DB_ROW['eleve_id']]);
			$csv_lignes_scores[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = $DB_ROW['saisie_note'];
		}
	}
	// assemblage du csv
	$tab_conversion = array( ''=>' ' , 'RR'=>'1' , 'R'=>'2' , 'V'=>'3' , 'VV'=>'4' , 'ABS'=>'A' , 'NN'=>'N' , 'DISP'=>'D' , 'REQ'=>'?' );
	foreach($tab_comp_id as $comp_id=>$val_comp)
	{
		$export_csv .= $csv_lignes_scores[$comp_id][0].$separateur;
		foreach($tab_user_id as $user_id=>$val_user)
		{
			$export_csv .= $tab_conversion[$csv_lignes_scores[$comp_id][$user_id]].$separateur;
		}
		$export_csv .= $csv_colonne_texte[$comp_id]."\r\n";
	}
	$export_csv .= $csv_ligne_eleve_nom."\r\n\r\n";
	// Enregistrer le csv
	$export_csv .= str_replace(':::',"\r\n",$descriptif)."\r\n\r\n";
	$export_csv .= 'CODAGES AUTORISÉS : 1 2 3 4 A N D'."\r\n";
	$fnom = 'saisie_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_'.$ref;
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	require('./_lib/FPDF/fpdf.php');
	require('./_inc/class.PDF.php');
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	// pdf contenant un tableau de saisie vide ; on a besoin de tourner du texte à 90°
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	$sacoche_pdf = new PDF($orientation='landscape',$marge_min=10,$couleur='non');
	$sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
	// 1ère ligne : référence devoir, noms élèves
	$sacoche_pdf->tableau_saisie_reference_devoir($descriptif);
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
	}
	// ligne suivantes : référence item, cases vides
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW['item_nom']);
		for($i=0 ; $i<$eleve_nb ; $i++)
		{
			$sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		}
		$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
	}
	$sacoche_pdf->Output($dossier_export.$fnom.'_sans_notes.pdf','F');
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	// pdf contenant un tableau de saisie plein ; on a besoin de tourner du texte à 90°
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	$sacoche_pdf = new PDF($orientation='landscape',$marge_min=10,$couleur='oui');
	$sacoche_pdf->tableau_saisie_initialiser($eleve_nb,$item_nb);
	// 1ère ligne : référence devoir, noms élèves
	$sacoche_pdf->tableau_saisie_reference_devoir($descriptif);
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$sacoche_pdf->tableau_saisie_reference_eleve($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
	}
	// ligne suivantes : référence item, cases vides
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
	foreach($DB_TAB_COMP as $DB_ROW_COMP)
	{
		$item_ref = $DB_ROW_COMP['item_ref'];
		$texte_socle = ($DB_ROW_COMP['entree_id']) ? ' [S]' : ' [–]';
		$sacoche_pdf->tableau_saisie_reference_item($item_ref.$texte_socle,$DB_ROW_COMP['item_nom']);
		foreach($DB_TAB_USER as $DB_ROW_USER)
		{
			$sacoche_pdf->afficher_note_lomer( $csv_lignes_scores[$DB_ROW_COMP['item_id']][$DB_ROW_USER['user_id']] , $border=1 , $br=0 );
		}
		$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
	}
	$sacoche_pdf->Output($dossier_export.$fnom.'_avec_notes.pdf','F');
	//
	// c'est fini ; affichage du retour
	//
	foreach($tab_affich as $comp_id => $tab_user)
	{
		if(!$comp_id)
		{
			echo'<thead>';
		}
		echo'<tr>';
		foreach($tab_user as $user_id => $val)
		{
			echo $val;
		}
		echo'</tr>';
		if(!$comp_id)
		{
			echo'</thead><tbody>';
		}
	}
	echo'</tbody>';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Voir en proportion la répartition, nominative ou quantitative, des élèves par item (html + pdf)
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir_repart') && $devoir_id && $groupe_id && $date && $descriptif ) // $date française pour le csv ; $descriptif séparé par :::
{
	// liste des items
	$DB_TAB_ITEM = DB_STRUCTURE_lister_items_devoir($devoir_id);
	// liste des élèves
	$DB_TAB_USER = DB_STRUCTURE_lister_eleves_actifs_regroupement($groupe_type,$groupe_id);
	// Let's go
	$item_nb = count($DB_TAB_ITEM);
	if(!$item_nb)
	{
		exit('Aucun item n\'est associé à cette évaluation !');
	}
	$eleve_nb = count($DB_TAB_USER);
	if(!$eleve_nb)
	{
		exit('Aucun élève n\'est associé à cette évaluation !');
	}
	$tab_user_id = array(); // pas indispensable, mais plus lisible
	$tab_item_id = array(); // pas indispensable, mais plus lisible
	// noms prénoms des élèves
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$tab_user_id[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
	}
	// noms des items
	foreach($DB_TAB_ITEM as $DB_ROW)
	{
		$texte_socle = ($DB_ROW['entree_id']) ? ' [S]' : ' [–]';
		$tab_item_id[$DB_ROW['item_id']] = array( $DB_ROW['item_ref'].$texte_socle , $DB_ROW['item_nom'] );
	}
	// tableaux utiles ou pour conserver les infos
	$tab_dossier = array( 'RR'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'R'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'V'=>$_SESSION['NOTE_DOSSIER'].'/h/' , 'VV'=>$_SESSION['NOTE_DOSSIER'].'/h/' );
	$tab_init_nominatif   = array('RR'=>array(),'R'=>array(),'V'=>array(),'VV'=>array());
	$tab_init_quantitatif = array('RR'=>0 ,'R'=>0 ,'V'=>0 ,'VV'=>0 );
	$tab_repartition_nominatif   = array();
	$tab_repartition_quantitatif = array();
	// initialisation
	foreach($tab_item_id as $item_id=>$tab_infos_item)
	{
		$tab_repartition_nominatif[$item_id]   = $tab_init_nominatif;
		$tab_repartition_quantitatif[$item_id] = $tab_init_quantitatif;
	}
	// 1e ligne : référence des codes
	$affichage_repartition_head = '<th class="nu"></th>';
	foreach($tab_init_quantitatif as $note=>$vide)
	{
		$affichage_repartition_head .= '<th><img alt="'.$note.'" src="./_img/note/'.$tab_dossier[$note].$note.'.gif" /></th>';
	}
	// ligne suivantes
	$DB_TAB = DB_STRUCTURE_lister_saisies_devoir($devoir_id,$with_REQ=false);
	foreach($DB_TAB as $DB_ROW)
	{
		// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
		if( isset($tab_user_id[$DB_ROW['eleve_id']]) && isset($tab_item_id[$DB_ROW['item_id']]) )
		{
			if(isset($tab_init_quantitatif[$DB_ROW['saisie_note']])) // On ne garde que RR R V VV
			{
				$tab_repartition_nominatif[$DB_ROW['item_id']][$DB_ROW['saisie_note']][] = $tab_user_id[$DB_ROW['eleve_id']];
				$tab_repartition_quantitatif[$DB_ROW['item_id']][$DB_ROW['saisie_note']]++;
			}
		}
	}
	// assemblage / affichage du tableau avec la répartition quantitative
	echo'<thead><tr>'.$affichage_repartition_head.'</tr></thead><tbody>';
	foreach($tab_item_id as $item_id=>$tab_infos_item)
	{
		echo'<tr>';
		echo'<th><b>'.html($tab_infos_item[0]).'</b><br />'.html($tab_infos_item[1]).'</th>';
		foreach($tab_repartition_quantitatif[$item_id] as $code=>$note_nb)
		{
			echo'<td style="font-size:'.round(75+100*$note_nb/$eleve_nb).'%">'.round(100*$note_nb/$eleve_nb).'%</td>';
		}
		echo'</tr>';
	}
	echo'</tbody>';
	// Séparateur
	echo'<SEP>';
	// assemblage / affichage du tableau avec la répartition nominative
	echo'<thead><tr>'.$affichage_repartition_head.'</tr></thead><tbody>';
	foreach($tab_item_id as $item_id=>$tab_infos_item)
	{
		echo'<tr>';
		echo'<th><b>'.html($tab_infos_item[0]).'</b><br />'.html($tab_infos_item[1]).'</th>';
		foreach($tab_repartition_nominatif[$item_id] as $code=>$tab_eleves)
		{
			echo'<td>'.implode('<br />',$tab_eleves).'</td>';
		}
		echo'</tr>';
	}
	echo'</tbody>';
	require('./_lib/FPDF/fpdf.php');
	require('./_inc/class.PDF.php');
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	// pdf contenant un tableau avec la répartition quantitative
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	$sacoche_pdf = new PDF($orientation='portrait',$marge_min=10,$couleur='oui');
	$sacoche_pdf->tableau_devoir_repartition_quantitative_initialiser($item_nb);
	// 1ère ligne : référence des codes
	$sacoche_pdf->tableau_saisie_reference_devoir($descriptif);
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche+$sacoche_pdf->reference_largeur , $sacoche_pdf->marge_haut);
	foreach($tab_init_quantitatif as $note=>$vide)
	{
		$sacoche_pdf->afficher_note_lomer($note,$border=1,$br=0);
	}
	// ligne suivantes : référence item, cases répartition quantitative
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
	foreach($tab_item_id as $item_id=>$tab_infos_item)
	{
		$sacoche_pdf->tableau_saisie_reference_item($tab_infos_item[0],$tab_infos_item[1]);
		foreach($tab_repartition_quantitatif[$item_id] as $code=>$note_nb)
		{
			$coefficient = $note_nb/$eleve_nb ;
			// Tracer un rectangle coloré d'aire et d'intensité de niveau de gris proportionnels
			$teinte_gris = 255-128*$coefficient ;
			$sacoche_pdf->SetFillColor($teinte_gris,$teinte_gris,$teinte_gris);
			$memo_X = $sacoche_pdf->GetX();
			$memo_Y = $sacoche_pdf->GetY();
			$rect_largeur = $sacoche_pdf->cases_largeur * sqrt( $coefficient ) ;
			$rect_hauteur = $sacoche_pdf->cases_hauteur * sqrt( $coefficient ) ;
			$pos_X = $memo_X + ($sacoche_pdf->cases_largeur - $rect_largeur) / 2 ;
			$pos_Y = $memo_Y + ($sacoche_pdf->cases_hauteur - $rect_hauteur) / 2 ;
			$sacoche_pdf->SetXY($pos_X , $pos_Y);
			$sacoche_pdf->Cell($rect_largeur , $rect_hauteur , '' , 0 , 0 , 'C' , true , '');
			// Écrire le %
			$sacoche_pdf->SetXY($memo_X , $memo_Y);
			$sacoche_pdf->SetFont('Helvetica' , '' , $sacoche_pdf->taille_police*(1+$coefficient));
			$sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , pdf(round(100*$coefficient).'%') , 1 , 0 , 'C' , false , '');
		}
		$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
	}
	$sacoche_pdf->Output($dossier_export.$fnom.'_repartition_quantitative.pdf','F');
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	// pdf contenant un tableau avec la répartition nominative
	// / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
	$sacoche_pdf = new PDF($orientation='portrait',$marge_min=10,$couleur='oui');
	// il faut additionner le nombre maxi d'élèves par case de chaque item (sans descendre en dessous de 4 pour avoir la place d'afficher l'intitulé de l'item) afin de prévoir le nb de lignes nécessaires
	$somme = 0;
	foreach($tab_repartition_quantitatif as $item_id => $tab_effectifs)
	{
		$somme += max(4,max($tab_effectifs));
	}
	$sacoche_pdf->tableau_devoir_repartition_nominative_initialiser($somme);
	// 1ère ligne : référence des codes
	$sacoche_pdf->tableau_saisie_reference_devoir($descriptif);
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche+$sacoche_pdf->reference_largeur , $sacoche_pdf->marge_haut);
	foreach($tab_init_quantitatif as $note=>$vide)
	{
		$sacoche_pdf->afficher_note_lomer($note,$border=1,$br=0);
	}
	// ligne suivantes : référence item, cases répartition nominative
	$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->marge_haut+$sacoche_pdf->etiquette_hauteur);
	foreach($tab_item_id as $item_id=>$tab_infos_item)
	{
		// il faut calculer la hauteur de la case
		$sacoche_pdf->cases_hauteur = $sacoche_pdf->lignes_hauteur * max(4,max($tab_repartition_quantitatif[$item_id]));
		$sacoche_pdf->tableau_saisie_reference_item($tab_infos_item[0],$tab_infos_item[1]);
		foreach($tab_repartition_nominatif[$item_id] as $code=>$tab_eleves)
		{
			// Ecrire les noms ; plus court avec MultiCell() mais pb des retours à la ligne pour les noms trop longs
			$memo_X = $sacoche_pdf->GetX();
			$memo_Y = $sacoche_pdf->GetY();
			foreach($tab_eleves as $key => $eleve_texte)
			{
				$taille_police = $sacoche_pdf->taille_police;
				while($sacoche_pdf->test_pas_trop_long($eleve_texte,$taille_police,$sacoche_pdf->cases_largeur-10)==false) // -10 car à cause des majuscules ça a tendance à déborder...
				{
					$taille_police -= 0.5 ;
				}
				$sacoche_pdf->SetFont('Helvetica' , '' , $taille_police);
				$sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->lignes_hauteur , pdf($eleve_texte) , 0 , 2 , 'L' , false , '');
			}
			// Ajouter la bordure
			$sacoche_pdf->SetXY($memo_X , $memo_Y);
			$sacoche_pdf->Cell($sacoche_pdf->cases_largeur , $sacoche_pdf->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		}
		$sacoche_pdf->SetXY($sacoche_pdf->marge_gauche , $sacoche_pdf->GetY()+$sacoche_pdf->cases_hauteur);
	}
	$sacoche_pdf->Output($dossier_export.$fnom.'_repartition_nominative.pdf','F');
	//
	// c'est fini...
	//
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Mettre à jour l'ordre des items d'une évaluation
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_ordre') && $devoir_id && count($tab_id) )
{
	DB_STRUCTURE_modifier_ordre_item($devoir_id,$tab_id);
	exit('<ok>');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Mettre à jour les items acquis par les élèves à une évaluation
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_saisie') && $devoir_id && $date && $date_visible )
{
	// Tout est transmis : il faut comparer avec le contenu de la base pour ne mettre à jour que ce dont il y a besoin
	// On récupère les données transmises dans $tab_post
	$tab_post = array();
	foreach($_POST as $key => $note)
	{
		$tab_key = explode('x',$key);
		if(count($tab_key)==2)
		{
			$item_id = clean_entier($tab_key[0]);
			$eleve_id = clean_entier($tab_key[1]);
			if( $item_id && $eleve_id )
			{
				$tab_post[$item_id.'x'.$eleve_id] = $note;
			}
		}
	}
	// On recupère le contenu de la base déjà enregistré pour le comparer ; on remplit au fur et à mesure $tab_nouveau_modifier / $tab_nouveau_supprimer
	// $tab_demande_supprimer sert à supprimer des demandes d'élèves dont on met une note.
	$tab_nouveau_modifier = array();
	$tab_nouveau_supprimer = array();
	$tab_demande_supprimer = array();
	$DB_TAB = DB_STRUCTURE_lister_saisies_devoir($devoir_id,$with_REQ=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$key = $DB_ROW['item_id'].'x'.$DB_ROW['eleve_id'];
		if($tab_post[$key]!=$DB_ROW['saisie_note'])
		{
			if($tab_post[$key]=='X')
			{
				// valeur de la base à supprimer
				$tab_nouveau_supprimer[$key] = $key;
			}
			else
			{
				// valeur de la base à modifier
				$tab_nouveau_modifier[$key] = $tab_post[$key];
				if($DB_ROW['saisie_note']=='REQ')
				{
					// demande d'évaluation à supprimer
					$tab_demande_supprimer[$key] = $key;
				}
			}
		}
		unset($tab_post[$key]);
	}
	// Il reste dans $tab_post les données à ajouter (mises dans $tab_nouveau_ajouter) et les données qui ne servent pas (non enregistrées et non saisies)
	$tab_nouveau_ajouter = array_filter($tab_post,'non_note');
	// Il n'y a plus qu'à mettre à jour la base
	if( !count($tab_nouveau_ajouter) && !count($tab_nouveau_modifier) && !count($tab_nouveau_supprimer) )
	{
		exit('Aucune modification détectée !');
	}
	// L'information associée à la note comporte le nom de l'évaluation + celui du professeur (c'est une information statique, conservée sur plusieurs années)
	$date_visible_mysql = ($date_visible=='identique') ? $date : convert_date_french_to_mysql($date_visible);
	$info = $info.' ('.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.)';
	foreach($tab_nouveau_ajouter as $key => $note)
	{
		list($item_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_ajouter_saisie($_SESSION['USER_ID'],$eleve_id,$devoir_id,$item_id,$date,$note,$info,$date_visible_mysql);
	}
	foreach($tab_nouveau_modifier as $key => $note)
	{
		list($item_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_modifier_saisie($eleve_id,$devoir_id,$item_id,$note,$info);
	}
	foreach($tab_nouveau_supprimer as $key => $key)
	{
		list($item_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_supprimer_saisie($eleve_id,$devoir_id,$item_id);
	}
	foreach($tab_demande_supprimer as $key => $key)
	{
		list($item_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_supprimer_demande($eleve_id,$item_id);
	}
	exit('<ok>');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Imprimer un cartouche d'une évaluation
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Imprimer_cartouche') && $devoir_id && $groupe_id && $date && $cart_contenu && $cart_detail && $orientation && $marge_min && $couleur )
{
	save_cookie_select('cartouche');
	$with_nom    = (substr($cart_contenu,0,8)=='AVEC_nom')  ? true : false ;
	$with_result = (substr($cart_contenu,9)=='AVEC_result') ? true : false ;
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_lister_items_devoir($devoir_id);
	// liste des élèves
	$DB_TAB_USER = DB_STRUCTURE_lister_eleves_actifs_regroupement($groupe_type,$groupe_id);
	// Let's go
	if(!count($DB_TAB_COMP))
	{
		exit('Aucun item n\'est associé à cette évaluation !');
	}
	if(!count($DB_TAB_USER))
	{
		exit('Aucun élève n\'est associé à cette évaluation !');
	}
	$tab_result  = array(); // tableau bi-dimensionnel [n°ligne=id_item][n°colonne=id_user]
	$tab_user_id = array(); // pas indispensable, mais plus lisible
	$tab_comp_id = array(); // pas indispensable, mais plus lisible
	$tab_user_nb_req = array(); // pour retenir le nb d'items par utilisateur : variable et utile uniquement si cartouche avec les demandes d'évaluations 
	// enregistrer noms prénoms des élèves
	foreach($DB_TAB_USER as $DB_ROW)
	{
		$tab_user_id[$DB_ROW['user_id']] = ($with_nom) ? html($DB_ROW['user_prenom'].' '.$DB_ROW['user_nom']) : '' ;
		$tab_user_nb_req[$DB_ROW['user_id']] = 0 ;
	}
	// enregistrer refs noms items
	foreach($DB_TAB_COMP as $DB_ROW)
	{
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
		$tab_comp_id[$DB_ROW['item_id']] = array($item_ref,$texte_socle.$DB_ROW['item_nom']);
	}
	// résultats vierges
	foreach($tab_user_id as $user_id=>$val_user)
	{
		foreach($tab_comp_id as $comp_id=>$val_comp)
		{
			$tab_result[$comp_id][$user_id] = '';
		}
	}
	// compléter si demandé avec les résultats et/ou les demandes d'évaluations
	if($with_result || $only_req)
	{
		$DB_TAB = DB_STRUCTURE_lister_saisies_devoir($devoir_id,$with_REQ=$only_req);
		foreach($DB_TAB as $DB_ROW)
		{
			// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
			if(isset($tab_result[$DB_ROW['item_id']][$DB_ROW['eleve_id']]))
			{
				$valeur = ($with_result) ? $DB_ROW['saisie_note'] : ( ($DB_ROW['saisie_note']) ? 'REQ' : '' ) ;
				if($valeur)
				{
					$tab_result[$DB_ROW['item_id']][$DB_ROW['eleve_id']] = $valeur ;
					$tab_user_nb_req[$DB_ROW['eleve_id']]++;
				}
			}
		}
	}
	// On attaque l'élaboration des sorties HTML, CSV et PDF
	$fnom = 'cartouche_'.$_SESSION['BASE'].'_'.$devoir_id.'_'.time();
	$sacoche_htm = '<hr /><a class="lien_ext" href="'.$dossier_export.$fnom.'.pdf">Cartouches &rarr; Archiver / Imprimer (format <em>pdf</em>).</a><br />';
	$sacoche_htm.= '<a class="lien_ext" href="'.$dossier_export.$fnom.'.zip">Cartouches &rarr; Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</a><p />';
	$sacoche_csv = '';
	// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
	$item_nb = count($tab_comp_id);
	if(!$only_req)
	{
		$tab_user_nb_req = array_fill_keys( array_keys($tab_user_nb_req) , $item_nb );
	}
	require('./_lib/FPDF/fpdf.php');
	require('./_inc/class.PDF.php');
	$sacoche_pdf = new PDF($orientation,$marge_min,$couleur);
	$sacoche_pdf->cartouche_initialiser($cart_detail,$item_nb);
	if($cart_detail=='minimal')
	{
		// dans le cas d'un cartouche minimal
		foreach($tab_user_id as $user_id=>$val_user)
		{
			if($tab_user_nb_req[$user_id])
			{
				$texte_entete = $date.' - '.$info.' - '.$val_user;
				$sacoche_htm .= '<table class="bilan"><thead><tr><th colspan="'.$tab_user_nb_req[$user_id].'">'.html($texte_entete).'</th></tr></thead><tbody>';
				$sacoche_csv .= $texte_entete."\r\n";
				$sacoche_pdf->cartouche_entete( $texte_entete , $lignes_nb=4 );
				$ligne1_csv = ''; $ligne1_html = '';
				$ligne2_csv = ''; $ligne2_html = '';
				foreach($tab_comp_id as $comp_id=>$tab_val_comp)
				{
					if( ($only_req==false) || ($tab_result[$comp_id][$user_id]) )
					{
						$ligne1_html .= '<td>'.html($tab_val_comp[0]).'</td>';
						$ligne2_html .= '<td class="hc">'.affich_note_html($tab_result[$comp_id][$user_id],$date,$info,false).'</td>';
						$ligne1_csv .= $tab_val_comp[0]."\t";
						$ligne2_csv .= $tab_result[$comp_id][$user_id]."\t";
						$sacoche_pdf->cartouche_minimal_competence($tab_val_comp[0] , $tab_result[$comp_id][$user_id]);
					}
				}
				$sacoche_htm .= '<tr>'.$ligne1_html.'</tr><tr>'.$ligne2_html.'</tr></tbody></table><p />';
				$sacoche_csv .= $ligne1_csv."\r\n".$ligne2_csv."\r\n\r\n";
				$sacoche_pdf->cartouche_interligne(4);
			}
		}
	}
	elseif($cart_detail=='complet')
	{
		// dans le cas d'un cartouche complet
		foreach($tab_user_id as $user_id=>$val_user)
		{
			if($tab_user_nb_req[$user_id])
			{
				$texte_entete = $date.' - '.$info.' - '.$val_user;
				$sacoche_htm .= '<table class="bilan"><thead><tr><th colspan="3">'.html($texte_entete).'</th></tr></thead><tbody>';
				$sacoche_csv .= $texte_entete."\r\n";
				$sacoche_pdf->cartouche_entete( $texte_entete , $lignes_nb=$tab_user_nb_req[$user_id]+1 );
				foreach($tab_comp_id as $comp_id=>$tab_val_comp)
				{
					if( ($only_req==false) || ($tab_result[$comp_id][$user_id]) )
					{
						$sacoche_htm .= '<tr><td>'.html($tab_val_comp[0]).'</td><td>'.html($tab_val_comp[1]).'</td><td>'.affich_note_html($tab_result[$comp_id][$user_id],$date,$info,false).'</td></tr>';
						$sacoche_csv .= $tab_val_comp[0]."\t".$tab_val_comp[1]."\t".$tab_result[$comp_id][$user_id]."\r\n";
						$sacoche_pdf->cartouche_complet_competence($tab_val_comp[0] , $tab_val_comp[1] , $tab_result[$comp_id][$user_id]);
					}
				}
				$sacoche_htm .= '</tbody></table><p />';
				$sacoche_csv .= "\r\n";
				$sacoche_pdf->cartouche_interligne(2);
			}
		}
	}
	// On archive le cartouche dans un fichier tableur zippé (csv tabulé)
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($sacoche_csv));
	$zip->close();
	// On archive le cartouche dans un fichier pdf
	$sacoche_pdf->Output($dossier_export.$fnom.'.pdf','F');
	// Affichage
	exit($sacoche_htm);
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Traiter une demande d'importation d'une saisie déportée ; on n'enregistre rien, on ne fait que le décrypter et renvoyer une chaine résultante au javascript
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( (isset($_GET['f_action'])) && ($_GET['f_action']=='importer_saisie_csv') )
{
	// Récupérer le contenu du fichier
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload());
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if(!in_array($extension,array('txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$contenu_csv = file_get_contents($fnom_serveur);
	$contenu_csv = utf8($contenu_csv); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu_csv); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	// Pas de ligne d'en-tête à supprimer
	// Mémoriser les eleve_id de la 1ère ligne
	$tab_eleve = array();
	$tab_elements = explode($separateur,$tab_lignes[0]);
	unset($tab_elements[0]);
	foreach ($tab_elements as $num_colonne => $element_contenu)
	{
		$eleve_id = clean_entier($element_contenu);
		if($eleve_id)
		{
			$tab_eleve[$num_colonne] = $eleve_id ;
		}
	}
	// Parcourir les lignes suivantes et mémoriser les scores
	$retour = '|';
	unset($tab_lignes[0]);
	$scores_autorises = '1234AaNnDd';
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		$item_id = clean_entier($tab_elements[0]);
		if($item_id)
		{
			foreach ($tab_eleve as $num_colonne => $eleve_id)
			{
				if( (isset($tab_elements[$num_colonne])) && ($tab_elements[$num_colonne]!='') )
				{
					$score = $tab_elements[$num_colonne];
					if(strpos($scores_autorises,$score)!==false)
					{
						$retour .= $eleve_id.'.'.$item_id.'.'.strtoupper($score).'|';
					}
				}
			}
		}
	}
	exit($retour);
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là !
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
