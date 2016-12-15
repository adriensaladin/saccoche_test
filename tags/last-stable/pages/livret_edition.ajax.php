<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO){Json::end( FALSE , 'Action désactivée pour la démo.' );}

/*
TODO : notes temporaires à virer
SUPPRIMÉ : $BILAN_TYPE
$make_officiel = TRUE;
$make_brevet   = FALSE;
$releve_modele            = 'multimatiere';
$synthese_modele          = 'multimatiere' ;
$releve_individuel_format = 'eleve';
$aff_etat_acquisition     = $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION'];
$aff_moyenne_scores       = $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES'];
$aff_pourcentage_acquis   = $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS'];
$conversion_sur_20        = $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20'];
$with_coef                = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
$matiere_id               = TRUE;
$matiere_nom              = '';
$retroactif               = $_SESSION['OFFICIEL']['RELEVE_RETROACTIF']; // C'est un relevé de notes sur une période donnée : aller chercher les notes antérieures serait curieux !
$fusion_niveaux           = $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX'];
$only_etat                = $_SESSION['OFFICIEL']['RELEVE_ONLY_ETAT'];
$only_socle               = $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE'];
$only_niveau              = 0; // pas jugé utile de le mettre en option...
$niveau_id                = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
$aff_reference            = $_SESSION['OFFICIEL']['RELEVE_AFF_REFERENCE'];
$aff_coef                 = $_SESSION['OFFICIEL']['RELEVE_AFF_COEF'];
$aff_socle                = $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE'];
$aff_comm                 = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
$aff_lien                 = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
$aff_start                = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
$aff_domaine              = $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE'];
$aff_theme                = $_SESSION['OFFICIEL']['RELEVE_AFF_THEME'];
$couleur                  = $_SESSION['OFFICIEL']['RELEVE_COULEUR'];
$fond                     = $_SESSION['OFFICIEL']['RELEVE_FOND'];
$legende                  = $_SESSION['OFFICIEL']['RELEVE_LEGENDE'];
$marge_gauche             = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
$marge_droite             = $_SESSION['OFFICIEL']['MARGE_DROITE'];
$marge_haut               = $_SESSION['OFFICIEL']['MARGE_HAUT'];
$marge_bas                = $_SESSION['OFFICIEL']['MARGE_BAS'];
$pages_nb                 = $_SESSION['OFFICIEL']['RELEVE_PAGES_NB'];
$cases_nb                 = $_SESSION['OFFICIEL']['RELEVE_CASES_NB'];
$cases_largeur            = 5; // pas jugé utile de le mettre en option...
$highlight_id             = 0; // Ne sert que pour le relevé d'items d'une matière
$tab_type[]               = 'individuel';
$type_individuel          = 1;
$type_synthese            = 0;
$type_bulletin            = 0;
$tab_matiere_id           = $tab_matiere_id;
$palier_id                = (int)substr($BILAN_TYPE,-1);
$palier_nom               = 'Palier '.$palier_id;
$only_presence            = $_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE'];
$aff_socle_PA             = $_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS'];
$aff_socle_EV             = $_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION'];
$mode                     = 'auto';
$nom_bilan_html           = 'releve_HTML_individuel';
  if(!$aff_coef)  { $texte_coef       = ''; }
  if(!$aff_socle) { $texte_socle      = ''; }
  if(!$aff_socle) { $texte_s2016      = ''; }
  if(!$aff_lien)  { $texte_lien_avant = ''; }
  if(!$aff_lien)  { $texte_lien_apres = ''; }
  $toggle_class = ($aff_start) ? 'toggle_moins' : 'toggle_plus' ;
  $toggle_etat  = ($aff_start) ? '' : ' class="hide"' ;
  $avec_texte_nombre = ( !$make_officiel || $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_NOMBRE'] ) ? TRUE : FALSE ;
  $avec_texte_code   = ( !$make_officiel || $_SESSION['OFFICIEL']['BULLETIN_ACQUIS_TEXTE_CODE']   ) ? TRUE : FALSE ;
$tab_precision_retroactif = array
(
  'auto'   => 'notes antérieures selon référentiels',
  'oui'    => 'avec notes antérieures',
  'non'    => 'sans notes antérieures',
  'annuel' => 'notes antérieures de l\'année scolaire',
);
$precision_socle  = $only_socle  ? ', restriction au socle' : '' ;
$precision_niveau = $only_niveau ? ', restriction au niveau de l\'élève' : '' ;
$texte_periode = 'Du '.$date_debut.' au '.$date_fin.'.';
$texte_precision = $tab_precision_retroactif[$retroactif].$precision_socle.$precision_niveau.'.';
*/

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Signaler une faute ou la correction d'une faute
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !$section && ( ($action=='signaler_faute') || ($action=='corriger_faute') ) )
{
  $destinataire_id = (isset($_POST['f_destinataire_id'])) ? Clean::entier($_POST['f_destinataire_id']) : 0;
  $message_contenu = (isset($_POST['f_message_contenu'])) ? Clean::texte($_POST['f_message_contenu'])  : '' ;
  if( !$destinataire_id || !$message_contenu )
  {
    Json::end( FALSE , 'Erreur avec les données transmises !' );
  }
  // Notification (qui est envoyée de suite)
  $abonnement_ref = 'bilan_officiel_appreciation';
  $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_avec_informations( $abonnement_ref , $destinataire_id );
  $destinataires_nb = count($DB_TAB);
  if(!$destinataires_nb)
  {
    // Normalement impossible, l'abonnement des personnels à ce type de de notification étant obligatoire
    Json::end( FALSE , 'Destinataire non trouvé ! !' );
  }
  $notification_debut = ($action=='signaler_faute') ? 'Signalement effectué par ' : 'Correction apportée par ' ;
  $notification_contenu = $notification_debut.To::texte_identite($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']).' :'."\r\n\r\n".$message_contenu."\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    // 1 seul passage en fait
    $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
    DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_visible( $DB_ROW['user_id'] , $abonnement_ref , $notification_statut , $notification_contenu );
    if($notification_statut=='envoyée')
    {
      $destinataire = $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>';
      $notification_contenu .= Sesamail::texte_pied_courriel( array('no_reply','notif_individuelle','signature') , $DB_ROW['user_email'] );
      $courriel_bilan = Sesamail::mail( $destinataire , 'Notification - Erreur appréciation livret scolaire' , $notification_contenu , NULL );
    }
  }
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$OBJET         = (isset($_POST['f_objet']))         ? Clean::texte($_POST['f_objet'])               : '';
$ACTION        = (isset($_POST['f_action']))        ? Clean::texte($_POST['f_action'])              : '';
$mode          = (isset($_POST['f_mode']))          ? Clean::texte($_POST['f_mode'])                : '';
$PAGE_REF      = (isset($_POST['f_page_ref']))      ? Clean::texte($_POST['f_page_ref'])            : '';
$periode       = (isset($_POST['f_periode']))       ? Clean::texte($_POST['f_periode'])             : '';
$classe_id     = (isset($_POST['f_classe']))        ? Clean::entier($_POST['f_classe'])             : 0;
$groupe_id     = (isset($_POST['f_groupe']))        ? Clean::entier($_POST['f_groupe'])             : 0;
$eleve_id      = (isset($_POST['f_user']))          ? Clean::entier($_POST['f_user'])               : 0;
$saisie_id     = (isset($_POST['f_saisie_id']))     ? Clean::entier($_POST['f_saisie_id'])          : 0;
$rubrique_type = (isset($_POST['f_rubrique_type'])) ? Clean::texte($_POST['f_rubrique_type'])       : '';
$rubrique_id   = (isset($_POST['f_rubrique_id']))   ? Clean::entier($_POST['f_rubrique_id'])        : 0;
$saisie_objet  = (isset($_POST['f_saisie_objet']))  ? Clean::texte($_POST['f_saisie_objet'])        : '';
$page_colonne  = (isset($_POST['f_page_colonne']))  ? Clean::texte($_POST['f_page_colonne'])        : '';
$prof_id       = (isset($_POST['f_prof']))          ? Clean::entier($_POST['f_prof'])               : 0; // id du prof dont on corrige l'appréciation
$appreciation  = (isset($_POST['f_appreciation']))  ? Clean::appreciation($_POST['f_appreciation']) : '';
$elements      = (isset($_POST['f_elements']))      ? Clean::appreciation($_POST['f_elements'])     : '';
$position      = (isset($_POST['f_position']))      ? Clean::decimal($_POST['f_position'])          : -1;
$import_info   = (isset($_POST['f_import_info']))   ? Clean::texte($_POST['f_import_info'])         : '';
$etape         = (isset($_POST['f_etape']))         ? Clean::entier($_POST['f_etape'])              : 0;

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

// Tableaux communs utiles

$tab_periode_livret = array(
  'periodeT1' => 'Trimestre 1/3' ,
  'periodeT2' => 'Trimestre 2/3' ,
  'periodeT3' => 'Trimestre 3/3' ,
  'periodeS1' => 'Semestre 1/2'  ,
  'periodeS2' => 'Semestre 2/2'  ,
  'cycle'     => 'Fin de cycle'  ,
  'college'   => 'Fin du collège',
);

// Vérification période + extraction des infos sur la période

if( !isset($tab_periode_livret[$periode]) )
{
  Json::end( FALSE , 'Période "'.html($periode).'" inconnue !' );
}
if(substr($periode,0,7)=='periode')
{
  $PAGE_PERIODICITE = 'periode';
  $JOINTURE_PERIODE = substr($periode,7);
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_periode_info( $JOINTURE_PERIODE , $classe_id );
  if(empty($DB_ROW))
  {
    Json::end( FALSE , 'Jointure période/classe transmise indéfinie !' );
  }
  $periode_id       = $DB_ROW['periode_id'];
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = To::date_mysql_to_french($date_mysql_debut);
  $date_fin   = To::date_mysql_to_french($date_mysql_fin);
}
else
{
  $PAGE_PERIODICITE = $periode;
  $JOINTURE_PERIODE = '';
  $periode_id       = 0;
  $date_mysql_debut = NULL;
  $date_mysql_fin   = NULL;
  $date_debut = '';
  $date_fin   = '';
}
$periode_nom = $tab_periode_livret[$periode];

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir    : affichage des données d'un élève | enregistrement/suppression d'une appréciation ou d'une note ou d'éléments ou d'un rattachement | recalculer une note
// Examiner  : recherche des saisies manquantes (notes et appréciations)
// Consulter : affichage des données d'un élève (HTML) | détail d'une rubrique pour un élève
// Imprimer  : affichage de la liste des élèves | étape d'impression PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array( $section , array('livret_saisir','livret_examiner','livret_consulter','livret_imprimer','livret_importer') ) )
{
  if( ($section=='livret_consulter') && ($action=='imprimer') )
  {
    // Il s'agit d'un test d'impression d'un bilan non encore clos (on vérifiera quand même par la suite que les conditions sont respectées (état du bilan, droit de l'utilisateur)
    $section = 'livret_imprimer';
    $OBJET = 'imprimer';
    $is_test_impression = TRUE;
  }
  require(CHEMIN_DOSSIER_INCLUDE.'fonction_livret.php');
  require(CHEMIN_DOSSIER_INCLUDE.'code_'.$section.'.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un archivage des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_actions = array
(
  'imprimer_donnees_eleves_prof'          => 'Mes appréciations pour chaque élève et le groupe classe',
  'imprimer_donnees_eleves_collegues'     => 'Appréciations des collègues pour chaque élève',
  'imprimer_donnees_classe_collegues'     => 'Appréciations des collègues sur le groupe classe',
  'imprimer_donnees_eleves_syntheses'     => 'Appréciations de synthèse générale pour chaque élève',
  'imprimer_donnees_eleves_moyennes'      => 'Tableau des moyennes pour chaque élève',
  'imprimer_donnees_eleves_recapitulatif' => 'Récapitulatif annuel des moyennes et appréciations par élève',
);

if( isset($tab_actions[$action]) )
{
  require(CHEMIN_DOSSIER_INCLUDE.'code_livret_archiver.php');
  // Normalement, on est stoppé avant.
  Json::end( FALSE , 'Problème de code : point d\'arrêt manquant !' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
