<?php
/**
 * @version $Id: directeur.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Gérer les directeurs";
?>

<p class="hc">
	<span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_directeurs">DOC : Gestion des directeurs</a></span>
</p>

<form action="">
	<table class="form">
		<thead>
			<tr>
				<th>Id. ENT</th>
				<th>Id. GEPI</th>
				<th>n° Sconet</th>
				<th>Référence</th>
				<th>Nom</th>
				<th>Prénom</th>
				<th>Login</th>
				<th>Mot de passe</th>
				<th class="nu"><q class="ajouter" title="Ajouter un directeur."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les directeurs
			$DB_SQL = 'SELECT * FROM livret_user ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut ';
			$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'directeur',':statut'=>1);
			$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['livret_user_id'].'">';
				echo	'<td>'.html($DB_ROW['livret_user_id_ent']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_id_gepi']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_num_sconet']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_reference']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_prenom']).'</td>';
				echo	'<td>'.html($DB_ROW['livret_user_login']).'</td>';
				echo	'<td class="i">champ crypté</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier ce directeur."></q>';
				echo		'<q class="desactiver" title="Enlever ce directeur."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<script type="text/javascript">var select_login="<?php echo $_SESSION['MODELE_PROF']; ?>";</script>
