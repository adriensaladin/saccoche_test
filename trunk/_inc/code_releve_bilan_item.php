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
 * [./releves_bilans__releve_items_matiere.ajax.php]
 * [./releves_bilans__releve_items_multimatiere.ajax.php]
 * [./releves_bilans__releve_items_selection.ajax.php]
 * 
 */

/*
$tab_type[]		individuel	synthese	bulletin
$format				matiere	selection	multimatiere
*/

$dossier         = './__tmp/export/';
$fichier_lien    = 'releve_item_'.$format.'_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();

if(!$aff_coef)  { $texte_coef       = ''; }
if(!$aff_socle) { $texte_socle      = ''; }
if(!$aff_lien)  { $texte_lien_avant = ''; }
if(!$aff_lien)  { $texte_lien_apres = ''; }

$date_complement = ($retroactif=='oui') ? ' (évaluations antérieures comptabilisées).' : '.';
$texte_periode   = ($format!='selection') ? 'Du '.$date_debut.' au '.$date_fin.$date_complement : false;
$tab_titre       = array('matiere'=>'d\'items - '.$matiere_nom , 'multimatiere'=>'d\'items pluridisciplinaire' , 'selection'=>'d\'items sélectionnés');

require('./_fpdf/fpdf.php');
require('./_inc/class.PDF.php');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_score_eleve_item         = array();	// Retenir les scores / élève / matière / item
$tab_score_item_eleve         = array();	// Retenir les scores / item / élève
$tab_moyenne_scores_eleve     = array();	// Retenir la moyenne des scores d'acquisitions / matière / élève
$tab_pourcentage_acquis_eleve = array();	// Retenir le pourcentage d'items acquis / matière / élève
$tab_infos_acquis_eleve       = array();	// Retenir les infos (nb A - VA - NA) à l'origine du tableau précédent / matière / élève
$tab_moyenne_scores_item      = array();	// Retenir la moyenne des scores d'acquisitions / item
$tab_pourcentage_acquis_item  = array();	// Retenir le pourcentage d'items acquis / item
$moyenne_moyenne_scores       = 0;	// moyenne des moyennes des scores d'acquisitions
$moyenne_pourcentage_acquis   = 0;	// moyenne des moyennes des pourcentages d'items acquis

/*
	On renseigne :
	$tab_score_eleve_item[$eleve_id][$matiere_id][$item_id]
	$tab_score_item_eleve[$item_id][$eleve_id]
	$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]
	$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id]
	$tab_infos_acquis_eleve[$matiere_id][$eleve_id]
*/

