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

$matiere_id    = (isset($_POST['f_matiere']))      ? clean_entier($_POST['f_matiere'])      : 0;
$niveau_id     = (isset($_POST['f_niveau']))       ? clean_entier($_POST['f_niveau'])       : 0;
$matiere_nom   = (isset($_POST['f_matiere_nom']))  ? clean_texte($_POST['f_matiere_nom'])   : '';
$niveau_nom    = (isset($_POST['f_niveau_nom']))   ? clean_texte($_POST['f_niveau_nom'])    : '';
$only_socle    = (isset($_POST['f_restriction']))  ? 1                                      : 0;
$aff_coef      = (isset($_POST['f_coef']))         ? 1                                      : 0;
$aff_socle     = (isset($_POST['f_socle']))        ? 1                                      : 0;
$aff_lien      = (isset($_POST['f_lien']))         ? 1                                      : 0;
$cases_nb      = (isset($_POST['f_cases_nb']))     ? clean_entier($_POST['f_cases_nb'])     : 0;
$cases_largeur = (isset($_POST['f_cases_larg']))   ? clean_entier($_POST['f_cases_larg'])   : 0;
$remplissage   = (isset($_POST['f_remplissage']))  ? clean_texte($_POST['f_remplissage'])   : '';
$colonne_vide  = (isset($_POST['f_colonne_vide'])) ? clean_entier($_POST['f_colonne_vide']) : 0;
$orientation   = (isset($_POST['f_orientation']))  ? clean_texte($_POST['f_orientation'])   : '';
$couleur       = (isset($_POST['f_couleur']))      ? clean_texte($_POST['f_couleur'])       : '';
$legende       = (isset($_POST['f_legende']))      ? clean_texte($_POST['f_legende'])       : '';
$marge_min     = (isset($_POST['f_marge_min']))    ? clean_texte($_POST['f_marge_min'])     : '';
$groupe_id     = (isset($_POST['f_groupe']))       ? clean_entier($_POST['f_groupe'])       : 0;	// en cas de manipulation type Firebug, peut être forcé pour l'élève à $_SESSION['ELEVE_CLASSE_ID']
$tab_eleve_id  = (isset($_POST['eleves']))         ? array_map('clean_entier',explode(',',$_POST['eleves'])) : array() ;	// en cas de manipulation type Firebug, peut être forcé pour l'élève avec $_SESSION['USER_ID']

$tab_eleve_id  = array_filter($tab_eleve_id,'positif');
$liste_eleve   = implode(',',$tab_eleve_id);

