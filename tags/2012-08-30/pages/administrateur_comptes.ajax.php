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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// Peut être appelé depuis toutes les pages de gestion des utilisateurs ( élèves, parents, profs, directeurs ) sauf les admins

$action     = (isset($_POST['f_action']))     ? $_POST['f_action']     : '';
$listing_id = (isset($_POST['f_listing_id'])) ? $_POST['f_listing_id'] : '';

$tab_user_id = array_filter( Clean::map_entier( explode(',',$listing_id) ) , 'positif' );
$nb_user = count($tab_user_id);

if( !$nb_user )
{
	exit('Aucun compte récupéré !');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retirer des comptes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='retirer')
{
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_users_statut($tab_user_id,FALSE);
	exit('ok,'.implode(',',$tab_user_id));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Réintégrer des comptes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='reintegrer')
{
	DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_users_statut($tab_user_id,TRUE);
	exit('ok,'.implode(',',$tab_user_id));
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Supprimer des comptes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='supprimer')
{
	// Récupérer le profil des utilisateurs indiqués, vérifier qu'ils sont déjà sortis et qu'on y a pas glissé l'id d'un administrateur
	$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( implode(',',$tab_user_id) , 'user_id,user_profil,user_sortie_date' , '' /*avec_info*/ );
	$tab_user_id = array();
	foreach($DB_TAB as $DB_ROW)
	{
		if( ($DB_ROW['user_sortie_date']<=TODAY_MYSQL) && ($DB_ROW['user_profil']!='administrateur') )
		{
			DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_utilisateur( $DB_ROW['user_id'] , $DB_ROW['user_profil'] );
			$tab_user_id[] = $DB_ROW['user_id'];
			// Log de l'action
			SACocheLog::ajouter('Suppression d\'un utilisateur ('.$DB_ROW['user_profil'].' '.$DB_ROW['user_id'].').');
		}
	}
	$retour = (count($tab_user_id)) ? 'ok,'.implode(',',$tab_user_id) : 'Aucun compte indiqué n\'est supprimable !' ;
	exit($retour);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
