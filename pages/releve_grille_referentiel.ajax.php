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

$matiere_id    = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$niveau_id     = (isset($_POST['f_niveau']))      ? clean_entier($_POST['f_niveau'])     : 0;
$matiere_nom   = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$niveau_nom    = (isset($_POST['f_niveau_nom']))  ? clean_texte($_POST['f_niveau_nom'])  : '';
$remplissage   = (isset($_POST['f_remplissage'])) ? clean_texte($_POST['f_remplissage']) : '';
$orientation   = (isset($_POST['f_orientation'])) ? clean_texte($_POST['f_orientation']) : '';
$marge_min     = (isset($_POST['f_marge_min']))   ? clean_texte($_POST['f_marge_min'])   : '';
$couleur       = (isset($_POST['f_couleur']))     ? clean_texte($_POST['f_couleur'])     : '';
$cases_nb      = (isset($_POST['f_cases_nb']))    ? clean_entier($_POST['f_cases_nb'])   : 0;
$cases_largeur = (isset($_POST['f_cases_larg']))  ? clean_entier($_POST['f_cases_larg']) : 0;
$cases_hauteur = (isset($_POST['f_cases_haut']))  ? clean_entier($_POST['f_cases_haut']) : 0;
$only_socle    = (isset($_POST['f_restriction'])) ? 1                                    : 0;
$aff_coef      = (isset($_POST['f_coef']))        ? 1                                    : 0;
$aff_socle     = (isset($_POST['f_socle']))       ? 1                                    : 0;
$aff_lien      = (isset($_POST['f_lien']))        ? 1                                    : 0;
$groupe_id     = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à $_SESSION['ELEVE_CLASSE_ID']
$tab_eleve_id  = (isset($_POST['eleves']))        ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec $_SESSION['USER_ID']

save_cookie_select($_SESSION['BASE'],$_SESSION['USER_ID']);

$tab_eleve_id  = array_filter($tab_eleve_id,'positif');
$liste_eleve   = implode(',',$tab_eleve_id);

