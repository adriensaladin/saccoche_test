<?php
/**
 * @version $Id: etabl_niveau.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Choix des niveaux";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_niveaux">DOC : Gestion des niveaux</a></span></p>

<hr />

<form id="niveau" action="">
	<table class="form">
		<thead>
			<tr><th class="nu"></th><th>Référence</th><th>Sigle Sconet</th><th>Nom complet</th></tr>
		</thead>
		<tbody>
			<?php
			$tab_check = explode(',',$_SESSION['NIVEAUX']);
			$DB_SQL = 'SELECT * FROM livret_niveau ';
			$DB_SQL.= 'WHERE livret_palier_id=0 ';
			$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL);
			foreach($DB_TAB as $key => $DB_ROW)
			{
				$checked = (in_array($DB_ROW['livret_niveau_id'],$tab_check)) ? ' checked="checked"' : '' ;
				echo'<tr>';
				echo'	<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['livret_niveau_id'].'"'.$checked.' /></td>';
				echo'	<td>'.html($DB_ROW['livret_niveau_ref']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_niveau_sigle']).'</td>';
				echo'	<td>'.html($DB_ROW['livret_niveau_nom']).'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
	<p>
		<input id="f_submit" type="button" value="Valider ce choix de niveaux." /> <label id="ajax_msg">&nbsp;</label>
	</p>
</form>
