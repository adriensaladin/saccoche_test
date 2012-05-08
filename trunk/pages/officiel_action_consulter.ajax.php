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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$objet      = (isset($_POST['f_objet']))      ? clean_texte($_POST['f_objet'])      : '';
$ACTION     = (isset($_POST['f_action']))     ? clean_texte($_POST['f_action'])     : '';
$BILAN_TYPE = (isset($_POST['f_bilan_type'])) ? clean_texte($_POST['f_bilan_type']) : '';
$periode_id = (isset($_POST['f_periode']))    ? clean_entier($_POST['f_periode'])   : 0;
$classe_id  = (isset($_POST['f_classe']))     ? clean_entier($_POST['f_classe'])    : 0;
$groupe_id  = (isset($_POST['f_groupe']))     ? clean_entier($_POST['f_groupe'])    : 0;
$eleve_id   = (isset($_POST['f_user']))       ? clean_entier($_POST['f_user'])      : 0;
// Autres chaines spécifiques...
$listing_piliers  = (isset($_POST['f_listing_piliers']))  ? $_POST['f_listing_piliers']  : '' ;
$tab_pilier_id  = array_filter( array_map( 'clean_entier' , explode(',',$listing_piliers) )  , 'positif' );
$liste_pilier_id  = implode(',',$tab_pilier_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_action = array('initialiser','charger');

$tab_types = array
(
	'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations') ,
	'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire') ,
	'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1') ,
	'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2') ,
	'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3')
);

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!isset($tab_types[$BILAN_TYPE])) || !$periode_id || !$classe_id || ( (!$eleve_id)&&($ACTION!='initialiser') ) )
{
	exit('Erreur avec les données transmises !');
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
if(!count($DB_ROW))
{
	exit('Association classe / période introuvable !');
}
$date_debut  = $DB_ROW['jointure_date_debut'];
$date_fin    = $DB_ROW['jointure_date_fin'];
$BILAN_ETAT  = $DB_ROW['officiel_'.$BILAN_TYPE];
$periode_nom = $DB_ROW['periode_nom'];
$classe_nom  = $DB_ROW['groupe_nom'];
if(!$BILAN_ETAT)
{
	exit('Bilan introuvable !');
}
if(!in_array($BILAN_ETAT,array('2rubrique','3synthese')))
{
	exit('Bilan d\'accès interdit pour cette action !');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage des données d'un élève (le premier si initialisation, l'élève indiqué sinon)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

// Si besoin, fabriquer le formulaire avec la liste des élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves

if($ACTION=='initialiser')
{
	$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
	if(!count($DB_TAB))
	{
		exit('Aucun élève trouvé dans ce regroupement !');
	}
	$tab_eleve_id = array();
	$form_choix_eleve = '<form action="#" method="post" id="form_choix_eleve"><div><b>'.html($periode_nom.' | '.$classe_nom ).' :</b> <button id="go_premier_eleve" type="button" class="go_premier">Premier</button> <button id="go_precedent_eleve" type="button" class="go_precedent">Précédent</button> <select id="go_selection_eleve" name="go_selection" class="b">';
	foreach($DB_TAB as $DB_ROW)
	{
		$form_choix_eleve .= '<option value="'.$DB_ROW['user_id'].'">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</option>';
		$tab_eleve_id[] = $DB_ROW['user_id'];
	}
	$form_choix_eleve .= '</select> <button id="go_suivant_eleve" type="button" class="go_suivant">Suivant</button> <button id="go_dernier_eleve" type="button" class="go_dernier">Dernier</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button></div></form><hr />';
	$eleve_id = $tab_eleve_id[0];
	// (re)calculer les moyennes des élèves, ainsi que les moyennes de classe (mises dans $_SESSION['tmp_moyenne'][$periode_id][$classe_id][$matiere_id]) 
	if( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] )
	{
		$liste_eleve_id = implode(',',$tab_eleve_id);
		$memo_moyennes_classe = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? TRUE : FALSE ;
		calculer_et_enregistrer_moyennes_eleves_bulletin( $periode_id , $classe_id , $liste_eleve_id , '' /*liste_matiere_id*/ , $memo_moyennes_classe );
	}
}

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné

