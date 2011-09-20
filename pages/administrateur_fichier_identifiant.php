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
$TITRE = "Importer / Imposer des identifiants";
$VERSION_JS_FILE += 8;
?>

<?php
require_once('./_inc/tableau_sso.php');

// Fabrication des éléments select du formulaire
$select_groupe = afficher_select(DB_STRUCTURE_OPT_regroupements_etabl(FALSE/*sans*/) , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="astuce">Pour un traitement individuel on peut utiliser les pages [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Gérer les élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Gérer les parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Gérer les professeurs</a>] [<a href="./index.php?page=administrateur_directeur">Gérer les directeurs</a>].</span></li>
	<li><span class="astuce">Les administrateurs ne se gèrent qu'individuellement depuis la page [<a href="./index.php?page=administrateur_administrateur">Gérer les administrateurs</a>].</span></li>
</ul>

<hr />

<form action="" method="post">

	<fieldset>
		<label class="tab" for="f_choix_principal">Objectif :</label>
		<select id="f_choix_principal" name="f_choix_principal">
			<option value=""></option>
			<option value="init_loginmdp">Initialiser des identifiants SACoche.</option>
			<option value="import_loginmdp">Importer / Imposer des identifiants SACoche.</option>
			<option value="import_id_lcs">Récupérer les identifiants du LCS.</option>
			<option value="import_id_argos">Récupérer les identifiants d'ARGOS.</option>
			<option value="import_id_ent_<?php echo $_SESSION['CONNEXION_MODE'] ?>">Importer / Imposer les identifiants d'un ENT.</option>
			<option value="import_id_gepi">Récupérer les identifiants de Gepi.</option>
		</select><br />
	</fieldset>

	<fieldset id="fieldset_init_loginmdp" class="hide">
		<hr />
		<p class="astuce">Les noms d'utilisateurs seront générés selon <a href="./index.php?page=administrateur_etabl_login">le format choisi</a>.</p>
		<table>
			<tr>
				<td class="nu" style="width:30em;text-align:left">
					<div><label class="tab" for="f_profil">Profil :</label><select id="f_profil" name="f_profil"><option value=""></option><option value="eleves">élèves</option><option value="parents">responsables légaux</option><option value="professeurs">professeurs</option><option value="directeurs">directeurs</option></select></div>
					<div><label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?></div>
					<div id="div_users" class="hide"><label class="tab" for="select_users">Utilisateurs :</label><select id="select_users" name="select_users[]" multiple size="8"><option value=""></option></select> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /></div>
				</td>
				<td id="td_bouton" class="nu" style="width:25em">
					<p><button id="init_login" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les noms d'utilisateurs.</button></p>
					<p><button id="init_mdp" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les mots de passe.</button></p>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset id="fieldset_import_loginmdp" class="hide">
		<hr />
		<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__force_login_mdp_tableur">DOC : Imposer identifiants SACoche avec un tableur</a></span></p>
		Vous pouvez <button id="user_export" type="button"><img alt="" src="./_img/bouton/fichier_export.png" /> récupérer un fichier csv avec les noms / prénoms / logins actuels</button> (le mot de passe, crypté, ne peut être restitué).<p />
		Modifiez les identifiants souhaités, puis indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu que vous souhaitez importer.
		<p><label class="tab" for="import_loginmdp">Envoyer le fichier :</label><button id="import_loginmdp" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></p>
	</fieldset>

	<fieldset id="fieldset_import_id_lcs" class="hide">
		<hr />
		<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__LCS">DOC : Intégration de SACoche dans un LCS</a></span></p>
		<?php
		$fichier = './webservices/import_lcs.php';
		echo (is_file($fichier)) ? '<button name="dupliquer" id="COPY_id_lcs_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Récupérer l\'identifiant LCS</button> comme identifiant de l\'ENT pour tous les utilisateurs.'
		                         : '<div class="danger">Le fichier &laquo;&nbsp;<b>'.$fichier.'</b>&nbsp;&raquo; devant figurer dans le paquet lcs-sacoche n\'a pas été détecté !</div>' ;
		?>
	</fieldset>

	<fieldset id="fieldset_import_id_argos" class="hide">
		<hr />
		<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__argos">DOC : Intégration de SACoche dans Argos</a></span></p>
		<?php
		$fichier = './webservices/import_argos.php';
		echo (is_file($fichier)) ? '<button name="dupliquer" id="COPY_id_argos_profs_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les professeurs &amp; directeurs.<br />
		                            <button name="dupliquer" id="COPY_id_argos_eleves_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les élèves.<br />
		                            <button name="dupliquer" id="COPY_id_argos_parents_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Récupérer l\'identifiant Argos</button> comme identifiant de l\'ENT pour tous les responsables légaux.'
		                         : '<div class="danger">Le fichier &laquo;&nbsp;<b>'.$fichier.'</b>&nbsp;&raquo; devant figurer dans l\'installation académique Argos n\'a pas été détecté !</div>' ;
		?>
	</fieldset>

	<fieldset id="fieldset_import_id_ent_normal" class="hide">
		<hr />
		<div class="astuce">Vous devez commencer par sélectionner votre ENT depuis la page "<a href="./index.php?page=administrateur_etabl_connexion">Mode d'identification</a>".</div>
	</fieldset>

	<fieldset id="fieldset_import_id_ent_cas" class="hide">
		<hr />
		<h4>En important un fichier</h4>
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_mode_identification__<?php echo $_SESSION['CONNEXION_NOM'] ?>">DOC : <?php echo $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['txt'] ?></a></span></li>
			<li>Importer le fichier <b>csv</b> provenant de l'ENT : <button id="import_ent" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
		<h4>En dupliquant un autre champ</h4>
		<ul class="puce">
			<li><button name="dupliquer" id="COPY_id_gepi_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Dupliquer l'identifiant de Gepi enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
			<li><button name="dupliquer" id="COPY_login_TO_id_ent" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Dupliquer le login de SACoche enregistré</button> comme identifiant de l'ENT pour tous les utilisateurs.</li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_import_id_gepi" class="hide">
		<hr />
		<h4>En important un fichier</h4>
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_identifiant_Gepi_SACoche">DOC : Import des identifiants de Gepi dans SACoche.</a></span></li>
			<li>Importer le fichier <b>base_eleve_gepi.csv</b> issu de Gepi (aide ci-dessus) : <button id="import_gepi_eleves" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
			<li>Importer le fichier <b>base_professeur_gepi.csv</b> issu de Gepi (aide ci-dessus) : <button id="import_gepi_profs" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
		<h4>En dupliquant un autre champ</h4>
		<ul class="puce">
			<li><button name="dupliquer" id="COPY_id_ent_TO_id_gepi" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Dupliquer l'identifiant de l'ENT enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
			<li><button name="dupliquer" id="COPY_login_TO_id_gepi" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Dupliquer le login de SACoche enregistré</button> comme identifiant de Gepi pour tous les utilisateurs.</li>
		</ul>
	</fieldset>

</form>

<hr />
<label id="ajax_msg">&nbsp;</label>
<p />
<div id="ajax_retour"></div>