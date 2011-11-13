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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$etape  = (isset($_POST['etape']))    ? clean_entier($_POST['etape'])   : 0;

$top_depart = microtime(TRUE);

$dossier_dump   = './__tmp/dump-base/';
$dossier_temp   = './__tmp/dump-base/'.$_SESSION['BASE'].'/';
$dossier_import = './__tmp/import/';

require('./_inc/fonction_dump.php');

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Sauvegarder la base
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='sauvegarder')
{
	// Nombre d'enregistrements à récupérer par "SELECT * FROM table_nom" et donc ensuite inséré par "INSERT INTO table_nom VALUES (...),(...),(...)"
	$nb_lignes_maxi = determiner_nombre_lignes_maxi_par_paquet();
	// Créer ou vider le dossier temporaire
	Creer_ou_Vider_Dossier($dossier_temp);
	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Sauvegarde de la base en cours.');
	// Remplir le dossier temporaire avec les fichiers de svg des tables
	sauvegarder_tables_base_etablissement($dossier_temp,$nb_lignes_maxi);
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Zipper les fichiers de svg
	$fichier_zip_nom = 'dump_SACoche_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.zip';
	zipper_fichiers_sauvegarde($dossier_temp,$dossier_dump,$fichier_zip_nom);
	// Supprimer le dossier temporaire
	Supprimer_Dossier($dossier_temp);
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Sauvegarde de la base réalisée en '.$duree.'s.</label></li>';
	echo'<li><a class="lien_ext" href="'.$dossier_dump.$fichier_zip_nom.'"><span class="file file_zip">Récupérez le fichier de sauvegarde au format ZIP.</span></a></li>';
	echo'<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Uploader et dezipper / vérifier un fichier à restaurer
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

elseif($action=='uploader')
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('<li><label class="alerte">Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload().'</label></li>');
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if($extension!='zip')
	{
		exit('<li><label class="alerte">Erreur : l\'extension du fichier transmis est incorrecte !</label></li>');
	}
	$fichier_upload_nom = 'dump_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.zip';
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_upload_nom))
	{
		exit('<li><label class="alerte">Erreur : le fichier n\'a pas pu être enregistré sur le serveur.</label></li>');
	}
	// Créer ou vider le dossier temporaire
	Creer_ou_Vider_Dossier($dossier_temp);
	// Dezipper dans le dossier temporaire
	$code_erreur = unzip( $dossier_import.$fichier_upload_nom , $dossier_temp , TRUE /*use_ZipArchive*/ );
	if($code_erreur)
	{
		require('./_inc/tableau_zip_error.php');
		exit('<li><label class="alerte">Erreur : votre archive ZIP n\'a pas pu être ouverte ('.$code_erreur.$tab_zip_error[$code_erreur].') !</label></li>');
	}
	unlink($dossier_import.$fichier_upload_nom);
	// Vérifier le contenu : noms des fichiers
	$fichier_taille_maximale = verifier_dossier_decompression_sauvegarde($dossier_temp);
	if(!$fichier_taille_maximale)
	{
		Supprimer_Dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP ne semble pas contenir les fichiers d\'une sauvegarde de la base effectuée par SACoche !</label></li>');
	}
	// Vérifier le contenu : taille des requêtes
	if( !verifier_taille_requetes($fichier_taille_maximale) )
	{
		Supprimer_Dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient au moins un fichier dont la taille dépasse la limitation <em>max_allowed_packet</em> de MySQL !</label></li>');
	}
	// Vérifier le contenu : version de la base compatible avec la version logicielle
	if( version_base_fichier_svg($dossier_temp) > VERSION_BASE )
	{
		Supprimer_Dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient une sauvegarde plus récente que celle supportée par cette installation ! Le webmestre doit préalablement mettre à jour le programme...</label></li>');
	}
	// Afficher le retour
	echo'<li><label class="valide">Contenu du fichier récupéré avec succès.</label></li>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Restaurer la base
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( ($action=='restaurer') && $etape )
{
	if($etape==1)
	{
		// Bloquer l'application
		bloquer_application('automate',$_SESSION['BASE'],'Restauration de la base en cours.');
	}
	// Restaurer des fichiers de svg et mettre la base à jour si besoin.
	$texte_etape = restaurer_tables_base_etablissement($dossier_temp,$etape);
	if(strpos($texte_etape,'terminée'))
	{
		// Débloquer l'application
		debloquer_application('automate',$_SESSION['BASE']);
		// Supprimer le dossier temporaire
		Supprimer_Dossier($dossier_temp);
	}
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">'.$texte_etape.' en '.$duree.'s.</label></li>';
	exit();
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
