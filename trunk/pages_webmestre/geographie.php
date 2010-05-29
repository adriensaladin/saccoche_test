<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Zones géographiques";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__zones_geographiques">DOC : Zones géographiques</a></span>
</p>

<hr />

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Identifiant</th>
				<th>Ordre</th>
				<th>Nom</th>
				<th class="nu"><q class="ajouter" title="Ajouter une zone."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les zones
			$DB_TAB = DB_lister_zones();
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['geo_id'].'">';
				echo	'<td>'.$DB_ROW['geo_id'].'</td>';
				echo	'<td>'.$DB_ROW['geo_ordre'].'</td>';
				echo	'<td>'.html($DB_ROW['geo_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cette zone."></q>';
				echo		'<q class="dupliquer" title="Dupliquer cette zone."></q>';
				// La zone d'id 1 ne peut être supprimée, c'est la zone par défaut.
				echo ($DB_ROW['geo_id']!=1) ? '<q class="supprimer" title="Supprimer cette zone."></q>' : '<q class="supprimer_non" title="La zone par défaut ne peut pas être supprimée."></q>' ;
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<p>&nbsp;</p>
