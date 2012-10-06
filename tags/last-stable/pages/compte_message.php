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
$TITRE = "Messages d'accueil";
?>

<?php
// Fabrication des éléments select du formulaire
$tab_groupes = ($_SESSION['USER_PROFIL']=='professeur') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;
$select_groupe = Form::afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');
$select_profil = '<option value=""></option>';
$select_profil.= ($_SESSION['USER_PROFIL']=='administrateur') ? '<option value="administrateur">Administrateurs</option>' : '' ;
$select_profil.= (in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur'))) ? '<option value="directeur">Directeurs</option>' : '' ;
$select_profil.= '<option value="professeur">Professeurs</option><option value="eleve">Élèves</option><option value="parent">Responsables légaux</option>';
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__messages_accueil">DOC : Messages d'accueil.</a></span></li>
</ul>

<hr />

<form action="#" id="form_principal" method="post">
	<table class="form hsort">
		<thead>
			<tr>
				<th>Date début</th>
				<th>Date fin</th>
				<th>Destinataires</th>
				<th>Contenu</th>
				<th class="nu"><q class="ajouter" title="Ajouter un message."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$script = '';
			// Lister les messages dont le user est l'auteur
			$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_messages_user_auteur($_SESSION['USER_ID']);
			if(!empty($DB_TAB))
			{
				foreach($DB_TAB as $DB_ROW)
				{
					// Afficher une ligne du tableau
					$date_debut_affich    = convert_date_mysql_to_french($DB_ROW['message_debut_date']);
					$date_fin_affich      = convert_date_mysql_to_french($DB_ROW['message_fin_date']);
					$destinataires_liste  = str_replace(',','_',mb_substr($DB_ROW['message_destinataires'],1,-1));
					$destinataires_nombre = (mb_substr_count($DB_ROW['message_destinataires'],',')-1);
					$destinataires_nombre = ($destinataires_nombre>1) ? $destinataires_nombre.' destinataires' : $destinataires_nombre.' destinataire' ;
					// $message_contenu      = (mb_strlen($DB_ROW['message_contenu'])<30) ? html($DB_ROW['message_contenu']) : html(mb_substr($DB_ROW['message_contenu'],0,25)).' <img alt="" src="./_img/bulle_aide.png" title="'.str_replace(array("\r\n","\r","\n"),'<br />',html($DB_ROW['message_contenu'])).'" />' ;
					// Afficher une ligne du tableau
					echo'<tr id="id_'.$DB_ROW['message_id'].'">';
					echo	'<td><i>'.$DB_ROW['message_debut_date'].'</i>'.$date_debut_affich.'</td>';
					echo	'<td><i>'.$DB_ROW['message_fin_date'].'</i>'.$date_fin_affich.'</td>';
					echo	'<td>'.$destinataires_nombre.'</td>';
					echo	'<td>'.html(mb_substr($DB_ROW['message_contenu'],0,30)).'</td>';
					echo	'<td class="nu">';
					echo		'<q class="modifier" title="Modifier ce message."></q>';
					echo		'<q class="supprimer" title="Supprimer ce message."></q>';
					echo	'</td>';
					echo'</tr>';
					// Pour js
					$script .= 'tab_destinataires['.$DB_ROW['message_id'].']="'.$destinataires_liste.'";';
					$script .= 'tab_msg_contenus['.$DB_ROW['message_id'].']="'.str_replace(array("\r\n","\r","\n"),array('\r\n','\r','\n'),html($DB_ROW['message_contenu'])).'";';
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

<script type="text/javascript">
	var input_date = "<?php echo TODAY_FR ?>";
	var date_mysql = "<?php echo TODAY_MYSQL ?>";
	var tab_destinataires = new Array();
	var tab_msg_contenus  = new Array();
	// <![CDATA[
	<?php echo $script ?>
	// ]]>
</script>

<form action="#" method="post" id="form_destinataires" class="hide">
	<table><tr>
		<td class="nu" style="width:30em">
			<b>Profil :</b><br />
			<select id="f_profil" name="f_profil"><?php echo $select_profil ?></select><br />
			<b>Regroupement :</b><br />
			<?php echo $select_groupe ?><br />
			<b>Utilisateur(s) :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
			<select id="f_user" name="f_user[]" multiple size="7" class="t8 hide"><option></option></select><br />
			<button id="ajouter_destinataires" type="button" class="groupe_ajouter" disabled>Ajouter.</button><br />
			<label id="ajax_msg_destinataires">&nbsp;</label>
		</td>
		<td class="nu" style="width:30em">
			<b>Liste des destinataires :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
			<select id="select_destinataires" name="select_destinataires[]" multiple size="12" class="t8"><option></option></select><br />
			<button id="retirer_destinataires" type="button" class="groupe_retirer" disabled>Retirer.</button>
		</td>
	</tr></table>
	<div><span class="tab"></span><button id="valider_destinataires" type="button" class="valider" disabled>Valider la liste de destinataires</button>&nbsp;&nbsp;&nbsp;<button id="annuler_destinataires" type="button" class="annuler">Annuler / Retour</button></div>
</form>

<form action="#" method="post" id="form_message" class="hide">
	<div>
		<label for="f_message" class="tab">Contenu du message :</label><textarea id="f_message" rows="5" cols="75"></textarea><br />
		<span class="tab"></span><label id="f_message_reste"></label><br />
		<span class="tab"></span><button id="valider_message" type="button" class="valider">Valider le contenu</button>&nbsp;&nbsp;&nbsp;<button id="annuler_message" type="button" class="annuler">Annuler / Retour</button>
	</div>
</form>
