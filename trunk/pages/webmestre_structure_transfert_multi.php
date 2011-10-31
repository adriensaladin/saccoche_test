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
$VERSION_JS_FILE += 3;
?>

<?php
$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : false ; // demande d'exports depuis structure_multi.php
$select_structure = Formulaire::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection , $optgroup='oui') ;
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__structure_transfert">DOC : Transfert d'établissements (multi-structures)</a></span></p>

<hr />

<h2>Exporter des établissements (données &amp; bases)</h2>

<form action="" method="post" id="form_exporter"><fieldset>
	<label class="tab" for="f_basic">Structure(s) <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> :</label><select id="f_base" name="f_base" multiple size="10"><?php echo $select_structure ?></select><br />
	<span class="tab"></span><button id="bouton_exporter" type="button"><img alt="" src="./_img/bouton/dump_export.png" /> Créer les fichiers d'export.</button><label id="ajax_msg_export">&nbsp;</label>
	<div id="div_info_export" class="hide">
		<ul id="puce_info_export" class="puce"><li></li></ul>
		<span id="ajax_export_num" class="hide"></span>
		<span id="ajax_export_max" class="hide"></span>
	</div>
	<div id="zone_actions_export" class="hide">
		<label class="alerte">Ces deux fichiers sont nécessaires pour toute importation ; vérifiez leur validité une fois récupérés.</label><br />
		<label class="alerte">Pour des raisons de sécurité et de confidentialité, ces fichiers seront effacés du serveur dans 1h.</label><br />
		Pour les structures sélectionnées : <!-- input listing_ids plus bas -->
		<button id="bouton_newsletter_export" type="button"><img alt="" src="./_img/bouton/mail_ecrire.png" /> Écrire un courriel.</button>
		<button id="bouton_stats_export" type="button"><img alt="" src="./_img/bouton/stats.png" /> Calculer les statistiques.</button>
		<button id="bouton_supprimer_export" type="button"><img alt="" src="./_img/bouton/supprimer.png" /> Supprimer.</button>
		<label id="ajax_supprimer_export">&nbsp;</label>
	</div>
</fieldset></form>


<hr />

<h2>Importer des établissements (données &amp; bases)</h2>

<form action="" method="post" id="form_importer"><fieldset>
		<label class="tab" for="bouton_form_csv">Uploader fichier CSV :</label><button id="bouton_form_csv" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button><label id="ajax_msg_csv">&nbsp;</label>
	<div id="div_zip" class="hide">
		<label class="tab" for="bouton_form_zip">Uploader fichier ZIP :</label><button id="bouton_form_zip" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button><label id="ajax_msg_zip">&nbsp;</label>
	</div>
	<div id="div_import" class="hide">
		<span class="tab"></span><button id="bouton_importer" type="button"><img alt="" src="./_img/bouton/valider.png" /> Restaurer les établissements.</button><label id="ajax_msg_import">&nbsp;</label>
	</div>
</fieldset></form>
<div id="div_info_import" class="hide">
	<ul id="puce_info_import" class="puce"><li></li></ul>
	<span id="ajax_import_num" class="hide"></span>
	<span id="ajax_import_max" class="hide"></span>
</div>

<p />

<form action="" method="post" id="structures" class="hide">
	<table class="form" id="transfert">
		<thead>
			<tr>
				<th class="nu"><input id="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input id="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th>
				<th>Id</th>
				<th>Structure</th>
				<th>Contact</th>
				<th>Bilan</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			</tr>
		</tbody>
	</table>
	<p id="zone_actions_import">
		Pour les structures cochées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
		<button id="bouton_newsletter_import" type="button"><img alt="" src="./_img/bouton/mail_ecrire.png" /> Écrire un courriel.</button>
		<button id="bouton_stats_import" type="button"><img alt="" src="./_img/bouton/stats.png" /> Calculer les statistiques.</button>
		<button id="bouton_supprimer_import" type="button"><img alt="" src="./_img/bouton/supprimer.png" /> Supprimer.</button>
		<label id="ajax_supprimer_import">&nbsp;</label>
	</p>
</form>