// Pour chaque élève...
foreach($tab_eleve as $key => $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
	// Si cet élève a été évalué...
	if(isset($tab_eval[$eleve_id]))
	{
		$tab_eleve[$key]['nb_items'] = 0;
		// Pour chaque matiere...
		foreach($tab_matiere as $matiere_id => $matiere_nom)
		{
			// Si cet élève a été évalué dans cette matière...
			if(isset($tab_eval[$eleve_id][$matiere_id]))
			{
				// Pour chaque item...
				foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab_devoirs)
				{
					extract($tab_item[$item_id][0]);	// $item_ref $item_nom $item_coef $item_socle $item_lien $calcul_methode $calcul_limite
					// calcul du bilan de l'item
					$tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
					$tab_score_item_eleve[$item_id][$eleve_id] = $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id];
				}
				// calcul des bilans des scores
				$tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id][$matiere_id],'non_nul');
				$nb_scores = count( $tableau_score_filtre );
				// la moyenne peut être pondérée par des coefficients
				$somme_scores_ponderes = 0;
				$somme_coefs = 0;
				if($nb_scores)
				{
					// En l'absence de coefficients, ces 2 lignes suffiraient :
					// $somme_scores_ponderes = array_sum($tableau_score_filtre);
					// $somme_coefs = $nb_scores;
					foreach($tableau_score_filtre as $item_id => $item_score)
					{
						$somme_scores_ponderes += $item_score*$tab_item[$item_id][0]['item_coef'];
						$somme_coefs += $tab_item[$item_id][0]['item_coef'];
					}
				}
				// ... un pour la moyenne des pourcentages d'acquisition
				if($somme_coefs)
				{
					$tab_moyenne_scores_eleve[$matiere_id][$eleve_id] = round($somme_scores_ponderes/$somme_coefs,0);
				}
				else
				{
					$tab_moyenne_scores_eleve[$matiere_id][$eleve_id] = false;
				}
				// ... un pour le nombre d\'items considérés acquis ou pas
				if($nb_scores)
				{
					$nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
					$nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
					$nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
					$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
					$tab_infos_acquis_eleve[$matiere_id][$eleve_id]       = $nb_acquis.$_SESSION['ACQUIS_TEXTE']['A'].' '. $nb_voie_acquis.$_SESSION['ACQUIS_TEXTE']['VA'].' '. $nb_non_acquis.$_SESSION['ACQUIS_TEXTE']['NA'];
				}
				else
				{
					$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] = false;
					$tab_infos_acquis_eleve[$matiere_id][$eleve_id]       = false;
				}
				$tab_eleve[$key]['nb_items'] += count($tab_score_eleve_item[$eleve_id][$matiere_id]);
			}
		}
		$tab_eleve[$key]['nb_matieres'] = count($tab_score_eleve_item[$eleve_id]);
	}
}

/*
	On renseigne (uniquement utile pour le tableau de synthèse) :
	$tab_moyenne_scores_item[$item_id]
	$tab_pourcentage_acquis_item[$item_id]
*/

