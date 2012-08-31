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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent que les directeurs.

class DB_STRUCTURE_DIRECTEUR extends DB
{

/**
 * compter_saisies_prof_classe
 *
 * @param void
 * @return array
 */
public function DB_compter_saisies_prof_classe()
{
	$DB_SQL = 'SELECT CONCAT(user_nom," ",user_prenom) AS professeur, groupe_nom, COUNT(saisie_note) AS nombre ';
	$DB_SQL.= 'FROM sacoche_user ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir ON sacoche_user.user_id=sacoche_devoir.prof_id ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_groupe USING (groupe_id) ';
	$DB_SQL.= 'WHERE user_profil=:user_profil AND groupe_type=:groupe_type ';
	$DB_SQL.= 'GROUP BY user_id,groupe_id';
	$DB_VAR = array(':user_profil'=>'professeur',':groupe_type'=>'classe');
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

}
?>