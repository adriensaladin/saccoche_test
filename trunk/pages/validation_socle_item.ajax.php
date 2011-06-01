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

$action      = (isset($_POST['f_action']))  ? clean_texte($_POST['f_action'])   : '';
$pilier_id   = (isset($_POST['f_pilier']))  ? clean_entier($_POST['f_pilier'])  : 0;
$domaine_id  = (isset($_POST['f_domaine'])) ? clean_entier($_POST['f_domaine']) : 0;
$eleve_id    = (isset($_POST['f_user']))    ? clean_entier($_POST['f_user'])    : 0;
$entree_id   = (isset($_POST['f_item']))    ? clean_entier($_POST['f_item'])    : 0;
$mode        = (isset($_POST['f_mode']))    ? clean_texte($_POST['f_mode'])     : '';
$langue      = (isset($_POST['langue']))    ? clean_entier($_POST['langue'])    : 0;
$tab_eleve   = (isset($_POST['eleves']))    ? array_map('clean_entier',explode(',',$_POST['eleves']))   : array() ;
$tab_domaine = (isset($_POST['domaines']))  ? array_map('clean_entier',explode(',',$_POST['domaines'])) : array() ;
$tab_matiere = (isset($_POST['matieres']))  ? array_map('clean_entier',explode(',',$_POST['matieres'])) : array() ;

