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
$TITRE = "Adresses des parents";
$VERSION_JS_FILE += 0;
?>

<?php
// Récupérer d'éventuels paramètres pour restreindre l'affichage
$debut_nom    = (isset($_POST['f_debut_nom']))    ? clean_nom($_POST['f_debut_nom'])      : '' ;
$debut_prenom = (isset($_POST['f_debut_prenom'])) ? clean_prenom($_POST['f_debut_prenom']) : '' ;
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></p>

<form action="./index.php?page=administrateur_parent&amp;section=adresse" method="POST" id="form0">
	<div><label class="tab" for="f_debut_nom">Affichage restreint :</label>le nom commence par <input type="text" id="f_debut_nom" name="f_debut_nom" value="<?php echo html($debut_nom) ?>" size="5" /> le prénom commence par <input type="text" id="f_debut_prenom" name="f_debut_prenom" value="<?php echo html($debut_prenom) ?>" size="5" /> <button id="actualiser" type="submit"><img alt="" src="./_img/bouton/actualiser.png" /> Actualiser.</button></div>
</form>

<hr />

<form action="" id="form1">
	<table class="form t9">
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
			$DB_TAB = DB_STRUCTURE_lister_parents_actifs_avec_infos_enfants($with_adresse=TRUE,$debut_nom,$debut_prenom);
			if(count($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					$parent_id = ($DB_ROW['parent_id']) ? 'M' : 'A' ; // Indiquer si le parent a une adresse dans la base ou pas.
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$parent_id.$DB_ROW['user_id'].'">';
					echo	($DB_ROW['enfants_nombre']) ? '<td>'.$DB_ROW['enfants_nombre'].' <img alt="" src="./_img/bulle_aide.png" title="'.str_replace('§BR§','<br />',html($DB_ROW['enfants_liste'])).'" /></td>' : '<td>0 <img alt="" src="./_img/bulle_aide.png" title="Aucun lien de responsabilité !" /></td>' ;
					echo	'<td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</td>';
					echo	'<td><span>'.html($DB_ROW['adresse_ligne1']).'</span> ; <span>'.html($DB_ROW['adresse_ligne2']).'</span> ; <span>'.html($DB_ROW['adresse_ligne3']).'</span> ; <span>'.html($DB_ROW['adresse_ligne4']).'</span></td>';
					echo	'<td>'.html($DB_ROW['adresse_postal_code']).'</td>';
					echo	'<td>'.html($DB_ROW['adresse_postal_libelle']).'</td>';
					echo	'<td>'.html($DB_ROW['adresse_pays_nom']).'</td>';
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
<script type="text/javascript">var select_login="<?php echo $_SESSION['MODELE_PARENT']; ?>";</script>
