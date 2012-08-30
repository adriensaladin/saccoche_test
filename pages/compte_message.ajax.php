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
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && (!in_array($_POST['f_action'],array('afficher_users','afficher_destinataires'))) ) {exit('Action désactivée pour la démo...');}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Récupération des valeurs transmises
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$action          = (isset($_POST['f_action']))          ? clean_texte($_POST['f_action'])          : '';
$profil          = (isset($_POST['f_profil']))          ? clean_texte($_POST['f_profil'])          : ''; // administrateur professeur directeur eleve parent
$groupe_type     = (isset($_POST['f_groupe_type']))     ? clean_texte($_POST['f_groupe_type'])     : ''; // d n c g b
$groupe_id       = (isset($_POST['f_groupe_id']))       ? clean_entier($_POST['f_groupe_id'])      : 0;
$message_id      = (isset($_POST['f_id']))              ? clean_entier($_POST['f_id'])             : 0;
$date_debut_fr   = (isset($_POST['f_debut_date']))      ? clean_texte($_POST['f_debut_date'])      : '';
$date_fin_fr     = (isset($_POST['f_fin_date']))        ? clean_texte($_POST['f_fin_date'])        : '';
$message_contenu = (isset($_POST['f_message_contenu'])) ? clean_texte($_POST['f_message_contenu']) : '' ;

// Contrôler la liste des destinataires transmis
$tab_destinataires = (isset($_POST['f_destinataires_liste'])) ? explode('_',$_POST['f_destinataires_liste']) : array() ;
$tab_destinataires = array_map('clean_entier',$tab_destinataires);
$tab_destinataires = array_filter($tab_destinataires,'positif');
$nb_destinataires  = count($tab_destinataires);

// Contrôler la liste des destinataires à récupérer
$tab_ids  = (isset($_POST['f_ids'])) ? explode('_',$_POST['f_ids']) : array() ;
$tab_ids  = array_map('clean_entier',$tab_ids);
$tab_ids  = array_filter($tab_ids,'positif');
$nb_ids   = count($tab_ids);

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Afficher une liste d'utilisateurs ou de destinataires
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

if( ($action=='afficher_users') && $profil && $groupe_id && isset($tab_types[$groupe_type]) )
{
	$champs = ($profil!='parent') ? 'CONCAT(user_nom," ",user_prenom) AS texte , user_id AS valeur' : 'CONCAT(parent.user_nom," ",parent.user_prenom," (",enfant.user_nom," ",enfant.user_prenom,")") AS texte , parent.user_id AS valeur' ;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $profil /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , $champs ) ;
	exit( Formulaire::afficher_select($DB_TAB , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non') );
}

if( ($action=='afficher_destinataires') && $nb_ids )
{
	$champs = ($profil!='parent') ? 'CONCAT(user_nom," ",user_prenom) AS texte , user_id AS valeur' : 'CONCAT(parent.user_nom," ",parent.user_prenom," (",enfant.user_nom," ",enfant.user_prenom,")") AS texte , parent.user_id AS valeur' ;
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( implode(',',$tab_ids) , 'user_id AS valeur, CONCAT(user_nom," ",user_prenom) AS texte' , '' /*avec_info*/ );
	exit( Formulaire::afficher_select($DB_TAB , $select_nom=false , $option_first='non' , $selection=true , $optgroup='non') );
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Ajouter un nouveau message
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $date_debut_fr && $date_fin_fr && $message_contenu && $nb_destinataires )
{
	$date_debut_mysql  = convert_date_french_to_mysql($date_debut_fr);
	$date_fin_mysql    = convert_date_french_to_mysql($date_fin_fr);
	if($date_fin_mysql<$date_debut_mysql)
	{
		exit('Date de fin antérieure à la date de début !');
	}
	$message_id = DB_STRUCTURE_COMMUN::DB_ajouter_message($_SESSION['USER_ID'],$date_debut_mysql,$date_fin_mysql,$message_contenu,$tab_destinataires);
	// Afficher le retour
	$destinataires_nombre = ($nb_destinataires>1) ? $nb_destinataires.' destinataires' : $nb_destinataires.' destinataire' ;
	echo'<tr id="id_'.$message_id.'" class="new">';
	echo	'<td><i>'.$date_debut_mysql.'</i>'.$date_debut_fr.'</td>';
	echo	'<td><i>'.$date_fin_mysql.'</i>'.$date_fin_fr.'</td>';
	echo	'<td>'.$destinataires_nombre.'</td>';
	echo	'<td>'.html(mb_substr($message_contenu,0,30)).'</td>';
	echo	'<td class="nu">';
	echo		'<q class="modifier" title="Modifier ce message."></q>';
	echo		'<q class="supprimer" title="Supprimer ce message."></q>';
	echo	'</td>';
	echo'</tr>';
	echo'<SCRIPT>';
	echo'tab_destinataires['.$message_id.']="'.implode('_',$tab_destinataires).'";';
	echo'tab_msg_contenus['.$message_id.']="'.str_replace(array("\r\n","\r","\n"),array('\r\n','\r','\n'),html($message_contenu)).'";';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Modifier un message existant
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $message_id && $date_debut_fr && $date_fin_fr && $message_contenu && $nb_destinataires )
{
	$date_debut_mysql  = convert_date_french_to_mysql($date_debut_fr);
	$date_fin_mysql    = convert_date_french_to_mysql($date_fin_fr);
	if($date_fin_mysql<$date_debut_mysql)
	{
		exit('Date de fin antérieure à la date de début !');
	}
	DB_STRUCTURE_COMMUN::DB_modifier_message($message_id,$_SESSION['USER_ID'],$date_debut_mysql,$date_fin_mysql,$message_contenu,$tab_destinataires);
	// Afficher le retour
	$destinataires_nombre = ($nb_destinataires>1) ? $nb_destinataires.' destinataires' : $nb_destinataires.' destinataire' ;
	echo'<td><i>'.$date_debut_mysql.'</i>'.$date_debut_fr.'</td>';
	echo'<td><i>'.$date_fin_mysql.'</i>'.$date_fin_fr.'</td>';
	echo'<td>'.$destinataires_nombre.'</td>';
	echo'<td>'.html(mb_substr($message_contenu,0,30)).'</td>';
	echo'<td class="nu">';
	echo	'<q class="modifier" title="Modifier ce message."></q>';
	echo	'<q class="supprimer" title="Supprimer ce message."></q>';
	echo'</td>';
	echo'<SCRIPT>';
	echo'tab_destinataires['.$message_id.']="'.implode('_',$tab_destinataires).'";';
	echo'tab_msg_contenus['.$message_id.']="'.str_replace(array("\r\n","\r","\n"),array('\r\n','\r','\n'),html($message_contenu)).'";';
	exit();
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Supprimer un message existant
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $message_id )
{
	DB_STRUCTURE_COMMUN::DB_supprimer_message($message_id,$_SESSION['USER_ID']);
	// Afficher le retour
	exit('<td>ok</td>');
}

//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là !
//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
