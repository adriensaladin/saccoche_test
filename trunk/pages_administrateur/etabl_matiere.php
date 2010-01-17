<?php
/**
 * @version $Id: etabl_matiere.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Choix des matières";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_matieres">DOC : Gestion des matières</a></span></p>

<hr />

<h2>Matières partagées</h2>

<form id="partage" action="">
	<table class="form">
		<thead>
			<tr><th class="nu"></th><th>Référence</th><th>Nom complet</th></tr>
		</thead>
		<tbody>
			<?php
			// Cases à cocher
			$tab_check = explode(',',$_SESSION['MATIERES']);
			// Lister les matières partagées
			$DB_TAB = DB_lister_matieres_partagees_SACoche();
			foreach($DB_TAB as $key => $DB_ROW)
			{
				// Afficher une ligne du tableau
				$checked  = (in_array($DB_ROW['livret_matiere_id'],$tab_check)) ? ' checked="checked"' : '' ;
				$disabled = ($DB_ROW['livret_matiere_transversal']) ? ' disabled="disabled"' : '' ;
				$tr_class = ($DB_ROW['livret_matiere_transversal']) ? ' class="new"' : '' ;
				$td_label = ($DB_ROW['livret_matiere_transversal']) ? '' : ' class="label"' ;
				$indic    = ($DB_ROW['livret_matiere_transversal']) ? ' <b>[obligatoire]</b>' : '' ;
				echo'<tr'.$tr_class.'>';
				echo	'<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['livret_matiere_id'].'"'.$disabled.$checked.' /></td>';
				echo	'<td'.$td_label.'>'.html($DB_ROW['livret_matiere_ref']).'</td>';
				echo	'<td'.$td_label.'>'.html($DB_ROW['livret_matiere_nom']).$indic.'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
	<p>
		<input id="f_submit_partage" type="button" value="Valider ce choix de matières." /> <label id="ajax_msg_partage">&nbsp;</label>
	</p>
</form>

<hr />

<h2>Matières spécifiques</h2>

<form id="perso" action="">
	<table class="form">
		<thead>
			<tr>
				<th>Référence</th>
				<th>Nom complet</th>
				<th class="nu"><q class="ajouter" title="Ajouter une matière."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les matières spécifiques
			$DB_TAB = DB_lister_matieres_specifiques($_SESSION['STRUCTURE_ID']);
			foreach($DB_TAB as $key => $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['livret_matiere_id'].'">';
				echo	'<td>'.html($DB_ROW['livret_matiere_ref']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_matiere_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cette matière."></q>';
				echo		'<q class="supprimer" title="Supprimer cette matière."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<p>&nbsp;</p>
