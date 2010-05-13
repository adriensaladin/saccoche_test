<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action       = (isset($_POST['f_action']))       ? clean_texte($_POST['f_action'])       : '';
$denomination = (isset($_POST['f_denomination'])) ? clean_texte($_POST['f_denomination']) : '';
$uai          = (isset($_POST['f_uai']))          ? clean_uai($_POST['f_uai'])            : '';
$adresse_site = (isset($_POST['f_adresse_site'])) ? clean_url($_POST['f_adresse_site'])   : '';
$logo         = (isset($_POST['f_logo']))         ? clean_texte($_POST['f_logo'])         : '';
$cnil         = (isset($_POST['f_cnil']))         ? clean_texte($_POST['f_cnil'])         : '';
$nom          = (isset($_POST['f_nom']))          ? clean_nom($_POST['f_nom'])            : '';
$prenom       = (isset($_POST['f_prenom']))       ? clean_prenom($_POST['f_prenom'])      : '';
$courriel     = (isset($_POST['f_courriel']))     ? clean_courriel($_POST['f_courriel'])  : '';

$dossier_images = './__hebergement_info/';
$tab_ext_images = array('bmp','gif','jpg','jpeg','png','svg');

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Contenu du select avec la liste des logos disponibles
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='select_logo')
{
	$tab_files = scandir($dossier_images);
	$options_logo = '';
	foreach($tab_files as $file)
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		if(in_array($extension,$tab_ext_images))
		{
			$selected = ($file==HEBERGEUR_LOGO) ? ' selected="selected"' : '' ;
			$options_logo .= '<option value="'.html($file).'"'.$selected.'>'.html($file).'</option>';
		}
	}
	$options_logo = ($options_logo) ? '<option value=""></option>'.$options_logo : '<option value="" disabled="disabled">Aucun fichier image trouvé !</option>';
	exit($options_logo);
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Contenu du ul avec la liste des logos disponibles
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif($action=='listing_logos')
{
	$tab_files = scandir($dossier_images);
	$li_logos = '';
	foreach($tab_files as $file)
	{
		$extension = pathinfo($file,PATHINFO_EXTENSION);
		if(in_array($extension,$tab_ext_images))
		{
			$li_logos .= '<li>'.html($file).' <q class="supprimer" title="Supprimer cette image du serveur (aucune confirmation ne sera demandée)."></q><br /><img style="margin:1ex 1ex 3ex" alt="'.html($file).'" src="'.$dossier_images.html($file).'" /></img></li>';
		}
	}
	$li_logos = ($li_logos) ? $li_logos : '<li>Aucun fichier image trouvé !</li>';
	exit($li_logos);
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Uploader un logo
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif($action=='upload_logo')
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		exit('Erreur : erreur avec le fichier transmis (taille dépassant probablement post_max_size ) !');
	}
	$extension = pathinfo($fnom_transmis,PATHINFO_EXTENSION);
	if(!in_array($extension,$tab_ext_images))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	if(!move_uploaded_file($fnom_serveur , $dossier_images.$fnom_transmis))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	echo'ok';
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Supprimer un logo
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif( ($action=='delete_logo') && $logo )
{
	unlink($dossier_images.$logo);
	// Si on supprime l'image actuellement utilisée, alors la retirer du fichier
	if($logo==HEBERGEUR_LOGO)
	{
		fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,HEBERGEUR_DENOMINATION,HEBERGEUR_UAI,HEBERGEUR_ADRESSE_SITE,'',HEBERGEUR_CNIL,WEBMESTRE_NOM,WEBMESTRE_PRENOM,WEBMESTRE_COURRIEL,WEBMESTRE_PASSWORD_MD5);
	}
	echo'ok';
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Enregistrer le nouveau fichier de paramètres
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif( ($action=='enregistrer') && $denomination && $cnil && $nom && $prenom && $courriel )
{
	fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,$denomination,$uai,$adresse_site,$logo,$cnil,$nom,$prenom,$courriel,WEBMESTRE_PASSWORD_MD5);
	if(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		// Personnaliser certains paramètres de la structure (pour une installation de type multi-structures, ça ce fait à la page de gestion des établissements)
		$tab_parametres = array();
		$tab_parametres['denomination'] = HEBERGEUR_DENOMINATION;
		$tab_parametres['uai']          = HEBERGEUR_UAI;
		DB_modifier_parametres($tab_parametres);
	}
	// On modifie aussi la session
	$_SESSION['USER_NOM']     = $nom ;
	$_SESSION['USER_PRENOM']  = $prenom ;
	echo'ok';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
