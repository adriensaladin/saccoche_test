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

$action             = (isset($_POST['f_action']))             ? $_POST['f_action']                          : '';

$tab_coordonnees    = (isset($_POST['f_coordonnees']))        ? $_POST['f_coordonnees']                     : array();
$infos_responsables = (isset($_POST['f_infos_responsables'])) ? clean_texte($_POST['f_infos_responsables']) : '';
$horizontal_gauche  = (isset($_POST['f_horizontal_gauche']))  ? clean_entier($_POST['f_horizontal_gauche']) : 0;
$horizontal_milieu  = (isset($_POST['f_horizontal_milieu']))  ? clean_entier($_POST['f_horizontal_milieu']) : 0;
$horizontal_droite  = (isset($_POST['f_horizontal_droite']))  ? clean_entier($_POST['f_horizontal_droite']) : 0;
$vertical_haut      = (isset($_POST['f_vertical_haut']))      ? clean_entier($_POST['f_vertical_haut'])     : 0;
$vertical_milieu    = (isset($_POST['f_vertical_milieu']))    ? clean_entier($_POST['f_vertical_milieu'])   : 0;
$vertical_bas       = (isset($_POST['f_vertical_bas']))       ? clean_entier($_POST['f_vertical_bas'])      : 0;
$nombre_exemplaires = (isset($_POST['f_nombre_exemplaires'])) ? clean_texte($_POST['f_nombre_exemplaires']) : '';
$marge_gauche       = (isset($_POST['f_marge_gauche']))       ? clean_entier($_POST['f_marge_gauche'])      : 0;
$marge_droite       = (isset($_POST['f_marge_droite']))       ? clean_entier($_POST['f_marge_droite'])      : 0;
$marge_haut         = (isset($_POST['f_marge_haut']))         ? clean_entier($_POST['f_marge_haut'])        : 0;
$marge_bas          = (isset($_POST['f_marge_bas']))          ? clean_entier($_POST['f_marge_bas'])         : 0;
$tampon_signature   = (isset($_POST['f_tampon_signature']))   ? clean_texte($_POST['f_tampon_signature'])   : '';
$user_id            = (isset($_POST['f_user_id']))            ? clean_entier($_POST['f_user_id'])           : -1;
$user_texte         = (isset($_POST['f_user_texte']))         ? clean_texte($_POST['f_user_texte'])         : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire form_mise_en_page
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='mise_en_page') && $infos_responsables && $horizontal_gauche && $horizontal_milieu && $horizontal_droite && $vertical_haut && $vertical_milieu && $vertical_bas && $nombre_exemplaires && $marge_gauche && $marge_droite && $marge_haut && $marge_bas && $tampon_signature )
{
	$tab_parametres = array();
	$tab_parametres['officiel_infos_etablissement'] = implode(',',$tab_coordonnees);
	$tab_parametres['officiel_infos_responsables']  = $infos_responsables;
	$tab_parametres['officiel_nombre_exemplaires']  = $nombre_exemplaires;
	$tab_parametres['officiel_marge_gauche']        = $marge_gauche;
	$tab_parametres['officiel_marge_droite']        = $marge_droite;
	$tab_parametres['officiel_marge_haut']          = $marge_haut;
	$tab_parametres['officiel_marge_bas']           = $marge_bas;
	$tab_parametres['officiel_tampon_signature']    = $tampon_signature;
	if($infos_responsables=='oui_force')
	{
		$tab_parametres['enveloppe_horizontal_gauche'] = $horizontal_gauche;
		$tab_parametres['enveloppe_horizontal_milieu'] = $horizontal_milieu;
		$tab_parametres['enveloppe_horizontal_droite'] = $horizontal_droite;
		$tab_parametres['enveloppe_vertical_haut']     = $vertical_haut;
		$tab_parametres['enveloppe_vertical_milieu']   = $vertical_milieu;
		$tab_parametres['enveloppe_vertical_bas']      = $vertical_bas;
	}
	DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
	// On modifie aussi la session
	$_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] = implode(',',$tab_coordonnees) ;
	$_SESSION['OFFICIEL']['INFOS_RESPONSABLES']  = $infos_responsables ;
	$_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']  = $nombre_exemplaires ;
	$_SESSION['OFFICIEL']['MARGE_GAUCHE']        = $marge_gauche ;
	$_SESSION['OFFICIEL']['MARGE_DROITE']        = $marge_droite ;
	$_SESSION['OFFICIEL']['MARGE_HAUT']          = $marge_haut ;
	$_SESSION['OFFICIEL']['MARGE_BAS']           = $marge_bas ;
	$_SESSION['OFFICIEL']['TAMPON_SIGNATURE']    = $tampon_signature ;
	if($infos_responsables=='oui_force')
	{
		$_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] = $horizontal_gauche ;
		$_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] = $horizontal_milieu ;
		$_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] = $horizontal_droite ;
		$_SESSION['ENVELOPPE']['VERTICAL_HAUT']     = $vertical_haut ;
		$_SESSION['ENVELOPPE']['VERTICAL_MILIEU']   = $vertical_milieu ;
		$_SESSION['ENVELOPPE']['VERTICAL_BAS']      = $vertical_bas ;
	}
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire form_tampon (upload d'un fichier image)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='upload_signature') && ($user_id>=0) && ($user_texte!='') )
{
	// récupération des infos
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload());
	}
	// vérifier l'extension
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	$tab_extension_images = array('gif','jpg','jpeg','png');
	if(!in_array($extension,$tab_extension_images))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	// vérifier le poids
	if( $ftaille > 100*1000 )
	{
		$conseil = (($extension=='jpg')||($extension=='jpeg')) ? 'réduisez les dimensions de l\'image' : 'convertissez l\'image au format JPEG' ;
		exit('Erreur : le poids du fichier dépasse les 100 Ko autorisés : '.$conseil.' !');
	}
	// vérifier la conformité du fichier image, récupérer les infos le concernant
	$tab_infos = @getimagesize($fnom_serveur);
	if($tab_infos==FALSE)
	{
		exit('Erreur : le fichier image ne semble pas valide !');
	}
	list($image_largeur, $image_hauteur, $image_type, $html_attributs) = $tab_infos;
	$tab_extension_types = array( IMAGETYPE_GIF=>'gif' , IMAGETYPE_JPEG=>'jpeg' , IMAGETYPE_PNG=>'png' ); // http://www.php.net/manual/fr/function.exif-imagetype.php#refsect1-function.exif-imagetype-constants
	if(!isset($tab_extension_types[$image_type]))
	{
		exit('Erreur : le fichier transmis n\'est pas un fichier image (type '.$image_type.') !');
	}
	$image_format = $tab_extension_types[$image_type];
	// enregistrer le fichier (temporairement)
	$dossier     = './__tmp/export/';
	$fichier_nom = 'signature_'.$_SESSION['BASE'].'_'.$user_id.'_'.fabriquer_fin_nom_fichier__date_et_alea().'.'.$extension;
	if(!move_uploaded_file($fnom_serveur , $dossier.$fichier_nom))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// stocker l'image dans la base
	DB_STRUCTURE_OFFICIEL::DB_modifier_signature( $user_id , base64_encode(file_get_contents($dossier.$fichier_nom)) , $image_format , $image_largeur , $image_hauteur );
	// Générer la balise html et afficher le retour
	list($width,$height) = dimensions_affichage_image( $image_largeur , $image_hauteur , 200 /*largeur_maxi*/ , 200 /*hauteur_maxi*/ );
	$user_texte = ($user_id) ? 'Signature '.$user_texte : $user_texte ;
	exit('<li id="sgn_'.$user_id.'">'.html($user_texte).' : <img src="'.$dossier.$fichier_nom.'" alt="'.html($user_texte).'" width="'.$width.'" height="'.$height.'" /><q class="supprimer" title="Supprimer cette image (aucune confirmation ne sera demandée)."></q></li>');
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Supprimer le tampon de l'établissement
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if( ($action=='delete_signature') && ($user_id>=0) )
{
	DB_STRUCTURE_OFFICIEL::DB_supprimer_signature($user_id);
	exit('ok');
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	On ne devrait pas en arriver là...
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

exit('Erreur avec les données transmises !');

?>
