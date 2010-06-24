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
 * [./releve_socle.ajax.php]
 * 
 */

$dossier      = './__tmp/export/';
$fichier_lien = 'grille_niveau_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();

$tab_modele_bon  = array('RR','R','V','VV');	// les notes prises en compte dans le calcul du score
$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');

function acquis($n)     {return $n>$_SESSION['CALCUL_SEUIL']['V'] ;}
function non_acquis($n) {return $n<$_SESSION['CALCUL_SEUIL']['R'] ;}
function calculer_note($tab_devoirs,$calcul_methode,$calcul_limite)
{
	// Attention !!! $tab_devoirs n'est pas sur le même modèle que dans le fichier code_releve_competence ; donc $tab_devoirs[$i]['note'] remplacé par $tab_devoirs[$i]
	global $tab_modele_bon;
	$devoirs_nb = count($tab_devoirs);
	// on passe en revue les évaluations disponibles, et on retient les scores exploitables
	$tab_note = array(); // pour les notes d'un élève
	for($i=0;$i<$devoirs_nb;$i++)
	{
		if(in_array($tab_devoirs[$i],$tab_modele_bon))
		{
			$tab_note[] = $_SESSION['CALCUL_VALEUR'][$tab_devoirs[$i]];
		}
	}
	// si pas de notes exploitables, on arrête de suite (sinon, on est certain de pouvoir renvoyer un score)
	$nb_note = count($tab_note);
	if($nb_note==0)
	{
		return false;
	}
	// si le paramétrage du référentiel l'indique, on tronque pour ne garder que les derniers résultats
	if( ($calcul_limite) && ($nb_note>$calcul_limite) )
	{
		$tab_note = array_slice($tab_note,-$calcul_limite);
		$nb_note = $calcul_limite;
	}
	// calcul de la note en fonction du mode du référentiel
	$somme_point = 0;
	$coef = 1;
	$somme_coef = 0;
	for($num_devoir=1 ; $num_devoir<=$nb_note ; $num_devoir++)
	{
		$somme_point += $tab_note[$num_devoir-1]*$coef;
		$somme_coef += $coef;
		// Calcul du coef de l'éventuel devoir suivant
		$coef = ($calcul_methode=='geometrique') ? $coef*2 : ( ($calcul_methode=='arithmetique') ? $coef+1 : 1 ) ;
	}
	return round($somme_point/$somme_coef,0);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Tableaux et variables pour mémoriser les infos ; dans cette section on ne fait que les calculs (aucun affichage)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_init_compet = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0);
$tab_score_pilier_eleve  = array();	// [pilier_id][eleve_id] => array(A,VA,NA,nb,%)  // Retenir le nb d'items validés ou pas / pilier / élève
$tab_score_section_eleve = array();	// [section_id][eleve_id] => array(A,VA,NA,nb,%) // Retenir le nb d'items validés ou pas / section / élève
$tab_score_socle_eleve   = array();	// [socle_id][eleve_id] => array(A,VA,NA,nb,%)   // Retenir le nb d'items validés ou pas / item / élève
$tab_infos_socle_eleve   = array();	// [socle_id][eleve_id] => array()               // Retenir les infos sur les items travaillés et leurs scores / item du socle / élève

