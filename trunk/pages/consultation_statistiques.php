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
$TITRE = "Nombre de saisies";
?>

<table id="bilan hsort">
	<thead>
		<tr>
			<th>Professeur</th>
			<th>Classe</th>
			<th>Saisies</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$DB_TAB = DB_STRUCTURE_DIRECTEUR::DB_compter_saisies_prof_classe();
		if(count($DB_TAB))
		{
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr>';
				echo	'<td>'.html($DB_ROW['professeur']).'</td>';
				echo	'<td>'.html($DB_ROW['groupe_nom']).'</td>';
				echo	'<td class="hc">'.$DB_ROW['nombre'].'</td>';
				echo'</tr>';
			}
		}
		else
		{
			echo'<tr><td colspan="3" class="hc">Aucune saisie effectuée...</td></tr>';
		}
		?>
	</tbody>
</table>