$listing_eleve_id   = implode(',',$tab_eleve);
$listing_domaine_id = implode(',',$tab_domaine);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher le tableau avec les états de validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Afficher_bilan') && $pilier_id && count($tab_domaine) && count($tab_eleve) && (in_array($mode,array('auto','manuel'))) )
{
	$affichage = '';
	// Tableau des langues
	$tfoot = '';
	require_once('./_inc/tableau_langues.php');
	$test_pilier_langue = (in_array($pilier_id,$tab_langue_piliers)) ? TRUE : FALSE ;
	// Récupérer les données des élèves
	$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($listing_eleve_id,$with_gepi=FALSE,$with_langue=TRUE);
	if(!is_array($tab_eleve))
	{
		exit('Aucun élève trouvé correspondant aux identifiants transmis !');
	}
	// Afficher la première ligne du tableau avec les étiquettes des élèves puis le nom du palier
	$tab_eleve_id     = array(); // listing des ids des élèves mis à jour au cas où la récupération dans la base soit différente des ids transmis...
	$tab_eleve_langue = array(); // listing des ids des langues, utile pour plus tard
	$affichage .= '<thead><tr>';
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_langue
		$affichage .= '<th><img id="I'.$eleve_id.'" alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'" /></th>';
		$tfoot .= '<td id="L'.$eleve_id.'" class="L'.$eleve_langue.'" title="'.$tab_langues[$eleve_langue]['texte'].'"></td>';
		$tab_eleve_langue[$eleve_id] = $eleve_langue;
		$tab_eleve_id[] = $eleve_id;
	}
	$affichage .= '<th><img alt="Tous les élèves" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode('TOUS LES ÉLÈVES').'" /></th>';
	$affichage .= '<th class="nu">&nbsp;&nbsp;&nbsp;</th>';
	$affichage .= '<th class="nu">';
	$affichage .=   '<p><input type="checkbox" id="Afficher_pourcentage" /><label for="Afficher_pourcentage"> <img alt="" src="./_img/bouton/voir.png" /> Afficher / Masquer les pourcentages d\'items d\'enseignements acquis.</label></p>';
	$affichage .=   '<p><button id="Enregistrer_validation" type="button"><img alt="" src="./_img/bouton/valider.png" /> Enregistrer les validations</button> <button id="fermer_zone_validation" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button><label id="ajax_msg_validation"></label></p>';
	$affichage .=   '<div class="m1 b">@PALIER@</div>';
	$affichage .=   '<div class="n1 b">@PILIER@</div>';
	$affichage .= '</th>';
	$affichage .= '</tr></thead>';
	$affichage .= '<tbody>';
	// Récupérer l'arborescence du pilier du socle sélectionné (éventuellement restreint à des domaines précisés)
	// Mémoriser au passage le listing des entrées du socle
	// Mémoriser au passage la liste des entrées du socle par pilier
	$tab_entree_id = array();
	$tab_pilier_entree = array();
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence_pilier($pilier_id,$listing_domaine_id);
	$pilier_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['pilier_id']!=$pilier_id)
		{
			$pilier_id  = $DB_ROW['pilier_id'];
			$section_id = 0;
			$entree_id  = 0;
		}
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			// Afficher la ligne du tableau avec les validations pour toute une section, puis le nom de la section (officiellement domaine)
			$affichage .= '<tr>';
			foreach($tab_eleve_id as $eleve_id)
			{
				$affichage .= '<th id="S'.$section_id.'U'.$eleve_id.'" class="down1" title="Modifier la validation de tout le domaine pour cet élève."></th>';
			}
			$affichage .= '<th id="S'.$section_id.'" class="diag1" title="Modifier la validation de tout le domaine pour tous les élèves."></th>';
			$affichage .= '<th class="nu" colspan="2"><div class="n2 b">'.html($DB_ROW['section_nom']).'</div></th>';
			$affichage .= '</tr>';
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
		{
			$entree_id = $DB_ROW['entree_id'];
			$tab_entree_id[] = $entree_id;
			$tab_pilier_entree[$pilier_id][] = $entree_id;
			// Afficher la ligne du tableau avec les validations des entrées, puis le nom de l'entrée (officiellement item)
			$affichage .= '<tr>';
			foreach($tab_eleve_id as $eleve_id)
			{
				$affichage .= '<td id="S'.$section_id.'U'.$eleve_id.'E'.$entree_id.'"></td>'; // class/title + lang + contenu seront ajoutés ensuite 
			}
			$affichage .= '<th id="E'.$entree_id.'" class="left1" title="Modifier la validation de cet item pour tous les élèves."></th>';
			$affichage .= '<th class="nu" colspan="2"><div class="n3">'.html($DB_ROW['entree_nom']).'</div></th>';
			$affichage .= '</tr>';
		}
	}
	$affichage .= '</tbody>';
	// Ligne avec le drapeau de la LV, si compétence concernée choisie.
	$affichage .= $test_pilier_langue ? '<tfoot>'.$tfoot.'<th class="nu" colspan="3"></th></tfoot>' : '' ;
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	// Maintenant, on prépare pour adapter le contenu des cellules en fonction des validations d'items, des validations de piliers, des % des items matières acquis
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	$tab_modif_cellule = array();	// ['html'] , ['class'] , ['title'] , ['lang']
	// Listing des élèves et des items
	$listing_eleve_id  = implode(',',$tab_eleve_id);
	$listing_entree_id = implode(',',$tab_entree_id);
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	// Récupérer la liste des résultats et calculer les scores
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	$tab_eval = array();	// [eleve_id][socle_id][item_id][]['note'] => note
	$tab_item = array();	// [item_id] => array(calcul_methode,calcul_limite);
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_palier($listing_eleve_id , $listing_entree_id , $date_debut=false , $date_fin=false , $_SESSION['USER_PROFIL']);
	foreach($DB_TAB as $DB_ROW)
	{
		$test_comptabilise = ($mode=='auto') ? ( !$test_pilier_langue || in_array($DB_ROW['matiere_id'],$tab_langues[$tab_eleve_langue[$DB_ROW['eleve_id']]]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere) ;
		if($test_comptabilise)
		{
			$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['socle_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
			$tab_item[$DB_ROW['item_id']] = array('calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}
	// Tableaux et variables pour mémoriser les infos
	$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');
	$tab_init_compet = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0); // et ensuite '%'=>
	$tab_score_socle_eleve = array();
	// Pour chaque élève...
	foreach($tab_eleve_id as $eleve_id)
	{
		// Pour chaque item du socle...
		foreach($tab_entree_id as $socle_id)
		{
			$tab_modif_cellule[$eleve_id][$socle_id] = array( 'html'=>'-' , 'class'=>' class="v2"' , 'title'=>'' , 'lang'=>'' );
			$tab_score_socle_eleve[$socle_id][$eleve_id] = $tab_init_compet;
			// Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
			if(isset($tab_eval[$eleve_id][$socle_id]))
			{
				foreach($tab_eval[$eleve_id][$socle_id] as $item_id => $tab_devoirs)
				{
					extract($tab_item[$item_id]);	// $calcul_methode $calcul_limite
					// calcul du bilan de l'item
					$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
					if($score!==false)
					{
						// on détermine si elle est acquise ou pas
						$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
						// on enregistre les infos
						$tab_score_socle_eleve[$socle_id][$eleve_id][$indice]++;
						$tab_score_socle_eleve[$socle_id][$eleve_id]['nb']++;
					}
				}
			}
		}
	}
	// On calcule les états d'acquisition à partir des A / VA / NA
	$tab_bad = array();
	$tab_bon = array();
	foreach($tab_score_socle_eleve as $socle_id=>$tab_socle_eleve)
	{
		foreach($tab_socle_eleve as $eleve_id=>$tab_scores)
		{
			if($tab_scores['nb'])
			{
				$tab_modif_cellule[$eleve_id][$socle_id]['html'] = round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0);
			}
		}
	}
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	// Récupérer la liste des jointures : validations d'entrées
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	$DB_TAB = DB_STRUCTURE_lister_jointure_user_entree($listing_eleve_id,$listing_entree_id,$domaine_id,$pilier_id,$palier_id=0);
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$etat = ($DB_ROW['validation_entree_etat']) ? 'Validé' : 'Invalidé' ;
		$tab_modif_cellule[$DB_ROW['user_id']][$DB_ROW['entree_id']]['class'] = ' class="v'.$DB_ROW['validation_entree_etat'].'"';
		$tab_modif_cellule[$DB_ROW['user_id']][$DB_ROW['entree_id']]['title'] = ' title="'.$etat.' le '.convert_date_mysql_to_french($DB_ROW['validation_entree_date']).' par '.html($DB_ROW['validation_entree_info']).'"';
	}
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	// Récupérer la liste des jointures : validations de piliers
	// - - - - - - - - - - - - - - - - - - - - - - - - -
	$DB_TAB = DB_STRUCTURE_lister_jointure_user_pilier($listing_eleve_id,$pilier_id,$palier_id=0);
	$tab_bad = array();
	$tab_bon = array();
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['validation_pilier_etat'])
		{
			foreach($tab_pilier_entree[$pilier_id] as $entree_id)
			{
				$tab_modif_cellule[$DB_ROW['user_id']][$entree_id]['lang'] = ' lang="done"';
			}
		}
	}
	// Afficher le résultat après adaptation des cellules "centrales".
	$tab_bad = array();
	$tab_bon = array();
	foreach($tab_eleve_id as $eleve_id)
	{
		foreach($tab_entree_id as $socle_id)
		{
			extract($tab_modif_cellule[$eleve_id][$socle_id]);	// $lang $class $title $html
			$tab_bad[] = 'U'.$eleve_id.'E'.$socle_id.'"></td>';
			$tab_bon[] = 'U'.$eleve_id.'E'.$socle_id.'"'.$lang.$class.$title.'>'.$html.'</td>';
		}
	}
	$affichage = str_replace($tab_bad,$tab_bon,$affichage);
	// $affichage = str_replace('class="v2"','class="v2" title="Cliquer pour valider ou invalider."',$affichage); // Retiré car embêtant si modifié ensuite.
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Afficher les informations pour aider à valider un item précis pour un élève donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='Afficher_information') && $eleve_id && $pilier_id && $entree_id && (in_array($mode,array('auto','manuel'))) )
{
	// Tableau des langues
	require_once('./_inc/tableau_langues.php');
	$test_pilier_langue = (in_array($pilier_id,$tab_langue_piliers)) ? TRUE : FALSE ;
	// Récupération de la liste des résultats
	$tab_eval = array();	// [item_id][]['note'] => note
	$tab_item = array();	// [item_id] => array(item_ref,item_nom,calcul_methode,calcul_limite);
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_palier($eleve_id , $entree_id , $date_debut=false , $date_fin=false , $_SESSION['USER_PROFIL']);
	foreach($DB_TAB as $DB_ROW)
	{
		$test_comptabilise = ($mode=='auto') ? ( !$test_pilier_langue || in_array($DB_ROW['matiere_id'],$tab_langues[$langue]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere) ;
		if($test_comptabilise)
		{
			$tab_eval[$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
			$tab_item[$DB_ROW['item_id']] = array('item_ref'=>$DB_ROW['item_ref'],'item_nom'=>$DB_ROW['item_nom'],'matiere_id'=>$DB_ROW['matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}
	// Elaboration du bilan relatif au socle : tableaux et variables pour mémoriser les infos
	$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');
	$tab_score_socle_eleve = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0); // et ensuite '%'=>
	$tab_infos_socle_eleve = array();
	// Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
	if(count($tab_eval))
	{
		foreach($tab_eval as $item_id => $tab_devoirs)
		{
			extract($tab_item[$item_id]);	// $item_ref $item_nom $matiere_id $calcul_methode $calcul_limite
			// calcul du bilan de l'item
			$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
			if($score!==false)
			{
				// on détermine si elle est acquise ou pas
				$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
				// on enregistre les infos
				$tab_infos_socle_eleve[] = html($item_ref.' || '.$item_nom).'<span class="'.$tab_etat[$indice].'">&nbsp;['.$score.'%]&nbsp;</span>';
				$tab_score_socle_eleve[$indice]++;
				$tab_score_socle_eleve['nb']++;
			}
		}
	}
	// On calcule les états d'acquisition à partir des A / VA / NA
	$tab_score_socle_eleve['%'] = ($tab_score_socle_eleve['nb']) ? round( 50 * ( ($tab_score_socle_eleve['A']*2 + $tab_score_socle_eleve['VA']) / $tab_score_socle_eleve['nb'] ) ,0) : false ;
	// Elaboration du bilan relatif au socle : mise en page, ligne de stats
	if($tab_score_socle_eleve['%']===false)
	{
		exit('Aucun item évalué n\'est relié avec cette entrée du socle !');
	}
	    if($tab_score_socle_eleve['%']<$_SESSION['CALCUL_SEUIL']['R']) {$etat = 'r';}
	elseif($tab_score_socle_eleve['%']>$_SESSION['CALCUL_SEUIL']['V']) {$etat = 'v';}
	else                                                               {$etat = 'o';}
	echo'<span class="'.$etat.'">&nbsp;'.$tab_score_socle_eleve['%'].'% acquis ('.$tab_score_socle_eleve['A'].html($_SESSION['ACQUIS_TEXTE']['A']).' '.$tab_score_socle_eleve['VA'].html($_SESSION['ACQUIS_TEXTE']['VA']).' '.$tab_score_socle_eleve['NA'].html($_SESSION['ACQUIS_TEXTE']['NA']).')&nbsp;</span>';
	// Elaboration du bilan relatif au socle : mise en page, paragraphe des items
	if( count($tab_infos_socle_eleve) )
	{
		echo'@'.implode('<br />',$tab_infos_socle_eleve);
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Enregistrer les états de validation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif($action=='Enregistrer_validation')
{
	// Récupérer les triplets {item;eleve;valid}
	$tab_valid = (isset($_POST['f_valid'])) ? explode(',',$_POST['f_valid']) : array() ;
	$tab_post = array();
	// Au passage, enregistrer la liste des items et des élèves
	$tab_eleve_id  = array();
	$tab_entree_id = array();
	foreach($tab_valid as $string_infos)
	{
		$string_infos = str_replace( array('U','E','V') , '_' , $string_infos);
		list($section,$eleve_id,$entree_id,$valid) = explode('_',$string_infos);
		$tab_post[$entree_id.'x'.$eleve_id] = (int)$valid;
		$tab_eleve_id[$eleve_id]   = $eleve_id;
		$tab_entree_id[$entree_id] = $entree_id;
	}
	if( (!count($tab_post)) || (count($tab_eleve_id)*count($tab_entree_id)!=count($tab_post)) )
	{
		exit('Erreur détectée avec les validations transmises !');
	}
	// On recupère le contenu de la base déjà enregistré pour le comparer
	$listing_eleve_id  = implode(',',$tab_eleve_id);
	$listing_entree_id = implode(',',$tab_entree_id);
	$DB_TAB = DB_STRUCTURE_lister_jointure_user_entree($listing_eleve_id,$listing_entree_id,$domaine_id=0,$pilier_id=0,$palier_id=0);
	// On remplit au fur et à mesure $tab_nouveau_modifier et $tab_nouveau_supprimer
	$tab_nouveau_modifier = array();
	$tab_nouveau_supprimer = array();
	foreach($DB_TAB as $DB_ROW)
	{
		$key = $DB_ROW['entree_id'].'x'.$DB_ROW['user_id'];
		if($tab_post[$key]==2)
		{
			// Validation présente dans la base mais annulée par le formulaire
			$tab_nouveau_supprimer[$key] = $key;
		}
		elseif($tab_post[$key]!=$DB_ROW['validation_entree_etat'])
		{
			// Validation présente dans la base mais modifiée par le formulaire
			$tab_nouveau_modifier[$key] = $tab_post[$key];
		}
		// Sinon, validation présente dans la base et confirmée par le formulaire : RAS
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
		list($entree_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_ajouter_validation('entree',$eleve_id,$entree_id,$etat,$date_mysql,$info);
	}
	foreach($tab_nouveau_modifier as $key => $etat)
	{
		list($entree_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_modifier_validation('entree',$eleve_id,$entree_id,$etat,$date_mysql,$info);
	}
	foreach($tab_nouveau_supprimer as $key)
	{
		list($entree_id,$eleve_id) = explode('x',$key);
		DB_STRUCTURE_supprimer_validation('entree',$eleve_id,$entree_id);
	}
	exit('OK');
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
