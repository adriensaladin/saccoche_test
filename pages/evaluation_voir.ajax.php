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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['f_action']!='Afficher_evaluations')&&($_POST['f_action']!='Voir_notes')){exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])     : '';
$eleve_id   = (isset($_POST['f_eleve']))      ? Clean::entier($_POST['f_eleve'])     : 0;
$date_debut = (isset($_POST['f_date_debut'])) ? Clean::texte($_POST['f_date_debut']) : '';
$date_fin   = (isset($_POST['f_date_fin']))   ? Clean::texte($_POST['f_date_fin'])   : '';
$devoir_id  = (isset($_POST['f_devoir']))     ? Clean::entier($_POST['f_devoir'])    : 0;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher une liste d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_evaluations') && $eleve_id && $date_debut && $date_fin )
{
	// Formater les dates
	$date_debut_mysql = convert_date_french_to_mysql($date_debut);
	$date_fin_mysql   = convert_date_french_to_mysql($date_fin);
	// Vérifier que la date de début est antérieure à la date de fin
	if($date_debut_mysql>$date_fin_mysql)
	{
		exit('Erreur : la date de début est postérieure à la date de fin !');
	}
	// Classe de l'élève
	$classe_id = 0;
	if( ($_SESSION['USER_PROFIL']=='eleve') || ( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) ) )
	{
		$classe_id = $_SESSION['ELEVE_CLASSE_ID'];
	}
	elseif( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
	{
		foreach($_SESSION['OPT_PARENT_ENFANTS'] as $tab_info)
		{
			if($tab_info['valeur']==$eleve_id)
			{
				$classe_id = $tab_info['classe_id'];
				break;
			}
		}
	}
	else
	{
		$classe_id = DB_STRUCTURE_ELEVE::DB_recuperer_classe_eleve($eleve_id);
	}
	// Lister les évaluations
	$script = '';
	$DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_devoirs_eleve($eleve_id,$classe_id,$date_debut_mysql,$date_fin_mysql);
	if(empty($DB_TAB))
	{
		exit('Aucune évaluation trouvée sur la période indiquée !');
	}
	foreach($DB_TAB as $DB_ROW)
	{
		$date_affich = convert_date_mysql_to_french($DB_ROW['devoir_date']);
		$image_sujet   = ($DB_ROW['devoir_doc_sujet'])   ? '<a href="'.$DB_ROW['devoir_doc_sujet'].'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
		$image_corrige = ($DB_ROW['devoir_doc_corrige']) ? '<a href="'.$DB_ROW['devoir_doc_corrige'].'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
		// Afficher une ligne du tableau
		echo'<tr>';
		echo	'<td><i>'.html($DB_ROW['devoir_date']).'</i>'.html($date_affich).'</td>';
		echo	'<td>'.html($DB_ROW['prof_nom'].' '.$DB_ROW['prof_prenom']{0}.'.').'</td>';
		echo	'<td>'.html($DB_ROW['devoir_info']).'</td>';
		echo	'<td>'.$image_sujet.$image_corrige.'</td>';
		echo	'<td class="nu" id="devoir_'.$DB_ROW['devoir_id'].'">';
		echo		'<q class="voir" title="Voir les items et les notes (si saisies)."></q>';
		if($DB_ROW['devoir_autoeval_date']=='0000-00-00')
		{
			echo'<q class="saisir_non" title="Devoir sans auto-évaluation."></q>';
		}
		elseif($DB_ROW['devoir_autoeval_date']<TODAY_MYSQL)
		{
			echo'<q class="saisir_non" title="Auto-évaluation terminée le '.convert_date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'."></q>';
		}
		else
		{
			echo'<q class="saisir" title="Auto-évaluation possible jusqu\'au '.convert_date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'."></q>';
			$script .=  'tab_dates['.$DB_ROW['devoir_id'].']="'.convert_date_mysql_to_french($DB_ROW['devoir_autoeval_date']).'";';
		}
		echo	'</td>';
		echo'</tr>';
	}
	echo'<SCRIPT>'.$script;
	exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Voir les notes saisies à un devoir
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Voir_notes') && $eleve_id && $devoir_id )
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_ELEVE::DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id);
	// Normalement, un devoir est toujours lié à au moins un item... sauf si l'item a été supprimé dans le référentiel !
	if(empty($DB_TAB_COMP))
	{
		exit('Ce devoir n\'est associé à aucun item !');
	}
	// Si l'élève peut formuler des demandes d'évaluations, on doit calculer le score (du coup, on choisit d'afficher le score pour tout le monde).
	$tab_liste_item = array_keys($DB_TAB_COMP);
	$liste_item_id = implode(',',$tab_liste_item);
	$tab_devoirs = array();
	$DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_result_eleve_items($eleve_id,$liste_item_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_devoirs[$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note']);
	}
	// préparer les lignes
	$tab_affich  = array();
	foreach($tab_liste_item as $item_id)
	{
		$DB_ROW = $DB_TAB_COMP[$item_id][0];
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
		$texte_lien_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
		$texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
		$score = (isset($tab_devoirs[$item_id])) ? calculer_score($tab_devoirs[$item_id],$DB_ROW['referentiel_calcul_methode'],$DB_ROW['referentiel_calcul_limite']) : FALSE ;
		$texte_demande_eval = ($_SESSION['USER_PROFIL']!='eleve') ? '' : ( ($DB_ROW['item_cart']) ? '<q class="demander_add" id="demande_'.$DB_ROW['matiere_id'].'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
		$tab_affich[$item_id] = '<tr><td>'.html($item_ref).'</td><td>'.$texte_socle.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.$texte_demande_eval.'</td><td class="hc">-</td>'.Html::td_score($score,$methode_tri='score',$pourcent='').'</tr>';
	}
	// récupérer les saisies et les ajouter
	$DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $eleve_id , FALSE /*with_REQ*/ );
	foreach($DB_TAB as $DB_ROW)
	{
		// Test pour éviter les pbs des élèves changés de groupes ou des items modifiés en cours de route
		if(isset($tab_affich[$DB_ROW['item_id']]))
		{
			$tab_affich[$DB_ROW['item_id']] = str_replace('>-<','>'.Html::note($DB_ROW['saisie_note'],'','',$tri=true).'<',$tab_affich[$DB_ROW['item_id']]);
		}
	}
	exit(implode('',$tab_affich));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir les notes d'un devoir (auto-évaluation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Saisir_notes') && $eleve_id && $devoir_id )
{
	// liste des items
	$DB_TAB_COMP = DB_STRUCTURE_ELEVE::DB_lister_items_devoir_avec_infos_pour_eleves($devoir_id);
	// Normalement, un devoir est toujours lié à au moins un item... sauf si l'item a été supprimé dans le référentiel !
	if(empty($DB_TAB_COMP))
	{
		exit('Ce devoir n\'est associé à aucun item !');
	}
	// Pas de demandes d'évaluations formulées depuis ce formulaire, pas de score affiché non plus
	$tab_liste_item = array_keys($DB_TAB_COMP);
	$liste_item_id = implode(',',$tab_liste_item);
	// boutons radio
	$tab_notes = array( 'X'=>'commun' , 'RR'=>$_SESSION['NOTE_DOSSIER'] , 'R'=>$_SESSION['NOTE_DOSSIER'] , 'V'=>$_SESSION['NOTE_DOSSIER'] , 'VV'=>$_SESSION['NOTE_DOSSIER'] );
	foreach($tab_notes as $note => $dossier)
	{
		$tab_radio_boutons[] = '<label for="item_X_'.$note.'"><input type="radio" id="item_X_'.$note.'" name="item_X" value="'.$note.'"><br /><img alt="'.$note.'" src="./_img/note/'.$dossier.'/h/'.$note.'.gif" /></label>';
	}
	$radio_boutons = '<td class="hc">'.implode('</td><td class="hc">',$tab_radio_boutons).'</td>';
	// récupérer les saisies
	$tab_radio = array();
	$DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $eleve_id , FALSE /*with_REQ*/ );
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_radio[$DB_ROW['item_id']] = str_replace( 'value="'.$DB_ROW['saisie_note'].'"' , 'value="'.$DB_ROW['saisie_note'].'" checked' , $radio_boutons );
	}
	// afficher les lignes
	foreach($tab_liste_item as $item_id)
	{
		$DB_ROW = $DB_TAB_COMP[$item_id][0];
		$item_ref = $DB_ROW['item_ref'];
		$texte_socle = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
		$texte_lien_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
		$texte_lien_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
		$boutons = (isset($tab_radio[$item_id])) ? $tab_radio[$item_id] : str_replace( 'value="X"' , 'value="X" checked' , $radio_boutons ) ;
		$boutons = str_replace( 'item_X' , 'item_'.$item_id , $boutons );
		echo'<tr>'.$boutons.'<td>'.html($item_ref).'<br />'.$texte_socle.$texte_lien_avant.html($DB_ROW['item_nom']).$texte_lien_apres.'</td></tr>';
	}
	exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer des notes saisies (auto-évaluation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Enregistrer_saisies') && $devoir_id )
{
	// On récupère les informations associées à ce devoir et on vérifie que l'élève est en droit de s'y auto-évaluer.
	$DB_ROW = DB_STRUCTURE_ELEVE::DB_recuperer_devoir_infos($devoir_id);
	if(empty($DB_ROW))
	{
		exit('Devoir introuvable !');
	}
	if($DB_ROW['devoir_autoeval_date']=='0000-00-00')
	{
		exit('Devoir sans auto-évaluation !');
	}
	if($DB_ROW['devoir_autoeval_date']<TODAY_MYSQL)
	{
		exit('Auto-évaluation terminée le '.convert_date_mysql_to_french($DB_ROW['devoir_autoeval_date']).' !');
	}
	$devoir_prof_id     = $DB_ROW['prof_id'];
	$devoir_date_mysql  = $DB_ROW['devoir_date'];
	$devoir_description = $DB_ROW['devoir_info'];
	$date_visible_mysql = $DB_ROW['devoir_visible_date'];
	$tab_profs_rss      = ($DB_ROW['devoir_partage']) ? explode(',',$DB_ROW['devoir_partage']) : array($DB_ROW['prof_id']) ;
	// Tout est transmis : il faut comparer avec le contenu de la base pour ne mettre à jour que ce dont il y a besoin
	// On récupère les notes transmises dans $tab_post
	$tab_post = array();
	foreach($_POST as $key => $val)
	{
		if(substr($key,0,5)=='item_')
		{
			$item_id = (int)substr($key,5);
			$note    = $val;
			$tab_post[$item_id] = $note;
		}
	}
	if(!count($tab_post))
	{
		exit('Aucune saisie récupérée !');
	}
	// On recupère le contenu de la base déjà enregistré pour le comparer ; on remplit au fur et à mesure $tab_nouveau_modifier / $tab_nouveau_supprimer
	// $tab_demande_supprimer sert à supprimer des demandes d'élèves dont on met une note.
	$tab_nouveau_modifier = array();
	$tab_nouveau_supprimer = array();
	$tab_demande_supprimer = array();
	$DB_TAB = DB_STRUCTURE_ELEVE::DB_lister_saisies_devoir_eleve( $devoir_id , $_SESSION['USER_ID'] , TRUE /*with_REQ*/ );
	foreach($DB_TAB as $DB_ROW)
	{
		$item_id = (int)$DB_ROW['item_id'];
		if(isset($tab_post[$item_id])) // Test nécessaire si élève ou item évalués dans ce devoir, mais retiré depuis (donc non transmis dans la nouvelle saisie, mais à conserver).
		{
			if($tab_post[$item_id]!=$DB_ROW['saisie_note'])
			{
				if($tab_post[$item_id]=='X')
				{
					// valeur de la base à supprimer
					$tab_nouveau_supprimer[$item_id] = $item_id;
				}
				else
				{
					// valeur de la base à modifier
					$tab_nouveau_modifier[$item_id] = $tab_post[$item_id];
					if($DB_ROW['saisie_note']=='REQ')
					{
						// demande d'évaluation à supprimer
						$tab_demande_supprimer[$item_id] = $item_id;
					}
				}
			}
			unset($tab_post[$item_id]);
		}
	}
	// Il reste dans $tab_post les données à ajouter (mises dans $tab_nouveau_ajouter) et les données qui ne servent pas (non enregistrées et non saisies)
	$tab_nouveau_ajouter = array_filter($tab_post,'non_note');
	// Il n'y a plus qu'à mettre à jour la base
	if( !count($tab_nouveau_ajouter) && !count($tab_nouveau_modifier) && !count($tab_nouveau_supprimer) )
	{
		exit('Aucune modification détectée !');
	}
	// L'information associée à la note comporte le nom de l'évaluation + celui de l'élève (c'est une information statique, conservée sur plusieurs années)
	$info = $devoir_description.' ('.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.)';
	foreach($tab_nouveau_ajouter as $item_id => $note)
	{
		DB_STRUCTURE_PROFESSEUR::DB_ajouter_saisie($devoir_prof_id,$_SESSION['USER_ID'],$devoir_id,$item_id,$devoir_date_mysql,$note,$info,$date_visible_mysql);
	}
	foreach($tab_nouveau_modifier as $item_id => $note)
	{
		DB_STRUCTURE_PROFESSEUR::DB_modifier_saisie($_SESSION['USER_ID'],$devoir_id,$item_id,$note,$info);
	}
	foreach($tab_nouveau_supprimer as $item_id)
	{
		DB_STRUCTURE_PROFESSEUR::DB_supprimer_saisie($_SESSION['USER_ID'],$devoir_id,$item_id);
	}
	foreach($tab_demande_supprimer as $item_id)
	{
		DB_STRUCTURE_PROFESSEUR::DB_supprimer_demande_precise($_SESSION['USER_ID'],$item_id);
	}
	// Ajout aux flux RSS des profs concernés
	$titre = 'Autoévaluation effectuée par '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']{0}.'.';
	$texte = $_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' s\'auto-évalue sur le devoir "'.$devoir_description.'"';
	$guid  = 'autoeval_'.$devoir_id.'-'.$_SESSION['USER_ID'];
	foreach($tab_profs_rss as $prof_id)
	{
		Modifier_RSS($prof_id,$titre,$texte,$guid);
	}
	exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
