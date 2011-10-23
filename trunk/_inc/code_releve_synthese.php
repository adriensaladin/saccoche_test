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

/**
 * Code inclus commun aux pages
 * [./releves_bilans__releve_synthese_matiere.ajax.php]
 * [./releves_bilans__releve_synthese_multimatiere.ajax.php]
 */

$dossier         = './__tmp/export/';
$fichier_lien    = 'releve_synthese_'.$format.'_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();

if(!$aff_coef)  { $texte_coef       = ''; }
if(!$aff_socle) { $texte_socle      = ''; }
if(!$aff_lien)  { $texte_lien_avant = ''; }
if(!$aff_lien)  { $texte_lien_apres = ''; }

$date_complement = ($retroactif=='oui') ? ' (évaluations antérieures comptabilisées).' : '.';
$texte_periode   = 'Du '.$date_debut.' au '.$date_fin.$date_complement ;
$tab_titre       = array('matiere'=>'d\'une matière' , 'multimatiere'=>'multidisciplinaire');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

//	////////////////////////////////////////////////////////////////////////////////////////////////////
//	Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
//	////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');

$tab_score_eleve_item      = array();	// Retenir les scores / élève / matière / synthese / item
$tab_infos_acquis_eleve    = array();	// Retenir les infos (nb A - VA - NA) / élève / matière / synthèse + total
$tab_infos_detail_synthese = array();	// Retenir le détail du contenu d'une synthèse / élève / synthèse

$nb_syntheses_total = 0 ;
/*
	On renseigne :
	$tab_score_eleve_item[$eleve_id][$matiere_id][$synthese_ref][$item_id]
	$tab_infos_acquis_eleve[$eleve_id][$matiere_id]
*/

// Pour chaque élève...
foreach($tab_eleve as $key => $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
	// Si cet élève a été évalué...
	if(isset($tab_eval[$eleve_id]))
	{
		// Pour chaque item on calcule son score bilan, et on mémorise les infos pour le détail HTML
		foreach($tab_eval[$eleve_id] as $item_id => $tab_devoirs)
		{
			// le score bilan
			extract($tab_item[$item_id][0]);	// $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $matiere_id $calcul_methode $calcul_limite $synthese_ref
			$score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite) ;
			$tab_score_eleve_item[$eleve_id][$matiere_id][$synthese_ref][$item_id] = $score;
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
			$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
			$texte_demande_eval = ($_SESSION['USER_PROFIL']!='eleve') ? '' : ( ($item_cart) ? '<q class="demander_add" lang="ids_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
			$tab_infos_detail_synthese[$eleve_id][$synthese_ref][] = '<span class="'.$tab_etat[$indice].'">'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_ref.' || '.$item_nom.' ['.$score.'%]').'</span>'.$texte_lien_apres.$texte_demande_eval;
		}
		// Pour chaque élément de synthèse, et pour chaque matière on recense le nombre d'items considérés acquis ou pas
		$tab_eleve[$key]['nb_matieres'] = count($tab_score_eleve_item[$eleve_id]);
		$tab_eleve[$key]['nb_syntheses'] = 0;
		foreach($tab_score_eleve_item[$eleve_id] as $matiere_id => $tab_matiere_scores)
		{
			$tab_eleve[$key]['nb_syntheses'] += count($tab_matiere_scores);
			foreach($tab_matiere_scores as $synthese_ref => $tab_synthese_scores)
			{
				$tableau_score_filtre = array_filter($tab_synthese_scores,'non_nul');
				$nb_scores = count( $tableau_score_filtre );
				if($nb_scores)
				{
					if(!isset($tab_infos_acquis_eleve[$eleve_id][$matiere_id]))
					{
						$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total'] = array('NA'=>0 , 'VA'=>0 , 'A'=>0);
					}
					$nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
					$nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
					$nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id][$synthese_ref] = array('NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis);
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['NA'] += $nb_non_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['VA'] += $nb_voie_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['A']  += $nb_acquis;
				}
			}
		}
		$nb_syntheses_total += $tab_eleve[$key]['nb_syntheses'];
	}
}

