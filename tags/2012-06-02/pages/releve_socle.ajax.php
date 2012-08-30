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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$palier_id     = (isset($_POST['f_palier']))        ? clean_entier($_POST['f_palier'])    : 0;
$palier_nom    = (isset($_POST['f_palier_nom']))    ? clean_texte($_POST['f_palier_nom']) : '';
$only_presence = (isset($_POST['f_only_presence'])) ? 1                                   : 0;
$aff_socle_PA  = (isset($_POST['f_socle_PA']))      ? 1                                   : 0;
$aff_socle_EV  = (isset($_POST['f_socle_EV']))      ? 1                                   : 0;
$groupe_id     = (isset($_POST['f_groupe']))        ? clean_entier($_POST['f_groupe'])    : 0;
$groupe_nom    = (isset($_POST['f_groupe_nom']))    ? clean_texte($_POST['f_groupe_nom']) : '';
$mode          = (isset($_POST['f_mode']))          ? clean_texte($_POST['f_mode'])       : '';
$aff_coef      = (isset($_POST['f_coef']))          ? 1                                   : 0;
$aff_socle     = (isset($_POST['f_socle']))         ? 1                                   : 0;
$aff_lien      = (isset($_POST['f_lien']))          ? 1                                   : 0;
$couleur       = (isset($_POST['f_couleur']))       ? clean_texte($_POST['f_couleur'])    : '';
$legende       = (isset($_POST['f_legende']))       ? clean_texte($_POST['f_legende'])    : '';
$marge_min     = (isset($_POST['f_marge_min']))     ? clean_entier($_POST['f_marge_min']) : 0;
// Normalement ce sont des tableaux qui sont transmis, mais au cas où...
$tab_pilier_id  = (isset($_POST['f_pilier']))  ? ( (is_array($_POST['f_pilier']))  ? $_POST['f_pilier']  : explode(',',$_POST['f_pilier'])  ) : array() ;
$tab_eleve_id   = (isset($_POST['f_eleve']))   ? ( (is_array($_POST['f_eleve']))   ? $_POST['f_eleve']   : explode(',',$_POST['f_eleve'])   ) : array() ;
$tab_matiere_id = (isset($_POST['f_matiere'])) ? ( (is_array($_POST['f_matiere'])) ? $_POST['f_matiere'] : explode(',',$_POST['f_matiere']) ) : array() ;
$tab_pilier_id  = array_filter( array_map( 'clean_entier' , $tab_pilier_id  ) , 'positif' );
$tab_eleve_id   = array_filter( array_map( 'clean_entier' , $tab_eleve_id   ) , 'positif' );
$tab_matiere_id = array_filter( array_map( 'clean_entier' , $tab_matiere_id ) , 'positif' );

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	$aff_socle_PA = (mb_substr_count($_SESSION['DROIT_SOCLE_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$aff_socle_EV = (mb_substr_count($_SESSION['DROIT_SOCLE_ETAT_VALIDATION']   ,$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$only_presence = 0;
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$groupe_id    = $_SESSION['ELEVE_CLASSE_ID'];
	$tab_eleve_id = array($_SESSION['USER_ID']);
}

if( (!$palier_id) || (!$palier_nom) || (!count($tab_pilier_id)) || (!in_array($mode,array('auto','manuel'))) || !$couleur || !$legende || !$marge_min )
{
	exit('Erreur avec les données transmises !');
}

Formulaire::save_choix('releve_socle');

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

// Permet d'avoir des informations accessibles en cas d'erreur type « PHP Fatal error : Allowed memory size of ... bytes exhausted ».
// ajouter_log_PHP( 'Demande de bilan' /*log_objet*/ , serialize($_POST) /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , TRUE /*only_sesamath*/ );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$make_officiel = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;
$make_graph    = FALSE;

require('./_inc/code_socle_releve.php');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du résultat
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($affichage_direct)
{
	echo'<hr />';
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'</ul>';
	echo $releve_html;
}
else
{
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.$fichier_nom.'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.$fichier_nom.'"><span class="file file_htm">Explorer / Détailler (format <em>html</em>).</span></a></li>';
	echo'</ul>';
}

?>
