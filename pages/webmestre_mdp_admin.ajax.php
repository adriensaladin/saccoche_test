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

$admin_id = (isset($_POST['f_admin']))  ? clean_entier($_POST['f_admin']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Modifier le mdp d'un administrateur et afficher les identifiants au webmestre
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if($admin_id)
{
	// Informations sur l'admin : nom / prénom / login.
	$DB_ROW = DB_STRUCTURE_WEBMESTRE::DB_recuperer_admin_identite($admin_id);
	if(!count($DB_ROW))
	{
		exit('Erreur : administrateur introuvable !');
	}
	$admin_nom    = $DB_ROW['user_nom'];
	$admin_prenom = $DB_ROW['user_prenom'];
	$admin_login  = $DB_ROW['user_login'];
	// Générer un nouveau mdp de l'admin
	$admin_password = fabriquer_mdp();
	DB_STRUCTURE_WEBMESTRE::DB_modifier_admin_mdp($admin_id,crypter_mdp($admin_password));
	// On affiche le retour
	echo'<ul class="puce">';
	echo'<li>Le mot de passe administrateur de <em>'.html($admin_prenom.' '.$admin_nom).'</em> vient d\'être réinitialisé.</li>';
	echo'<li>nom d\'utilisateur " '.$admin_login.' "</li>';
	echo'<li>mot de passe " '.$admin_password.' "</li>';
	echo'<li>Pour se connecter comme administrateur, utiliser l\'adresse <a href="'.SERVEUR_ADRESSE.'">'.SERVEUR_ADRESSE.'</a></li>';
	echo'</ul><p />';
}
else
{
	echo'Erreur avec les données transmises !';
}
?>
