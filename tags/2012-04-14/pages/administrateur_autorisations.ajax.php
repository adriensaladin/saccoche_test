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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Récupération des informations transmises
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$f_objet   = (isset($_POST['f_objet']))   ? clean_texte($_POST['f_objet'])   : '';
$f_profils = (isset($_POST['f_profils'])) ? clean_texte($_POST['f_profils']) : 'erreur';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Vérification des informations transmises
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_objet_profils = array();
$tab_objet_profils['droit_validation_entree']  = array('directeur','professeur','profprincipal','aucunprof');
$tab_objet_profils['droit_validation_pilier']  = array('directeur','professeur','profprincipal','aucunprof');
$tab_objet_profils['droit_annulation_pilier']  = array('directeur','professeur','profprincipal','aucunprof');
$tab_objet_profils['droit_gerer_referentiel']  = array('professeur','profcoordonnateur','aucunprof');
$tab_objet_profils['droit_gerer_ressource']    = array('professeur','profcoordonnateur','aucunprof');
$tab_objet_profils['droit_voir_referentiels']  = array('directeur','professeur','parent','eleve');
$tab_objet_profils['droit_voir_grilles_items'] = array('directeur','professeur','parent','eleve');
$tab_objet_profils['droit_voir_score_bilan']   = array('directeur','professeur','parent','eleve');
$tab_objet_profils['droit_voir_algorithme']    = array('directeur','professeur','parent','eleve');
$tab_objet_profils['droit_modifier_mdp']       = array('directeur','professeur','parent','eleve');
$tab_objet_profils['droit_bilan_moyenne_score']      = array('parent','eleve');
$tab_objet_profils['droit_bilan_pourcentage_acquis'] = array('parent','eleve');
$tab_objet_profils['droit_bilan_note_sur_vingt']     = array('parent','eleve');
$tab_objet_profils['droit_socle_acces']              = array('parent','eleve');
$tab_objet_profils['droit_socle_pourcentage_acquis'] = array('parent','eleve');
$tab_objet_profils['droit_socle_etat_validation']    = array('parent','eleve');
$tab_objet_profils['droit_bulletin_appreciation_generale'] = array('directeur','professeur','profprincipal','aucunprof');
$tab_objet_profils['droit_bulletin_impression_pdf']        = array('directeur','professeur','profprincipal','aucunprof');

$test_objet_prof = in_array($f_objet,array('droit_validation_entree','droit_validation_pilier','droit_annulation_pilier','droit_bulletin_appreciation_generale','droit_bulletin_impression_pdf')) ? TRUE : FALSE ;

if(!isset($tab_objet_profils[$f_objet]))
{
	exit('Droit inconnu !');
}

if($f_profils=='')
{
	// Les profils peuvent être vides sauf certains paramètres devant contenir la chaine 'prof'
	$test_options = ($test_objet_prof) ? false : true ;
}
else
{
	$nettoyage = str_replace( $tab_objet_profils[$f_objet] , '*' , $f_profils );
	$nettoyage = str_replace( '*,' , '' , $nettoyage.',' );
	// Test supplémentaire : certains paramètres doivent contenir la chaine 'prof'
	$test_socle   = ( (!$test_objet_prof) || (strpos($f_profils,'prof')!==FALSE) ) ? TRUE : FALSE ;
	$test_options = ( ($nettoyage=='') && $test_socle ) ? TRUE : FALSE;
}
if(!$test_options)
{
	exit('Profils incohérents !');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Tout est ok : on applique la modification demandée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

DB_STRUCTURE_COMMUN::DB_modifier_parametres( array($f_objet=>$f_profils) );
// ne pas oublier de mettre aussi à jour la session
$_SESSION[strtoupper($f_objet)] = $f_profils;
exit('ok');

?>