if( $matiere_id && $niveau_id && $matiere_nom && $niveau_nom && $remplissage && $orientation && $marge_min && $couleur && $cases_nb && $cases_largeur && $cases_hauteur )
{

	ajouter_log_PHP( $log_objet='Demande de bilan' , $log_contenu=serialize($_POST) , $log_fichier=__FILE__ , $log_ligne=__LINE__ , $only_sesamath=true );

	$tab_domaine    = array();	// [domaine_id] => array(domaine_ref,domaine_nom,domaine_nb_lignes);
	$tab_theme      = array();	// [domaine_id][theme_id] => array(theme_ref,theme_nom,theme_nb_lignes);
	$tab_item       = array();	// [theme_id][item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_eval       = array();	// [eleve_id][item_id] => array(note,date,info)

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items pour la matière et le niveau sélectionné
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_socle,$only_item=false,$socle_nom=false);
	if(count($DB_TAB))
	{
		$domaine_id = 0;
		$theme_id   = 0;
		$item_id    = 0;
		foreach($DB_TAB as $DB_ROW)
		{
			if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
			{
				$domaine_id  = $DB_ROW['domaine_id'];
				$domaine_ref = $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'];
				$tab_domaine[$domaine_id] = array('domaine_ref'=>$domaine_ref,'domaine_nom'=>$DB_ROW['domaine_nom'],'domaine_nb_lignes'=>2);
			}
			if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
			{
				$theme_id  = $DB_ROW['theme_id'];
				$theme_ref = $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'];
				$first_theme_of_domaine = (isset($tab_theme[$domaine_id])) ? false : true ;
				$tab_theme[$domaine_id][$theme_id] = array('theme_ref'=>$theme_ref,'theme_nom'=>$DB_ROW['theme_nom'],'theme_nb_lignes'=>1);
			}
			if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
			{
				$item_id = $DB_ROW['item_id'];
				$item_ref = $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'];
				$tab_item[$theme_id][$item_id] = array('item_ref'=>$item_ref,'item_nom'=>$DB_ROW['item_nom'],'item_coef'=>$DB_ROW['item_coef'],'item_cart'=>$DB_ROW['item_cart'],'item_socle'=>$DB_ROW['entree_id'],'item_lien'=>$DB_ROW['item_lien']);
				$tab_theme[$domaine_id][$theme_id]['theme_nb_lignes']++;
				if($first_theme_of_domaine)
				{
					$tab_domaine[$domaine_id]['domaine_nb_lignes']++;
				}
				$tab_liste_item[] = $item_id;
			}
		}
	}
	if(count($tab_liste_item))
	{
		$liste_item = implode(',',$tab_liste_item);
	}
	else
	{
		exit('Aucun item référencé pour cette matière et ce niveau !');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des élèves (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		$tab_eleve[] = array('eleve_id'=>$_SESSION['USER_ID'],'eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM']);
	}
	elseif($groupe_id && count($tab_eleve_id))
	{
		$tab_eleve = DB_STRUCTURE_lister_eleves_cibles($liste_eleve);
	}
	else
	{
		$tab_eleve[] = array('eleve_id'=>0,'eleve_nom'=>'','eleve_prenom'=>'');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($groupe_id && count($tab_eleve_id) && $remplissage=='plein')
	{
		$DB_TAB = DB_STRUCTURE_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_debut=false , $date_fin=false) ;
		foreach($DB_TAB as $DB_ROW)
		{
			$user_id = ($_SESSION['USER_PROFIL']=='eleve') ? $_SESSION['USER_ID'] : $DB_ROW['eleve_id'] ;
			$tab_eval[$user_id][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
		}
	}
	// On tronque s'il y en a trop
	foreach($tab_eleve_id as $eleve_id)
	{
		foreach($tab_liste_item as $item_id)
		{
			$eval_nb = (isset($tab_eval[$eleve_id][$item_id])) ? count($tab_eval[$eleve_id][$item_id]) : 0;
			if($eval_nb>$cases_nb)
			{
				$tab_eval[$eleve_id][$item_id] = array_slice($tab_eval[$eleve_id][$item_id],$eval_nb-$cases_nb);
			}
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Elaboration de la grille de compétences, en HTML et PDF
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	// Initialiser au cas où $aff_coef / $aff_socle / $aff_lien sont à 0
	$texte_coef       = '';
	$texte_socle      = '';
	$texte_lien_avant = '';
	$texte_lien_apres = '';
	// Les variables $releve_html et $releve_pdf vont contenir les sorties
	$only_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
	$releve_html  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_html .= '<h1>Grille de compétences</h1>';
	$releve_html .= '<h2>'.html($matiere_nom.' - Niveau '.$niveau_nom.$only_socle).'</h2>';
	// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
	require('./_fpdf/fpdf.php');
	require('./_inc/class.PDF.php');
	$releve_pdf = new PDF($orientation,$marge_min,$couleur);
	$releve_pdf->grille_niveau_initialiser($cases_nb,$cases_largeur,$cases_hauteur);

	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
		// On met le document au nom de l'élève, ou on établit un document générique
		$releve_pdf->grille_niveau_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom);
		$releve_html .= ($eleve_id) ? '<hr /><h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : '<hr /><h2>Grille générique</h2>' ;
		$releve_html .= '<table class="bilan">';
		// Pour chaque domaine...
		if(count($tab_domaine))
		{
			foreach($tab_domaine as $domaine_id => $tab)
			{
				extract($tab);	// $domaine_ref $domaine_nom $domaine_nb_lignes
				$releve_html .= '<tr><th colspan="2" class="domaine">'.html($domaine_nom).'</th><th colspan="'.$cases_nb.'" class="nu"></th></tr>'."\r\n";
				$releve_pdf->grille_niveau_domaine($domaine_nom,$domaine_nb_lignes);
				// Pour chaque thème...
				if(isset($tab_theme[$domaine_id]))
				{
					foreach($tab_theme[$domaine_id] as $theme_id => $tab)
					{
						extract($tab);	// $theme_ref $theme_nom $theme_nb_lignes
						$releve_html .= '<tr><th>'.$theme_ref.'</th><th>'.html($theme_nom).'</th><th colspan="'.$cases_nb.'" class="nu"></th></tr>'."\r\n";
						$releve_pdf->grille_niveau_theme($theme_ref,$theme_nom,$theme_nb_lignes);
						// Pour chaque item...
						if(isset($tab_item[$theme_id]))
						{
							foreach($tab_item[$theme_id] as $item_id => $tab)
							{
								extract($tab);	// $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien
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
								$releve_html .= '<tr><td>'.$item_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_nom).$texte_lien_apres.'</td>';
								$releve_pdf->grille_niveau_competence($item_ref,$texte_coef.$texte_socle.$item_nom);
								// Pour chaque case...
								for($i=0;$i<$cases_nb;$i++)
								{
									if(isset($tab_eval[$eleve_id][$item_id][$i]))
									{
										extract($tab_eval[$eleve_id][$item_id][$i]);	// $note $date $info
									}
									else
									{
										$note = '-'; $date = ''; $info = '';
									}
									if($remplissage=='plein')
									{
										$releve_html .= '<td>'.affich_note_html($note,$date,$info,false).'</td>';
										$releve_pdf->afficher_note_lomer($note);
										$releve_pdf->Cell($cases_largeur , $cases_hauteur , '' , 1 , floor(($i+1)/$cases_nb) , 'C' , false , '');
									}
									else
									{
										$releve_html .= '<td>&nbsp;</td>';
										$releve_pdf->Cell($cases_largeur , $cases_hauteur , '' , 1 , floor(($i+1)/$cases_nb) , 'C' , true , '');
									}
								}
								$releve_html .= '</tr>'."\r\n";
							}
						}
					}
				}
			}
		}
		$releve_html .= '</table><p />';
	}

	// Chemins d'enregistrement
	$dossier      = './__tmp/export/';
	$fichier_lien = 'grille_niveau_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'.html',$releve_html);
	$releve_pdf->Output($dossier.$fichier_lien.'.pdf','F');
	// Affichage du résultat
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		echo'<ul class="puce">';
		echo'<li><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Téléchargez au format PDF le fichier généré avec la grille de compétences (selon les options choisies).</a></label></li>';
		echo'</ul><p />';
		echo $releve_html;
	}
	else
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Grille au format HTML (bulles d\'information, liens...).</a></li>';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Grille au format PDF (imprimable).</a></li>';
		echo'</ul><p />';
	}

}

else
{
	echo'Erreur avec les données transmises !';
}
?>
