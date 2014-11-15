<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$objet     = (isset($_POST['f_objet']))  ? Clean::texte($_POST['f_objet'])   : '';
$ACTION    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])  : '';
$classe_id = (isset($_POST['f_classe'])) ? Clean::entier($_POST['f_classe']) : 0;
$groupe_id = (isset($_POST['f_groupe'])) ? Clean::entier($_POST['f_groupe']) : 0;
$etape     = (isset($_POST['f_etape']))  ? Clean::entier($_POST['f_etape'])  : 0;
// Autres chaines spécifiques...
$listing_eleves = (isset($_POST['f_listing_eleves']))  ? $_POST['f_listing_eleves']  : '' ;
$tab_eleve_id   = array_filter( Clean::map_entier( explode(',',$listing_eleves) )  , 'positif' );
$liste_eleve_id = implode(',',$tab_eleve_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_objet  = array('imprimer','voir_archive');
$tab_action = array('initialiser','imprimer');

$bilan_type = 'brevet';
$annee_session_brevet = annee_session_brevet();

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!in_array($objet,$tab_objet)) || !$classe_id || ( (!$liste_eleve_id)&&($ACTION!='initialiser') ) )
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que la fiche brevet est bien accessible en impression et on récupère les infos associées (nom de la classe, id des élèves concernés avec lesquels l'intersection est faite ultérieurement).

$DB_ROW = DB_STRUCTURE_BREVET::DB_recuperer_brevet_classe_infos($classe_id);
if(empty($DB_ROW))
{
  exit('Classe sans élèves concernés !');
}
$BILAN_ETAT = $DB_ROW['fiche_brevet'];
$classe_nom = $DB_ROW['groupe_nom'];
$tab_id_eleves_avec_notes = explode(',',$DB_ROW['listing_user_id']);

if(!$BILAN_ETAT)
{
  exit('Fiche brevet introuvable !');
}
if( ($BILAN_ETAT!='4complet') && empty($is_test_impression) )
{
  exit('Fiche brevet interdite d\'accès pour cette action !');
}
if(!$DB_ROW['listing_user_id'])
{
  exit('Aucun élève concerné dans cette classe !');
}

if( !empty($is_test_impression) && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique( $_SESSION['DROIT_FICHE_BREVET_IMPRESSION_PDF'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  exit('Droits insuffisants pour cette action !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='initialiser')
{
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
  if(empty($DB_TAB))
  {
    exit('Aucun élève trouvé dans ce regroupement !');
  }
  $tab_eleve_id = array();
  foreach($DB_TAB as $DB_ROW)
  {
    if(in_array($DB_ROW['user_id'],$tab_id_eleves_avec_notes))
    {
      $tab_eleve_id[] = $DB_ROW['user_id'];
      $tab_eleve_td[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
    }
  }
  if(empty($tab_eleve_id))
  {
    exit('Aucun élève concerné dans ce regroupement !');
  }
  $liste_eleve_id = implode(',',$tab_eleve_id);

  // lister les fiches brevets archivées de l'année courante, affichage du retour
  $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_fichiers($liste_eleve_id);
  $_SESSION['tmp_droit_voir_archive'] = array(); // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
  foreach($tab_eleve_id as $eleve_id)
  {
    if($objet=='imprimer')
    {
      $checked    = (isset($DB_TAB[$eleve_id])) ? '' : ' checked' ;
      $archive_td = (isset($DB_TAB[$eleve_id])) ? 'Oui, le '.convert_date_mysql_to_french($DB_TAB[$eleve_id][0]['fichier_date']) : 'Non' ;
      echo'<tr id="id_'.$eleve_id.'">';
      echo'<td class="nu"><input type="checkbox" name="f_ids" value="'.$eleve_id.'"'.$checked.' /></td>';
      echo'<td class="label">'.$tab_eleve_td[$eleve_id].'</td>';
      echo'<td class="label hc">'.$archive_td.'</td>';
      echo'</tr>';
    }
    elseif($objet=='voir_archive')
    {
      if(!isset($DB_TAB[$eleve_id]))
      {
        $archive_td = 'Non, pas encore imprimé' ;
      }
      elseif(is_file(CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $bilan_type , $annee_session_brevet )))
      {
        $_SESSION['tmp_droit_voir_archive'][$eleve_id.$bilan_type] = TRUE; // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et a donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
        $archive_td = '<a href="releve_pdf.php?fichier='.$eleve_id.'_'.$bilan_type.'_'.$annee_session_brevet.'" target="_blank">Oui, le '.convert_date_mysql_to_french($DB_TAB[$eleve_id][0]['fichier_date']).'</a>' ;
      }
      else
      {
        $archive_td = 'Oui, mais archive non présente sur ce serveur' ;
      }
      echo'<tr>';
      echo'<td>'.$tab_eleve_td[$eleve_id].'</td>';
      echo'<td class="hc">'.$archive_td.'</td>';
      echo'</tr>';
    }
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 2/4 - Le PDF complet est généré ; on archive individuellement les fiches brevet informatives (qui sont y rester une année scolaire)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==2) )
{
  Erreur500::prevention_et_gestion_erreurs_fatales( FALSE /*memory*/ , TRUE /*time*/ );
  foreach($_SESSION['tmp']['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    list( $eleve_identite , $page_numero ) = $tab_tirages[1];
    DB_STRUCTURE_BREVET::DB_modifier_brevet_fichier($eleve_id);
    $fichier_extraction_chemin = CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $bilan_type , $annee_session_brevet );
    unset($_SESSION['tmp']['tab_pages_decoupe_pdf'][$eleve_id][1]);
    $releve_pdf = new PDFMerger;
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $page_numero ) -> merge( 'file' , $fichier_extraction_chemin );
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 3/4 - Le PDF complet est généré ; on découpe individuellement les fiches brevet par élève puis on zippe l'ensemble
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==3) )
{
  Erreur500::prevention_et_gestion_erreurs_fatales( FALSE /*memory*/ , TRUE /*time*/ );
  $date = date('Y-m-d');
  $tab_pages_non_anonymes = array();
  $chemin_temp_pdf = CHEMIN_DOSSIER_EXPORT.'pdf_'.mt_rand().DS;
  FileSystem::creer_ou_vider_dossier($chemin_temp_pdf);
  foreach($_SESSION['tmp']['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    list( $eleve_identite , $page_numero ) = $tab_tirages[0];
    $tab_pages_non_anonymes[] = $page_numero;
    $fichier_extraction_chemin = $chemin_temp_pdf.'officiel_'.$bilan_type.'_'.Clean::fichier($eleve_identite).'_'.$date.'.pdf';
    $releve_pdf = new PDFMerger;
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $page_numero ) -> merge( 'file' , $fichier_extraction_chemin );
  }
  FileSystem::zipper_fichiers( $chemin_temp_pdf , CHEMIN_DOSSIER_EXPORT , $_SESSION['tmp']['fichier_nom'].'.zip' );
  FileSystem::supprimer_dossier($chemin_temp_pdf);
  $_SESSION['tmp']['pages_non_anonymes'] = implode(',',$tab_pages_non_anonymes);
  unset($_SESSION['tmp']['tab_pages_decoupe_pdf']);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 4/4 - Le PDF complet est généré ; on n'en garde que les fiches brevet officielles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==4) )
{
  $releve_pdf = new PDFMerger;
  if($_SESSION['tmp']['pages_non_anonymes']!='') // Potentiellement possible si on veut imprimer un ou plusieurs bulletins d'élèves sans aucune donnée, ce qui provoque l'erreur "FPDF error: Pagenumber is wrong!"
  {
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $_SESSION['tmp']['pages_non_anonymes'] ) -> merge( 'file' , CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' );
  }
  echo'<ul class="puce">';
  echo'<li><a target="_blank" href="'.URL_DIR_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf"><span class="file file_pdf">Récupérer, <span class="u">pour impression</span>, l\'ensemble des fiches brevet en un seul document.</span></a></li>';
  echo'<li><a target="_blank" href="'.URL_DIR_EXPORT.$_SESSION['tmp']['fichier_nom'].'.zip"><span class="file file_zip">Récupérer, <span class="u">pour archivage</span>, les fiches brevet dans des documents individuels.</span></a></li>';
  echo'</ul>';
  unset( $_SESSION['tmp']['fichier_nom'] , $_SESSION['tmp']['pages_non_anonymes'] );
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 1/4 - Génération de l'impression PDF (archive + officiel)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION!='imprimer') || ($etape!=1) )
{
  exit('Erreur avec les données transmises !');
}

// Bloc des coordonnées de l'établissement

$tab_etabl_coords = array( 0 => $_SESSION['ETABLISSEMENT']['DENOMINATION'] );
if($_SESSION['ETABLISSEMENT']['ADRESSE1'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
if($_SESSION['ETABLISSEMENT']['ADRESSE2'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
if($_SESSION['ETABLISSEMENT']['ADRESSE3'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords[] = 'Tel : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
if($_SESSION['ETABLISSEMENT']['FAX'])       { $tab_etabl_coords[] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
if($_SESSION['ETABLISSEMENT']['COURRIEL'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['COURRIEL']; }

// académie, département, année

$DB_ROW = DB_STRUCTURE_BREVET::DB_recuperer_departement_academie($_SESSION['WEBMESTRE_UAI']);
if(empty($DB_ROW))
{
 $geo_departement_nom = $geo_academie_nom = '' ;
}
else
{
  extract($DB_ROW);  // $geo_departement_nom $geo_academie_nom
}

$annee_session_brevet = annee_session_brevet();

// Tag date heure initiales

$tag_date_heure_initiales = date('d/m/Y H:i').' '.afficher_identite_initiale($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_pages_decoupe_pdf = array();
$make_action = 'imprimer';
$make_html   = FALSE;
$make_pdf    = TRUE;
$make_graph  = FALSE;

$groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
$groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
$tab_eleve      = $tab_eleve_id;
$liste_eleve    = $liste_eleve_id;
$tab_matiere_id = array();
require(CHEMIN_DOSSIER_INCLUDE.'noyau_brevet_fiches.php');
$nom_bilan_html = 'releve_HTML';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat (pas grand chose, car la découpe du PDF intervient lors d'appels ajax ultérieurs, sauf s'il s'agissait d'un test d'impression auquel cas on ajoute un filigrane et on s'arrête là)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($is_test_impression))
{
  if(!count($tab_pages_decoupe_pdf))
  {
    exit('Erreur : aucune donnée trouvée pour le ou les élèves concernés !');
  }
  $_SESSION['tmp']['fichier_nom'] = $fichier_nom;
  $_SESSION['tmp']['tab_pages_decoupe_pdf'] = $tab_pages_decoupe_pdf;
  exit('ok');
}
else
{
  exit('ok;'.URL_DIR_EXPORT.$fichier_nom.'.pdf');
}

?>