if( $matiere_id && $niveau_id && $matiere_nom && $niveau_nom && $remplissage && $orientation && $couleur && $legende && $marge_min && $cases_nb && $cases_largeur )
{

	save_cookie_select('grille_referentiel');
	save_cookie_select('matiere');

	$tab_domaine    = array();	// [domaine_id] => array(domaine_ref,domaine_nom,domaine_nb_lignes);
	$tab_theme      = array();	// [domaine_id][theme_id] => array(theme_ref,theme_nom,theme_nb_lignes);
	$tab_item       = array();	// [theme_id][item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien);
	$tab_liste_item = array();	// [i] => item_id
	$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
	$tab_eval       = array();	// [eleve_id][item_id] => array(note,date,info)

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des items pour la matière et le niveau sélectionné
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$lignes_nb = 0;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_socle,$only_item=false,$socle_nom=false);
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
				$lignes_nb++;
			}
			if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
			{
				$theme_id  = $DB_ROW['theme_id'];
				$theme_ref = $DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'];
				$first_theme_of_domaine = (isset($tab_theme[$domaine_id])) ? false : true ;
				$tab_theme[$domaine_id][$theme_id] = array('theme_ref'=>$theme_ref,'theme_nom'=>$DB_ROW['theme_nom'],'theme_nb_lignes'=>1);
				$lignes_nb++;
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
				$lignes_nb++;
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
	elseif(count($tab_eleve_id))
	{
		$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($liste_eleve,$with_gepi=FALSE,$with_langue=FALSE);
	}
	else
	{
		$tab_eleve[] = array('eleve_id'=>0,'eleve_nom'=>'','eleve_prenom'=>'');
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Récupération de la liste des résultats (si demandé)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if(count($tab_eleve_id) && $remplissage=='plein')
	{
		$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_debut=false , $date_fin=false , $_SESSION['USER_PROFIL']) ;
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
	/* 
	 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
	 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
	 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
	 */
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	DB::close(SACOCHE_STRUCTURE_BD_NAME);
	unset($DB_TAB);

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// Elaboration de la grille d'items d'un référentiel, en HTML et PDF
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	// Initialiser au cas où $aff_coef / $aff_socle / $aff_lien sont à 0
	$texte_coef       = '';
	$texte_socle      = '';
	$texte_lien_avant = '';
	$texte_lien_apres = '';
	// Les variables $releve_HTML et $releve_PDF vont contenir les sorties
	$only_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
	$releve_HTML  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML .= '<h1>Grille d\'items d\'un référentiel</h1>';
	$releve_HTML .= '<h2>'.html($matiere_nom.' - Niveau '.$niveau_nom.$only_socle).'</h2>';
	// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
	$releve_PDF = new PDF($orientation,$marge_min,$couleur,$legende);
	$releve_PDF->grille_referentiel_initialiser($cases_nb,$cases_largeur,$lignes_nb,$colonne_vide);

	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
		// On met le document au nom de l'élève, ou on établit un document générique
		$releve_PDF->grille_referentiel_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom);
		$releve_HTML .= ($eleve_id) ? '<hr /><h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : '<hr /><h2>Grille générique</h2>' ;
		$releve_HTML .= '<table class="bilan">';
		// Pour chaque domaine...
		if(count($tab_domaine))
		{
			foreach($tab_domaine as $domaine_id => $tab)
			{
				extract($tab);	// $domaine_ref $domaine_nom $domaine_nb_lignes
				$releve_HTML .= '<tr><th colspan="2" class="domaine">'.html($domaine_nom).'</th><th colspan="'.$cases_nb.'" class="nu"></th></tr>'."\r\n";
				$releve_PDF->grille_referentiel_domaine($domaine_nom,$domaine_nb_lignes);
				// Pour chaque thème...
				if(isset($tab_theme[$domaine_id]))
				{
					foreach($tab_theme[$domaine_id] as $theme_id => $tab)
					{
						extract($tab);	// $theme_ref $theme_nom $theme_nb_lignes
						$releve_HTML .= '<tr><th>'.$theme_ref.'</th><th>'.html($theme_nom).'</th><th colspan="'.$cases_nb.'" class="nu"></th></tr>'."\r\n";
						$releve_PDF->grille_referentiel_theme($theme_ref,$theme_nom,$theme_nb_lignes);
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
								$releve_HTML .= '<tr><td>'.$item_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_nom).$texte_lien_apres.'</td>';
								$releve_PDF->grille_referentiel_item($item_ref,$texte_coef.$texte_socle.$item_nom);
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
										$releve_HTML .= '<td>'.affich_note_html($note,$date,$info,false).'</td>';
										$releve_PDF->afficher_note_lomer($note,$border=1,$br=floor(($i+1)/$cases_nb));
									}
									else
									{
										$releve_HTML .= '<td>&nbsp;</td>';
										$releve_PDF->Cell($cases_largeur , $releve_PDF->cases_hauteur , '' , 1 , floor(($i+1)/$cases_nb) , 'C' , true , '');
									}
								}
								$releve_HTML .= '</tr>'."\r\n";
							}
						}
					}
				}
			}
		}
		$releve_HTML .= '</table><p />';
		if($legende=='oui')
		{
			$releve_PDF->grille_referentiel_legende();
			$releve_HTML .= affich_legende_html($note_Lomer=TRUE,$etat_bilan=FALSE);
		}
	}

	// Chemins d'enregistrement
	$dossier      = './__tmp/export/';
	$fichier_lien = 'releve_grille_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'.html',$releve_HTML);
	$releve_PDF->Output($dossier.$fichier_lien.'.pdf','F');
	// Affichage du résultat
	if($_SESSION['USER_PROFIL']=='eleve')
	{
		echo'<ul class="puce">';
		echo'<li><label class="alerte"><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Archiver / Imprimer (format <em>pdf</em>).</a></label></li>';
		echo'</ul><p />';
		echo $releve_HTML;
	}
	else
	{
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'.pdf">Archiver / Imprimer (format <em>pdf</em>).</a></li>';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'">Explorer / Détailler (format <em>html</em>).</a></li>';
		echo'</ul><p />';
	}

}

else
{
	echo'Erreur avec les données transmises !';
}
?>
