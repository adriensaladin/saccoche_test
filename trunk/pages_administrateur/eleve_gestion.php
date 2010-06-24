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
$TITRE = "Gérer les élèves";
?>

<?php
// Récupérer d'éventuels paramètres pour restreindre l'affichage
$groupe      = (isset($_POST['f_groupes'])) ? clean_texte($_POST['f_groupes']) : 'd2';
$groupe_type = clean_texte( substr($groupe,0,1) );
$groupe_id   = clean_entier( substr($groupe,1) );
// Construire et personnaliser le select pour restreindre à une classe ou un groupe
$select_f_groupes = afficher_select(DB_STRUCTURE_OPT_regroupements_etabl() , $select_nom='f_groupes' , $option_first='non' , $selection=$groupe , $optgroup='oui');
?>

<div class="hc">
	<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_eleves">DOC : Gestion des élèves</a></span>
</div>

<form action="./index.php?dossier=<?php echo $DOSSIER ?>&amp;fichier=<?php echo $FICHIER ?>&amp;section=<?php echo $SECTION ?>" method="POST" id="form0">
	<div>Restreindre l'affichage : <?php echo $select_f_groupes ?> <input type="submit" value="Actualiser." /></div>
</form>

<hr />

<form action="" id="form1">
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
				<th class="nu"><q class="ajouter" title="Ajouter un élève."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les élèves
			$tab_types = array('d'=>'Divers' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe');
			$groupe_type = $tab_types[$groupe_type];
			if($groupe_type=='Divers')
			{
				$groupe_type = ($groupe_id==1) ? 'sdf' : 'all' ;
			}
			$DB_TAB = DB_STRUCTURE_lister_eleves_actifs_regroupement($groupe_type,$groupe_id);
			if(count($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$DB_ROW['user_id'].'">';
					echo	'<td>'.html($DB_ROW['user_id_ent']).'</td>';
					echo	'<td>'.html($DB_ROW['user_id_gepi']).'</td>';
					echo	'<td>'.html($DB_ROW['user_num_sconet']).'</td>';
					echo	'<td>'.html($DB_ROW['user_reference']).'</td>';
					echo	'<td>'.html($DB_ROW['user_nom']).'</td>';
					echo	'<td>'.html($DB_ROW['user_prenom']).'</td>';
					echo	'<td>'.html($DB_ROW['user_login']).'</td>';
					echo	'<td class="i">champ crypté</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier cet élève."></q>';
					echo		'<q class="desactiver" title="Enlever cet élève."></q>';
					echo	'</td>';
					echo'</tr>';
				}
			}
			?>
		</tbody>
	</table>
</form>

<script type="text/javascript">var select_login="<?php echo $_SESSION['MODELE_ELEVE']; ?>";</script>
