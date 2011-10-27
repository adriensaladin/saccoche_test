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
$TITRE = "Gérer ses groupes de besoin";
$VERSION_JS_FILE += 1;
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__gestion_groupes_besoin">DOC : Gestion des groupes de besoin.</a></span></li>
	<li><span class="danger">Un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être supprimé (sinon vous n'aurez plus accès à certaines saisies) !</span></li>
</ul>

<hr />

<form action="" method="post">
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
			// Lister les groupes de besoin du prof dont il est propriétaire
			$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_groupes_besoins($_SESSION['USER_ID'],TRUE /* is_proprio */);
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['groupe_id'].'">';
				echo	'<td><i>'.sprintf("%02u",$DB_ROW['niveau_ordre']).'</i>'.html($DB_ROW['niveau_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['groupe_nom']).'</td>';
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

<hr />

<h2>Autres groupes de besoin vous concernant</h2>
<p><span class="astuce">Il s'agit d'éventuels groupes créés par des collègues et auxquels ils vous ont associé (seul le créateur d'un groupe peut le modifier).</span></p>

<?php
// Affichage du bilan des affectations des élèves et des professeurs dans les groupes de besoin
$tab_niveau_groupe = array();
$tab_eleve         = array();
$tab_prof          = array();
// Lister les groupes de besoin du prof dont il n'est pas propriétaire
$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_groupes_besoins($_SESSION['USER_ID'],FALSE /* is_proprio */);
foreach($DB_TAB as $DB_ROW)
{
	$tab_niveau_groupe[$DB_ROW['niveau_id']][$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
	$tab_eleve[$DB_ROW['groupe_id']] = '';
	$tab_prof[$DB_ROW['groupe_id']]  = '';
}
// Récupérer la liste des élèves et professeurs / groupes de besoin
if( count($tab_eleve) )
{
	$listing_groupes_id = implode(',',array_keys($tab_eleve));
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_users_avec_groupes_besoins( 'eleve' , $listing_groupes_id );
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleve[$DB_ROW['groupe_id']] .= html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'<br />';
	}
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_users_avec_groupes_besoins( 'professeur' , $listing_groupes_id );
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_prof[$DB_ROW['groupe_id']] .= ($DB_ROW['jointure_pp']) ? '<span class="proprio">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</span><br />' : html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'<br />' ;
	}
	// Assemblage du tableau résultant
	$TH = array();
	$TB = array();
	$TF = array();
	foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
	{
		$TH[$niveau_id] = '';
		$TB[$niveau_id] = '';
		$TF[$niveau_id] = '';
		foreach($tab_groupe as $groupe_id => $groupe_nom)
		{
			$TH[$niveau_id] .= '<th>'.$groupe_nom.'</th>';
			$TB[$niveau_id] .= '<td>'.mb_substr($tab_eleve[$groupe_id],0,-6,'UTF-8').'</td>';
			$TF[$niveau_id] .= '<td>'.mb_substr($tab_prof[$groupe_id],0,-6,'UTF-8').'</td>';
		}
	}
	foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
	{
		echo'<table class="affectation">';
		echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
		echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
		echo'<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
		echo'</table><p />';
	}
}
else
{
	echo'<ul class="puce"><li>Aucun groupe trouvé.</li></ul>';
}
?>

<?php
$select_niveau = '<option value=""></option>';
$tab_niveau_ordre_js = 'var tab_niveau_ordre = new Array();';

if($_SESSION['NIVEAUX'])
{
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$listing_cycles=false);
	foreach($DB_TAB as $DB_ROW)
	{
		$select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
		$tab_niveau_ordre_js .= 'tab_niveau_ordre["'.html($DB_ROW['niveau_nom']).'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
	}
}
else
{
	$select_niveau .= '<option value="" disabled>Aucun niveau n\'est rattaché à l\'établissement !</option>';
}
?>

<script type="text/javascript">
	// <![CDATA[
	var select_niveau="<?php echo str_replace('"','\"',$select_niveau); ?>";
	// ]]>
	<?php echo $tab_niveau_ordre_js ?>
</script>
