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

// Fonction pour mettre à jour la base. Ce script est appelé :
// + par un administrateur après une restauration de la base (automatique)
// + par un utilisateur arrivant sur le portail d'identification de la structure si besoin il y a (automatique)

function maj_base($version_actuelle)
{
	if($version_actuelle=='2010-05-15')
	{
		$version_actuelle = '2010-06-03';
		// script pour migrer vers la version suivante
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_connexion_date DATETIME NOT NULL AFTER user_statut' );
		// y compris la mise à jour du champ "version_base" justement
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_actuelle.'" WHERE parametre_nom="version_base" LIMIT 1' );
	}
	if($version_actuelle=='2010-06-03')
	{
		$version_actuelle = '2010-06-12';
		// script pour migrer vers la version suivante
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD user_tentative_date DATETIME NOT NULL AFTER user_statut' );
		// y compris la mise à jour du champ "version_base" justement
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_actuelle.'" WHERE parametre_nom="version_base" LIMIT 1' );
	}
	if($version_actuelle=='2010-06-12')
	{
		$version_actuelle = '2010-07-04';
		// script pour migrer vers la version suivante
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD INDEX groupe_type (groupe_type)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_groupe ADD INDEX groupe_prof_id (groupe_prof_id)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_parametre ADD PRIMARY KEY (parametre_nom)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD UNIQUE (user_login)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_profil (user_profil)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_statut (user_statut)' );
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'ALTER TABLE sacoche_user ADD INDEX user_id_ent (user_id_ent)' );
		// y compris la mise à jour du champ "version_base" justement
		DB::query(SACOCHE_STRUCTURE_BD_NAME , 'UPDATE sacoche_parametre SET parametre_valeur="'.$version_actuelle.'" WHERE parametre_nom="version_base" LIMIT 1' );
	}
	// Log de l'action
	ajouter_log('Mise à jour automatique de la base '.SACOCHE_STRUCTURE_BD_NAME.'.');
}

?>