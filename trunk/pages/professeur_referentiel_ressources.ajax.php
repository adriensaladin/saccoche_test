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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='Voir_referentiel')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['action']))      ? clean_texte($_POST['action'])      : '';
$matiere_id  = (isset($_POST['matiere_id']))  ? clean_entier($_POST['matiere_id']) : 0;
$matiere_ref = (isset($_POST['matiere_ref'])) ? clean_texte($_POST['matiere_ref']) : '';
$niveau_id   = (isset($_POST['niveau_id']))   ? clean_entier($_POST['niveau_id'])  : 0;
$item_id     = (isset($_POST['item_id']))     ? clean_entier($_POST['item_id'])    : 0;
$item_nom    = (isset($_POST['item_nom']))    ? clean_texte($_POST['item_nom'])    : '';
$item_lien   = (isset($_POST['item_lien']))   ? clean_texte($_POST['item_lien'])   : '';
$objet       = (isset($_POST['page_mode']))   ? clean_texte($_POST['page_mode'])   : '';
$ressources  = (isset($_POST['ressources']))  ? clean_texte($_POST['ressources'])  : '';
$findme      = (isset($_POST['findme']))      ? clean_texte($_POST['findme'])      : '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher le référentiel d'une matière et d'un niveau
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Voir_referentiel') && $matiere_id && $niveau_id && $matiere_ref )
{
	// $matiere_ref trasmis maintenant car pas possible lors du AjaxUpload (moment où on en a besoin) ; du coup on le garde au chaud
	$_SESSION['tmp']['matiere_ref'] = $matiere_ref;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_socle=false,$only_item=false,$socle_nom=true);
	exit( afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_cart=false,$aff_socle=false,$aff_lien='image',$aff_input=false,$aff_id_li='n3') );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Renommer un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Enregistrer_lien') && $item_id )
{
	DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_lien_ressources($item_id,$item_lien);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Élaborer ou d'éditer sur le serveur communautaire une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Charger_ressources') && $item_id )
{
	exit( afficher_liens_ressources($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$item_id,$item_lien) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Enregistrer sur le serveur communautaire le contenu d'une page de liens pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Enregistrer_ressources') && $item_id && $item_nom && in_array($objet,array('page_create','page_update')) && $ressources )
{
	$tab_elements = array();
	$tab_ressources = explode('}¤{',$ressources);
	foreach($tab_ressources as $ressource)
	{
		if(strpos($ressource,']¤['))
		{
			list($lien_nom,$lien_url) = explode(']¤[',$ressource);
			$tab_elements[] = array( $lien_nom => $lien_url );
		}
		else
		{
			$tab_elements[] = $ressource;
		}
	}
	exit( fabriquer_liens_ressources($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$item_id,$item_nom,$objet,serialize($tab_elements)) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Rechercher sur le serveur communautaire à partir de mots clefs des liens existants de ressources pour travailler
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Rechercher_liens_ressources') && $item_id && $findme )
{
	exit( rechercher_liens_ressources($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$item_id,$findme) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Rechercher sur le serveur communautaire des documents ressources existants uploadés par l'établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='Rechercher_documents')
{
	exit( rechercher_documents($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY']) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Uploader une ressource sur le serveur communautaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

/*
Pour uploader une ressource directement sur le serveur communautaire, le fichier "professeur_referentiel_edition.php" devrait l'appeller dans une iframe (le bouton d'upload du formulaire devant se trouver sur ce serveur). Mais :
- une fois l'upload effectué, pour faire remonter l'info au serveur où est installé SACoche, se pose un souci de cross-domain javascript.
- il faudrait reproduire côté serveur communautaire tout un environnement (js & css) semblable à celui du serveur d'installation de SACoche
Finalement, j'ai opté pour le plus simple même si ce n'est pas le plus économe : uploadé sur le serveur SACoche puis transférer vers le serveur communautaire avec url_get_contents().
Ca va qu'une limite de 500Ko est imposée...
*/

if( ($action=='Uploader_document') && isset($_FILES['userfile']) )
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur  = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('Problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload());
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if(in_array( $extension , array('bat','com','exe','php','zip') ))
	{
		exit('L\'extension du fichier transmis est interdite !');
	}
	if($fnom_transmis{0}=='.')
	{
		exit('Le nom du fichier ne doit pas commencer par un point !');
	}
	if($ftaille>500000)
	{
		exit('Le fichier dépasse les 500Ko autorisés pour ce service !');
	}
	exit( uploader_ressource( $_SESSION['SESAMATH_ID'] , $_SESSION['SESAMATH_KEY'] , $_SESSION['tmp']['matiere_ref'] , clean_fichier($fnom_transmis) , file_get_contents($fnom_serveur) ) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
