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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$type          = (isset($_POST['f_type']))       ? Clean::texte($_POST['f_type'])       : '';
$mode          = (isset($_POST['f_mode']))       ? Clean::texte($_POST['f_mode'])       : '';
$palier_id     = (isset($_POST['f_palier']))     ? Clean::entier($_POST['f_palier'])    : 0;
$palier_nom    = (isset($_POST['f_palier_nom'])) ? Clean::texte($_POST['f_palier_nom']) : '';
$groupe_id     = (isset($_POST['f_groupe']))     ? Clean::entier($_POST['f_groupe'])    : 0;
$groupe_nom    = (isset($_POST['f_groupe_nom'])) ? Clean::texte($_POST['f_groupe_nom']) : '';
$couleur       = (isset($_POST['f_couleur']))    ? Clean::texte($_POST['f_couleur'])    : '';
$legende       = (isset($_POST['f_legende']))    ? Clean::texte($_POST['f_legende'])    : '';
$marge_min     = (isset($_POST['f_marge_min']))  ? Clean::entier($_POST['f_marge_min']) : 0;
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_pilier_id  = (isset($_POST['f_pilier']))  ? ( (is_array($_POST['f_pilier']))  ? $_POST['f_pilier']  : explode(',',$_POST['f_pilier'])  ) : array() ;
$tab_eleve_id   = (isset($_POST['f_eleve']))   ? ( (is_array($_POST['f_eleve']))   ? $_POST['f_eleve']   : explode(',',$_POST['f_eleve'])   ) : array() ;
$tab_matiere_id = (isset($_POST['f_matiere'])) ? ( (is_array($_POST['f_matiere'])) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_pilier_id  = array_filter( Clean::map_entier($tab_pilier_id)  , 'positif' );
$tab_eleve_id   = array_filter( Clean::map_entier($tab_eleve_id)   , 'positif' );
$tab_matiere_id = array_filter( Clean::map_entier($tab_matiere_id) , 'positif' );

$memo_demande  = (count($tab_pilier_id)>1) ? 'palier' : 'pilier' ;
$liste_eleve   = implode(',',$tab_eleve_id);

if( (!$palier_id) || (!$palier_nom) || (!$groupe_id) || (!$groupe_nom) || (!count($tab_eleve_id)) || (!count($tab_pilier_id)) || (!in_array($type,array('pourcentage','validation'))) || (!in_array($mode,array('auto','manuel'))) || !$couleur || !$legende || !$marge_min )
{
	exit('Erreur avec les données transmises !');
}

Form::save_choix('synthese_socle');

// La récupération de beaucoup d'informations peut provoquer un dépassement de mémoire.
// Et la classe FPDF a besoin de mémoire, malgré toutes les optimisations possibles, pour générer un PDF comportant parfois entre 100 et 200 pages.
// De plus la consommation d'une classe PHP n'est pas mesurable - non comptabilisée par memory_get_usage() - et non corrélée à la taille de l'objet PDF en l'occurrence...
// Un memory_limit() de 64Mo est ainsi dépassé avec un pdf d'environ 150 pages, ce qui est atteint avec 4 pages par élèves ou un groupe d'élèves > effectif moyen d'une classe.
// D'où le ini_set(), même si cette directive peut être interdite dans la conf PHP ou via Suhosin (http://www.hardened-php.net/suhosin/configuration.html#suhosin.memory_limit)
// En complément, register_shutdown_function() permet de capter une erreur fatale de dépassement de mémoire, sauf si CGI.
// D'où une combinaison avec une détection par javascript du statusCode.

augmenter_memory_limit();
register_shutdown_function('rapporter_erreur_fatale');

$tab_pilier       = array();	// [pilier_id] => array(pilier_ref,pilier_nom,pilier_nb_entrees);
$tab_socle        = array();	// [pilier_id][socle_id] => array(section_nom,socle_nom);
$tab_entree_id    = array();	// [i] => entree_id
$tab_eleve        = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom,eleve_langue)
$tab_eval         = array();	// [eleve_id][socle_id][item_id][]['note'] => note   [type "pourcentage" uniquement]
$tab_item         = array();	// [item_id] => array(calcul_methode,calcul_limite); [type "pourcentage" uniquement]
$tab_user_entree  = array();	// [eleve_id][entree_id] => array(etat,date,info);   [type "validation" uniquement]
$tab_user_pilier  = array();	// [eleve_id][pilier_id] => array(etat,date,info);   [type "validation" uniquement]

