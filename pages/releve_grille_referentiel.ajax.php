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

$remplissage       = (isset($_POST['f_remplissage']))   ? clean_texte($_POST['f_remplissage'])   : '';
$colonne_bilan     = (isset($_POST['f_colonne_bilan'])) ? clean_texte($_POST['f_colonne_bilan']) : '';
$colonne_vide      = (isset($_POST['f_colonne_vide']))  ? clean_entier($_POST['f_colonne_vide']) : 0;
$tableau_tri_objet = (isset($_POST['f_tri_objet']))     ? clean_texte($_POST['f_tri_objet'])     : '';
$tableau_tri_mode  = (isset($_POST['f_tri_mode']))      ? clean_texte($_POST['f_tri_mode'])      : '';
$matiere_id        = (isset($_POST['f_matiere']))       ? clean_entier($_POST['f_matiere'])      : 0;
$matiere_nom       = (isset($_POST['f_matiere_nom']))   ? clean_texte($_POST['f_matiere_nom'])   : '';
$groupe_id         = (isset($_POST['f_groupe']))        ? clean_entier($_POST['f_groupe'])       : 0;
$groupe_nom        = (isset($_POST['f_groupe_nom']))    ? clean_texte($_POST['f_groupe_nom'])    : '';
$niveau_id         = (isset($_POST['f_niveau']))        ? clean_entier($_POST['f_niveau'])       : 0;
$niveau_nom        = (isset($_POST['f_niveau_nom']))    ? clean_texte($_POST['f_niveau_nom'])    : '';
$only_socle        = (isset($_POST['f_restriction']))   ? 1                                      : 0;
$aff_coef          = (isset($_POST['f_coef']))          ? 1                                      : 0;
$aff_socle         = (isset($_POST['f_socle']))         ? 1                                      : 0;
$aff_lien          = (isset($_POST['f_lien']))          ? 1                                      : 0;
$orientation       = (isset($_POST['f_orientation']))   ? clean_texte($_POST['f_orientation'])   : '';
$couleur           = (isset($_POST['f_couleur']))       ? clean_texte($_POST['f_couleur'])       : '';
$legende           = (isset($_POST['f_legende']))       ? clean_texte($_POST['f_legende'])       : '';
$marge_min         = (isset($_POST['f_marge_min']))     ? clean_texte($_POST['f_marge_min'])     : '';
$cases_nb          = (isset($_POST['f_cases_nb']))      ? clean_entier($_POST['f_cases_nb'])     : 0;
$cases_largeur     = (isset($_POST['f_cases_larg']))    ? clean_entier($_POST['f_cases_larg'])   : 0;

// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_eleve_id = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_type     = (isset($_POST['f_type']))  ? ( (is_array($_POST['f_type']))  ? $_POST['f_type']  : explode(',',$_POST['f_type'])  ) : array() ;
$tab_eleve_id = array_filter( array_map( 'clean_entier' , $tab_eleve_id ) , 'positif' );
$tab_type     = array_map( 'clean_texte' , $tab_type );

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if($_SESSION['USER_PROFIL']=='eleve')
{
	$groupe_id    = $_SESSION['ELEVE_CLASSE_ID'];
	$tab_eleve_id = array($_SESSION['USER_ID']);
}
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	$tab_type     = array('individuel');
}

$type_generique  = (in_array('generique',$tab_type))  ? 1 : 0 ;
$type_individuel = (in_array('individuel',$tab_type)) ? 1 : 0 ;
$type_synthese   = (in_array('synthese',$tab_type))   ? 1 : 0 ;

if($type_generique)
{
	$groupe_id    = 0;
	$tab_eleve_id = array();
}

$liste_eleve = implode(',',$tab_eleve_id);

if( !$matiere_id || !$niveau_id || !$matiere_nom || !$niveau_nom || !$remplissage || !$colonne_bilan || !$orientation || !$couleur || !$legende || !$marge_min || !$cases_nb || !$cases_largeur || !count($tab_type) )
{
	exit('Erreur avec les données transmises !');
}

// Ces 3 choix sont passés de modifiables à imposés pour élèves & parents (25 février 2012) ; il faut les rétablir à leur bonnes valeurs si besoin (1ère soumission du formulaire depuis ce changement).
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	$remplissage   = 'plein';
	$colonne_bilan = 'oui';
	$colonne_vide  = 0;
}

// Enregistrer les préférences utilisateurs
Formulaire::save_choix('grille_referentiel');

