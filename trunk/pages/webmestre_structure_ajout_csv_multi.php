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

// Créer un csv d'exemple
$separateur  = ';';
$adresse_csv = './__tmp/ajout_structures.csv';
$contenu_csv = 'Id_Import'.$separateur.'Id_Zone'.$separateur.'Localisation'.$separateur.'Dénomination'.$separateur.'UAI'.$separateur.'Contact_Nom'.$separateur.'Contact_Prénom'.$separateur.'Contact_Courriel'."\r\n";
$contenu_csv.= ''.$separateur.'1'.$separateur.'Jolieville'.$separateur.'CLG du Bonheur'.$separateur.'0123456A'.$separateur.'EDISON'.$separateur.'Thomas'.$separateur.'t.edison@mail.fr'."\r\n";
Ecrire_Fichier($adresse_csv,$contenu_csv);
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__structure_ajout_csv">DOC : Ajout CSV d'établissements (multi-structures)</a></span></p>

<ul class="puce">
	<li><a class="lien_ext" href="<?php echo $adresse_csv ?>"><span class="file file_txt">Récupérer le modèle de fichier <em>CSV</em> à utiliser.</span></a></li>
</ul>

<hr />

<h2>Importer un listing d'établissements</h2>

<form action="#" method="post" id="form_importer"><fieldset>
	<label class="tab" for="bouton_form_csv">Uploader fichier CSV :</label><button id="bouton_form_csv" type="button" class="fichier_import">Parcourir...</button><label id="ajax_msg_csv">&nbsp;</label><br />
	<span class="tab"></span><input id="f_courriel_envoi" name="f_courriel_envoi" type="checkbox" value="1" checked /><label for="f_courriel_envoi"> envoyer le courriel d'inscription</label>
	<div id="div_import" class="hide">
		<span class="tab"></span><button id="bouton_importer" type="button" class="valider">Ajouter les établissements du fichier.</button><label id="ajax_msg_import">&nbsp;</label>
	</div>
</fieldset></form>

<div id="div_info_import" class="hide">
	<ul id="puce_info_import" class="puce"><li></li></ul>
	<span id="ajax_import_num" class="hide"></span>
	<span id="ajax_import_max" class="hide"></span>
</div>

<p>&nbsp;</p>

<form action="#" method="post" id="structures" class="hide">
	<table class="form" id="transfert">
		<thead>
			<tr>
				<th class="nu"><input id="all_check" type="image" alt="Tout cocher." src="./_img/all_check.gif" title="Tout cocher." /> <input id="all_uncheck" type="image" alt="Tout décocher." src="./_img/all_uncheck.gif" title="Tout décocher." /></th>
				<th>Id</th>
				<th>Structure</th>
				<th>Contact</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			</tr>
		</tbody>
	</table>
	<p id="zone_actions">
		Pour les structures cochées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
		<button id="bouton_newsletter" type="button" class="mail_ecrire">Écrire un courriel.</button>
		<button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
		<label id="ajax_supprimer">&nbsp;</label>
	</p>
</form>