// Tableau des langues
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues.php');
$tab_eleve_langue = array(); // id de l'élève => id de la langue
$tab_item_pilier  = array(); // id de l'item => id du pilier

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items du socle pour le ou les piliers sélectionné(s)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
$DB_TAB = ($memo_demande=='pilier') ? DB_STRUCTURE_SOCLE::DB_recuperer_arborescence_pilier($tab_pilier_id[0]) : DB_STRUCTURE_SOCLE::DB_recuperer_arborescence_piliers(implode(',',$tab_pilier_id)) ;
if(!count($DB_TAB))
{
	exit('Aucun item référencé pour cette partie du socle commun !');
}
$pilier_id  = 0;
$socle_id   = 0;
foreach($DB_TAB as $DB_ROW)
{
	if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
	{
		$pilier_id  = $DB_ROW['pilier_id'];
		$tab_pilier[$pilier_id] = array('pilier_ref'=>$DB_ROW['pilier_ref'],'pilier_nom'=>$DB_ROW['pilier_nom'],'pilier_nb_entrees'=>0);
	}
	if(!is_null($DB_ROW['section_id']))
	{
		$section_nom = $DB_ROW['section_nom'];
	}
	if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
	{
		$socle_id = $DB_ROW['entree_id'];
		$tab_socle[$pilier_id][$socle_id] = $section_nom.' » '.$DB_ROW['entree_nom'];
		$tab_pilier[$pilier_id]['pilier_nb_entrees']++;
		$tab_entree_id[] = $socle_id;
		if( ($type=='pourcentage') && ($mode=='auto') )
		{
			$tab_item_pilier[$socle_id] = $pilier_id;
		}
	}
}
$listing_entree_id = implode(',',$tab_entree_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($liste_eleve,$with_gepi=FALSE,$with_langue=TRUE);
if( ($type=='pourcentage') && ($mode=='auto') )
{
	foreach($tab_eleve as $key => $tab)
	{
		$tab_eleve_langue[$tab['eleve_id']] = $tab['eleve_langue'];
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des résultats [type "pourcentage" uniquement]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($type=='pourcentage')
{
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_palier_sans_infos_items($liste_eleve , $listing_entree_id , $_SESSION['USER_PROFIL']);
	foreach($DB_TAB as $DB_ROW)
	{
		$test_comptabilise = ($mode=='auto') ? ( !in_array($tab_item_pilier[$DB_ROW['socle_id']],$tab_langue_piliers) || in_array($DB_ROW['matiere_id'],$tab_langues[$tab_eleve_langue[$DB_ROW['eleve_id']]]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere_id) ;
		if($test_comptabilise)
		{
			$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['socle_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
			$tab_item[$DB_ROW['item_id']] = TRUE;
		}
	}
	if(count($tab_item))
	{
		$listing_item_id = implode(',',array_keys($tab_item));
		$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_infos_items($listing_item_id,$detail=FALSE);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_item[$DB_ROW['item_id']] = array('calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}
}

// Ces tableaux ne servent plus
unset($tab_item_pilier,$tab_eleve_langue);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des validations [type "validation" uniquement]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($type=='validation')
{
	// On commence par remplir tout le tableau des items pour ne pas avoir ensuite à tester tout le temps si le champ existe
	foreach($tab_eleve_id as $eleve_id)
	{
		foreach($tab_entree_id as $entree_id)
		{
			$tab_user_entree[$eleve_id][$entree_id] = array('etat'=>2,'date'=>'','info'=>'');
		}
	}
	// Maintenant on complète avec les valeurs de la base
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_entree($liste_eleve,$listing_entree_id,$domaine_id=0,$pilier_id=0,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les entrées
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_user_entree[$DB_ROW['user_id']][$DB_ROW['entree_id']] = array('etat'=>$DB_ROW['validation_entree_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_entree_date']),'info'=>$DB_ROW['validation_entree_info']);
	}
	// On commence par remplir tout le tableau des piliers pour ne pas avoir ensuite à tester tout le temps si le champ existe
	foreach($tab_eleve_id as $eleve_id)
	{
		foreach($tab_pilier as $pilier_id => $tab)
		{
			$tab_user_pilier[$eleve_id][$pilier_id] = array('etat'=>2,'date'=>'','info'=>'');
		}
	}
	// Maintenant on complète avec les valeurs de la base
	$listing_pilier_id = implode(',',array_keys($tab_pilier));
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_pilier($liste_eleve,$listing_pilier_id,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les piliers
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_user_pilier[$DB_ROW['user_id']][$DB_ROW['pilier_id']] = array('etat'=>$DB_ROW['validation_pilier_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_pilier_date']),'info'=>$DB_ROW['validation_pilier_info']);
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration de la synthèse de maîtrise du socle, en HTML et PDF => Tableaux, variables, calculs (aucun affichage). [type "pourcentage" uniquement]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($type=='pourcentage')
{
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
			$tab_score_socle_eleve[$socle_id][$eleve_id] = $tab_init_compet;
			// Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
			if(isset($tab_eval[$eleve_id][$socle_id]))
			{
				foreach($tab_eval[$eleve_id][$socle_id] as $item_id => $tab_devoirs)
				{
					extract($tab_item[$item_id]);	// $calcul_methode $calcul_limite
					// calcul du bilan de l'item
					$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
					if($score!==FALSE)
					{
						// on détermine si elle est acquise ou pas
						$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
						// on enregistre les infos
						$tab_score_socle_eleve[$socle_id][$eleve_id][$indice]++;
						$tab_score_socle_eleve[$socle_id][$eleve_id]['nb']++;
					}
				}
			}
			// On calcule les états d'acquisition à partir des A / VA / NA
			$tab_score_socle_eleve[$socle_id][$eleve_id]['%'] = ($tab_score_socle_eleve[$socle_id][$eleve_id]['nb']) ? round( 50 * ( ($tab_score_socle_eleve[$socle_id][$eleve_id]['A']*2 + $tab_score_socle_eleve[$socle_id][$eleve_id]['VA']) / $tab_score_socle_eleve[$socle_id][$eleve_id]['nb'] ) ,0) : FALSE ;
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration de la synthèse de maîtrise du socle, en HTML et PDF => Production et mise en page
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$affichage_direct   = ( ( in_array($_SESSION['USER_PROFIL'],array('eleve','parent')) ) && (SACoche!='webservices') ) ? TRUE : FALSE ;
$affichage_checkbox = ( ($_SESSION['USER_PROFIL']=='professeur') && (SACoche!='webservices') ) ? TRUE : FALSE ;

$eleves_nb   = count($tab_eleve_id);
$items_nb    = count($tab_entree_id);
$piliers_nb  = count($tab_pilier);
$cellules_nb = $items_nb+1;
$cellules_nb+= ($affichage_checkbox) ? 1 : 0 ;
$titre_info1 = ($type=='pourcentage') ? 'pourcentage d\'items disciplinaires acquis' : 'validation des items et des compétences' ;
$titre_info1.= ( ($type=='pourcentage') && ($mode=='manuel') ) ? ' [matières resteintes]' : '' ;
$titre_info2 = ($memo_demande=='palier') ? $palier_nom : $palier_nom.' – '.mb_substr($tab_pilier[$pilier_id]['pilier_nom'],0,mb_strpos($tab_pilier[$pilier_id]['pilier_nom'],'–')) ;
$releve_html  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>';
$releve_html .= $affichage_direct ? '' : '<style type="text/css">thead th{text-align:center}tbody th,tbody td{width:8px;height:8px;vertical-align:middle}.nu2{background:#EAEAFF;border:none;}	/* classe existante nu non utilisée à cause de son height imposé */</style>';
$releve_html .= $affichage_direct ? '' : '<h1>Synthèse de maîtrise du socle : '.$titre_info1.'</h1>';
$releve_html .= $affichage_direct ? '' : '<h2>'.To::html($groupe_nom).' - '.To::html($titre_info2).'</h2>';
// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
$releve_pdf = new PDF( FALSE /*officiel*/ , 'landscape' /*orientation*/ , $marge_min /*marge_gauche*/ , $marge_min /*marge_droite*/ , $marge_min /*marge_haut*/ , $marge_min /*marge_bas*/ , $couleur , $legende );
$releve_pdf->releve_synthese_socle_initialiser($titre_info1,$groupe_nom,$titre_info2,$eleves_nb,$items_nb,$piliers_nb);
// - - - - - - - - - -
// Lignes d'en-tête
// - - - - - - - - - -
$releve_html_head = '<tr><td class="nu2" rowspan="2"></td>';
$releve_html_head.= ($affichage_checkbox) ? '<td class="nu2" rowspan="2"></td>' : '' ;
foreach($tab_pilier as $tab)
{
	extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
	$texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
	$releve_html_head .= '<th class="nu2"></th><th colspan="'.$pilier_nb_entrees.'" title="'.To::html($pilier_nom).'">'.$texte.$pilier_ref.'</th>';
}
$releve_html_head .= '</tr><tr>';
foreach($tab_socle as $tab)
{
	$releve_html_head .= '<th class="nu2"></th>';
	foreach($tab as $socle_nom)
	{
		$releve_html_head .= '<th class="info" title="'.To::html($socle_nom).'"></th>';
	}
}
$releve_html_head .= '</tr>';
$releve_pdf->releve_synthese_socle_entete($tab_pilier);
// - - - - - - - - - -
// Lignes suivantes
// - - - - - - - - - -
$releve_html_body = '';
// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_langue
	$drapeau_langue = count(array_intersect($tab_pilier_id,$tab_langue_piliers)) ? $eleve_langue : 0 ;
	$image_langue = ($drapeau_langue) ? '<img src="./_img/drapeau/'.$drapeau_langue.'.gif" alt="" title="'.$tab_langues[$drapeau_langue]['texte'].'" /> ' : '' ;
	$releve_html_body .= '<tr><td class="nu2" colspan="'.$cellules_nb.'" style="height:4px"></td></tr>';
	if($type=='pourcentage')
	{
		// - - - - -
		// Indication des pourcentages
		// - - - - -
		$checkbox = ($affichage_checkbox) ? '<th class="nu2"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></th>' : '' ;
		$releve_html_body .= '<tr>'.$checkbox.'<th>'.$image_langue.To::html($eleve_nom.' '.$eleve_prenom).'</th>';
		// Pour chaque entrée du socle...
		foreach($tab_socle as $pilier_id => $tab)
		{
			$releve_html_body .= '<td class="nu2"></td>';
			foreach($tab as $socle_id => $socle_nom)
			{
				$releve_html_body .= Html::td_pourcentage( 'td' , $tab_score_socle_eleve[$socle_id][$eleve_id] , FALSE /*detail*/ , FALSE /*largeur*/ );
			}
		}
		$releve_html_body .= '</tr>';
		$releve_pdf->releve_synthese_socle_pourcentage_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_score_socle_eleve,$tab_socle,$drapeau_langue);
	}
	if($type=='validation')
	{
		// - - - - -
		// Indication des compétences validées
		// - - - - -
		$checkbox = ($affichage_checkbox) ? '<th class="nu" rowspan="2"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></th>' : '' ;
		$releve_html_body .= '<tr>'.$checkbox.'<th rowspan="2">'.$image_langue.To::html($eleve_nom.' '.$eleve_prenom).'</th>';
		// Pour chaque pilier...
		foreach($tab_pilier as $pilier_id => $tab)
		{
			extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
			$releve_html_body .= '<td class="nu2"></td>';
			$releve_html_body .= Html::td_validation( 'td' , $tab_user_pilier[$eleve_id][$pilier_id] , $detail=false , $etat_pilier=false , $colspan=$pilier_nb_entrees );
		}
		$releve_html_body .= '</tr><tr>';
		// - - - - -
		// Indication des items validés
		// - - - - -
		// Pour chaque entrée du socle...
		foreach($tab_socle as $pilier_id => $tab)
		{
			$releve_html_body .= '<td class="nu2"></td>';
			foreach($tab as $socle_id => $socle_nom)
			{
				$releve_html_body .= Html::td_validation( 'td' , $tab_user_entree[$eleve_id][$socle_id] , $detail=false , $tab_user_pilier[$eleve_id][$pilier_id]['etat'] );
			}
		}
		$releve_html_body .= '</tr>';
		$releve_pdf->releve_synthese_socle_validation_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_user_pilier,$tab_user_entree,$tab_pilier,$tab_socle,$drapeau_langue);
	}
}
$releve_html .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">' : '' ;
$releve_html .= '<table class="bilan"><thead>'.$releve_html_head.'</thead><tbody>'.$releve_html_body.'</tbody></table>';
$releve_html .= ($affichage_checkbox) ? '<p><label class="tab">Action <img alt="" src="./_img/bulle_aide.png" title="Cocher auparavant les cases adéquates." /> :</label><button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=evaluation_gestion\';form.submit();">Préparer une évaluation.</button> <button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=professeur_groupe_besoin\';form.submit();">Constituer un groupe de besoin.</button></p></form>' : '';
$releve_html .= Html::legende( FALSE /*codes_notation*/ , FALSE /*etat_acquisition*/ , ($type=='pourcentage') /*pourcentage_acquis*/ , ($type=='validation') /*etat_validation*/ );
$releve_pdf->releve_synthese_socle_legende($legende,$type);

// Chemins d'enregistrement
$fichier = 'releve_socle_synthese_'.Clean::fichier(substr($palier_nom,0,strpos($palier_nom,' ('))).'_'.Clean::fichier($groupe_nom).'_'.$type.'_'.fabriquer_fin_nom_fichier__date_et_alea();
// On enregistre les sorties HTML et PDF
FileSystem::ecrire_fichier(CHEMIN_DOSSIER_EXPORT.$fichier.'.html',$releve_html);
$releve_pdf->Output(CHEMIN_DOSSIER_EXPORT.$fichier.'.pdf','F');
// Affichage du résultat
if($affichage_direct)
{
	echo'<hr />';
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fichier.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'</ul>';
	echo $releve_html;
}
else
{
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fichier.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier.'"><span class="file file_htm">Explorer / Détailler (format <em>html</em>).</span></a></li>';
	echo'</ul>';
}

?>
