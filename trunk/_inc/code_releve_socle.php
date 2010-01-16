<?php
/**
 * @version $Id: code_releve_socle.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://socles.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

/**
 * Code inclus commun aux pages
 * [./releve_socle.ajax.php]
 * 
 */

$dossier      = './__tmp/export/';
$fichier_lien = 'grille_niveau_etabl'.$_SESSION['STRUCTURE_ID'].'_user'.$_SESSION['USER_ID'].'_'.time();

function acquis($n)     {return $n>$_SESSION['PARAM_CALCUL']['seuil']['V'] ;}
function non_acquis($n) {return $n<$_SESSION['PARAM_CALCUL']['seuil']['R'] ;}
$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Tableaux et variables pour mémoriser les infos ; dans cette section on ne fait que les calculs (aucun affichage)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_modele_bon  = array('RR','R','V','VV');	// les notes prises en compte dans le calcul du score
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
									foreach($tab_eval[$eleve_id][$socle_id] as $competence_id => $tab_devoirs)
									{
										extract($tab_competence[$competence_id]);	// $competence_ref $competence_nom
										$evaluation_nb = count($tab_devoirs);
										// on passe en revue les évaluations disponibles, et on retient les scores exploitables
										$tab_note = array(); // pour les notes d'un élève
										for($i=0;$i<$evaluation_nb;$i++)
										{
											if(in_array($tab_devoirs[$i],$tab_modele_bon))
											{
												$tab_note[] = $_SESSION['PARAM_CALCUL']['valeur'][$tab_devoirs[$i]];
											}
										}
										// calcul du bilan de l'item
										$note_nb = count($tab_note);
										if($note_nb>4)
										{
											$tab_note = array_slice($tab_note,-4);
										}
										if(count($tab_note))
										{
											switch ($note_nb)
											{
												case 1 :	$note = $tab_note[0]*$_SESSION['PARAM_CALCUL']['coef'][1][1]; break;
												case 2 :	$note = $tab_note[0]*$_SESSION['PARAM_CALCUL']['coef'][2][1] + $tab_note[1]*$_SESSION['PARAM_CALCUL']['coef'][2][2] ; break;
												case 3 :	$note = $tab_note[0]*$_SESSION['PARAM_CALCUL']['coef'][3][1] + $tab_note[1]*$_SESSION['PARAM_CALCUL']['coef'][3][2] + $tab_note[2]*$_SESSION['PARAM_CALCUL']['coef'][3][3] ; break;
												default:	$note = $tab_note[0]*$_SESSION['PARAM_CALCUL']['coef'][4][1] + $tab_note[1]*$_SESSION['PARAM_CALCUL']['coef'][4][2] + $tab_note[2]*$_SESSION['PARAM_CALCUL']['coef'][4][3] + $tab_note[3]*$_SESSION['PARAM_CALCUL']['coef'][4][4] ; break;
											}
											$note = round($note,0);
											// on détermine si elle est acquise ou pas
											if(non_acquis($note)) {$indice = 'NA';}
											elseif(acquis($note)) {$indice = 'A';}
											else                  {$indice = 'VA';}
											// on enregistre les infos
											if($detail=='complet')
											{
												$tab_infos_socle_eleve[$socle_id][$eleve_id][] = '<span class="'.$tab_etat[$indice].'">'.html($competence_ref.' || '.$competence_nom.' ['.$note.'%]').'</span>';
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
	$tab_moyenne_scores_competence[$competence_id]
	$tab_pourcentage_validations_competence[$competence_id]
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