if(in_array('synthese',$tab_type))
{
	// Pour chaque item...
	foreach($tab_liste_item as $item_id)
	{
		$tableau_score_filtre = array_filter($tab_score_item_eleve[$item_id],'non_nul');
		$nb_scores = count( $tableau_score_filtre );
		if($nb_scores)
		{
			$somme_scores = array_sum($tableau_score_filtre);
			$nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
			$nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
			$nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
			$tab_moyenne_scores_item[$item_id]     = round($somme_scores/$nb_scores,0);
			$tab_pourcentage_acquis_item[$item_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
		}
		else
		{
			$tab_moyenne_scores_item[$item_id]     = false;
			$tab_pourcentage_acquis_item[$item_id] = false;
		}
	}
}

/*
	On renseigne (utile pour le tableau de synthèse et le bulletin) :
	$moyenne_moyenne_scores
	$moyenne_pourcentage_acquis
*/
/*
	on pourrait calculer de 2 façons chacune des deux valeurs...
	pour la moyenne des moyennes obtenues par élève : c'est simple car les coefs ont déjà été pris en compte dans le calcul pour chaque élève
	pour la moyenne des moyennes obtenues par item : c'est compliqué car il faudrait repondérer par les coefs éventuels de chaque item
	donc la 1ère technique a été retenue, à défaut d'essayer de calculer les deux et d'en faire la moyenne ;-)
*/

if( (in_array('synthese',$tab_type)) || (in_array('bulletin',$tab_type)) )
{
	// $moyenne_moyenne_scores
	$somme  = array_sum($tab_moyenne_scores_eleve[$matiere_id]);
	$nombre = count( array_filter($tab_moyenne_scores_eleve[$matiere_id],'non_nul') );
	$moyenne_moyenne_scores = ($nombre) ? round($somme/$nombre,0) : false;
	// $moyenne_pourcentage_acquis
	$somme  = array_sum($tab_pourcentage_acquis_eleve[$matiere_id]);
	$nombre = count( array_filter($tab_pourcentage_acquis_eleve[$matiere_id],'non_nul') );
	$moyenne_pourcentage_acquis = ($nombre) ? round($somme/$nombre,0) : false;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Elaboration du bilan individuel, disciplinaire ou transdisciplinaire, en HTML et PDF
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

if(in_array('individuel',$tab_type))
{
	$releve_HTML_individuel  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML_individuel .= '<h1>Bilan '.$tab_titre[$format].'</h1>';
	$releve_HTML_individuel .= '<h2>'.html($texte_periode).'</h2>';
	// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
	$releve_PDF = new PDF($orientation,$marge_min,$couleur,$legende);
	$releve_PDF->bilan_item_individuel_initialiser($format,$cases_nb,$cases_largeur,$lignes_nb=$item_nb+$aff_bilan_MS+$aff_bilan_PA,$eleve_nb);
	$bilan_colspan = $cases_nb + 2 ;
	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi $nb_matieres $nb_items
		// Si cet élève a été évalué...
		if(isset($tab_eval[$eleve_id]))
		{
			$releve_PDF->bilan_item_individuel_entete($format,$nb_matieres,$nb_items+($aff_bilan_MS+$aff_bilan_PA)*$nb_matieres,$tab_titre[$format],$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom);
			// Intitulé
			$releve_HTML_individuel .= '<hr class="breakafter" /><h2>'.html($groupe_nom).' - '.html($eleve_nom).' '.html($eleve_prenom).'</h2>';
			// Pour chaque matiere...
			foreach($tab_matiere as $matiere_id => $matiere_nom)
			{
				// Si cet élève a été évalué dans cette matière...
				if(isset($tab_eval[$eleve_id][$matiere_id]))
				{
					if( ($format=='multimatiere') || ($format=='selection') )
					{
						$item_matiere_nb = count($tab_eval[$eleve_id][$matiere_id]);
						$releve_PDF->bilan_item_individuel_transdisciplinaire_ligne_matiere($matiere_nom,$lignes_nb=$item_matiere_nb+$aff_bilan_MS+$aff_bilan_PA,$eleve_nom,$eleve_prenom);
					}
					$releve_HTML_individuel .= '<h3>'.html($matiere_nom).'</h3>';
					// On passe au tableau
					$releve_HTML_table_head = '<thead><tr><th>Ref.</th><th>Nom de l\'item</th>';
					for($num_case=0;$num_case<$cases_nb;$num_case++)
					{
						$releve_HTML_table_head .= '<th></th>';	// Pas de colspan sinon pb avec le tri
					}
					$releve_HTML_table_head .= '<th>score</th></tr></thead>';
					$releve_HTML_table_body = '<tbody>';
					// Pour chaque item...
					foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab_devoirs)
					{
						extract($tab_item[$item_id][0]);	// $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $calcul_methode $calcul_limite
						// cases référence et nom
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
						$texte_demande_eval = ( ($_SESSION['USER_PROFIL']!='eleve') || ($_SESSION['DROIT_ELEVE_DEMANDES']==0) ) ? '' : ( ($item_cart) ? '<q class="demander_add" lang="ids_'.$matiere_id.'_'.$item_id.'_'.$tab_score_eleve_item[$eleve_id][$matiere_id][$item_id].'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
						$releve_HTML_table_body .= '<tr><td>'.$item_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_nom).$texte_lien_apres.$texte_demande_eval.'</td>';
						$releve_PDF->bilan_item_individuel_debut_ligne_item($item_ref,$texte_coef.$texte_socle.$item_nom);
						// cases d'évaluations
						$devoirs_nb = count($tab_devoirs);
						// on passe en revue les cases disponibles et on remplit en fonction des évaluations disponibles
						$decalage = $devoirs_nb - $cases_nb;
						for($i=0;$i<$cases_nb;$i++)
						{
							// on doit remplir une case
							if($decalage<0)
							{
								// il y a moins d'évaluations que de cases à remplir : on met un score dispo ou une case blanche si plus de score dispo
								if($i<$devoirs_nb)
								{
									extract($tab_devoirs[$i]);	// $note $date $info
									$releve_HTML_table_body .= '<td>'.affich_note_html($note,$date,$info,true).'</td>';
									$releve_PDF->afficher_note_lomer($note,$border=1,$br=0);
								}
								else
								{
									$releve_HTML_table_body .= '<td>&nbsp;</td>';
									$releve_PDF->afficher_note_lomer($note='',$border=1,$br=0);
								}
							}
							// il y a plus d'évaluations que de cases à remplir : on ne prend que les dernières (décalage d'indice)
							else
							{
								extract($tab_devoirs[$i+$decalage]);	// $note $date $info
								$releve_HTML_table_body .= '<td>'.affich_note_html($note,$date,$info,true).'</td>';
								$releve_PDF->afficher_note_lomer($note,$border=1,$br=0);
							}
						}
						// affichage du bilan de l'item
						$releve_HTML_table_body .= affich_score_html($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id],'score');
						$releve_PDF->afficher_score_bilan($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id],$br=1);
						$releve_HTML_table_body .= '</tr>'."\r\n";
					}
					$releve_HTML_table_body .= '</tbody>';
					$releve_HTML_table_foot = '';
					// affichage des bilans des scores
					// ... un pour la moyenne des pourcentages d'acquisition
					if( $aff_bilan_MS )
					{
						if($tab_moyenne_scores_eleve[$matiere_id][$eleve_id] !== false)
						{
							$texte_bilan  = $tab_moyenne_scores_eleve[$matiere_id][$eleve_id].'%';
							$texte_bilan .= ($aff_conv_sur20) ? ' soit '.sprintf("%04.1f",$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5).'/20' : '' ;
						}
						else
						{
							$texte_bilan = '---';
						}
						$releve_HTML_table_foot .= '<tr><td class="nu">&nbsp;</td><td colspan="'.$bilan_colspan.'">Moyenne pondérée des scores d\'acquisitions : '.$texte_bilan.'</td><td class="nu"></td></tr>'."\r\n";
						$releve_PDF->bilan_item_individuel_ligne_synthese('Moyenne pondérée des scores d\'acquisitions : '.$texte_bilan);
					}
					// ... un pour le nombre d'items considérés acquis ou pas
					if( $aff_bilan_PA )
					{
						if($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] !== false)
						{
							$texte_bilan  = '('.$tab_infos_acquis_eleve[$matiere_id][$eleve_id].') : '.$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id].'%';
							$texte_bilan .= ($aff_conv_sur20) ? ' soit '.sprintf("%04.1f",$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id]/5).'/20' : '' ;
						}
						else
						{
							$texte_bilan = '---';
						}
						$releve_HTML_table_foot .= '<tr><td class="nu">&nbsp;</td><td colspan="'.$bilan_colspan.'">Pourcentage d\'items acquis '.$texte_bilan.'</td><td class="nu"></td></tr>'."\r\n";
						$releve_PDF->bilan_item_individuel_ligne_synthese('Pourcentage d\'items acquis '.$texte_bilan);
					}
					$releve_HTML_table_foot = ($releve_HTML_table_foot) ? '<tfoot>'.$releve_HTML_table_foot.'</tfoot>'."\r\n" : '';
					$releve_HTML_individuel .= '<table id="table'.$eleve_id.'x'.$matiere_id.'" class="bilan">'.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table><p />';
					$releve_HTML_individuel .= '<script type="text/javascript">$("#table'.$eleve_id.'x'.$matiere_id.'").tablesorter();</script>';
				}
			}
			if($legende=='oui')
			{
				$releve_PDF->bilan_item_individuel_legende($format);
			}
		}
	}
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'_individuel.html',$releve_HTML_individuel);
	$releve_PDF->Output($dossier.$fichier_lien.'_individuel.pdf','F');
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Elaboration de la synthèse collective en HTML et PDF
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

