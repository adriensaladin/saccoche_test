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
 * [./pages/releve_synthese_matiere.ajax.php]
 * [./pages/releve_synthese_multimatiere.ajax.php]
 * [./pages/officiel_action_***.ajax.php]
 */

// Chemins d'enregistrement

$dossier     = './__tmp/export/';
$fichier_nom = 'releve_synthese_'.$format.'_'.clean_fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier();

// Initialisation de tableaux

$tab_item       = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,matiere_id,calcul_methode,calcul_limite,synthese_ref);
$tab_liste_item = array();	// [i] => item_id
$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
$tab_matiere    = array();	// [matiere_id] => matiere_nom
$tab_synthese   = array();	// [synthese_ref] => synthese_nom
$tab_eval       = array();	// [eleve_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

// Initialisation de variables

if( ($make_html) || ($make_pdf) )
{
	$tab_titre = array('matiere'=>'d\'une matière' , 'multimatiere'=>'multidisciplinaire');
	if(!$aff_coef)  { $texte_coef       = ''; }
	if(!$aff_socle) { $texte_socle      = ''; }
	if(!$aff_lien)  { $texte_lien_avant = ''; }
	if(!$aff_lien)  { $texte_lien_apres = ''; }
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Période concernée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($periode_id==0)
{
	$date_mysql_debut = convert_date_french_to_mysql($date_debut);
	$date_mysql_fin   = convert_date_french_to_mysql($date_fin);
}
else
{
	$DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
	if(!count($DB_ROW))
	{
		exit('La classe et la période ne sont pas reliées !');
	}
	$date_mysql_debut = $DB_ROW['jointure_date_debut'];
	$date_mysql_fin   = $DB_ROW['jointure_date_fin'];
	$date_debut = convert_date_mysql_to_french($date_mysql_debut);
	$date_fin   = convert_date_mysql_to_french($date_mysql_fin);
}
if($date_mysql_debut>$date_mysql_fin)
{
	exit('La date de début est postérieure à la date de fin !');
}

$date_complement = ($retroactif=='oui') ? ' (notes antérieures comptées).' : '.';
$texte_periode   = 'Du '.$date_debut.' au '.$date_fin.$date_complement;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items travaillés durant la période choisie, pour les élèves selectionnés, toutes matières confondues
// Récupération de la liste des synthèses concernées (nom de thèmes ou de domaines suivant les référentiels)
// Récupération de la liste des matières concernées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($format=='matiere')
{
	list($tab_item,$tab_synthese) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_synthese($liste_eleve,$matiere_id,$only_socle,$only_niveau,$mode_synthese,$date_mysql_debut,$date_mysql_fin);
	$tab_matiere[$matiere_id] = $matiere_nom;
}
elseif($format=='multimatiere')
{
	$matiere_id = 0;
	list($tab_item,$tab_synthese,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_synthese($liste_eleve,$matiere_id,$only_socle,$only_niveau,$mode_synthese='predefini',$date_mysql_debut,$date_mysql_fin);
}
$item_nb = count($tab_item);
if(!$item_nb)
{
	exit('Aucun item évalué sur cette période selon les critères indiqués !');
}
$tab_liste_item = array_keys($tab_item);
$liste_item = implode(',',$tab_liste_item);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des élèves
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($liste_eleve,$with_gepi=FALSE,$with_langue=FALSE);
if(!is_array($tab_eleve))
{
	exit('Aucun élève trouvé correspondant aux identifiants transmis !');
}
$eleve_nb = count($tab_eleve);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres donnée(s), pour les élèves selectionnés, sur la période sélectionnée
// Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_score_a_garder = array();
$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items($liste_eleve,$liste_item);
foreach($DB_TAB as $DB_ROW)
{
	$tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
}

$date_mysql_debut = ($retroactif=='non') ? $date_mysql_debut : FALSE;
$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($liste_eleve , $liste_item , $matiere_id , $date_mysql_debut , $date_mysql_fin , $_SESSION['USER_PROFIL']);
foreach($DB_TAB as $DB_ROW)
{
	if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
	{
		$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
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
			if($make_html)
			{
				if($score!==FALSE)
				{
					$indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
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
					$texte_demande_eval = ($_SESSION['USER_PROFIL']!='eleve') ? '' : ( ($item_cart) ? '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
					$tab_infos_detail_synthese[$eleve_id][$synthese_ref][] = '<span class="'.$tab_etat[$indice].'">'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_ref.' || '.$item_nom.' ['.$score.'%]').'</span>'.$texte_lien_apres.$texte_demande_eval;
				}
			}
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
				if(!isset($tab_infos_acquis_eleve[$eleve_id][$matiere_id]))
				{
					// Le mettre avant le test sur $nb_scores permet d'avoir au moins le titre des matières où il y a des saisies mais seulement ABS NN etc. (et donc d'avoir la rubrique sur le bulletin).
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total'] = array('NA'=>0 , 'VA'=>0 , 'A'=>0);
				}
				if($nb_scores)
				{
					$nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
					$nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
					$nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id][$synthese_ref] = ($make_for=='releve') ? array( 'NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis ) : array( 'NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis, 'nb'=>$nb_scores , '%'=>round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0) ) ;
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

$affichage_direct = ( ( ( in_array($_SESSION['USER_PROFIL'],array('eleve','parent')) ) && (SACoche!='webservices') ) || ($make_for=='officiel') ) ? TRUE : FALSE ;

// Préparatifs
if($make_html)
{
	$releve_HTML  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML .= $affichage_direct ? '' : '<h1>Synthèse '.$tab_titre[$format].'</h1>';
	$releve_HTML .= $affichage_direct ? '' : '<h2>'.html($texte_periode).'</h2>';
	$releve_HTML .= '<div class="astuce">Cliquer sur les icones &laquo;<img src="./_img/toggle_plus.gif" alt="+" />&raquo; pour accéder au détail.</div>';
	$separation = (count($tab_eleve)>1) ? '<hr class="breakafter" />' : '' ;
}
if($make_pdf)
{
	$releve_PDF = new PDF($orientation='portrait',$marge_min=7,$couleur,$legende);
	$releve_PDF->bilan_synthese_initialiser($format,$nb_syntheses_total,$eleve_nb);
}
// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi $nb_matieres $nb_syntheses
	// Si cet élève a été évalué...
	if(isset($tab_infos_acquis_eleve[$eleve_id]))
	{
		// Intitulé
		if($make_pdf)  { $releve_PDF->bilan_synthese_entete($format,$nb_matieres,$nb_syntheses,$tab_titre[$format],$texte_periode,$groupe_nom,$eleve_nom,$eleve_prenom); }
		if($make_html) { $releve_HTML .= ($make_for=='releve') ? $separation.'<h2>'.html($groupe_nom).' - '.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : '' ; }
		// On passe en revue les matières...
		foreach($tab_infos_acquis_eleve[$eleve_id] as $matiere_id => $tab_infos_matiere)
		{
			if( ($make_for=='releve') || (($make_action=='saisir')&&($BILAN_ETAT=='3synthese')) || (($make_action=='saisir')&&($BILAN_ETAT=='2rubrique')&&(in_array($matiere_id,$tab_matiere_id))) || (($make_action=='examiner')&&(in_array($matiere_id,$tab_matiere_id))) || ($make_action=='consulter') )
			{
				$tab_infos_matiere['total'] = array_filter($tab_infos_matiere['total'],'non_zero'); // Retirer les valeurs nulles
				$total = array_sum($tab_infos_matiere['total']) ; // La somme ne peut être nulle, sinon la matière ne se serait pas affichée
				if($make_pdf)
				{
					$releve_PDF->bilan_synthese_ligne_matiere($format,$tab_matiere[$matiere_id],$tab_infos_matiere['total'],$total);
				}
				if($make_html)
				{
					$releve_HTML .= '<table class="bilan" style="width:900px;margin-bottom:0"><tbody>';
					$releve_HTML .= '<tr><th style="width:540px">'.html($tab_matiere[$matiere_id]).'</th>'.affich_barre_synthese_html($width=360,$tab_infos_matiere['total'],$total).'</tr>';
					$releve_HTML .= '</tbody></table>'; // Utilisation de 2 tableaux sinon bugs constatés lors de l'affichage des détails...
					$releve_HTML .= '<table class="bilan" style="width:900px;margin-top:0"><tbody>';
				}
				//  On passe en revue les synthèses...
				unset($tab_infos_matiere['total']);
				foreach($tab_infos_matiere as $synthese_ref => $tab_infos_synthese)
				{
					if($make_for=='releve')
					{
						$tab_infos_synthese = array_filter($tab_infos_synthese,'non_zero'); // Retirer les valeurs nulles
						$total = array_sum($tab_infos_synthese) ; // La somme ne peut être nulle (sinon la matière ne se serait pas affichée)
					}
					if($make_pdf)
					{
						$releve_PDF->bilan_synthese_ligne_synthese($tab_synthese[$synthese_ref],$tab_infos_synthese,$total);
					}
					if($make_html)
					{
						$releve_HTML .= '<tr>';
						$releve_HTML .= ($make_for=='releve') ? affich_barre_synthese_html($width=180,$tab_infos_synthese,$total) : affich_pourcentage_html( 'td' , $tab_infos_synthese , FALSE /*detail*/ , 50 /*largeur*/ ) ;
						$releve_HTML .= ($make_for=='releve') ? '<td style="width:720px">' : '<td style="width:850px">' ;
						$releve_HTML .= '<a href="#" id="to_'.$synthese_ref.'_'.$eleve_id.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir / masquer le détail des items associés." class="toggle" /></a> ';
						$releve_HTML .= html($tab_synthese[$synthese_ref]);
						$releve_HTML .= '<div id="'.$synthese_ref.'_'.$eleve_id.'" class="hide">'.implode('<br />',$tab_infos_detail_synthese[$eleve_id][$synthese_ref]).'</div>';
						$releve_HTML .= '</td></tr>';
					}
				}
				if($make_html)
				{
					// Bulletin - Note
					if( ($make_for=='officiel') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) )
					{
						// $tab_saisie[$eleve_id][$matiere_id][0] est normalement toujours défini : soit calculé lors de l'initialisation du bulletin, soit effacé et non recalculé volontairement mais alors vaut NULL
						extract($tab_saisie[$eleve_id][$matiere_id][0]);	// $prof_info $appreciation $note
						if($make_html)
						{
							$bouton_nettoyer  = ($appreciation!='') ? ' <button type="button" class="nettoyer">Effacer et recalculer.</button>' : '' ;
							$bouton_supprimer = ($note!==NULL)      ? ' <button type="button" class="supprimer">Supprimer sans recalculer</button>' : '' ;
							$note = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? $note : ($note*5).'%' ) : '-' ;
							$appreciation = ($appreciation!='') ? $appreciation : 'Moyenne calculée / reportée / actualisée automatiquement.' ;
							$action = ( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') ) ? ' <button type="button" class="modifier">Modifier</button>'.$bouton_nettoyer.$bouton_supprimer : '' ;
							$moyenne_classe = '';
							if( ($make_action=='consulter') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) )
							{
								$note_moyenne = ($_SESSION['tmp_moyenne'][$periode_id][$classe_id][$matiere_id]!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($_SESSION['tmp_moyenne'][$periode_id][$classe_id][$matiere_id],1,'.','') : round($_SESSION['tmp_moyenne'][$periode_id][$classe_id][$matiere_id]*5).'%' ) : '-' ;
								$moyenne_classe = ' Moyenne de classe : '.$note_moyenne;
							}
							$releve_HTML .= '<tr id="note_'.$matiere_id.'_0"><td class="now moyenne">'.$note.'</td><td class="now"><span class="notnow">'.html($appreciation).$action.'</span>'.$moyenne_classe.'</td></tr>'."\r\n";
						}
					}
					// Bulletin - Appréciation
					if( ($make_for=='officiel') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) )
					{
						// $tab_saisie[$eleve_id][$matiere_id][0] est normalement toujours défini : soit calculé lors de l'initialisation du bulletin, soit effacé et non recalculé volontairement mais alors vaut NULL
						foreach($tab_saisie[$eleve_id][$matiere_id] as $prof_id => $tab)
						{
							if($prof_id) // Sinon c'est la note.
							{
								extract($tab);	// $prof_info $appreciation $note
								if($make_html)
								{
									$action = ( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') && ($prof_id==$_SESSION['USER_ID']) ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : ' <button type="button" class="signaler">Signaler une erreur</button>' ;
									$releve_HTML .= '<tr id="appr_'.$matiere_id.'_'.$prof_id.'"><td colspan="2" class="now"><div class="notnow">'.html($prof_info).$action.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'."\r\n";
								}
							}
						}
						if( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') )
						{
							if(!isset($tab_saisie[$eleve_id][$matiere_id][$_SESSION['USER_ID']]))
							{
								$releve_HTML .= '<tr id="appr_'.$matiere_id.'_'.$_SESSION['USER_ID'].'"><td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter une appréciation.</button></div></td></tr>'."\r\n";
							}
						}
					}
					$releve_HTML .= '</tbody></table>';
				}
				// Examen de présence des appréciations et des notes
				if($make_action=='examiner')
				{
					if( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && ($tab_saisie[$eleve_id][$matiere_id][0]['note']===NULL) )
					{
						$tab_resultat_examen[$tab_matiere[$matiere_id]][] = 'Absence de note pour '.html($eleve_nom.' '.$eleve_prenom);
					}
					if( ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) && (count($tab_saisie[$eleve_id][$matiere_id])<2) )
					{
						$tab_resultat_examen[$tab_matiere[$matiere_id]][] = 'Absence d\'appréciation pour '.html($eleve_nom.' '.$eleve_prenom);
					}
				}
			}
		}
		// Bulletin - Appréciation générale
		if( ($make_for=='officiel') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) && ($BILAN_ETAT=='3synthese') )
		{
			if($make_html)
			{
				$releve_HTML .= '<table class="bilan" style="width:900px"><tbody>';
				$releve_HTML .= '<tr><th>Synthèse générale</th></tr>';
				if(isset($tab_saisie[$eleve_id][0]))
				{
					list($prof_id,$tab) = each($tab_saisie[$eleve_id][0]);
					extract($tab);	// $prof_info $appreciation $note
					$action = ( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
					$releve_HTML .= '<tr id="appr_0_'.$prof_id.'"><td class="now"><div class="notnow">'.html($prof_info).$action.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'."\r\n";
				}
				elseif( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') )
				{
					$releve_HTML .= '<tr id="appr_0_'.$_SESSION['USER_ID'].'"><td class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'appréciation générale.</button></div></td></tr>'."\r\n";
				}
				$releve_HTML .= '</tbody></table>'."\r\n";
			}
		}
		// Examen de présence de l'appréciation générale
		if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) && (in_array(0,$tab_rubrique_id)) )
		{
			if(!isset($tab_saisie[$eleve_id][0]))
			{
				$tab_resultat_examen['Synthèse générale'][] = 'Absence d\'appréciation générale pour '.html($eleve_nom.' '.$eleve_prenom);
			}
		}
		if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
		{
			if($make_pdf)  { $releve_PDF->bilan_synthese_legende($format); }
			if($make_html) { $releve_HTML .= affich_legende_html($note_Lomer=FALSE,$etat_bilan=TRUE); }
		}
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On enregistre les sorties HTML et PDF
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($make_html) { Ecrire_Fichier($dossier.$fichier_nom.'.html',$releve_HTML); }
if($make_pdf)  { $releve_PDF->Output($dossier.$fichier_nom.'.pdf','F'); }

?>