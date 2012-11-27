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
$TITRE = "Gérer les professeurs";

// Récupérer d'éventuels paramètres pour restreindre l'affichage
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$statut = (isset($_POST['f_statut'])) ? Clean::entier($_POST['f_statut']) : 1 ;
// Construire et personnaliser le formulaire pour restreindre l'affichage
$select_f_statuts = Form::afficher_select(Form::$tab_select_statut , $select_nom='f_statut' , $option_first='non' , $selection=$statut , $optgroup='non');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_professeurs">DOC : Gestion des professeurs</a></span></p>

<form action="./index.php?page=administrateur_professeur&amp;section=gestion" method="post" id="form0">
	<div><label class="tab" for="f_statut">Statut :</label><?php echo $select_f_statuts ?></div>
</form>

<form action="#" method="post" id="form1">
	<hr />
	<table class="form t9 hsort">
		<thead>
			<tr>
				<th class="nu"><input name="leurre" type="image" alt="leurre" src="./_img/auto.gif" /><input id="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /><br /><input id="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>
				<th>Id. ENT</th>
				<th>Id. GEPI</th>
				<th>Id Sconet</th>
				<th>Référence</th>
				<th>Nom</th>
				<th>Prénom</th>
				<th>Login</th>
				<th>Mot de passe</th>
				<th>Date sortie</th>
				<th class="nu"><q class="ajouter" title="Ajouter un professeur."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les professeurs
			$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'professeur' , $statut , 'user_id,user_id_ent,user_id_gepi,user_sconet_id,user_reference,user_nom,user_prenom,user_login,user_sortie_date' /*liste_champs*/ , FALSE /*with_classe*/ );
			if(!empty($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					// Formater la date (dont on ne garde que le jour)
					$date_mysql  = $DB_ROW['user_sortie_date'];
					$date_affich = ($date_mysql!=SORTIE_DEFAUT_MYSQL) ? convert_date_mysql_to_french($date_mysql) : '-' ;
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$DB_ROW['user_id'].'">';
					echo	'<td class="nu"><input type="checkbox" name="f_ids" value="'.$DB_ROW['user_id'].'" /></td>';
					echo	'<td class="label">'.html($DB_ROW['user_id_ent']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_id_gepi']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_sconet_id']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_reference']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_nom']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_prenom']).'</td>';
					echo	'<td class="label">'.html($DB_ROW['user_login']).'</td>';
					echo	'<td class="label i">champ crypté</td>';
					echo	'<td class="label"><i>'.$date_mysql.'</i>'.$date_affich.'</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier ce professeur."></q>';
					echo	'</td>';
					echo'</tr>';
				}
			}
			?>
		</tbody>
	</table>
	<div id="zone_actions" style="margin-left:3em">
		<div class="p"><span class="u">Pour les utilisateurs cochés :</span> <input id="listing_ids" name="listing_ids" type="hidden" value="" /><label id="ajax_msg1">&nbsp;</label></div>
		<button id="retirer" type="button" class="user_desactiver">Retirer</button> (date de sortie au <?php echo TODAY_FR ?>).<br />
		<button id="reintegrer" type="button" class="user_ajouter">Réintégrer</button> (retrait de la date de sortie).<br />
		<button id="supprimer" type="button" class="supprimer">Supprimer</button> sans attendre 3 ans (uniquement si déjà sortis).
	</div>
</form>

<script type="text/javascript">
	var input_date = "<?php echo TODAY_FR ?>";
	var date_mysql = "<?php echo TODAY_MYSQL ?>";
	var select_login="<?php echo $_SESSION['MODELE_PROFESSEUR']; ?>";
	var mdp_longueur_mini=<?php echo $_SESSION['MDP_LONGUEUR_MINI'] ?>;
</script>
