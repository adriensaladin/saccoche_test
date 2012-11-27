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

/**
 * Code inclus commun aux pages
 * [./pages/releve_synthese_matiere.ajax.php]
 * [./pages/releve_synthese_multimatiere.ajax.php]
 * [./pages/officiel_action_***.ajax.php]
 */

// La récupération de beaucoup d'informations peut provoquer un dépassement de mémoire.
// Et la classe FPDF a besoin de mémoire, malgré toutes les optimisations possibles, pour générer un PDF comportant parfois entre 100 et 200 pages.
// De plus la consommation d'une classe PHP n'est pas mesurable - non comptabilisée par memory_get_usage() - et non corrélée à la taille de l'objet PDF en l'occurrence...
// Un memory_limit() de 64Mo est ainsi dépassé avec un pdf d'environ 150 pages, ce qui est atteint avec 4 pages par élèves ou un groupe d'élèves > effectif moyen d'une classe.
// D'où le ini_set(), même si cette directive peut être interdite dans la conf PHP ou via Suhosin (http://www.hardened-php.net/suhosin/configuration.html#suhosin.memory_limit)
// En complément, register_shutdown_function() permet de capter une erreur fatale de dépassement de mémoire, sauf si CGI.
// D'où une combinaison de toutes ces pistes, plus une détection par javascript du statusCode.

augmenter_memory_limit();
register_shutdown_function('rapporter_erreur_fatale_memoire');

// Chemins d'enregistrement

$fichier_nom = ($make_action!='imprimer') ? 'releve_synthese_'.$format.'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() : 'officiel_'.$BILAN_TYPE.'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() ;

// Initialisation de tableaux

$tab_item       = array();	// [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,matiere_id,calcul_methode,calcul_limite,calcul_retroactif,synthese_ref);
$tab_liste_item = array();	// [i] => item_id
$tab_eleve      = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
$tab_matiere    = array();	// [matiere_id] => matiere_nom
$tab_synthese   = array();	// [synthese_ref] => synthese_nom
$tab_eval       = array();	// [eleve_id][item_id][devoir] => array(note,date,info) On utilise un tableau multidimensionnel vu qu'on ne sait pas à l'avance combien il y a d'évaluations pour un élève et un item donnés.

// Initialisation de variables

