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

$action      = (isset($_POST['f_action']))      ? clean_texte($_POST['f_action'])      : '';
$profil      = (isset($_POST['f_profil']))      ? clean_texte($_POST['f_profil'])      : ''; // administrateur professeur directeur eleve parent
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : ''; // d n c g b
$groupe_id   = (isset($_POST['f_groupe_id']))   ? clean_entier($_POST['f_groupe_id'])  : 0;
$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

if( ($action=='afficher_users') && $profil && $groupe_id && isset($tab_types[$groupe_type]) )
{
	$champs = ($profil!='parent') ? 'CONCAT(user_nom," ",user_prenom) AS texte , user_id AS valeur' : 'CONCAT(parent.user_nom," ",parent.user_prenom," (",enfant.user_nom," ",enfant.user_prenom,")") AS texte , parent.user_id AS valeur' ;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $profil /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , $champs ) ;
	exit( Formulaire::afficher_select($DB_TAB , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non') );
}

$tab_ids  = (isset($_POST['f_ids'])) ? explode('_',$_POST['f_ids']) : array() ;
$tab_ids  = array_map('clean_entier',$tab_ids);
$tab_ids  = array_filter($tab_ids,'positif');
$nb_ids   = count($tab_ids);

if( ($action=='afficher_destinataires') && $nb_ids )
{
	$champs = ($profil!='parent') ? 'CONCAT(user_nom," ",user_prenom) AS texte , user_id AS valeur' : 'CONCAT(parent.user_nom," ",parent.user_prenom," (",enfant.user_nom," ",enfant.user_prenom,")") AS texte , parent.user_id AS valeur' ;
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( implode(',',$tab_ids) , 'user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte' , '' /*avec_info*/ );
	exit( Formulaire::afficher_select($DB_TAB , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non') );
}

?>