if(in_array('synthese',$tab_type))
{
	$releve_HTML_synthese  = '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML_synthese .= '<h1>Bilan '.$tab_titre[$format].'</h1>';
	$releve_HTML_synthese .= '<h2>'.html($matiere_nom.' - '.$groupe_nom).'</h2>';
	if($texte_periode)
	{
		$releve_HTML_synthese .= '<h2>'.html($texte_periode).'</h2>';
	}
	// Appel de la classe et redéfinition de qqs variables supplémentaires pour la mise en page PDF ; on impose l'orientation paysage
	$releve_PDF = new PDF('landscape',$marge_min,$couleur);
	$releve_PDF->bilan_periode_synthese_initialiser($eleve_nb,$item_nb);

	if($format=='matiere')   {$releve_PDF->bilan_periode_synthese_entete($tab_titre[$format],$matiere_nom,$texte_periode,$groupe_nom);}
	if($format=='selection') {$releve_PDF->bilan_periode_synthese_entete($tab_titre[$format],$matiere_nom,false,$groupe_nom);}

			
	// 1ère ligne commune aux deux tableaux
	$releve_PDF->Cell($releve_PDF->eleve_largeur , $releve_PDF->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
	$releve_PDF->choisir_couleur_fond('gris_clair');
	$releve_HTML_table_head = '<thead><tr><th>Elève</th>';
	// Pour chaque item...
	foreach($tab_liste_item as $item_id)
	{
		$memo_x = $releve_PDF->GetX();
		$memo_y = $releve_PDF->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$tab_item[$item_id][0]['item_ref'],2);
		$releve_PDF->SetFont('Arial' , '' , $releve_PDF->taille_police-1);
		$releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->cases_hauteur/2 , pdf($ref_matiere) , 0 , 2 , 'C' , true , '');
		$releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->cases_hauteur/2 , pdf($ref_suite) , 0 , 2 , 'C' , true , '');
		$releve_PDF->SetFont('Arial' , '' , $releve_PDF->taille_police);
		$releve_PDF->SetXY($memo_x , $memo_y);
		$releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->cases_hauteur , '' , 1 , 0 , 'C' , false , '');
		$releve_HTML_table_head .= '<th title="'.html($tab_item[$item_id][0]['item_nom']).'">'.html($tab_item[$item_id][0]['item_ref']).'</th>';
	}
	$releve_PDF->SetX( $releve_PDF->GetX()+2 );
	$releve_PDF->choisir_couleur_fond('gris_moyen');
	$releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->cases_hauteur , '[ * ]'  , 1 , 0 , 'C' , true , '');
	$releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->cases_hauteur , '[ ** ]' , 1 , 1 , 'C' , true , '');
	$releve_HTML_table_head .= '<th class="nu">&nbsp;</th><th>[ * ]</th><th>[ ** ]</th></tr></thead>'."\r\n";
	// lignes suivantes
	$releve_HTML_table_body1 = '';
	$releve_HTML_table_body2 = '';
	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
		$releve_PDF->choisir_couleur_fond('gris_clair');
		$releve_PDF->Cell($releve_PDF->eleve_largeur , $releve_PDF->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 , 0 , 'L' , true , '');
		$releve_HTML_table_body1 .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
		$releve_HTML_table_body2 .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
		// Pour chaque item...
		foreach($tab_liste_item as $item_id)
		{
			$score = (isset($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] : false ;
			$releve_PDF->afficher_score_bilan($score,$br=0);
			$releve_HTML_table_body1 .= affich_score_html($score,'score');
			$releve_HTML_table_body2 .= affich_score_html($score,'etat');
		}
		$releve_PDF->bilan_periode_synthese_pourcentages($tab_moyenne_scores_eleve[$matiere_id][$eleve_id],$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id],false,true);
		$releve_HTML_table_body1 .= '<td class="nu">&nbsp;</td>'.affich_score_html($tab_moyenne_scores_eleve[$matiere_id][$eleve_id],'score','%').affich_score_html($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id],'score','%').'</tr>'."\r\n";
		$releve_HTML_table_body2 .= '<td class="nu">&nbsp;</td>'.affich_score_html($tab_moyenne_scores_eleve[$matiere_id][$eleve_id],'etat','%').affich_score_html($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id],'etat','%').'</tr>'."\r\n";
	}
	$releve_HTML_table_body1 = '<tbody>'.$releve_HTML_table_body1.'</tbody>'."\r\n";
	$releve_HTML_table_body2 = '<tbody>'.$releve_HTML_table_body2.'</tbody>'."\r\n";
	// dernière ligne (doublée)
	$colspan = $item_nb+4;
	$memo_y = $releve_PDF->GetY()+2;
	$releve_PDF->SetY( $memo_y );
	$releve_PDF->choisir_couleur_fond('gris_moyen');
	$releve_PDF->Cell($releve_PDF->eleve_largeur , $releve_PDF->cases_hauteur , 'moyenne scores [*]' , 1 , 2 , 'C' , true , '');
	$releve_PDF->Cell($releve_PDF->eleve_largeur , $releve_PDF->cases_hauteur , '% validations [**]' , 1 , 0 , 'C' , true , '');
	$releve_HTML_table_foot1 = '<tr><th>moyenne scores [*]</th>';
	$releve_HTML_table_foot2 = '<tr><th>% validations [**]</th>';
	$memo_x = $releve_PDF->GetX();
	$releve_PDF->SetXY($memo_x,$memo_y);
	// Pour chaque item...
	foreach($tab_liste_item as $item_id)
	{
		$releve_PDF->bilan_periode_synthese_pourcentages($tab_moyenne_scores_item[$item_id],$tab_pourcentage_acquis_item[$item_id],true,false);
		$releve_HTML_table_foot1 .= affich_score_html($tab_moyenne_scores_item[$item_id],'score','%');
		$releve_HTML_table_foot2 .= affich_score_html($tab_pourcentage_acquis_item[$item_id],'score','%');
	}
	// les deux dernières cases (moyenne des moyennes)
	$releve_PDF->bilan_periode_synthese_pourcentages($moyenne_moyenne_scores,$moyenne_pourcentage_acquis,true,true);
	$releve_HTML_table_foot1 .= '<th class="nu">&nbsp;</th>'.affich_score_html($moyenne_moyenne_scores,'score','%').'<th class="nu">&nbsp;</th></tr>';
	$releve_HTML_table_foot2 .= '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.affich_score_html($moyenne_pourcentage_acquis,'score','%').'</tr>';
	$releve_HTML_table_foot = '<tfoot><tr><td class="nu" colspan="'.$colspan.'" style="font-size:0;height:9px">&nbsp;</td></tr>'.$releve_HTML_table_foot1.$releve_HTML_table_foot2.'</tfoot>'."\r\n";
	$num_hide = $item_nb+1;
	// pour la sortie HTML, on peut placer les tableaux de synthèse au début
	$releve_HTML_synthese .= '<hr /><h2>SYNTHESE - Colonnes triables par score (intérêt pour un tri simple)</h2>';
	$releve_HTML_synthese .= '<table id="table_s1" class="bilan_synthese">'.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body1.'</table>';
	$releve_HTML_synthese .= '<script type="text/javascript">$("#table_s1").tablesorter({ headers:{'.$num_hide.':{sorter:false}} });</script>'; // Non placé dans le fichier js car mettre une valeur à la place d'une variable pour $num_hide ne fonctionne pas
	$releve_HTML_synthese .= '<hr /><h2>SYNTHESE - Colonnes triables par état de validation (intérêt pour un tri multiple)</h2></h2>';
	$releve_HTML_synthese .= '<table id="table_s2" class="bilan_synthese">'.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body2.'</table>';
	$releve_HTML_synthese .= '<script type="text/javascript">$("#table_s2").tablesorter({ headers:{'.$num_hide.':{sorter:false}} });</script>'; // Non placé dans le fichier js car mettre une valeur à la place d'une variable pour $num_hide ne fonctionne pas
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'_synthese.html',$releve_HTML_synthese);
	$releve_PDF->Output($dossier.$fichier_lien.'_synthese.pdf','F');
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Elaboration du bulletin (moyenne & appréciation) en HTML et CSV pour GEPI
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

