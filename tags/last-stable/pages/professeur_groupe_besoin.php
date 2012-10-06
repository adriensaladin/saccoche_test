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

require(CHEMIN_DOSSIER_INCLUDE.'fonction_affichage_sections_communes.php');

$tab_groupe_proprio = array();
$tab_groupe_associe = array();
$tab_niveau_groupe  = array();

// Lister les groupes de besoin auxquels le prof est rattaché, propriétaire ou pas.

$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_groupes_besoins($_SESSION['USER_ID']);
foreach($DB_TAB as $DB_ROW)
{
	if($DB_ROW['jointure_pp'])
	{
		$tab_groupe_proprio[$DB_ROW['groupe_id']] = array
		(
			'niveau'     => '<i>'.sprintf("%02u",$DB_ROW['niveau_ordre']).'</i>'.html($DB_ROW['niveau_nom']) ,
			'nom'        => html($DB_ROW['groupe_nom']) ,
			'eleve'      => array() ,
			'professeur' => array()
		);
	}
	else
	{
		$tab_niveau_groupe[$DB_ROW['niveau_id']][] = $DB_ROW['groupe_id'];
		$tab_groupe_associe[$DB_ROW['groupe_id']] = array
		(
			'nom'        => html($DB_ROW['groupe_nom']) ,
			'eleve'      => '' ,
			'professeur' => ''
		);
	}
}

// Récupérer la liste des élèves et professeurs / groupes de besoin

if( !empty($DB_TAB) )
{
	$listing_groupes_id = implode( ',' , array_merge(array_keys($tab_groupe_proprio),array_keys($tab_groupe_associe)) );
	$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_users_avec_groupes_besoins($listing_groupes_id);
	foreach($DB_TAB as $DB_ROW)
	{
		if(isset($tab_groupe_proprio[$DB_ROW['groupe_id']]))
		{
			$tab_groupe_proprio[$DB_ROW['groupe_id']][$DB_ROW['user_profil']][] = $DB_ROW['user_id'];
		}
		else
		{
			$tab_groupe_associe[$DB_ROW['groupe_id']][$DB_ROW['user_profil']] .= ($DB_ROW['jointure_pp']) ? '<span class="proprio">'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'</span><br />' : html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']).'<br />' ;
		}
	}
}

// Eléments javascript concernant les niveaux : select_niveau & tab_niveau_ordre_js

$select_niveau = '<option value=""></option>';
$tab_niveau_ordre_js = 'var tab_niveau_ordre = new Array();';

$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(FALSE /*with_specifiques*/);
if(!empty($DB_TAB))
{
	foreach($DB_TAB as $DB_ROW)
	{
		$select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
		$tab_niveau_ordre_js .= 'tab_niveau_ordre["'.html($DB_ROW['niveau_nom']).'"]="'.sprintf("%02u",$DB_ROW['niveau_ordre']).'";';
	}
}
else
{
	$select_niveau .= '<option value="" disabled>Aucun niveau de classe n\'est choisi pour l\'établissement !</option>';
}

// Réception d'un formulaire depuis un tableau de synthèse bilan
// Pas de passage par la page ajax.php, mais pas besoin ici de protection contre attaques type CSRF
$tab_users = ( isset($_POST['id_user']) && is_array($_POST['id_user']) ) ? $_POST['id_user'] : array() ;
$tab_users = Clean::map_entier($tab_users);
$tab_users = array_filter($tab_users,'positif');
$nb_users  = count($tab_users);
$txt_users = ($nb_users) ? ( ($nb_users>1) ? $nb_users.' élèves' : $nb_users.' élève' ) : 'aucun' ;
$reception_todo = ($nb_users) ? 'true' : 'false' ;
$script_reception = 'var reception_todo = '.$reception_todo.';';
$script_reception.= 'var reception_users_texte = "'.$txt_users.'";';
$script_reception.= 'var reception_users_liste = "'.implode('_',$tab_users).'";';