$tab_saisie = array();	// [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note,info);
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies($BILAN_TYPE,$periode_id,$eleve_id);
foreach($DB_TAB as $DB_ROW)
{
	$tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$DB_ROW['prof_info'] , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$make_for    = 'officiel';
$make_action = 'consulter';
$make_html   = TRUE;
$make_pdf    = FALSE;

if($BILAN_TYPE=='releve')
{
	$format          = 'multimatiere';
	$aff_bilan_MS    = $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES'];
	$aff_bilan_PA    = $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS'];
	$aff_conv_sur20  = 0; // pas jugé utile de le mettre en option...
	$with_coef       = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
	$matiere_id      = true;
	$matiere_nom     = '';
	$groupe_id       = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
	$groupe_nom      = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
	$date_debut      = '';
	$date_fin        = '';
	$retroactif      = 'non'; // C'est un relevé de notes sur une période donnée : pas jugé utile de le mettre en option...
	$only_socle      = 0;     // pas jugé utile de le mettre en option...
	$aff_coef        = $_SESSION['OFFICIEL']['RELEVE_AFF_COEF'];
	$aff_socle       = $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE'];
	$aff_lien        = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$aff_domaine     = $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE'];
	$aff_theme       = $_SESSION['OFFICIEL']['RELEVE_AFF_THEME'];
	$orientation     = 'portrait'; // pas jugé utile de le mettre en option...
	$couleur         = $_SESSION['OFFICIEL']['RELEVE_COULEUR'];
	$legende         = $_SESSION['OFFICIEL']['RELEVE_LEGENDE'];
	$marge_min       = max( $_SESSION['OFFICIEL']['MARGE_GAUCHE'] , $_SESSION['OFFICIEL']['MARGE_DROITE'] , $_SESSION['OFFICIEL']['MARGE_HAUT'] ); // à revoir...
	$pages_nb        = 'optimise'; // pas jugé utile de le mettre en option... à revoir...
	$cases_nb        = $_SESSION['OFFICIEL']['RELEVE_CASES_NB'];
	$cases_largeur   = 5; // pas jugé utile de le mettre en option...
	$tab_eleve       = array($eleve_id); // tableau de l'unique élève à considérer
	$liste_eleve     = (string)$eleve_id;
	$tab_type[]      = 'individuel';
	$type_individuel = 1;
	$type_synthese   = 0;
	$type_bulletin   = 0;
	$tab_matiere_id  = array();
	require('./_inc/code_items_releve.php');
	$nom_bilan_html = 'releve_HTML_individuel';
}
elseif($BILAN_TYPE=='bulletin')
{
	$format         = 'multimatiere' ;
	$groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
	$groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
	$date_debut     = '';
	$date_fin       = '';
	$retroactif     = 'oui'; // Pas jugé utile de le mettre en option...
	$niveau_id      = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
	$aff_coef       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$aff_socle      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$aff_lien       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$only_socle     = 0; // pas jugé utile de le mettre en option...
	$only_niveau    = 0; // pas jugé utile de le mettre en option...
	$couleur        = $_SESSION['OFFICIEL']['BULLETIN_COULEUR'];
	$legende        = $_SESSION['OFFICIEL']['BULLETIN_LEGENDE'];
	$tab_eleve      = array($eleve_id); // tableau de l'unique élève à considérer
	$liste_eleve    = (string)$eleve_id;
	$tab_matiere_id = array();
	/*
	Il reste ...
	$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] <<< A FAIRE >>>
	*/
	require('./_inc/code_items_synthese.php');
	$nom_bilan_html = 'releve_HTML';
}
elseif(in_array($BILAN_TYPE,array('palier1','palier2','palier3')))
{
	$palier_id      = (int)substr($BILAN_TYPE,-1);
	$palier_nom     = 'Palier '.$palier_id;
	$only_presence  = $_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE'];
	$aff_socle_PA   = $_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS'];
	$aff_socle_EV   = $_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION'];
	$groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
	$groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
	$mode           = 'auto';
	$aff_coef       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$aff_socle      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$aff_lien       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
	$tab_pilier_id  = $tab_pilier_id;
	$tab_eleve_id   = array($eleve_id); // tableau de l'unique élève à considérer
	$tab_matiere_id = array();
	/*
	Il reste ...
	$_SESSION['OFFICIEL']['SOCLE_COULEUR'] ???
	$_SESSION['OFFICIEL']['SOCLE_LEGENDE'] ???
	*/
	require('./_inc/code_socle_releve.php');
	$nom_bilan_html = 'releve_html';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du résultat
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

echo ($ACTION=='initialiser') ? '<h2>Consulter le contenu</h2>'.$form_choix_eleve.'<div id="zone_resultat_eleve">'.${$nom_bilan_html}.'</div>' : ${$nom_bilan_html} ;
exit();

?>
