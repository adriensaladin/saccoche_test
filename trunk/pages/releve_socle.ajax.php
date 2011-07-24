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

$palier_id     = (isset($_POST['f_palier']))     ? clean_entier($_POST['f_palier'])    : 0;
$palier_nom    = (isset($_POST['f_palier_nom'])) ? clean_texte($_POST['f_palier_nom']) : '';
$pilier_id     = (isset($_POST['f_pilier']))     ? clean_entier($_POST['f_pilier'])    : -1;
$aff_socle_PA  = (isset($_POST['f_socle_PA']))   ? 1                                   : 0;	// en cas de manipulation type Firebug, peut être forcé pour élève/parent avec (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL']))
$aff_socle_EV  = (isset($_POST['f_socle_EV']))   ? 1                                   : 0;	// en cas de manipulation type Firebug, peut être forcé pour élève/parent avec (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION'],$_SESSION['USER_PROFIL']))
$groupe_id     = (isset($_POST['f_groupe']))     ? clean_entier($_POST['f_groupe'])    : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à $_SESSION['ELEVE_CLASSE_ID']
$mode          = (isset($_POST['f_mode']))       ? clean_texte($_POST['f_mode'])       : '';
$aff_coef      = (isset($_POST['f_coef']))       ? 1                                   : 0;
$aff_socle     = (isset($_POST['f_socle']))      ? 1                                   : 0;
$aff_lien      = (isset($_POST['f_lien']))       ? 1                                   : 0;
$tab_pilier_id = (isset($_POST['piliers']))      ? array_map('clean_entier',explode(',',$_POST['piliers']))  : array() ;
$tab_eleve_id  = (isset($_POST['eleves']))       ? array_map('clean_entier',explode(',',$_POST['eleves']))   : array() ;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec $_SESSION['USER_ID']
$tab_matiere   = (isset($_POST['matieres']))     ? array_map('clean_entier',explode(',',$_POST['matieres'])) : array() ;

$memo_demande  = (count($tab_pilier_id)>1) ? 'palier' : 'pilier' ;
$tab_eleve_id  = array_filter($tab_eleve_id,'positif');
$liste_eleve   = implode(',',$tab_eleve_id);

$test_affichage_Pourcentage = ($groupe_id && count($tab_eleve_id) && $aff_socle_PA) ? true : false;
$test_affichage_Validation  = ($groupe_id && count($tab_eleve_id) && $aff_socle_EV) ? true : false;

if( (!$palier_id) || (!$palier_nom) || (!count($tab_pilier_id)) || (!in_array($mode,array('auto','manuel'))) )
{
	exit('Erreur avec les données transmises !');
}

save_cookie_select('palier');

// Permet d'avoir des informations accessibles en cas d'erreur type « PHP Fatal error : Allowed memory size of ... bytes exhausted ».
// ajouter_log_PHP( $log_objet='Demande de bilan' , $log_contenu=serialize($_POST) , $log_fichier=__FILE__ , $log_ligne=__LINE__ , $only_sesamath=true );

$tab_pilier       = array();	// [pilier_id] => array(pilier_nom,pilier_nb_lignes);
$tab_section      = array();	// [pilier_id][section_id] => section_nom;
$tab_socle        = array();	// [section_id][socle_id] => socle_nom;
$tab_entree_id    = array();	// [i] => entree_id
$tab_eleve        = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom,eleve_langue)
$tab_eval         = array();	// [eleve_id][socle_id][item_id][]['note'] => note
$tab_item         = array();	// [item_id] => array(item_ref,item_nom,item_cart,matiere_id,calcul_methode,calcul_limite);
$tab_user_entree  = array();	// [eleve_id][entree_id] => array(etat,date,info);
$tab_user_pilier  = array();	// [eleve_id][pilier_id] => array(etat,date,info);

