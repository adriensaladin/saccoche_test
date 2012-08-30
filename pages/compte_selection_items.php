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
$TITRE = "Gérer ses regroupements d'items";

require('./_inc/fonction_affichage_sections_communes.php');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__gestion_regroupements_items">DOC : Gestion des regroupements d'items.</a></span></li>
</ul>

<hr />

<form action="#" method="post">
	<table class="form hsort">
		<thead>
			<tr>
				<th>Nom</th>
				<th>Items</th>
				<th class="nu"><q class="ajouter" title="Ajouter un regroupement d'items."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$tab_listing_js = '';
			$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_selection_items($_SESSION['USER_ID']);
			if(count($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					$items_liste  = str_replace(',','_',mb_substr($DB_ROW['selection_item_liste'],1,-1));
					$items_nombre = (mb_substr_count($DB_ROW['selection_item_liste'],',')-1);
					$items_texte  = ($items_nombre>1) ? $items_nombre.' items' : '1 item' ;
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$DB_ROW['selection_item_id'].'">';
					echo	'<td>'.$DB_ROW['selection_item_nom'].'</td>';
					echo	'<td>'.$items_texte.'</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
					echo		'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
					echo	'</td>';
					echo'</tr>';
					// Pour js
					$tab_listing_js .= 'tab_items["'.$DB_ROW['selection_item_id'].'"]="'.$items_liste.'";';
				}
			}
			else
			{
				echo'<tr><td class="nu" colspan="3"></td></tr>';
			}
			?>
		</tbody>
	</table>
</form>

<script type="text/javascript">
	var tab_items = new Array();
	<?php echo $tab_listing_js ?>
</script>

<form action="#" method="post" id="zone_matieres_items" class="arbre_dynamique arbre_check hide">
	<div>Tout déployer / contracter : <a href="m1" class="all_extend"><img alt="m1" src="./_img/deploy_m1.gif" /></a> <a href="m2" class="all_extend"><img alt="m2" src="./_img/deploy_m2.gif" /></a> <a href="n1" class="all_extend"><img alt="n1" src="./_img/deploy_n1.gif" /></a> <a href="n2" class="all_extend"><img alt="n2" src="./_img/deploy_n2.gif" /></a> <a href="n3" class="all_extend"><img alt="n3" src="./_img/deploy_n3.gif" /></a></div>
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php
	// Affichage de la liste des items pour toutes les matières d'un professeur ou toutes les matières de l'établissement si directeur, sur tous les niveaux
	$user_id = ($_SESSION['USER_PROFIL']=='professeur') ? $_SESSION['USER_ID'] : 0 ;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($user_id,$matiere_id=0,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=false);
	echo afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=true,$reference=true,$aff_coef=false,$aff_cart=false,$aff_socle='texte',$aff_lien=false,$aff_input=true);
	?>
	<div><span class="tab"></span><button id="valider_compet" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_compet" type="button" class="annuler">Annuler / Retour</button></div>
</form>
