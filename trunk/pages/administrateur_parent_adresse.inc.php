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
if(!isset($afficher))   {exit('Ce fichier ne peut être appelé directement !');}
?>

<form action="#" method="post" id="form1">
	<table class="form t9 hsort">
		<thead>
			<tr>
				<th>Resp</th>
				<th>Nom Prénom</th>
				<th>Adresse (4 lignes)</th>
				<th>C.P.</th>
				<th>Commune</th>
				<th>Pays</th>
				<th class="nu"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les parents
			$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_avec_infos_enfants( TRUE /*with_adresse*/ , TRUE /*statut*/ , $debut_nom , $debut_prenom );
			if(count($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					$parent_id = ($DB_ROW['parent_id']) ? 'M' : 'A' ; // Indiquer si le parent a une adresse dans la base ou pas.
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$parent_id.$DB_ROW['user_id'].'">';
					echo	($DB_ROW['enfants_nombre']) ? '<td>'.$DB_ROW['enfants_nombre'].' <img alt="" src="./_img/bulle_aide.png" title="'.str_replace('§BR§','<br />',To::html($DB_ROW['enfants_liste'])).'" /></td>' : '<td>0 <img alt="" src="./_img/bulle_aide.png" title="Aucun lien de responsabilité !" /></td>' ;
					echo	'<td>'.To::html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</td>';
					echo	'<td><span>'.To::html($DB_ROW['adresse_ligne1']).'</span> ; <span>'.To::html($DB_ROW['adresse_ligne2']).'</span> ; <span>'.To::html($DB_ROW['adresse_ligne3']).'</span> ; <span>'.To::html($DB_ROW['adresse_ligne4']).'</span></td>';
					echo	'<td>'.To::html($DB_ROW['adresse_postal_code']).'</td>';
					echo	'<td>'.To::html($DB_ROW['adresse_postal_libelle']).'</td>';
					echo	'<td>'.To::html($DB_ROW['adresse_pays_nom']).'</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier ce parent."></q>';
					echo	'</td>';
					echo'</tr>';
				}
			}
			?>
		</tbody>
	</table>
</form>
<div id="temp_td" class="hide"></div>