// Tableau des langues
require_once('./_inc/tableau_langues.php');
$tab_eleve_langue = array(); // id de l'élève => id de la langue
$tab_item_pilier  = array(); // id de l'item => id du pilier

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items du socle pour le palier ou le pilier sélectionné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
$DB_TAB = ($memo_demande=='pilier') ? DB_STRUCTURE_recuperer_arborescence_pilier($pilier_id) : DB_STRUCTURE_recuperer_arborescence_palier($palier_id,implode(',',$tab_pilier_id)) ;
if(!count($DB_TAB))
{
	exit('Aucun item référencé pour cette partie du socle commun !');
}
$pilier_id  = 0;
$section_id = 0;
$socle_id   = 0;
foreach($DB_TAB as $DB_ROW)
{
	if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
	{
		$pilier_id  = $DB_ROW['pilier_id'];
		$tab_pilier[$pilier_id] = array('pilier_nom'=>$DB_ROW['pilier_nom'],'pilier_nb_lignes'=>1);
	}
	if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
	{
		$section_id  = $DB_ROW['section_id'];
		$tab_section[$pilier_id][$section_id] = $DB_ROW['section_nom'];
		$tab_pilier[$pilier_id]['pilier_nb_lignes']++;
	}
	if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
	{
		$socle_id = $DB_ROW['entree_id'];
		$tab_socle[$section_id][$socle_id] = $DB_ROW['entree_nom'];
		$tab_pilier[$pilier_id]['pilier_nb_lignes']++;
		$tab_entree_id[] = $socle_id;
		if($mode=='auto')
		{
			$tab_item_pilier[$socle_id] = $pilier_id;
		}
	}
}
$listing_entree_id = implode(',',$tab_entree_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des élèves (si demandé)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_eleve[] = array('eleve_id'=>$_SESSION['USER_ID'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM'],'eleve_langue'=>$_SESSION['ELEVE_LANGUE']);
	$tab_eleve_langue[$_SESSION['USER_ID']] = $_SESSION['ELEVE_LANGUE'];
}
elseif($groupe_id && count($tab_eleve_id))
{
	$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($liste_eleve,$with_gepi=FALSE,$with_langue=TRUE);
	if($mode=='auto')
	{
		foreach($tab_eleve as $key => $tab)
		{
			$tab_eleve_langue[$tab['eleve_id']] = $tab['eleve_langue'];
		}
	}
}
else
{
	$tab_eleve[] = array('eleve_id'=>0,'eleve_nom'=>'','eleve_prenom'=>'','eleve_langue'=>0);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des résultats (si pas fiche générique)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if($groupe_id && count($tab_eleve_id))
{
	$DB_TAB = DB_STRUCTURE_lister_result_eleves_palier_sans_infos_items($liste_eleve , $listing_entree_id , $_SESSION['USER_PROFIL']);
	foreach($DB_TAB as $DB_ROW)
	{
		$test_comptabilise = ($mode=='auto') ? ( !in_array($tab_item_pilier[$DB_ROW['socle_id']],$tab_langue_piliers) || in_array($DB_ROW['matiere_id'],$tab_langues[$tab_eleve_langue[$DB_ROW['eleve_id']]]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere) ;
		if($test_comptabilise)
		{
			$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['socle_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
			$tab_item[$DB_ROW['item_id']] = TRUE;
		}
	}
	if(count($tab_item))
	{
		$listing_item_id = implode(',',array_keys($tab_item));
		$DB_TAB = DB_STRUCTURE_lister_infos_items($listing_item_id,$detail=TRUE);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_item[$DB_ROW['item_id']] = array('item_ref'=>$DB_ROW['item_ref'],'item_nom'=>$DB_ROW['item_nom'],'item_coef'=>$DB_ROW['item_coef'],'item_cart'=>$DB_ROW['item_cart'],'item_socle'=>$DB_ROW['socle_id'],'item_lien'=>$DB_ROW['item_lien'],'matiere_id'=>$DB_ROW['matiere_id'],'calcul_methode'=>$DB_ROW['calcul_methode'],'calcul_limite'=>$DB_ROW['calcul_limite']);
		}
	}
}

// Ces tableaux ne servent plus
unset($tab_item_pilier,$tab_eleve_langue);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des validations (si demandé)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if($test_affichage_Validation)
{
	// On commence par remplir tout le tableau des items pour ne pas avoir ensuite à tester tout le temps si le champ existe
	foreach($tab_eleve_id as $eleve_id)
	{
		foreach($tab_entree_id as $entree_id)
		{
			$tab_user_entree[$eleve_id][$entree_id] = array('etat'=>2,'date'=>'','info'=>'');
		}
	}
	//Maintenant on complète avec les valeurs de la base
	$DB_TAB = DB_STRUCTURE_lister_jointure_user_entree($liste_eleve,$listing_entree_id,$domaine_id=0,$pilier_id=0,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les entrées
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
	//Maintenant on complète avec les valeurs de la base
	$listing_pilier_id = implode(',',array_keys($tab_pilier));
	$DB_TAB = DB_STRUCTURE_lister_jointure_user_pilier($liste_eleve,$listing_pilier_id,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les piliers
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
// Elaboration du bilan relatif au socle, en HTML et PDF => Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if(!$aff_coef)  { $texte_coef       = ''; }
if(!$aff_socle) { $texte_socle      = ''; }
if(!$aff_lien)  { $texte_lien_avant = ''; }
if(!$aff_lien)  { $texte_lien_apres = ''; }

$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');
$tab_init_compet = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0);
//	$tab_score_pilier_eleve  = array();	// [pilier_id][eleve_id] => array(A,VA,NA,nb,%)  // Retenir le nb d'items acquis ou pas / pilier / élève
//	$tab_score_section_eleve = array();	// [section_id][eleve_id] => array(A,VA,NA,nb,%) // Retenir le nb d'items acquis ou pas / section / élève
$tab_score_socle_eleve   = array();	// [socle_id][eleve_id] => array(A,VA,NA,nb,%)   // Retenir le nb d'items acquis ou pas / item / élève
$tab_infos_socle_eleve   = array();	// [socle_id][eleve_id] => array()               // Retenir les infos sur les items travaillés et leurs scores / item du socle / élève

// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
	// Pour chaque pilier...
	if(count($tab_pilier))
	{
		foreach($tab_pilier as $pilier_id => $tab)
		{
			extract($tab);	// $pilier_nom $pilier_nb_lignes
			// $tab_score_pilier_eleve[$pilier_id][$eleve_id] = $tab_init_compet;
			// Pour chaque section...
			if(isset($tab_section[$pilier_id]))
			{
				foreach($tab_section[$pilier_id] as $section_id => $section_nom)
				{
					// $tab_score_section_eleve[$section_id][$eleve_id] = $tab_init_compet;
					// Pour chaque item du socle...
					if(isset($tab_socle[$section_id]))
					{
						foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
						{
							$tab_score_socle_eleve[$socle_id][$eleve_id] = $tab_init_compet;
							$tab_infos_socle_eleve[$socle_id][$eleve_id] = array();
							// Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
							if(isset($tab_eval[$eleve_id][$socle_id]))
							{
								foreach($tab_eval[$eleve_id][$socle_id] as $item_id => $tab_devoirs)
								{
									extract($tab_item[$item_id]);	// $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $matiere_id $calcul_methode $calcul_limite
									// calcul du bilan de l'item
									$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
									if($score!==false)
									{
										// le détail HTML
										if($aff_coef)
										{
											$texte_coef = '['.$item_coef.'] ';
										}
										if($aff_socle)
										{
											$texte_socle = ($item_socle) ? '[S] ' : '[–] ';
										}
										if($aff_lien)
										{
											$texte_lien_avant = ($item_lien) ? '<a class="lien_ext" href="'.html($item_lien).'">' : '';
											$texte_lien_apres = ($item_lien) ? '</a>' : '';
										}
										// on détermine si elle est acquise ou pas
										$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
										// on enregistre les infos
										$texte_demande_eval = ($_SESSION['USER_PROFIL']!='eleve') ? '' : ( ($item_cart) ? '<q class="demander_add" lang="ids_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
										$tab_infos_socle_eleve[$socle_id][$eleve_id][] = '<span class="'.$tab_etat[$indice].'">'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_ref.' || '.$item_nom.' ['.$score.'%]').'</span>'.$texte_lien_apres.$texte_demande_eval;
										$tab_score_socle_eleve[$socle_id][$eleve_id][$indice]++;
										$tab_score_socle_eleve[$socle_id][$eleve_id]['nb']++;
										// $tab_score_section_eleve[$section_id][$eleve_id][$indice]++;
										// $tab_score_section_eleve[$section_id][$eleve_id]['nb']++;
										// $tab_score_pilier_eleve[$pilier_id][$eleve_id][$indice]++;
										// $tab_score_pilier_eleve[$pilier_id][$eleve_id]['nb']++;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

// On calcule les états d'acquisition à partir des A / VA / NA

if($test_affichage_Pourcentage)
{
	// Pour les piliers
	// foreach($tab_score_pilier_eleve as $pilier_id=>$tab_pilier_eleve)
	// {
	// 	foreach($tab_pilier_eleve as $eleve_id=>$tab_scores)
	// 	{
	// 		$tab_score_pilier_eleve[$pilier_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
	// 	}
	// }
	// Pour les sections
	// foreach($tab_score_section_eleve as $section_id=>$tab_section_eleve)
	// {
	// 	foreach($tab_section_eleve as $eleve_id=>$tab_scores)
	// 	{
	// 		$tab_score_section_eleve[$section_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
	// 	}
	// }
	// Pour les items du socle
	foreach($tab_score_socle_eleve as $socle_id=>$tab_socle_eleve)
	{
		foreach($tab_socle_eleve as $eleve_id=>$tab_scores)
		{
			$tab_score_socle_eleve[$socle_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration du bilan relatif au socle, en HTML et PDF => Production et mise en page
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$titre1 = ($mode=='manuel') ? 'Détail de maîtrise du socle commun [matières resteintes]' : 'Détail de maîtrise du socle commun' ;
$titre2 = ($memo_demande=='palier') ? $palier_nom : $palier_nom.' – '.mb_substr($pilier_nom,0,mb_strpos($pilier_nom,'–')) ;
$break = ($memo_demande=='palier') ? 0 : $tab_pilier[$pilier_id]['pilier_nb_lignes'] ;
$releve_html  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
$releve_html .= '<h1>'.html($titre1).'</h1>';
$releve_html .= '<h2>'.html($titre2).'</h2>';
// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
require('./_lib/FPDF/fpdf.php');
require('./_inc/class.PDF.php');
$releve_pdf = new PDF($orientation='portrait',$marge_min=7.5,$couleur='oui');
$releve_pdf->releve_socle_initialiser($test_affichage_Pourcentage,$test_affichage_Validation);

// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_langue
	// On met le document au nom de l'élève, ou on établit un document générique
	$releve_pdf->releve_socle_entete($titre1,$titre2,$break,$eleve_id,$eleve_nom,$eleve_prenom);
	$releve_html .= ($eleve_id) ? '<hr /><h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : '<hr /><h2>Attestation générique</h2>' ;
	$releve_html .= '<table class="bilan">';
	// Pour chaque pilier...
	if(count($tab_pilier))
	{
		foreach($tab_pilier as $pilier_id => $tab)
		{
			extract($tab);	// $pilier_nom $pilier_nb_lignes
			$drapeau_langue = (in_array($pilier_id,$tab_langue_piliers)) ? $eleve_langue : 0 ;
			$case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
			$case_valid = $test_affichage_Validation ? affich_validation_html( 'th' , $tab_user_pilier[$eleve_id][$pilier_id] , $detail=true ) : '' ;
			$image_langue = ($drapeau_langue) ? ' <img src="./_img/drapeau/'.$drapeau_langue.'.gif" alt="" title="'.$tab_langues[$drapeau_langue]['texte'].'" />' : '' ;
			$releve_html .= '<tr>'.$case_score.'<th>'.html($pilier_nom).$image_langue.'</th>'.$case_valid.'<th class="nu"></th></tr>'."\r\n";
			$tab_pilier_validation = $test_affichage_Validation ? $tab_user_pilier[$eleve_id][$pilier_id] : array() ;
			$releve_pdf->releve_socle_pilier($pilier_nom,$pilier_nb_lignes,$test_affichage_Validation,$tab_pilier_validation,$drapeau_langue);
			// Pour chaque section...
			if(isset($tab_section[$pilier_id]))
			{
				foreach($tab_section[$pilier_id] as $section_id => $section_nom)
				{
					$case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
					$case_valid = '<th class="nu"></th>' ;
					$releve_html .= '<tr>'.$case_score.'<th colspan="2">'.html($section_nom).'</th>'.$case_valid.'</tr>'."\r\n";
					$releve_pdf->releve_socle_section($section_nom);
					// Pour chaque item du socle...
					if(isset($tab_socle[$section_id]))
					{
						foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
						{
							$tab_item_pourcentage = $test_affichage_Pourcentage ? $tab_score_socle_eleve[$socle_id][$eleve_id] : array() ;
							$tab_item_validation  = $test_affichage_Validation ? $tab_user_entree[$eleve_id][$socle_id] : array() ;
							$releve_pdf->releve_socle_item($socle_nom,$test_affichage_Pourcentage,$tab_item_pourcentage,$test_affichage_Validation,$tab_item_validation);
							$socle_nom  = html($socle_nom);
							$socle_nom  = (mb_strlen($socle_nom)<160) ? $socle_nom : mb_substr($socle_nom,0,150).' [...] <img src="./_img/puce_astuce.png" alt="" title="'.$socle_nom.'" />';
							if( $tab_infos_socle_eleve[$socle_id][$eleve_id] )
							{
								$lien_toggle = '<a href="#" lang="'.$socle_id.'_'.$eleve_id.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer le détail des items associés." class="toggle" /></a> ';
								$div_competences = '<div id="'.$socle_id.'_'.$eleve_id.'" class="hide">'.implode('<br />',$tab_infos_socle_eleve[$socle_id][$eleve_id]).'</div>';
							}
							else
							{
								$lien_toggle = '<img src="./_img/toggle_none.gif" alt="" /> ';
								$div_competences = '';
							}
							$case_score = $test_affichage_Pourcentage ? affich_pourcentage_html( 'td' , $tab_score_socle_eleve[$socle_id][$eleve_id] , $detail=true) : '' ;
							$case_valid = $test_affichage_Validation ? affich_validation_html( 'td' , $tab_user_entree[$eleve_id][$socle_id] , $detail=true ) : '' ;
							$releve_html .= '<tr>'.$case_score.'<td colspan="2">'.$lien_toggle.$socle_nom.$div_competences.'</td>'.$case_valid.'</tr>'."\r\n";
						}
					}
				}
			}
			$releve_html .= '<tr><td colspan="4" class="nu"></td></tr>'."\r\n";
		}
	}
	$releve_html .= '</table><p />';
}

// Chemins d'enregistrement
$dossier      = './__tmp/export/';
$fichier_lien = 'releve_socle_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();
// On enregistre les sorties HTML et PDF
Ecrire_Fichier($dossier.$fichier_lien.'.html',$releve_html);
$releve_pdf->Output($dossier.$fichier_lien.'.pdf','F');
// Affichage du résultat
if($_SESSION['USER_PROFIL']=='eleve')
{
	echo'<ul class="puce">';
	echo'<li><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Archiver / Imprimer (format <em>pdf</em>).</a></label></li>';
	echo'</ul><p />';
	echo $releve_html;
}
else
{
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Archiver / Imprimer (format <em>pdf</em>).</a></li>';
	echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Explorer / Détailler (format <em>html</em>).</a></li>';
	echo'</ul><p />';
}

?>