if(in_array('bulletin',$tab_type))
{
	/*
	$tab_bad[] = '0NA'; $tab_bon[] = '0 non acquise';
	$tab_bad[] = '1NA'; $tab_bon[] = '1 non acquise';
	$tab_bad[] =  'NA'; $tab_bon[] = ' non acquises';
	$tab_bad[] = '0VA'; $tab_bon[] = '0 partiellement acquise ;';
	$tab_bad[] = '1VA'; $tab_bon[] = '1 partiellement acquise ;';
	$tab_bad[] =  'VA'; $tab_bon[] = ' partiellement acquises ;';
	$tab_bad[] =  '0A'; $tab_bon[] = '0 acquise ;';
	$tab_bad[] =  '1A'; $tab_bon[] = '1 acquise ;';
	$tab_bad[] =   'A'; $tab_bon[] = ' acquises ;';
	// pour str_replace($tab_bad,$tab_bon,$tab_infos_acquis_eleve[$matiere_id][$eleve_id])
	*/
	$bulletin_body = '';
	$bulletin_csv_gepi = 'GEPI_IDENTIFIANT;NOTE;APPRECIATION'."\r\n";	// Ajout du préfixe 'GEPI_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr)
	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
		$note         = ($tab_moyenne_scores_eleve[$matiere_id][$eleve_id] !== false)          ? sprintf("%04.1f",$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5)                                                                             : '-' ;
		$appreciation = ($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] !== false) ? $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id].'% d\'items acquis ('.$tab_infos_acquis_eleve[$matiere_id][$eleve_id].')' : '-' ;
		$bulletin_body     .= '<tr><th>'.html($eleve_nom.' '.$eleve_prenom).'</th><td>'.$note.'</td><td>'.$appreciation.'</td></tr>'."\r\n";
		// Pour gépi je remplace le point décimal par une virgule sinon le tableur convertit en date...
		$bulletin_csv_gepi .= $eleve_id_gepi.';'.str_replace('.',',',$note).';'.$appreciation."\r\n";
	}
	$bulletin_head  = '<thead><tr><th>Elève</th><th>Moyenne pondérée sur 20<br />(des scores d\'acquisitions)</th><th>Élément d\'appréciation<br />(pourcentage d\'items acquis)</th></tr></thead>'."\r\n";
	$bulletin_body  = '<tbody>'."\r\n".$bulletin_body.'</tbody>'."\r\n";
	$bulletin_foot  = '<tfoot><tr><th>Moyenne sur 20</th><th>'.sprintf("%04.1f",$moyenne_moyenne_scores/5).'</th><th>'.$moyenne_pourcentage_acquis.'% d\'items acquis</th></tr></tfoot>'."\r\n";
	$bulletin_html  = '<h1>Bilan disciplinaire</h1>';
	$bulletin_html .= '<h2>'.html($matiere_nom.' - '.$groupe_nom).'</h2>';
	$bulletin_html .= '<h2>Du '.$date_debut.' au '.$date_fin.$date_complement.'</h2>';
	$bulletin_html .= '<h2>Tableau de notes sur 20</h2>';
	$bulletin_html .= '<table id="export20">'."\r\n".$bulletin_head.$bulletin_foot.$bulletin_body.'</table>'."\r\n";
	$bulletin_html .= '<script type="text/javascript">$("#export20").tablesorter({ headers:{2:{sorter:false}} });</script>';
	// On enregistre la sortie HTML et CSV
	Ecrire_Fichier($dossier.$fichier_lien.'_bulletin.html',$bulletin_html);
	Ecrire_Fichier($dossier.$fichier_lien.'_bulletin.csv',utf8_decode($bulletin_csv_gepi));
}

?>