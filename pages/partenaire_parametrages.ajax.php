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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])    : '' ;
$logo        = (isset($_POST['f_logo']))        ? Clean::texte($_POST['f_logo'])      : '' ; // inutilisé
$adresse_web = (isset($_POST['f_adresse_web'])) ? Clean::url($_POST['f_adresse_web']) : '' ;
$message     = (isset($_POST['f_message']))     ? Clean::texte($_POST['f_message'])   : '' ;

$tab_ext_images = array('bmp','gif','jpg','jpeg','png');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Uploader un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_logo')
{
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , NULL /*fichier_nom*/ , $tab_ext_images /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  // vérifier la conformité du fichier image, récupérer les infos le concernant
  $tab_infos = @getimagesize(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
  if($tab_infos==FALSE)
  {
    unlink(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    exit('Erreur : le fichier image ne semble pas valide !');
  }
  list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' , IMAGETYPE_BMP=>'bmp' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    unlink(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    exit('Erreur : le fichier transmis n\'est pas un fichier image !');
  }
  // vérifier les dimensions
  if( ($image_largeur>400) || ($image_hauteur>200) )
  {
    unlink(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    exit('Erreur : le fichier transmis a des dimensions trop grandes ('.$image_largeur.' sur '.$image_hauteur.', maximum autorisé 400 sur 200).');
  }
  // On ne met pas encore à jour le logo : on place pour l'instant l'adresse de l'image en session (comme marqueur) en attendant confirmation.
  $_SESSION['TMP']['partenaire_logo_new_filename'] = FileSystem::$file_saved_name;
  $_SESSION['TMP']['partenaire_logo_new_file_ext'] = $tab_extension_types[$image_type];
  // Retour
  exit('ok-'.URL_DIR_IMPORT.FileSystem::$file_saved_name);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un logo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='delete_logo')
{
  // On ne supprime pas encore le logo : on place pour l'instant l'adresse de l'image vide en session (comme marqueur) en attendant confirmation.
  $_SESSION['TMP']['partenaire_logo_new_filename'] = '';
  // Retour
  exit('ok-'.URL_DIR_IMG.'auto.gif');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrer le nouveau fichier de paramètres
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='enregistrer')
{
  // Pour le logo, ... 
  if(!isset($_SESSION['TMP']['partenaire_logo_new_filename']))
  {
    // soit on conserve le précédent (éventuellement rien),
  }
  elseif($_SESSION['TMP']['partenaire_logo_new_filename']=='')
  {
    // soit on le supprime,
    if(is_file(CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename']))
    {
      unlink(CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename']);
    }
    $_SESSION['TMP']['partenaire_logo_actuel_filename'] = '';
  }
  elseif(is_file(CHEMIN_DOSSIER_IMPORT.$_SESSION['TMP']['partenaire_logo_new_filename']))
  {
    // soit on prend le nouveau, auquel cas il faut aussi le déplacer dans CHEMIN_DOSSIER_PARTENARIAT, et éventuellement supprimer l'ancien
    if( ($_SESSION['TMP']['partenaire_logo_actuel_filename']) && (is_file(CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename'])) )
    {
      unlink(CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename']);
    }
    $_SESSION['TMP']['partenaire_logo_actuel_filename'] = 'logo_'.$_SESSION['USER_ID'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.'.$_SESSION['TMP']['partenaire_logo_new_file_ext'];
    copy( CHEMIN_DOSSIER_IMPORT.$_SESSION['TMP']['partenaire_logo_new_filename'] , CHEMIN_DOSSIER_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename'] );
  }
  unset( $_SESSION['TMP']['partenaire_logo_new_filename'] , $_SESSION['TMP']['partenaire_logo_new_file_ext'] );
  // On fabrique le fichier avec les infos et on l'enregistre
  FileSystem::fabriquer_fichier_partenaire_message( $_SESSION['USER_ID'] , $_SESSION['TMP']['partenaire_logo_actuel_filename'] , $adresse_web , $message );
  // Retour
  $partenaire_logo_url = ($_SESSION['TMP']['partenaire_logo_actuel_filename']) ? URL_DIR_PARTENARIAT.$_SESSION['TMP']['partenaire_logo_actuel_filename'] : URL_DIR_IMG.'auto.gif' ;
  $partenaire_lien_ouvrant = ($adresse_web) ? '<a href="'.html($adresse_web).'" class="lien_ext">' : '' ;
  $partenaire_lien_fermant = ($adresse_web) ? '</a>' : '' ;
  exit('ok-'.$partenaire_lien_ouvrant.'<span id="partenaire_logo"><img src="'.html($partenaire_logo_url).'" /></span><span id="partenaire_message">'.nl2br(html($message)).'</span>'.$partenaire_lien_fermant.'<hr id="partenaire_hr" />');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