if( ($make_html) || ($make_pdf) || ($make_graph) )
{
	$tab_titre = array('matiere'=>'d\'une matière' , 'multimatiere'=>'multidisciplinaire');
	if(!$aff_coef)  { $texte_coef       = ''; }
	if(!$aff_socle) { $texte_socle      = ''; }
	if(!$aff_lien)  { $texte_lien_avant = ''; }
	if(!$aff_lien)  { $texte_lien_apres = ''; }
	$toggle_img   = ($aff_start) ? 'toggle_moins' : 'toggle_plus' ;
	$toggle_class = ($aff_start) ? '' : ' class="hide"' ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Période concernée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($periode_id==0)
{
	$date_mysql_debut = convert_date_french_to_mysql($date_debut);
	$date_mysql_fin   = convert_date_french_to_mysql($date_fin);
}
else
{
	$DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
	if(empty($DB_ROW))
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

$tab_precision = array
(
	'auto' => 'notes antérieures comptées selon les référentiels',
	'oui'  => 'notes antérieures prises en compte',
	'non'  => 'notes antérieures ignorées'
);
$texte_periode = 'Du '.$date_debut.' au '.$date_fin.' ('.$tab_precision[$retroactif].').';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items travaillés durant la période choisie, pour les élèves selectionnés, toutes matières confondues
// Récupération de la liste des synthèses concernées (nom de thèmes ou de domaines suivant les référentiels)
// Récupération de la liste des matières concernées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
if( !$item_nb && !$make_officiel ) // Dans le cas d'un bilan officiel, où l'on regarde les élèves d'un groupe un à un, ce ne doit pas être bloquant.
{
	exit('Aucun item évalué sur cette période selon les paramètres choisis !');
}
$tab_liste_item = array_keys($tab_item);
$liste_item = implode(',',$tab_liste_item);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles($liste_eleve,$with_gepi=FALSE,$with_langue=FALSE);
if(!is_array($tab_eleve))
{
	exit('Aucun élève trouvé correspondant aux identifiants transmis !');
}
$eleve_nb = count($tab_eleve);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres donnée(s), pour les élèves selectionnés, sur la période sélectionnée
// Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_score_a_garder = array();
if($item_nb) // Peut valoir 0 dans le cas d'un bilan officiel où l'on regarde les élèves d'un groupe un à un (il ne faut pas qu'un élève sans rien soit bloquant).
{
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items($liste_eleve,$liste_item);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
	}

	$date_mysql_start = ($retroactif=='non') ? $date_mysql_debut : FALSE ; // En 'auto' il faut faire le tri après.
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($liste_eleve , $liste_item , $matiere_id , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL']);
	foreach($DB_TAB as $DB_ROW)
	{
		if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
		{
			if( ($retroactif!='auto') || ($tab_item[$DB_ROW['item_id']][0]['calcul_retroactif']=='oui') || ($DB_ROW['date']>=$date_mysql_debut) )
			{
				$tab_eval[$DB_ROW['eleve_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
			}
		}
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////

DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
			extract($tab_item[$item_id][0]);	// $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $matiere_id $calcul_methode $calcul_limite $calcul_retroactif $synthese_ref
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
		foreach($tab_score_eleve_item[$eleve_id] as $matiere_id => $tab_matiere_scores)
		{
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
					// $tab_infos_acquis_eleve[$eleve_id][$matiere_id][$synthese_ref] = (!$make_officiel) ? array( 'NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis ) : array( 'NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis, 'nb'=>$nb_scores , '%'=>round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0) ) ;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id][$synthese_ref] = array( 'NA'=>$nb_non_acquis , 'VA'=>$nb_voie_acquis , 'A'=>$nb_acquis );
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['NA'] += $nb_non_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['VA'] += $nb_voie_acquis;
					$tab_infos_acquis_eleve[$eleve_id][$matiere_id]['total']['A']  += $nb_acquis;
				}
			}
		}
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Compter le nombre de lignes à afficher par élève par matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_nb_lignes = array();
$tab_nb_lignes_par_matiere = array();
$nb_lignes_appreciation_intermediaire_par_prof_hors_intitule = $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE'] / 100 / 2 ;
$nb_lignes_appreciation_generale_avec_intitule = ( $make_officiel && $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE'] ) ? 1+6     : 0 ;
$nb_lignes_assiduite                           = ( $make_officiel && ($affichage_assiduite) )                                  ? 0.5+1.5 : 0 ;
$nb_lignes_supplementaires                     = ( $make_officiel && $_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE'] )  ? 0.5+1.5 : 0 ;
$nb_lignes_legendes                            = ($legende=='oui') ? 0.5 + 1 : 0 ;
$nb_lignes_matiere_marge    = 1 ;
$nb_lignes_matiere_intitule = 2 ;
$nb_lignes_matiere_intitule_et_marge = $nb_lignes_matiere_marge + $nb_lignes_matiere_intitule ;

foreach($tab_eleve as $key => $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
	foreach($tab_matiere as $matiere_id => $matiere_nom)
	{
		if(isset($tab_score_eleve_item[$eleve_id][$matiere_id]))
		{
			// Ne pas compter les lignes de synthèses dont aucun item n'a été évalué
			foreach($tab_score_eleve_item[$eleve_id][$matiere_id] as $synthese_ref => $tab_items)
			{
				$nb_items_evalues = count(array_filter($tab_items,'non_nul'));
				if(!$nb_items_evalues)
				{
					unset($tab_score_eleve_item[$eleve_id][$matiere_id][$synthese_ref]);
				}
			}
			$nb_lignes_rubriques = count($tab_score_eleve_item[$eleve_id][$matiere_id]) ;
			$nb_lignes_appreciations = ( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) && (isset($tab_saisie[$eleve_id][$matiere_id])) ) ? ($nb_lignes_appreciation_intermediaire_par_prof_hors_intitule * count($tab_saisie[$eleve_id][$matiere_id]) ) + 1 : 0 ; // + 1 pour "Appréciation / Conseils pour progresser"
			$tab_nb_lignes[$eleve_id][$matiere_id] = $nb_lignes_matiere_intitule_et_marge + max($nb_lignes_rubriques,$nb_lignes_appreciations) ;
		}
	}
}

// Calcul des totaux une unique fois par élève
$tab_nb_lignes_total_eleve = array();
foreach($tab_nb_lignes as $eleve_id => $tab)
{
	$tab_nb_lignes_total_eleve[$eleve_id] = array_sum($tab);
}
$nb_lignes_total = array_sum($tab_nb_lignes_total_eleve);

// Nombre de boucles par élève (entre 1 et 3 pour les bilans officiels, dans ce cas $tab_destinataires[] est déjà complété ; une seule dans les autres cas).
if(!isset($tab_destinataires))
{
	foreach($tab_eleve as $tab)
	{
		$tab_destinataires[$tab['eleve_id']][0] = TRUE ;
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la synthèse matière ou multi-matières, en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_direct = ( ( ( in_array($_SESSION['USER_PROFIL'],array('eleve','parent')) ) && (SACoche!='webservices') ) || ($make_officiel) ) ? TRUE : FALSE ;

$tab_graph_data = array();

// Préparatifs
if( ($make_html) || ($make_graph) )
{
	$bouton_print_appr = ((!$make_graph)&&($make_officiel)) ? ' <button id="imprimer_appreciations_perso" type="button" class="imprimer">Imprimer mes appréciations</button> <button id="imprimer_appreciations_all" type="button" class="imprimer">Imprimer toutes les appréciations</button>' : '' ;
	$releve_HTML  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML .= $affichage_direct ? '' : '<h1>Synthèse '.$tab_titre[$format].'</h1>';
	$releve_HTML .= $affichage_direct ? '' : '<h2>'.html($texte_periode).'</h2>';
	$releve_HTML .= (!$make_graph) ? '<div class="astuce">Cliquer sur <img src="./_img/toggle_plus.gif" alt="+" /> / <img src="./_img/toggle_moins.gif" alt="+" /> pour afficher / masquer le détail.'.$bouton_print_appr.'</div>' : '<div id="div_graphique"></div>' ;
	$separation = (count($tab_eleve)>1) ? '<hr class="breakafter" />' : '' ;
	$legende_html = ($legende=='oui') ? Html::legende( FALSE /*codes_notation*/ , FALSE /*anciennete_notation*/ , FALSE /*score_bilan*/ , TRUE /*etat_acquisition*/ , FALSE /*pourcentage_acquis*/ , FALSE /*etat_validation*/ , $make_officiel ) : '' ;
	$width_barre = (!$make_officiel) ? 180 : 50 ;
	$width_texte = 900 - $width_barre;
}
if($make_pdf)
{
	$releve_PDF = new PDF( $make_officiel , 'portrait' /*orientation*/ , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $legende );
	$releve_PDF->bilan_synthese_initialiser($format,$nb_lignes_total,$eleve_nb);
}
// Pour chaque élève...
foreach($tab_eleve as $tab)
{
	extract($tab);	// $eleve_id $eleve_nom $eleve_prenom $eleve_id_gepi
	foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
	{
		// Si cet élève a été évalué...
		if(isset($tab_infos_acquis_eleve[$eleve_id]))
		{
			// Intitulé
			if($make_html) { $releve_HTML .= (!$make_officiel) ? $separation.'<h2>'.html($groupe_nom.' - '.$eleve_nom.' '.$eleve_prenom).'</h2>' : '' ; }
			if($make_pdf)
			{
				$eleve_nb_lignes  = $tab_nb_lignes_total_eleve[$eleve_id] + $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite + $nb_lignes_supplementaires;
				$tab_infos_entete = (!$make_officiel) ? array( $tab_titre[$format] , $texte_periode , $groupe_nom ) : array($tab_etabl_coords,$etabl_coords__bloc_hauteur,$tab_bloc_titres,$tab_adresse,$tag_date_heure_initiales) ;
				$releve_PDF->bilan_synthese_entete( $format , $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_nb_lignes );
			}
			// On passe en revue les matières...
			foreach($tab_infos_acquis_eleve[$eleve_id] as $matiere_id => $tab_infos_matiere)
			{
				if( (!$make_officiel) || (($make_action=='saisir')&&($BILAN_ETAT=='3synthese')) || (($make_action=='saisir')&&($BILAN_ETAT=='2rubrique')&&(in_array($matiere_id,$tab_matiere_id))) || (($make_action=='examiner')&&(in_array($matiere_id,$tab_matiere_id))) || ($make_action=='consulter') || ($make_action=='imprimer') )
				{
					// Bulletin - Interface graphique
					if($make_graph)
					{
						$tab_graph_data['categories'][$matiere_id] = '"'.addcslashes($tab_matiere[$matiere_id],'"').'"';
						$tab_graph_data['series_data_NA'][$matiere_id] = $tab_infos_matiere['total']['NA'];
						$tab_graph_data['series_data_VA'][$matiere_id] = $tab_infos_matiere['total']['VA'];
						$tab_graph_data['series_data_A'][$matiere_id]  = $tab_infos_matiere['total']['A'];
						if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
						{
							$tab_graph_data['series_data_MoyEleve'][$matiere_id] = ($tab_saisie[$eleve_id][$matiere_id][0]['note']!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $tab_saisie[$eleve_id][$matiere_id][0]['note'] : round($tab_saisie[$eleve_id][$matiere_id][0]['note']*5) ) : 'null' ;
							if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
							{
								$tab_graph_data['series_data_MoyClasse'][$matiere_id] = ($_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id]!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id] : round($_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id]*5) ) : 'null' ;
							}
						}
					}
					$tab_infos_matiere['total'] = array_filter($tab_infos_matiere['total'],'non_zero'); // Retirer les valeurs nulles
					$total = array_sum($tab_infos_matiere['total']) ; // La somme ne peut être nulle, sinon la matière ne se serait pas affichée
					if($make_pdf)
					{
						$moyenne_eleve  = NULL;
						$moyenne_classe = NULL;
						if( ($make_officiel) && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && (isset($tab_saisie[$eleve_id][$matiere_id][0])) )
						{
							// $tab_saisie[$eleve_id][$matiere_id][0] est normalement toujours défini : soit calculé lors de l'initialisation du bulletin, soit effacé et non recalculé volontairement mais alors vaut NULL (à moins que le choix de l'affichage d'une moyenne se fasse simultanément)
							extract($tab_saisie[$eleve_id][$matiere_id][0]);	// $prof_info $appreciation $note
							$moyenne_eleve = $note;
							if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
							{
								$moyenne_classe = $_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id];
							}
						}
						$releve_PDF->bilan_synthese_ligne_matiere($format,$tab_matiere[$matiere_id],$tab_nb_lignes[$eleve_id][$matiere_id],$tab_infos_matiere['total'],$total,$moyenne_eleve,$moyenne_classe);
					}
					if($make_html)
					{
						$releve_HTML .= '<table class="bilan" style="width:900px;margin-bottom:0"><tbody><tr>';
						$releve_HTML .= '<th style="width:540px">'.html($tab_matiere[$matiere_id]).'</th>';
						$releve_HTML .= ($_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS']) ? Html::td_barre_synthese($width=360,$tab_infos_matiere['total'],$total) : '<td style="width:360px"></td>' ;
						$releve_HTML .= '</tr></tbody></table>'; // Utilisation de 2 tableaux sinon bugs constatés lors de l'affichage des détails...
						$releve_HTML .= '<table class="bilan" style="width:900px;margin-top:0"><tbody>';
					}
					//  On passe en revue les synthèses...
					unset($tab_infos_matiere['total']);
					$nb_syntheses = count($tab_infos_matiere);
					if($nb_syntheses)
					{
						$hauteur_ligne_synthese = ($make_officiel) ? ( $tab_nb_lignes[$eleve_id][$matiere_id] - $nb_lignes_matiere_intitule_et_marge ) / count($tab_infos_matiere) : 1 ;
						foreach($tab_infos_matiere as $synthese_ref => $tab_infos_synthese)
						{
							$tab_infos_synthese = array_filter($tab_infos_synthese,'non_zero'); // Retirer les valeurs nulles
							$total = array_sum($tab_infos_synthese) ; // La somme ne peut être nulle (sinon la matière ne se serait pas affichée)
							if($make_pdf)
							{
								$releve_PDF->bilan_synthese_ligne_synthese($tab_synthese[$synthese_ref],$tab_infos_synthese,$total,$hauteur_ligne_synthese);
							}
							if($make_html)
							{
								$releve_HTML .= '<tr>';
								$releve_HTML .= Html::td_barre_synthese($width_barre,$tab_infos_synthese,$total);
								$releve_HTML .= '<td style="width:'.$width_texte.'px">' ;
								$releve_HTML .= '<a href="#" id="to_'.$synthese_ref.'_'.$eleve_id.'"><img src="./_img/'.$toggle_img.'.gif" alt="" title="Voir / masquer le détail des items associés." class="toggle" /></a> ';
								$releve_HTML .= html($tab_synthese[$synthese_ref]);
								$releve_HTML .= '<div id="'.$synthese_ref.'_'.$eleve_id.'"'.$toggle_class.'>'.implode('<br />',$tab_infos_detail_synthese[$eleve_id][$synthese_ref]).'</div>';
								$releve_HTML .= '</td></tr>';
							}
						}
					}
					elseif( ($make_officiel) && ($make_pdf) )
					{
						// Il est possible qu'aucun item n'ait été évalué pour un élève (absent...) : il faut quand même dessiner un cadre pour ne pas provoquer un décalage, d'autant plus qu'il peut y avoir une appréciation à côté.
						$hauteur_ligne_synthese = $tab_nb_lignes[$eleve_id][$matiere_id] - $nb_lignes_matiere_intitule_et_marge ;
						$releve_PDF->bilan_synthese_ligne_synthese('',array(),0,$hauteur_ligne_synthese);
					}
					if($make_html)
					{
						// Bulletin - Note (HTML)
						if( ($make_html) && ($make_officiel) && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && (isset($tab_saisie[$eleve_id][$matiere_id][0])) )
						{
							// $tab_saisie[$eleve_id][$matiere_id][0] est normalement toujours défini : soit calculé lors de l'initialisation du bulletin, soit effacé et non recalculé volontairement mais alors vaut NULL (à moins que le choix de l'affichage d'une moyenne se fasse simultanément)
							extract($tab_saisie[$eleve_id][$matiere_id][0]);	// $prof_info $appreciation $note
							$bouton_nettoyer  = ($appreciation!='') ? ' <button type="button" class="nettoyer">Effacer et recalculer.</button>' : '' ;
							$bouton_supprimer = ($note!==NULL)      ? ' <button type="button" class="supprimer">Supprimer sans recalculer</button>' : '' ;
							$note = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $note : ($note*5).'&nbsp;%' ) : '-' ;
							$appreciation = ($appreciation!='') ? $appreciation : 'Moyenne calculée / reportée / actualisée automatiquement.' ;
							$action = ( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') ) ? ' <button type="button" class="modifier">Modifier</button>'.$bouton_nettoyer.$bouton_supprimer : '' ;
							$moyenne_classe = '';
							if( ($make_action=='consulter') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) )
							{
								$note_moyenne = ($_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id]!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id],1,'.','') : round($_SESSION['tmp_moyenne_classe'][$periode_id][$classe_id][$matiere_id]*5).'&nbsp;%' ) : '-' ;
								$moyenne_classe = ' Moyenne de classe : '.$note_moyenne;
							}
							$releve_HTML .= '<tr id="note_'.$matiere_id.'_0"><td class="now moyenne">'.$note.'</td><td class="now"><span class="notnow">'.html($appreciation).$action.'</span>'.$moyenne_classe.'</td></tr>'."\r\n";
						}
						// Bulletin - Appréciations intermédiaires (HTML)
						if( ($make_html) && ($make_officiel) && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) )
						{
							// $tab_saisie[$eleve_id][$matiere_id] n'est pas défini si bulletin dans note et pas d'appréciation encore saisie
							if(isset($tab_saisie[$eleve_id][$matiere_id]))
							{
								foreach($tab_saisie[$eleve_id][$matiere_id] as $prof_id => $tab)
								{
									if($prof_id) // Sinon c'est la note.
									{
										extract($tab);	// $prof_info $appreciation $note
										$action = ( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') && ($prof_id==$_SESSION['USER_ID']) ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : ( ($prof_id!=$_SESSION['USER_ID']) ? ' <button type="button" class="signaler">Signaler une erreur</button>' : '' ) ;
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
					// Examen de présence des appréciations intermédiaires et des notes
					if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && ( (!isset($tab_saisie[$eleve_id][$matiere_id][0])) || ($tab_saisie[$eleve_id][$matiere_id][0]['note']===NULL) ) )
					{
						$tab_resultat_examen[$tab_matiere[$matiere_id]][] = 'Absence de note pour '.html($eleve_nom.' '.$eleve_prenom);
					}
					if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) && ( (!isset($tab_saisie[$eleve_id][$matiere_id])) || (max(array_keys($tab_saisie[$eleve_id][$matiere_id]))==0) ) )
					{
						$tab_resultat_examen[$tab_matiere[$matiere_id]][] = 'Absence d\'appréciation pour '.html($eleve_nom.' '.$eleve_prenom);
					}
					// Impression des appréciations intermédiaires (PDF)
					if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) )
					{
						$nb_lignes_en_moins = ( $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] || $_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS'] ) ? $nb_lignes_matiere_intitule_et_marge : $nb_lignes_matiere_marge ;
						$releve_PDF->bilan_synthese_appreciation_rubrique( ( (!isset($tab_saisie[$eleve_id][$matiere_id])) || (max(array_keys($tab_saisie[$eleve_id][$matiere_id]))==0) ) ? NULL : $tab_saisie[$eleve_id][$matiere_id] , $tab_nb_lignes[$eleve_id][$matiere_id] - $nb_lignes_en_moins );
					}
				}
			}
			// Bulletin - Appréciation générale + Moyenne générale
			if( ($make_officiel) && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) && ($BILAN_ETAT=='3synthese') )
			{
				if( ($make_html) || ($make_graph) )
				{
					$releve_HTML .= '<table class="bilan" style="width:900px"><tbody>'."\r\n";
					$releve_HTML .= '<tr><th colspan="2">Synthèse générale</th></tr>'."\r\n";
					if( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']) )
					{
						$note = ($_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][$eleve_id]!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? $_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][$eleve_id] : round($_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][$eleve_id]*5).'&nbsp;%' ) : '-' ;
						$moyenne_classe = '';
						if( ($make_action=='consulter') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) )
						{
							$note_moyenne = ($_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][0]!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][0],1,'.','') : round($_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][0]*5).'&nbsp;%' ) : '-' ;
							$moyenne_classe = ' Moyenne de classe : '.$note_moyenne;
						}
						$releve_HTML .= '<tr><td class="now moyenne">'.$note.'</td><td class="now" style="width:850px"><span class="notnow">Moyenne générale (calculée / actualisée automatiquement).</span>'.$moyenne_classe.'</td></tr>'."\r\n";
					}
					if(isset($tab_saisie[$eleve_id][0]))
					{
						list($prof_id,$tab) = each($tab_saisie[$eleve_id][0]);
						extract($tab);	// $prof_info $appreciation $note
						$action = ( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') ) ? ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>' : '' ;
						$releve_HTML .= '<tr id="appr_0_'.$prof_id.'"><td colspan="2" class="now"><div class="notnow">'.html($prof_info).$action.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'."\r\n";
					}
					elseif( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') )
					{
						$releve_HTML .= '<tr id="appr_0_'.$_SESSION['USER_ID'].'"><td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'appréciation générale.</button></div></td></tr>'."\r\n";
					}
					$releve_HTML .= '</tbody></table>'."\r\n";
				}
			}
			// Examen de présence de l'appréciation générale
			if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) && (in_array(0,$tab_rubrique_id)) && (!isset($tab_saisie[$eleve_id][0])) )
			{
				$tab_resultat_examen['Synthèse générale'][] = 'Absence d\'appréciation générale pour '.html($eleve_nom.' '.$eleve_prenom);
			}
			// Impression de l'appréciation générale + Moyenne générale
			if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE']) )
			{
				if(isset($tab_saisie[$eleve_id][0]))
				{
					reset($tab_saisie[$eleve_id][0]);
					list($prof_id,$tab_infos) = each($tab_saisie[$eleve_id][0]);
					if( ($numero_tirage==0) || ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='sans') || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='tampon') && (!$tab_signature[0]) ) || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') && (!$tab_signature[$prof_id]) ) || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature_ou_tampon') && (!$tab_signature[0]) && (!$tab_signature[$prof_id]) ) )
					{
						$tab_image_tampon_signature = NULL;
					}
					else
					{
						$tab_image_tampon_signature = ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') || (!$tab_signature[0]) ) ? $tab_signature[$prof_id] : $tab_signature[0] ;
					}
				}
				else
				{
					$prof_id = 0;
					$tab_infos = array('prof_info'=>'','appreciation'=>'');
					$tab_image_tampon_signature = ( ($numero_tirage>0) && (in_array($_SESSION['OFFICIEL']['TAMPON_SIGNATURE'],array('tampon','signature_ou_tampon'))) ) ? $tab_signature[0] : NULL;
				}
				$moyenne_generale_eleve  = NULL;
				$moyenne_generale_classe = NULL;
				if( ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']) )
				{
					$moyenne_generale_eleve = $_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][$eleve_id];
					if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
					{
						$moyenne_generale_classe = $_SESSION['tmp_moyenne_generale'][$periode_id][$classe_id][0];
					}
				}
				$releve_PDF->bilan_synthese_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite+$nb_lignes_supplementaires+$nb_lignes_legendes , $moyenne_generale_eleve , $moyenne_generale_classe );
			}
			// Bulletin - Absences et retard
			if( ($make_officiel) && ($affichage_assiduite) )
			{
				$texte_assiduite = texte_ligne_assiduite($tab_assiduite[$eleve_id]);
				if( ($make_html) || ($make_graph) )
				{
					$releve_HTML .= '<p class="i">'.$texte_assiduite.'</p>'."\r\n";
				}
				elseif($make_action=='imprimer')
				{
					$releve_PDF->afficher_assiduite($texte_assiduite);
				}
			}
			// Bulletin - Ligne additionnelle
			if( ($make_action=='imprimer') && ($nb_lignes_supplementaires) )
			{
				$releve_PDF->afficher_ligne_additionnelle($_SESSION['OFFICIEL']['BULLETIN_LIGNE_SUPPLEMENTAIRE']);
			}
			// Mémorisation des pages de début et de fin pour chaque élève pour découpe et archivage ultérieur
			if($make_action=='imprimer')
			{
				$page_debut = (isset($page_fin)) ? $page_fin+1 : 1 ;
				$page_fin   = $releve_PDF->page;
				$tab_pages_decoupe_pdf[$eleve_id][$numero_tirage] = array( $eleve_nom.' '.$eleve_prenom , $page_debut.'-'.$page_fin );
			}
			if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
			{
				if($make_pdf)  { $releve_PDF->bilan_synthese_legende($format); }
				if($make_html) { $releve_HTML .= $legende_html; }
			}
		}
	}
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On enregistre les sorties HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($make_html) { FileSystem::ecrire_fichier(CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.html',$releve_HTML); }
if($make_pdf)  { $releve_PDF->Output(CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf','F'); }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le diagramme graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $make_graph && (count($tab_graph_data)) )
{
	$js_graph .= '<SCRIPT>';
	// Matières sur l'axe des abscisses
	$js_graph .= 'ChartOptions.title.text = null;';
	$js_graph .= 'ChartOptions.xAxis.categories = ['.implode(',',$tab_graph_data['categories']).'];';
	// Second axe des ordonnés pour les moyennes
	if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
	{
		$js_graph .= 'delete ChartOptions.yAxis[1];';
	}
	else
	{
		$ymax = ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? 20 : 100 ;
		$js_graph .= 'ChartOptions.yAxis[1] = { min: 0, max: '.$ymax.', title: { style: { color: "#333" } , text: "Moyennes" }, opposite: true };';
	}
	// Séries de valeurs
	$tab_graph_series = array();
	$tab_graph_series['A']  = '{ name: "'.addcslashes($_SESSION['ACQUIS_LEGENDE']['A'],'"').'", data: ['.implode(',',$tab_graph_data['series_data_A']).'] }';
	$tab_graph_series['VA'] = '{ name: "'.addcslashes($_SESSION['ACQUIS_LEGENDE']['VA'],'"').'", data: ['.implode(',',$tab_graph_data['series_data_VA']).'] }';
	$tab_graph_series['NA'] = '{ name: "'.addcslashes($_SESSION['ACQUIS_LEGENDE']['NA'],'"').'", data: ['.implode(',',$tab_graph_data['series_data_NA']).'] }';
	if(isset($tab_graph_data['series_data_MoyClasse']))
	{
		$tab_graph_series['MoyClasse'] = '{ type: "line", name: "Moyenne classe", data: ['.implode(',',$tab_graph_data['series_data_MoyClasse']).'], marker: {symbol: "circle"}, color: "#999", yAxis: 1 }';
	}
	if(isset($tab_graph_data['series_data_MoyEleve']))
	{
		$tab_graph_series['MoyEleve']  = '{ type: "line", name: "Moyenne élève", data: ['.implode(',',$tab_graph_data['series_data_MoyEleve']).'], marker: {symbol: "circle"}, color: "#139", yAxis: 1 }';
	}
	$js_graph .= 'ChartOptions.series = ['.implode(',',$tab_graph_series).'];';
	$js_graph .= 'graphique = new Highcharts.Chart(ChartOptions);';
}

?>