if($type_generique)
{
	$remplissage   = 'vide';
	$colonne_bilan = 'non';
	$colonne_vide  = 0;
	$type_individuel = 0 ;
	$type_synthese   = 0 ;
}

$tab_domaine        = array();	// [domaine_id] => array(domaine_ref,domaine_nom,domaine_nb_lignes);
$tab_theme          = array();	// [domaine_id][theme_id] => array(theme_ref,theme_nom,theme_nb_lignes);
$tab_item           = array();	// [theme_id][item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien);
$tab_item_synthese  = array();	// [item_id] => array(item_ref,item_nom);
$tab_liste_item     = array();	// [i] => item_id
$tab_eleve          = array();	// [i] => array(eleve_id,eleve_nom,eleve_prenom)
$tab_eval           = array();	// [eleve_id][item_id] => array(note,date,info)

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des items pour la matière et le niveau sélectionné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$lignes_nb = 0;
$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , $niveau_id , $only_socle , FALSE /*only_item*/ , FALSE /*socle_nom*/ );
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
			$tab_item_synthese[$item_id] = array('item_ref'=>$DB_ROW['matiere_ref'].'.'.$item_ref,'item_nom'=>$DB_ROW['item_nom'],'item_coef'=>$DB_ROW['item_coef']);
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
$item_nb = count($tab_liste_item);
if(!$item_nb)
{
	exit('Aucun item référencé pour cette matière et ce niveau !');
}
$liste_item = implode(',',$tab_liste_item);

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
	if(!is_array($tab_eleve))
	{
		exit('Aucun élève trouvé correspondant aux identifiants transmis !');
	}
}
else
{
	$tab_eleve[] = array('eleve_id'=>0,'eleve_nom'=>'','eleve_prenom'=>'');
}
$eleve_nb = count($tab_eleve);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Récupération de la liste des résultats (si pas grille générique et si demandé ou si besoin pour colonne bilan ou si besoin pour synthèse ou si besoin car profil élève donc panier afin de solliciter une évaluation)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( !$type_generique && ( ($remplissage=='plein') || ($colonne_bilan=='oui') || $type_synthese || ($_SESSION['USER_PROFIL']=='eleve') ) )
{
	$DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_matiere($liste_eleve , $liste_item , $date_debut=false , $date_fin=false , $_SESSION['USER_PROFIL']) ;
	foreach($DB_TAB as $DB_ROW)
	{
		$user_id = ($_SESSION['USER_PROFIL']=='eleve') ? $_SESSION['USER_ID'] : $DB_ROW['eleve_id'] ;
		$tab_eval[$user_id][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
	}
	// Récupération de calcul_methode et calcul_limite
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_referentiels_infos_details_matieres_niveaux( $matiere_id , $niveau_id );
	$calcul_methode = $DB_TAB[0]['referentiel_calcul_methode'];
	$calcul_limite  = $DB_TAB[0]['referentiel_calcul_limite'];
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
// Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$dossier         = './__tmp/export/';
$fichier_lien    = 'releve_grille_item_etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.time();

$tab_score_eleve_item         = array();	// Retenir les scores / élève / item
$tab_score_item_eleve         = array();	// Retenir les scores / item / élève
$tab_moyenne_scores_eleve     = array();	// Retenir la moyenne des scores d'acquisitions / élève
$tab_pourcentage_acquis_eleve = array();	// Retenir le pourcentage d'items acquis / élève
$tab_moyenne_scores_item      = array();	// Retenir la moyenne des scores d'acquisitions / item
$tab_pourcentage_acquis_item  = array();	// Retenir le pourcentage d'items acquis / item
$moyenne_moyenne_scores       = 0;	// moyenne des moyennes des scores d'acquisitions
$moyenne_pourcentage_acquis   = 0;	// moyenne des moyennes des pourcentages d'items acquis

/*
	Calcul des états d'acquisition (si besoin) et des données nécessaires pour le tableau de synthèse (si besoin).
	$tab_score_eleve_item[$eleve_id][$item_id]
	$tab_score_item_eleve[$item_id][$eleve_id]
	$tab_moyenne_scores_eleve[$eleve_id]
	$tab_pourcentage_acquis_eleve[$eleve_id]
*/

if(count($tab_eval))
{
	foreach($tab_eval as $eleve_id => $tab_items) // Ne pas écraser $tab_item déjà utilisé
	{
		foreach($tab_items as $item_id => $tab_devoirs)
		{
			// calcul du bilan de l'item
			$tab_score_eleve_item[$eleve_id][$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
			if($type_synthese)
			{
				$tab_score_item_eleve[$item_id][$eleve_id] = $tab_score_eleve_item[$eleve_id][$item_id];
			}
		}
		if($type_synthese)
		{
			// calcul des bilans des scores
			$tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id],'non_nul');
			$nb_scores = count( $tableau_score_filtre );
			// la moyenne peut être pondérée par des coefficients
			$somme_scores_ponderes = 0;
			$somme_coefs = 0;
			if($nb_scores)
			{
				foreach($tableau_score_filtre as $item_id => $item_score)
				{
					$somme_scores_ponderes += $item_score*$tab_item_synthese[$item_id]['item_coef'];
					$somme_coefs += $tab_item_synthese[$item_id]['item_coef'];
				}
			}
			// ... un pour la moyenne des pourcentages d'acquisition
			if($somme_coefs)
			{
				$tab_moyenne_scores_eleve[$eleve_id] = round($somme_scores_ponderes/$somme_coefs,0);
			}
			else
			{
				$tab_moyenne_scores_eleve[$eleve_id] = false;
			}
			// ... un pour le nombre d\'items considérés acquis ou pas
			if($nb_scores)
			{
				$nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
				$nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
				$nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
				$tab_pourcentage_acquis_eleve[$eleve_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
			}
			else
			{
				$tab_pourcentage_acquis_eleve[$eleve_id] = false;
			}
		}
	}
}

/*
	On renseigne (uniquement utile pour le tableau de synthèse) :
	$tab_moyenne_scores_item[$item_id]
	$tab_pourcentage_acquis_item[$item_id]
*/

if($type_synthese)
{
	// Pour chaque item...
	foreach($tab_liste_item as $item_id)
	{
		$tableau_score_filtre = isset($tab_score_item_eleve[$item_id]) ? array_filter($tab_score_item_eleve[$item_id],'non_nul') : array() ; // Test pour éviter de rares "array_filter() expects parameter 1 to be array, null given"
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

if( $type_synthese )
{
	// $moyenne_moyenne_scores
	$somme  = array_sum($tab_moyenne_scores_eleve);
	$nombre = count( array_filter($tab_moyenne_scores_eleve,'non_nul') );
	$moyenne_moyenne_scores = ($nombre) ? round($somme/$nombre,0) : false;
	// $moyenne_pourcentage_acquis
	$somme  = array_sum($tab_pourcentage_acquis_eleve);
	$nombre = count( array_filter($tab_pourcentage_acquis_eleve,'non_nul') );
	$moyenne_pourcentage_acquis = ($nombre) ? round($somme/$nombre,0) : false;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On tronque les notes les plus anciennes s'il y en a trop par rapport au nombre de colonnes affichées.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $type_individuel )
{
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
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Elaboration de la grille d'items d'un référentiel, en HTML et PDF
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$affichage_direct = ( ( in_array($_SESSION['USER_PROFIL'],array('eleve','parent')) ) && (SACoche!='webservices') ) ? TRUE : FALSE ;

if( $type_generique || $type_individuel )
{
	$suffixe = ($type_individuel) ? 'individuel' : 'generique' ;
	// Initialiser au cas où $aff_coef / $aff_socle / $aff_lien sont à 0
	$texte_coef       = '';
	$texte_socle      = '';
	$texte_lien_avant = '';
	$texte_lien_apres = '';
	// Les variables $releve_HTML_individuel et $releve_PDF vont contenir les sorties
	$colspan = ($colonne_bilan=='non') ? $cases_nb : $cases_nb+1 ;
	$msg_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
	$releve_HTML_individuel  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML_individuel .= $affichage_direct ? '' : '<h1>Grille d\'items d\'un référentiel</h1>';
	$releve_HTML_individuel .= $affichage_direct ? '' : '<h2>'.html($matiere_nom.' - Niveau '.$niveau_nom.$msg_socle).'</h2>';
	// Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
	$releve_PDF = new PDF($orientation,$marge_min,$couleur,$legende);
	$releve_PDF->grille_referentiel_initialiser($cases_nb,$cases_largeur,$lignes_nb,$colonne_bilan,$colonne_vide);
	$separation = (count($tab_eleve)>1) ? '<hr />' : '' ;

	// Pour chaque élève...
	foreach($tab_eleve as $tab)
	{
		extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
		// On met le document au nom de l'élève, ou on établit un document générique
		$releve_PDF->grille_referentiel_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom);
		$releve_HTML_individuel .= ($eleve_id) ? $separation.'<h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>' : $separation.'<h2>Grille générique</h2>' ;
		$releve_HTML_individuel .= '<table class="bilan">';
		// Pour chaque domaine...
		if(count($tab_domaine))
		{
			foreach($tab_domaine as $domaine_id => $tab)
			{
				extract($tab);	// $domaine_ref $domaine_nom $domaine_nb_lignes
				$releve_HTML_individuel .= '<tr><th colspan="2" class="domaine">'.html($domaine_nom).'</th><th colspan="'.$colspan.'" class="nu"></th></tr>'."\r\n";
				$releve_PDF->grille_referentiel_domaine($domaine_nom,$domaine_nb_lignes);
				// Pour chaque thème...
				if(isset($tab_theme[$domaine_id]))
				{
					foreach($tab_theme[$domaine_id] as $theme_id => $tab)
					{
						extract($tab);	// $theme_ref $theme_nom $theme_nb_lignes
						$releve_HTML_individuel .= '<tr><th>'.$theme_ref.'</th><th>'.html($theme_nom).'</th><th colspan="'.$colspan.'" class="nu"></th></tr>'."\r\n";
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
								$score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : FALSE ;
								$texte_demande_eval = ($_SESSION['USER_PROFIL']!='eleve') ? '' : ( ($item_cart) ? '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
								$releve_HTML_individuel .= '<tr><td>'.$item_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.html($item_nom).$texte_lien_apres.$texte_demande_eval.'</td>';
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
										$releve_HTML_individuel .= '<td>'.affich_note_html($note,$date,$info,false).'</td>';
										$releve_PDF->afficher_note_lomer($note,$border=1,$br=floor(($i+1)/$colspan));
									}
									else
									{
										$releve_HTML_individuel .= '<td>&nbsp;</td>';
										$releve_PDF->Cell($cases_largeur , $releve_PDF->cases_hauteur , '' , 1 , floor(($i+1)/$colspan) , 'C' , true , '');
									}
								}
								// Case bilan
								if($colonne_bilan=='oui')
								{
									$releve_HTML_individuel .= affich_score_html($score,'score');
									$releve_PDF->afficher_score_bilan($score,$br=1);
								}
								$releve_HTML_individuel .= '</tr>'."\r\n";
							}
						}
					}
				}
			}
		}
		$releve_HTML_individuel .= '</table>';
		if($legende=='oui')
		{
			$releve_PDF->grille_referentiel_legende();
			$releve_HTML_individuel .= affich_legende_html($note_Lomer=TRUE,$etat_bilan=FALSE);
		}
	}
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'_'.$suffixe.'.html',$releve_HTML_individuel);
	$releve_PDF->Output($dossier.$fichier_lien.'_'.$suffixe.'.pdf','F');
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Elaboration de la synthèse collective en HTML et PDF
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

if($type_synthese)
{
	$tab_titre = 'd\'items d\'un référentiel';
	$msg_socle = ($only_socle) ? ' - Socle uniquement' : '' ;
	$matiere_et_niveau = $matiere_nom.' - Niveau '.$niveau_nom.$msg_socle ;
	$releve_HTML_synthese  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>';
	$releve_HTML_synthese .= $affichage_direct ? '' : '<h1>Bilan '.$tab_titre.'</h1>';
	$releve_HTML_synthese .= '<h2>'.html($matiere_et_niveau).'</h2>';
	// Appel de la classe et redéfinition de qqs variables supplémentaires pour la mise en page PDF
	// On définit l'orientation la plus adaptée
	$orientation = ( ( ($eleve_nb>$item_nb) && ($tableau_tri_objet=='eleve') ) || ( ($item_nb>$eleve_nb) && ($tableau_tri_objet=='item') ) ) ? 'portrait' : 'landscape' ;
	$releve_PDF = new PDF($orientation,$marge_min,$couleur);
	$releve_PDF->bilan_periode_synthese_initialiser($eleve_nb,$item_nb,$tableau_tri_objet);
	$releve_PDF->bilan_periode_synthese_entete($tab_titre,$matiere_et_niveau,''/*texte_periode*/);
	// 1ère ligne
	$releve_PDF->Cell($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , '' , 0 , 0 , 'C' , false , '');
	$releve_PDF->choisir_couleur_fond('gris_clair');
	$th = ($tableau_tri_objet=='eleve') ? 'Elève' : 'Item' ;
	$releve_HTML_table_head = '<thead><tr><th>'.$th.'</th>';
	if($tableau_tri_objet=='eleve')
	{
		foreach($tab_liste_item as $item_id)	// Pour chaque item...
		{
			$releve_PDF->VertCellFit($releve_PDF->cases_largeur, $releve_PDF->etiquette_hauteur, $tab_item_synthese[$item_id]['item_ref'], 1 /*border*/, 0 /*br*/, TRUE /*fill*/);
			$releve_HTML_table_head .= '<th title="'.html($tab_item_synthese[$item_id]['item_nom']).'"><img alt="'.html($tab_item_synthese[$item_id]['item_ref']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($tab_item_synthese[$item_id]['item_ref']).'&amp;size=8" /></th>';
		}
	}
	else
	{
		foreach($tab_eleve as $tab)	// Pour chaque élève...
		{
			extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
			$releve_PDF->VertCellFit($releve_PDF->cases_largeur, $releve_PDF->etiquette_hauteur, $eleve_nom.' '.$eleve_prenom, 1 /*border*/, 0 /*br*/, TRUE /*fill*/);
			$releve_HTML_table_head .= '<th><img alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'&amp;size=8" /></th>';
		}
	}
	$releve_PDF->SetX( $releve_PDF->GetX()+2 );
	$releve_PDF->choisir_couleur_fond('gris_moyen');
	$releve_PDF->VertCell($releve_PDF->cases_largeur , $releve_PDF->etiquette_hauteur , '[ * ]'  , 1 , 0 , 'C' , true , '');
	$releve_PDF->VertCell($releve_PDF->cases_largeur , $releve_PDF->etiquette_hauteur , '[ ** ]' , 1 , 1 , 'C' , true , '');
	$releve_HTML_table_head .= '<th class="nu">&nbsp;</th><th>[ * ]</th><th>[ ** ]</th></tr></thead>'."\r\n";
	// lignes suivantes
	$releve_HTML_table_body = '';
	if($tableau_tri_objet=='eleve')
	{
		foreach($tab_eleve as $tab)	// Pour chaque élève...
		{
			extract($tab);	// $eleve_id $eleve_nom $eleve_prenom
			$releve_PDF->choisir_couleur_fond('gris_clair');
			$releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 , 0 , 'L' , true , '');
			$releve_HTML_table_body .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
			foreach($tab_liste_item as $item_id)	// Pour chaque item...
			{
				$score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : false ;
				$releve_PDF->afficher_score_bilan($score,$br=0);
				$releve_HTML_table_body .= affich_score_html($score,$tableau_tri_mode);
			}
			$valeur1 = (isset($tab_moyenne_scores_eleve[$eleve_id])) ? $tab_moyenne_scores_eleve[$eleve_id] : FALSE ;
			$valeur2 = (isset($tab_pourcentage_acquis_eleve[$eleve_id])) ? $tab_pourcentage_acquis_eleve[$eleve_id] : FALSE ;
			$releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,FALSE,TRUE);
			$releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.affich_score_html($valeur1,$tableau_tri_mode,'%').affich_score_html($valeur2,$tableau_tri_mode,'%').'</tr>'."\r\n";
		}
	}
	else
	{
		foreach($tab_liste_item as $item_id)	// Pour chaque item...
		{
			$releve_PDF->choisir_couleur_fond('gris_clair');
			$releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , pdf($tab_item_synthese[$item_id]['item_ref']) , 1 , 0 , 'L' , true , '');
			$releve_HTML_table_body .= '<tr><td title="'.html($tab_item_synthese[$item_id]['item_nom']).'">'.html($tab_item_synthese[$item_id]['item_ref']).'</td>';
			foreach($tab_eleve as $tab)	// Pour chaque élève...
			{
				$eleve_id = $tab['eleve_id'];
				$score = (isset($tab_score_eleve_item[$eleve_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$item_id] : false ;
				$releve_PDF->afficher_score_bilan($score,$br=0);
				$releve_HTML_table_body .= affich_score_html($score,$tableau_tri_mode);
			}
			$valeur1 = $tab_moyenne_scores_item[$item_id];
			$valeur2 = $tab_pourcentage_acquis_item[$item_id];
			$releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,FALSE,TRUE);
			$releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.affich_score_html($valeur1,$tableau_tri_mode,'%').affich_score_html($valeur2,$tableau_tri_mode,'%').'</tr>'."\r\n";
		}
	}
	$releve_HTML_table_body = '<tbody>'.$releve_HTML_table_body.'</tbody>'."\r\n";
	// dernière ligne (doublée)
	$memo_y = $releve_PDF->GetY()+2;
	$releve_PDF->SetY( $memo_y );
	$releve_PDF->choisir_couleur_fond('gris_moyen');
	$releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , 'moyenne scores [*]' , 1 , 2 , 'C' , true , '');
	$releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , '% validations [**]' , 1 , 0 , 'C' , true , '');
	$releve_HTML_table_foot1 = '<tr><th>moyenne scores [*]</th>';
	$releve_HTML_table_foot2 = '<tr><th>% validations [**]</th>';
	$memo_x = $releve_PDF->GetX();
	$releve_PDF->SetXY($memo_x,$memo_y);
	if($tableau_tri_objet=='eleve')
	{
		foreach($tab_liste_item as $item_id)	// Pour chaque item...
		{
			$valeur1 = $tab_moyenne_scores_item[$item_id];
			$valeur2 = $tab_pourcentage_acquis_item[$item_id];
			$releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,TRUE,FALSE);
			$releve_HTML_table_foot1 .= affich_score_html($valeur1,'score','%');
			$releve_HTML_table_foot2 .= affich_score_html($valeur2,'score','%');
		}
	}
	else
	{
		foreach($tab_eleve as $tab)	// Pour chaque élève...
		{
			$eleve_id = $tab['eleve_id'];
			$valeur1 = (isset($tab_moyenne_scores_eleve[$eleve_id])) ? $tab_moyenne_scores_eleve[$eleve_id] : FALSE ;
			$valeur2 = (isset($tab_pourcentage_acquis_eleve[$eleve_id])) ? $tab_pourcentage_acquis_eleve[$eleve_id] : FALSE ;
			$releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,TRUE,FALSE);
			$releve_HTML_table_foot1 .= affich_score_html($valeur1,'score','%');
			$releve_HTML_table_foot2 .= affich_score_html($valeur2,'score','%');
		}
	}
	// les deux dernières cases (moyenne des moyennes)
	$colspan = ($tableau_tri_objet=='eleve') ? $item_nb+4 : $eleve_nb+4 ;
	$releve_PDF->bilan_periode_synthese_pourcentages($moyenne_moyenne_scores,$moyenne_pourcentage_acquis,TRUE,TRUE);
	$releve_HTML_table_foot1 .= '<th class="nu">&nbsp;</th>'.affich_score_html($moyenne_moyenne_scores,'score','%').'<th class="nu">&nbsp;</th></tr>';
	$releve_HTML_table_foot2 .= '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.affich_score_html($moyenne_pourcentage_acquis,'score','%').'</tr>';
	$releve_HTML_table_foot = '<tfoot><tr><td class="nu" colspan="'.$colspan.'" style="font-size:0;height:9px">&nbsp;</td></tr>'.$releve_HTML_table_foot1.$releve_HTML_table_foot2.'</tfoot>'."\r\n";
	// pour la sortie HTML, on peut placer les tableaux de synthèse au début
	$num_hide = ($tableau_tri_objet=='eleve') ? $item_nb+1 : $eleve_nb+1 ;
	$releve_HTML_synthese .= '<hr /><h2>SYNTHESE (selon l\'objet et le mode de tri choisis)</h2>';
	$releve_HTML_synthese .= '<table id="table_s" class="bilan_synthese vsort">'.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>';
	$releve_HTML_synthese .= '<script type="text/javascript">$("#table_s").tablesorter({ headers:{'.$num_hide.':{sorter:false}} });</script>'; // Non placé dans le fichier js car mettre une variable à la place d'une valeur pour $num_hide ne fonctionne pas
	// On enregistre les sorties HTML et PDF
	Ecrire_Fichier($dossier.$fichier_lien.'_synthese.html',$releve_HTML_synthese);
	$releve_PDF->Output($dossier.$fichier_lien.'_synthese.pdf','F');
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Affichage du résultat
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

if($affichage_direct)
{
	echo'<hr />';
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_'.$suffixe.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'</ul>';
	echo $releve_HTML_individuel;
}
else
{
	if($type_synthese)
	{
		echo'<h2>Synthèse collective</h2>';
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_synthese.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_synthese"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
		echo'</ul>';
	}
	if( $type_generique || $type_individuel )
	{
		$h2 = ($type_individuel) ? 'Relevé individuel' : 'Relevé générique' ;
		echo'<h2>'.$h2.'</h2>';
		echo'<ul class="puce">';
		echo'<li><a class="lien_ext" href="'.$dossier.$fichier_lien.'_'.$suffixe.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
		echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_lien.'_'.$suffixe.'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
		echo'</ul>';
	}
}

?>
