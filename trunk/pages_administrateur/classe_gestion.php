<?php
/**
 * @version $Id: classe_gestion.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gérer les classes";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_classes">DOC : Gestion des classes</a></span>
</p>

<hr />

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Niveau</th>
				<th>Référence</th>
				<th>Nom complet</th>
				<th class="nu"><q class="ajouter" lang="Ajouter" title="Ajouter une classe."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les classes
			$DB_SQL = 'SELECT * FROM livret_groupe ';
			$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
			$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_ref ASC';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':type'=>'classe');
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['livret_groupe_id'].'">';
				echo	'<td><i>'.sprintf("%02u",$DB_ROW['livret_niveau_ordre']).'</i>'.html($DB_ROW['livret_niveau_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_groupe_ref']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_groupe_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cette classe."></q>';
				echo		'<q class="supprimer" title="Supprimer cette classe."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<?php
$tab_niveau_ordre_js = 'var tab_niveau_ordre = new Array();';
$select_niveau = '<option value=""></option>';

if($_SESSION['NIVEAUX'])
{
	$DB_SQL = 'SELECT * FROM livret_niveau ';
	$DB_SQL.= 'WHERE livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
	foreach($DB_TAB as $DB_ROW)
	{
		$select_niveau .= '<option value="'.$DB_ROW['livret_niveau_id'].'">'.html($DB_ROW['livret_niveau_nom']).'</option>';
		$tab_niveau_ordre_js .= 'tab_niveau_ordre["'.html($DB_ROW['livret_niveau_nom']).'"]="'.sprintf("%02u",$DB_ROW['livret_niveau_ordre']).'";';
	}
}
else
{
	$select_niveau .= '<option value="" disabled="disabled">Aucun niveau n\'est rattaché à l\'établissement !</option>';
}
?>

<script type="text/javascript">
	// <![CDATA[
	var select_niveau="<?php echo str_replace('"','\"',$select_niveau); ?>";
	// ]]>
	<?php echo $tab_niveau_ordre_js ?>
</script>
