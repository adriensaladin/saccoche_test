<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

/**
 * Code inclus commun aux pages
 * [./releve_grille.ajax.php]
 * 
 */

$dossier      = './__tmp/export/';
$fichier_lien = 'grille_niveau_etabl'.$_SESSION['STRUCTURE_ID'].'_user'.$_SESSION['USER_ID'].'_'.time();

// Initialiser au cas où $aff_coef / $aff_socle / $aff_lien sont à 0
$texte_coef       = '';
$texte_socle      = '';
$texte_lien_avant = '';
$texte_lien_apres = '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration de la grille de compétences, en HTML et PDF
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$releve_html  = '<h1>Livret de connaissances et de compétences</h1>';
$releve_html .= '<h2>'.html($matiere_nom.' - Niveau '.$niveau_nom).'</h2>';
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
					if(isset($tab_competence[$theme_id]))
					{
						foreach($tab_competence[$theme_id] as $competence_id => $tab)
						{
							extract($tab);	// $competence_ref $competence_nom $competence_coef $competence_socle $competence_lien
							if($aff_coef)
							{
								$texte_coef = '['.$competence_coef.'] ';
							}
							if($aff_socle)
							{
								$texte_socle = ($competence_socle) ? '[S] ' : '[–] ';
							}
							if($aff_lien)
							{
								$texte_lien_avant = ($competence_lien) ? '<a class="lien_ext" href="'.html($competence_lien).'">' : '';
								$texte_lien_apres = ($competence_lien) ? '</a>' : '';
							}
							$releve_html .= '<tr><td>'.$competence_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.html($competence_nom).$texte_lien_apres.'</td>';
							$releve_pdf->grille_niveau_competence($competence_ref,$texte_coef.$texte_socle.$competence_nom);
							// Pour chaque case...
							for($i=0;$i<$cases_nb;$i++)
							{
								if(isset($tab_eval[$eleve_id][$competence_id][$i]))
								{
									extract($tab_eval[$eleve_id][$competence_id][$i]);	// $note $date $info
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

// On enregistre les sorties HTML et PDF
file_put_contents($dossier.$fichier_lien.'.html',$releve_html);
$releve_pdf->Output($dossier.$fichier_lien.'.pdf','F');

?>