?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__gestion_groupes_besoin">DOC : Gestion des groupes de besoin.</a></span></li>
	<li><span class="danger">Un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être supprimé (sinon vous n'aurez plus accès aux saisies) !</span></li>
</ul>

<hr />

<form action="#" method="post">
	<table class="form hsort">
		<thead>
			<tr>
				<th>Niveau</th>
				<th>Nom</th>
				<th>Élèves</th>
				<th>Profs</th>
				<th class="nu"><q class="ajouter" title="Ajouter un groupe de besoin."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$tab_listing_js = '';
			if(count($tab_groupe_proprio))
			{
				foreach($tab_groupe_proprio as $groupe_id => $tab_td)
				{
					$eleves_nombre = count($tab_td['eleve']);
					$profs_nombre  = count($tab_td['professeur']);
					$eleves_texte  = ($eleves_nombre>1) ? $eleves_nombre.' élèves' : '1 élève' ;
					$profs_texte   = ($profs_nombre>1)  ? $profs_nombre.' profs' : 'moi seul' ;
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$groupe_id.'">';
					echo	'<td>'.$tab_td['niveau'].'</td>';
					echo	'<td>'.$tab_td['nom'].'</td>';
					echo	'<td>'.$eleves_texte.'</td>';
					echo	'<td>'.$profs_texte.'</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier ce groupe de besoin."></q>';
					echo		'<q class="supprimer" title="Supprimer ce groupe de besoin."></q>';
					echo	'</td>';
					echo'</tr>';
					// Pour js
					$tab_listing_js .= 'tab_eleves["'.$groupe_id.'"]="'.implode('_',$tab_td['eleve']).'";';
					$tab_listing_js .= 'tab_profs["'.$groupe_id.'"]="'.implode('_',$tab_td['professeur']).'";';
				}
			}
			else
			{
				echo'<tr><td class="nu" colspan="5"></td></tr>';
			}
			?>
		</tbody>
	</table>
</form>

<hr />

<h2>Autres groupes de besoin vous concernant</h2>
<p><span class="astuce">Il s'agit d'éventuels groupes créés par des collègues et auxquels ils vous ont associé (seul le créateur d'un groupe peut le modifier).</span></p>

<?php
if( count($tab_groupe_associe) )
{
	// Assemblage du tableau
	$TH = array();
	$TB = array();
	$TF = array();
	foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
	{
		$TH[$niveau_id] = '';
		$TB[$niveau_id] = '';
		$TF[$niveau_id] = '';
		foreach($tab_groupe as $groupe_id)
		{
			$TH[$niveau_id] .= '<th>'.$tab_groupe_associe[$groupe_id]['nom'].'</th>';
			$TB[$niveau_id] .= '<td>'.mb_substr($tab_groupe_associe[$groupe_id]['eleve'],0,-6,'UTF-8').'</td>';
			$TF[$niveau_id] .= '<td>'.mb_substr($tab_groupe_associe[$groupe_id]['professeur'],0,-6,'UTF-8').'</td>';
		}
	}
	// Affichage du tableau
	foreach($tab_niveau_groupe as $niveau_id => $tab_groupe)
	{
		echo'<table class="affectation">';
		echo'<thead><tr>'.$TH[$niveau_id].'</tr></thead>';
		echo'<tbody><tr>'.$TB[$niveau_id].'</tr></tbody>';
		echo'<tfoot><tr>'.$TF[$niveau_id].'</tr></tfoot>';
		echo'</table>';
	}
}
else
{
	echo'<ul class="puce"><li>Aucun groupe trouvé.</li></ul>';
}
?>

<script type="text/javascript">
	<?php echo $script_reception ?>
	var tab_eleves = new Array();
	var tab_profs  = new Array();
	<?php echo $tab_listing_js ?>
	// <![CDATA[
	var select_niveau="<?php echo str_replace('"','\"',$select_niveau); ?>";
	// ]]>
	<?php echo $tab_niveau_ordre_js ?>
</script>

<form action="#" method="post" id="zone_profs" class="hide">
	<div class="astuce">Vous pouvez associer des collègues à vos groupes de besoin, mais pas vous retirer de vos propres groupes de besoin !</div>
	<?php echo afficher_form_element_checkbox_collegues() ?>
	<div style="clear:both"><button id="valider_profs" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_profs" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<form action="#" method="post" id="zone_eleve" class="arbre_dynamique hide">
	<p>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>) :</p>
	<?php echo afficher_form_element_checkbox_eleves_professeur(TRUE /*with_pourcent*/); ?>
	<p class="danger">Un groupe déjà utilisé lors d'une évaluation ne devrait pas voir ses élèves modifiés.<br />En particulier, retirer des élèves du groupe empêche l'accès aux notes saisies correspondantes !</p>
	<div><span class="tab"></span><button id="valider_eleve" type="button" class="valider">Valider la sélection</button>&nbsp;&nbsp;&nbsp;<button id="annuler_eleve" type="button" class="annuler">Annuler / Retour</button></div>
</form>
