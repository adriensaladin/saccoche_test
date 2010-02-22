<?php
/**
 * @version $Id: periode_gestion.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Gérer les périodes";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_periodes">DOC : Gestion des périodes</a></span>
</p>

<hr />

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Ordre</th>
				<th>Nom</th>
				<th class="nu"><q class="ajouter" title="Ajouter une période."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les périodes
			$DB_SQL = 'SELECT * FROM livret_periode ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
			$DB_SQL.= 'ORDER BY livret_periode_ordre ASC';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['livret_periode_id'].'">';
				echo	'<td>'.$DB_ROW['livret_periode_ordre'].'</td>';
				echo	'<td>'.html($DB_ROW['livret_periode_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cette période."></q>';
				echo		'<q class="dupliquer" title="Dupliquer cette période."></q>';
				echo		'<q class="supprimer" title="Supprimer cette période."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<p>&nbsp;</p>