//	////////////////////////////////////////////////////////////////////////////////////////////////////
//	Elaboration de la synthèse matière ou multi-matières, en HTML et PDF
//	////////////////////////////////////////////////////////////////////////////////////////////////////

// Préparatifs
$releve_HTML  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
$releve_HTML .= '<h1>Synthèse '.$tab_titre[$format].'</h1>';
$releve_HTML .= '<h2>'.html($texte_periode).'</h2>';
$releve_HTML .= '<div class="astuce">Cliquer sur les icones &laquo;<img src="./_img/toggle_plus.gif" alt="+" />&raquo; pour accéder au détail.';
$releve_PDF = new PDF($orientation='portrait',$marge_min=7,$couleur,$legende);
$releve_PDF->bilan_synthese_initialiser($format,$nb_syntheses_total,$eleve_nb);
// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi $nb_matieres $nb_syntheses
	// Si cet élève a été évalué...
	if(isset($tab_infos_acquis_eleve[$eleve_id]))
	{
		// Intitulé
		$releve_PDF->bilan_synthese_entete($format,$nb_matieres,$nb_syntheses,$tab_titre[$format],$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom);
		$releve_HTML .= '<hr class="breakafter" /><h2>'.html($groupe_nom).' - '.html($eleve_nom).' '.html($eleve_prenom).'</h2>';
		// On passe en revue les matières...
		foreach($tab_infos_acquis_eleve[$eleve_id] as $matiere_id => $tab_infos_matiere)
		{
			$tab_infos_matiere['total'] = array_filter($tab_infos_matiere['total'],'non_zero'); // Retirer les valeurs nulles
			$total = array_sum($tab_infos_matiere['total']) ; // La somme ne peut être nulle, sinon la matière ne se serait pas affichée
			$releve_PDF->bilan_synthese_ligne_matiere($format,$tab_matiere[$matiere_id],$tab_infos_matiere['total'],$total);
			$releve_HTML .= '<table class="bilan" style="width:900px"><tbody>';
			$releve_HTML .= '<tr><th style="width:540px">'.html($tab_matiere[$matiere_id]).'</th>'.affich_barre_synthese_html($width=360,$tab_infos_matiere['total'],$total).'</tr>';
			$releve_HTML .= '</tbody></table>'; // Utilisation de 2 tableaux sinon bugs constatés lors de l'affichage des détails...
			$releve_HTML .= '<table class="bilan" style="width:900px"><tbody>';
			//  On passe en revue les synthèses...
			unset($tab_infos_matiere['total']);
			foreach($tab_infos_matiere as $synthese_ref => $tab_infos_synthese)
			{
				$tab_infos_synthese = array_filter($tab_infos_synthese,'non_zero'); // Retirer les valeurs nulles
				$total = array_sum($tab_infos_synthese) ; // La somme ne peut être nulle (sinon la matière ne se serait pas affichée)
				$releve_PDF->bilan_synthese_ligne_synthese($tab_synthese[$synthese_ref],$tab_infos_synthese,$total);
				$releve_HTML .= '<tr>'.affich_barre_synthese_html($width=180,$tab_infos_synthese,$total).'<td style="width:720px">';
				$releve_HTML .= '<a href="#" lang="'.$synthese_ref.'_'.$eleve_id.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer le détail des items associés." class="toggle" /></a> ';
				$releve_HTML .= html($tab_synthese[$synthese_ref]);
				$releve_HTML .= '<div id="'.$synthese_ref.'_'.$eleve_id.'" class="hide">'.implode('<br />',$tab_infos_detail_synthese[$eleve_id][$synthese_ref]).'</div>';
				$releve_HTML .= '</td></tr>';
			}
			$releve_HTML .= '</tbody></table>';
			$releve_HTML .= '<p />';
		}
		if($legende=='oui')
		{
			$releve_PDF->bilan_synthese_legende($format);
			$releve_HTML .= affich_legende_html($note_Lomer=FALSE,$etat_bilan=TRUE);
		}
	}
}
// On enregistre les sorties HTML et PDF
Ecrire_Fichier($dossier.$fichier_lien.'.html',$releve_HTML);
$releve_PDF->Output($dossier.$fichier_lien.'.pdf','F');

?>