if($test_affichage_scores)
{
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
				$tab_score_pilier_eleve[$pilier_id][$eleve_id] = $tab_init_compet;
				// Pour chaque section...
				if(isset($tab_section[$pilier_id]))
				{
					foreach($tab_section[$pilier_id] as $section_id => $section_nom)
					{
						$tab_score_section_eleve[$section_id][$eleve_id] = $tab_init_compet;
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
										extract($tab_item[$item_id]);	// $item_ref $item_nom $matiere_id $calcul_methode $calcul_limite
										// calcul du bilan de l'item
										$note = calculer_note($tab_devoirs,$calcul_methode,$calcul_limite);
										if($note!==false)
										{
											// on détermine si elle est acquise ou pas
											if(non_acquis($note)) {$indice = 'NA';}
											elseif(acquis($note)) {$indice = 'A';}
											else                  {$indice = 'VA';}
											// on enregistre les infos
											if($detail=='complet')
											{
												$texte_demande_eval = ( ($_SESSION['USER_PROFIL']=='eleve') && ($_SESSION['ELEVE_DEMANDES']>0) ) ? '<q class="demander_add" lang="ids_'.$eleve_id.'_'.$matiere_id.'_'.$item_id.'_'.$note.'" title="Ajouter aux demandes d\'évaluations."></q>' : '' ;
												$tab_infos_socle_eleve[$socle_id][$eleve_id][] = '<span class="'.$tab_etat[$indice].'">'.html($item_ref.' || '.$item_nom.' ['.$note.'%]').'</span>'.$texte_demande_eval;
												$tab_score_socle_eleve[$socle_id][$eleve_id][$indice]++;
												$tab_score_socle_eleve[$socle_id][$eleve_id]['nb']++;
											}
											$tab_score_section_eleve[$section_id][$eleve_id][$indice]++;
											$tab_score_section_eleve[$section_id][$eleve_id]['nb']++;
											$tab_score_pilier_eleve[$pilier_id][$eleve_id][$indice]++;
											$tab_score_pilier_eleve[$pilier_id][$eleve_id]['nb']++;
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
}

/*
	On calcule les états d'acquisition à partir des A / VA / NA
	$tab_moyenne_scores_item[$item_id]
	$tab_pourcentage_validations_item[$item_id]
*/

if($test_affichage_scores)
{
	// Pour les piliers
	foreach($tab_score_pilier_eleve as $pilier_id=>$tab_pilier_eleve)
	{
		foreach($tab_pilier_eleve as $eleve_id=>$tab_scores)
		{
			$tab_score_pilier_eleve[$pilier_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
		}
	}
	// Pour les sections
	foreach($tab_score_section_eleve as $section_id=>$tab_section_eleve)
	{
		foreach($tab_section_eleve as $eleve_id=>$tab_scores)
		{
			$tab_score_section_eleve[$section_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
		}
	}
	// Pour les items du socle
	if($detail=='complet')
	{
		foreach($tab_score_socle_eleve as $socle_id=>$tab_socle_eleve)
		{
			foreach($tab_socle_eleve as $eleve_id=>$tab_scores)
			{
				$tab_score_socle_eleve[$socle_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : false ;
			}
		}
	}
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Elaboration de l'attestation relative au socle commun, en HTML et PDF
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$releve_html  = '<h1>Attestation de maîtrise du socle commun</h1>';
$releve_html .= '<h2>'.html($palier_nom).'</h2>';
// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
require('./_fpdf/fpdf.php');
require('./_inc/class.PDF.php');
$releve_pdf = new PDF($orientation='portrait',$marge_min=7.5,$couleur='oui');
$releve_pdf->releve_socle_initialiser($detail,$test_affichage_scores);

// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
	// On met le document au nom de l'élève, ou on établit un document générique
	$releve_pdf->releve_socle_entete($palier_nom,$eleve_id,$eleve_nom,$eleve_prenom);
	$releve_html .= ($eleve_id) ? '<hr /><h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : '<hr /><h2>Attestation générique</h2>' ;
	$releve_html .= '<table class="bilan">';
	// Pour chaque pilier...
	if(count($tab_pilier))
	{
		foreach($tab_pilier as $pilier_id => $tab)
		{
			extract($tab);	// $pilier_nom $pilier_nb_lignes
			$case_score = $test_affichage_scores ? affich_validation_html('th',$tab_score_pilier_eleve[$pilier_id][$eleve_id]) : '<th class="nu"></th>' ;
			$releve_html .= '<tr><th class="pilier">'.html($pilier_nom).'</th>'.$case_score.'<th colspan="2" class="nu"></th></tr>'."\r\n";
			if($test_affichage_scores) {$releve_pdf->releve_socle_pilier($pilier_nom,$pilier_nb_lignes,true,$tab_score_pilier_eleve[$pilier_id][$eleve_id]);}
			else                       {$releve_pdf->releve_socle_pilier($pilier_nom,$pilier_nb_lignes,false,array());}
			// Pour chaque section...
			if(isset($tab_section[$pilier_id]))
			{
				foreach($tab_section[$pilier_id] as $section_id => $section_nom)
				{
					$case_score = $test_affichage_scores ? affich_validation_html('th',$tab_score_section_eleve[$section_id][$eleve_id]) : '<th class="nu"></th>' ;
					$releve_html .= '<tr><th colspan="2">'.html($section_nom).'</th>'.$case_score.'<th class="nu"></th></tr>'."\r\n";
					if($test_affichage_scores) {$releve_pdf->releve_socle_section($section_nom,true,$tab_score_section_eleve[$section_id][$eleve_id]);}
					else                       {$releve_pdf->releve_socle_section($section_nom,false,array());}
					// Pour chaque item du socle...
					if($detail=='complet')
					{
						if(isset($tab_socle[$section_id]))
						{
							foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
							{
								
								if($test_affichage_scores) {$releve_pdf->releve_socle_item($socle_nom,$test_affichage_scores,$tab_score_socle_eleve[$socle_id][$eleve_id]);}
								else                       {$releve_pdf->releve_socle_item($socle_nom,false,array());}
								$socle_nom  = html($socle_nom);
								$socle_nom  = (mb_strlen($socle_nom)<160) ? $socle_nom : mb_substr($socle_nom,0,150).' [...] <img src="./_img/puce_astuce.png" alt="" title="'.$socle_nom.'" />';
								if( $test_affichage_scores && $tab_infos_socle_eleve[$socle_id][$eleve_id] )
								{
									$lien_toggle = '<a href="#" lang="'.$socle_id.'_'.$eleve_id.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer le détail des items associés." class="toggle" /></a> ';
									$div_competences = '<div id="'.$socle_id.'_'.$eleve_id.'" class="hide">'.implode('<br />',$tab_infos_socle_eleve[$socle_id][$eleve_id]).'</div>';
								}
								else
								{
									$lien_toggle = '<img src="./_img/toggle_none.gif" alt="" /> ';
									$div_competences = '';
								}
								$releve_html .= '<tr><td colspan="3">'.$lien_toggle.$socle_nom.$div_competences.'</td>';
								$case_score = $test_affichage_scores ? affich_validation_html('td',$tab_score_socle_eleve[$socle_id][$eleve_id]) : '<td class="nu"></td>' ;
								$releve_html .= $case_score;
								$releve_html .= '</tr>'."\r\n";
							}
						}
					}
				}
			}
			$releve_html .= '<tr><td colspan="4" class="nu"></td></tr>'."\r\n";
		}
	}
	$releve_html .= '</table><p />';
}

// On enregistre les sorties HTML et PDF
file_put_contents($dossier.$fichier_lien.'.html',$releve_html);
$releve_pdf->Output($dossier.$fichier_lien.'.pdf','F');

?>
