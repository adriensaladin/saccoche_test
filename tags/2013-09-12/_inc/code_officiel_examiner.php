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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])   : '';
$periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])     : 0;
$classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
$groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;
// Autres chaines spécifiques...
$listing_matieres  = (isset($_POST['f_listing_matieres']))  ? $_POST['f_listing_matieres']  : '' ;
$listing_piliers   = (isset($_POST['f_listing_piliers']))   ? $_POST['f_listing_piliers']   : '' ;
$listing_rubriques = (isset($_POST['f_listing_rubriques'])) ? $_POST['f_listing_rubriques'] : '' ;
$tab_matiere_id  = array_filter( Clean::map_entier( explode(',',$listing_matieres) ) , 'positif' );
$tab_pilier_id   = array_filter( Clean::map_entier( explode(',',$listing_piliers) )  , 'positif' );
$tab_rubrique_id = Clean::map_entier(explode(',',$listing_rubriques) ); // Pas de array_filter(...,'positif') car la valeur 0 est autorisée
$liste_matiere_id  = implode(',',$tab_matiere_id);
$liste_pilier_id   = implode(',',$tab_pilier_id);
$liste_rubrique_id = implode(',',$tab_rubrique_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_types = array
(
  'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
  'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
  'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1'  ) ,
  'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2'  ) ,
  'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3'  ) ,
);

// On vérifie les paramètres

if( (!isset($tab_types[$BILAN_TYPE])) || !$periode_id || !$classe_id || (!count($tab_rubrique_id)) )
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que le bilan est bien accessible en modification et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
if(empty($DB_ROW))
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
  exit('Bilan interdit d\'accès pour cette action !');
}

// Lister les élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves

$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
if(empty($DB_TAB))
{
  exit('Aucun élève trouvé dans ce regroupement !');
}
$tab_eleve_id = array();
foreach($DB_TAB as $DB_ROW)
{
  $tab_eleve_id[] = $DB_ROW['user_id'];
}
$liste_eleve_id = implode(',',$tab_eleve_id);

// Il ne s'agit pas de simplement récupérer ce qui est présent dans la table sacoche_officiel_saisie ; en effet :
// - pour un relevé de notes ou un bulletin il faut se restreindre à ce qui est vraiment évalué pour l'élève
// - pour une maîtrise du socle on peut se restreindre à ce qui contient des éléments
// Du coup le plus simple est de simuler la génération du document, sans sortie html / pdf, mais en notant au fur et à mesure ce qui manque

// (re)calculer les moyennes des élèves

if( ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] )
{
  // Attention ! On doit calculer des moyennes de classe, pas de groupe !
  if(!$is_sous_groupe)
  {
    $liste_eleve_id_tmp = $liste_eleve_id;
  }
  else
  {
    $tab_eleve_id_tmp = array();
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleve_id_tmp[] = $DB_ROW['user_id'];
    }
    $liste_eleve_id_tmp = implode(',',$tab_eleve_id_tmp);
  }
  calculer_et_enregistrer_moyennes_eleves_bulletin( $periode_id , $classe_id , $liste_eleve_id_tmp , $liste_rubrique_id , $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'] , FALSE /*memo_moyennes_classe*/ , FALSE /*memo_moyennes_generale*/ );
}

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note);
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$DB_ROW['prof_info'] , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
}


// Pas besoin de récupérer les absences / retards

$affichage_assiduite = FALSE ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_resultat_examen = array();
$make_officiel = TRUE;
$make_brevet   = FALSE;
$make_action   = 'examiner';
$make_html     = FALSE;
$make_pdf      = FALSE;
$make_graph    = FALSE;

if($BILAN_TYPE=='releve')
{
  $format                 = 'multimatiere';
  $aff_etat_acquisition   = 0; // Inutile pour un examen de précence des appréciations
  $aff_moyenne_scores     = 0; // Inutile pour un examen de précence des appréciations
  $aff_pourcentage_acquis = 0; // Inutile pour un examen de précence des appréciations
  $conversion_sur_20      = 0; // Inutile pour un examen de précence des appréciations
  $with_coef              = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
  $matiere_id             = TRUE;
  $matiere_nom            = '';
  $groupe_id              = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom             = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $date_debut             = '';
  $date_fin               = '';
  $retroactif             = $_SESSION['OFFICIEL']['RELEVE_RETROACTIF']; // C'est un relevé de notes sur une période donnée : aller chercher les notes antérieures serait curieux !
  $only_socle             = $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE'];
  $aff_domaine            = 0;
  $aff_theme              = 0;
  $legende                = 0;
  $tab_eleve              = $tab_eleve_id;
  $liste_eleve            = $liste_eleve_id;
  $tab_type[]             = 'individuel';
  $type_individuel        = 1;
  $type_synthese          = 0;
  $type_bulletin          = 0;
  $tab_matiere_id         = $tab_rubrique_id; // N'est pas utilisé pour la récupération des résultats mais juste pour tester si on doit vérifier cette partie (ce serait un double souci sinon : il faut tester les bilans élèves qui ont des résultats ailleurs + ce tableau peut contenir la valeur 0).
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_releve.php');
}
elseif($BILAN_TYPE=='bulletin')
{
  $format         = 'multimatiere' ;
  $groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $date_debut     = '';
  $date_fin       = '';
  $retroactif     = $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'];
  $fusion_niveaux = $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX'];
  $niveau_id      = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
  $only_socle     = $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'];
  $only_niveau    = 0; // pas jugé utile de le mettre en option...
  $legende        = 0;
  $tab_eleve      = $tab_eleve_id;
  $liste_eleve    = $liste_eleve_id;
  $tab_matiere_id = $tab_rubrique_id; // N'est pas utilisé pour la récupération des résultats mais juste pour tester si on doit vérifier cette partie (ce serait un double souci sinon : il faut tester les bilans élèves qui ont des résultats ailleurs + ce tableau peut contenir la valeur 0).
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_items_synthese.php');
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
  $legende        = 0;
  $tab_pilier_id  = $tab_pilier_id; // Pas $tab_rubrique_id car il ne faut pas juste restreindre à la liste des rubriques dont on souhaite vérifier l'appréciation afin de récupérer les bilans de tous les élèves concernés.
  $tab_eleve_id   = $tab_eleve_id;
  $tab_matiere_id = array();
  require(CHEMIN_DOSSIER_INCLUDE.'noyau_socle_releve.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat de l'analyse
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$nb_pb_rubriques = count($tab_resultat_examen);
if(!$nb_pb_rubriques)
{
  exit('<p class="ti"><label class="valide">Aucune saisie manquante trouvée.</label></p>');
}
else
{
  $nb_pb_saisies = count($tab_resultat_examen,COUNT_RECURSIVE) - $nb_pb_rubriques ;
  $sr = ($nb_pb_rubriques>1) ? 's' : '' ;
  $ss = ($nb_pb_saisies>1)   ? 's' : '' ;
  echo'<p class="ti"><label class="danger">'.$nb_pb_saisies.' saisie'.$ss.' manquante'.$ss.' répartie'.$ss.' parmi '.$nb_pb_rubriques.' rubrique'.$sr.' !</label></p>';
  foreach($tab_resultat_examen as $rubrique_nom => $tab)
  {
    echo'<h4>'.html($rubrique_nom).'</h4>';
    echo'<ul class="puce"><li>'.implode('</li><li>',$tab).'</li></ul>';
  }
  exit();
}

?>
