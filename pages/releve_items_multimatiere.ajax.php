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

$format         = 'multimatiere';
$aff_bilan_MS   = (isset($_POST['f_bilan_MS']))    ? 1                                    : 0;
$aff_bilan_PA   = (isset($_POST['f_bilan_PA']))    ? 1                                    : 0;
$aff_conv_sur20 = (isset($_POST['f_conv_sur20']))  ? 1                                    : 0;
$with_coef      = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
$matiere_id     = TRUE;
$matiere_nom    = '';
$groupe_id      = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_nom     = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';
$periode_id     = (isset($_POST['f_periode']))     ? clean_entier($_POST['f_periode'])     : 0;
$date_debut     = (isset($_POST['f_date_debut']))  ? clean_texte($_POST['f_date_debut'])   : '';
$date_fin       = (isset($_POST['f_date_fin']))    ? clean_texte($_POST['f_date_fin'])     : '';
$retroactif     = (isset($_POST['f_retroactif']))  ? clean_texte($_POST['f_retroactif'])   : '';
$only_socle     = (isset($_POST['f_restriction'])) ? 1                                    : 0;
$aff_coef       = (isset($_POST['f_coef']))        ? 1                                    : 0;
$aff_socle      = (isset($_POST['f_socle']))       ? 1                                    : 0;
$aff_lien       = (isset($_POST['f_lien']))        ? 1                                    : 0;
$aff_domaine    = (isset($_POST['f_domaine']))     ? 1                                     : 0;
$aff_theme      = (isset($_POST['f_theme']))       ? 1                                     : 0;
$orientation    = (isset($_POST['f_orientation'])) ? clean_texte($_POST['f_orientation'])  : '';
$couleur        = (isset($_POST['f_couleur']))     ? clean_texte($_POST['f_couleur'])      : '';
$legende        = (isset($_POST['f_legende']))     ? clean_texte($_POST['f_legende'])      : '';
$marge_min      = (isset($_POST['f_marge_min']))   ? clean_entier($_POST['f_marge_min'])   : 0;
$pages_nb       = (isset($_POST['f_pages_nb']))    ? clean_texte($_POST['f_pages_nb'])     : '';
$cases_nb       = (isset($_POST['f_cases_nb']))    ? clean_entier($_POST['f_cases_nb'])    : 0;
$cases_largeur  = (isset($_POST['f_cases_larg']))  ? clean_entier($_POST['f_cases_larg'])  : 0;

// Normalement c'est un tableau qui est transmis, mais au cas où...
$tab_eleve = (isset($_POST['f_eleve'])) ? ( (is_array($_POST['f_eleve'])) ? $_POST['f_eleve'] : explode(',',$_POST['f_eleve']) ) : array() ;
$tab_eleve = array_filter( array_map( 'clean_entier' , $tab_eleve ) , 'positif' );
$tab_type[] = 'individuel';
$type_individuel = 1;

// En cas de manipulation du formulaire (avec Firebug par exemple) ; on pourrait aussi vérifier pour un parent que c'est bien un de ses enfants...
if(in_array($_SESSION['USER_PROFIL'],array('parent','eleve')))
{
	$aff_bilan_MS   = (mb_substr_count($_SESSION['DROIT_BILAN_MOYENNE_SCORE']     ,$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$aff_bilan_PA   = (mb_substr_count($_SESSION['DROIT_BILAN_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$aff_conv_sur20 = (mb_substr_count($_SESSION['DROIT_BILAN_NOTE_SUR_VINGT']    ,$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$groupe_id  = $_SESSION['ELEVE_CLASSE_ID'];
	$groupe_nom = $_SESSION['ELEVE_CLASSE_NOM'];
	$tab_eleve  = array($_SESSION['USER_ID']);
}

$type_individuel = 1;
$type_synthese   = 0;
$type_bulletin   = 0;

$liste_eleve = implode(',',$tab_eleve);

if( !$orientation || !$couleur || !$legende || !$marge_min || !$pages_nb || !$cases_nb || !$cases_largeur || ( !$periode_id && (!$date_debut || !$date_fin) ) || !$retroactif || !$matiere_id || !$groupe_id || !$groupe_nom || !count($tab_eleve) || !count($tab_type) )
{
	exit('Erreur avec les données transmises !');
}

Formulaire::save_choix('items_multimatiere');

$marge_gauche = $marge_droite = $marge_haut = $marge_bas = $marge_min ;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$make_officiel = FALSE;
$make_action   = '';
$make_html     = TRUE;
$make_pdf      = TRUE;
$make_graph    = FALSE;

require('./_inc/code_items_releve.php');

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On retourne les résultats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($affichage_direct)
{
	echo'<hr />';
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'</ul>';
	echo $releve_HTML_individuel;
}
else
{
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf"><span class="file file_pdf">Archiver / Imprimer (format <em>pdf</em>).</span></a></li>';
	echo'<li><a class="lien_ext" href="./releve-html.php?fichier='.str_replace('<REPLACE>','individuel',$fichier_nom).'"><span class="file file_htm">Explorer / Manipuler (format <em>html</em>).</span></a></li>';
	echo'</ul>';
}

?>
