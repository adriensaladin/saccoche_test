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

$action                      = (isset($_POST['action']))                        ? $_POST['action']                                      : '';
$geo1                        = (isset($_POST['f_geo1']))                        ? Clean::entier($_POST['f_geo1'])                        : 0;
$geo2                        = (isset($_POST['f_geo2']))                        ? Clean::entier($_POST['f_geo2'])                        : 0;
$geo3                        = (isset($_POST['f_geo3']))                        ? Clean::entier($_POST['f_geo3'])                        : 0;
$uai                         = (isset($_POST['f_uai']))                         ? Clean::uai($_POST['f_uai'])                            : '';

$sesamath_id                 = (isset($_POST['f_sesamath_id']))                 ? Clean::entier($_POST['f_sesamath_id'])                 : 0;
$sesamath_uai                = (isset($_POST['f_sesamath_uai']))                ? Clean::uai($_POST['f_sesamath_uai'])                   : '';
$sesamath_type_nom           = (isset($_POST['f_sesamath_type_nom']))           ? Clean::texte($_POST['f_sesamath_type_nom'])            : '';
$sesamath_key                = (isset($_POST['f_sesamath_key']))                ? Clean::texte($_POST['f_sesamath_key'])                 : '';

$etablissement_denomination  = (isset($_POST['f_etablissement_denomination']))  ? Clean::texte($_POST['f_etablissement_denomination'])   : '';
$etablissement_adresse1      = (isset($_POST['f_etablissement_adresse1']))      ? Clean::texte($_POST['f_etablissement_adresse1'])       : '';
$etablissement_adresse2      = (isset($_POST['f_etablissement_adresse2']))      ? Clean::texte($_POST['f_etablissement_adresse2'])       : '';
$etablissement_adresse3      = (isset($_POST['f_etablissement_adresse3']))      ? Clean::texte($_POST['f_etablissement_adresse3'])       : '';
$etablissement_telephone     = (isset($_POST['f_etablissement_telephone']))     ? Clean::texte($_POST['f_etablissement_telephone'])      : '';
$etablissement_fax           = (isset($_POST['f_etablissement_fax']))           ? Clean::texte($_POST['f_etablissement_fax'])            : '';
$etablissement_courriel      = (isset($_POST['f_etablissement_courriel']))      ? Clean::texte($_POST['f_etablissement_courriel'])       : '';

$mois_bascule_annee_scolaire = (isset($_POST['f_mois_bascule_annee_scolaire'])) ? Clean::entier($_POST['f_mois_bascule_annee_scolaire']) : 0;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo1 et le renvoyer en HTML
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='Afficher_form_geo1')
{
  exit( ServeurCommunautaire::Sesamath_afficher_formulaire_geo1() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo2 et le renvoyer en HTML
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_form_geo2') && ($geo1>0) )
{
  exit( ServeurCommunautaire::Sesamath_afficher_formulaire_geo2($geo1) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire f_geo3 et le renvoyer en HTML
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_form_geo3') && ($geo1>0) && ($geo2>0) )
{
  exit( ServeurCommunautaire::Sesamath_afficher_formulaire_geo3($geo1,$geo2) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le résultat de la recherche de structure, soit à partir du n°UAI soit à partir du code de commune
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Afficher_structures') && ( ($geo3>0) || ($uai!='') ) )
{
  echo ($geo3) ? ServeurCommunautaire::Sesamath_lister_structures_by_commune($geo3) : ServeurCommunautaire::Sesamath_recuperer_structure_by_UAI($uai) ;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les informations form_sesamath
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $sesamath_id && $sesamath_type_nom && $sesamath_key )
{
  $retour = ServeurCommunautaire::Sesamath_enregistrer_structure($sesamath_id,$sesamath_key);
  if($retour!='ok')
  {
    exit($retour);
  }
  // Si on arrive là, alors tout s'est bien passé.
  $tab_parametres = array();
  $tab_parametres['sesamath_id']       = $sesamath_id;
  $tab_parametres['sesamath_uai']      = $sesamath_uai;
  $tab_parametres['sesamath_type_nom'] = $sesamath_type_nom;
  $tab_parametres['sesamath_key']      = $sesamath_key;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['SESAMATH_ID']       = $sesamath_id ;
  $_SESSION['SESAMATH_UAI']      = $sesamath_uai ;
  $_SESSION['SESAMATH_TYPE_NOM'] = $sesamath_type_nom ;
  $_SESSION['SESAMATH_KEY']      = $sesamath_key ;
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour les informations form_etablissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $etablissement_denomination )
{
  $tab_parametres = array();
  $tab_parametres['etablissement_denomination'] = $etablissement_denomination;
  $tab_parametres['etablissement_adresse1']     = $etablissement_adresse1;
  $tab_parametres['etablissement_adresse2']     = $etablissement_adresse2;
  $tab_parametres['etablissement_adresse3']     = $etablissement_adresse3;
  $tab_parametres['etablissement_telephone']    = $etablissement_telephone;
  $tab_parametres['etablissement_fax']          = $etablissement_fax;
  $tab_parametres['etablissement_courriel']     = $etablissement_courriel;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['ETABLISSEMENT']['DENOMINATION'] = $etablissement_denomination;
  $_SESSION['ETABLISSEMENT']['ADRESSE1']     = $etablissement_adresse1;
  $_SESSION['ETABLISSEMENT']['ADRESSE2']     = $etablissement_adresse2;
  $_SESSION['ETABLISSEMENT']['ADRESSE3']     = $etablissement_adresse3;
  $_SESSION['ETABLISSEMENT']['TELEPHONE']    = $etablissement_telephone;
  $_SESSION['ETABLISSEMENT']['FAX']          = $etablissement_fax;
  $_SESSION['ETABLISSEMENT']['COURRIEL']     = $etablissement_courriel;
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire form_logo (upload d'un fichier image)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='upload_logo')
{
  $fichier_nom = 'logo_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.<EXT>';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_nom /*fichier_nom*/ , array('gif','jpg','jpeg','png') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , 100 /*taille_maxi*/ , NULL /*filename_in_zip*/ );
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
  $tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
  // vérifier le type 
  if(!isset($tab_extension_types[$image_type]))
  {
    unlink(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name);
    exit('Erreur : le fichier transmis n\'est pas un fichier image (type '.$image_type.') !');
  }
  $image_format = $tab_extension_types[$image_type];
  // stocker l'image dans la base
  DB_STRUCTURE_IMAGE::DB_modifier_image( 0 /*user_id*/ , 'logo' , base64_encode(file_get_contents(CHEMIN_DOSSIER_IMPORT.FileSystem::$file_saved_name)) , $image_format , $image_largeur , $image_hauteur );
  // Générer la balise html et afficher le retour
  list($width,$height) = dimensions_affichage_image( $image_largeur , $image_hauteur , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
  exit('<li><img src="'.URL_DIR_IMPORT.FileSystem::$file_saved_name.'" alt="Logo établissement" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un fichier image (logo de l'établissement)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='delete_logo')
{
  DB_STRUCTURE_IMAGE::DB_supprimer_image( 0 /*user_id*/ , 'logo' );
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour mois_bascule_annee_scolaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $mois_bascule_annee_scolaire )
{
  $tab_parametres = array();
  $tab_parametres['mois_bascule_annee_scolaire'] = $mois_bascule_annee_scolaire;
  DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
  // On modifie aussi la session
  $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'] = $mois_bascule_annee_scolaire;
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
