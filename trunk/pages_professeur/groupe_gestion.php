<?php
/**
 * @version $Id: groupe_gestion.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gérer les groupes de besoin";
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_groupes_besoin">DOC : Gestion des groupes de besoin.</a></span></li>
	<li><span class="danger">Un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être supprimé (sinon vous n'aurez plus accès à certaines saisies) !</span></li>
</ul>

<hr />

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Niveau</th>
				<th>Nom</th>
				<th class="nu"><q class="ajouter" title="Ajouter un groupe de besoin."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les groupes de besoin du prof
			$DB_SQL = 'SELECT livret_groupe_id, livret_groupe_nom, livret_niveau_ordre, livret_niveau_nom FROM livret_groupe ';
			$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_prof_id=:user_id AND livret_groupe_type=:type ';
			$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$_SESSION['USER_ID'],':type'=>'besoin');
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			foreach($DB_TAB as $key => $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['livret_groupe_id'].'">';
				echo	'<td><i>'.sprintf("%02u",$DB_ROW['livret_niveau_ordre']).'</i>'.html($DB_ROW['livret_niveau_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_groupe_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
				echo		'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<?php
$select_niveau = '<option value=""></option>';
$tab_niveau_ordre_js = 'var tab_niveau_ordre = new Array();';

if($_SESSION['NIVEAUX'])
{
	$DB_SQL = 'SELECT livret_niveau_id, livret_niveau_nom, livret_niveau_ordre FROM livret_niveau ';
	$DB_SQL.= 'WHERE livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
	foreach($DB_TAB as $key => $DB_ROW)
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
