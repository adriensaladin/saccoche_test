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
?>

<?php
$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : false ; // demande de newsletter depuis webmestre_structure_multi.php ou webmestre_statistiques.php
$select_structure = Formulaire::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection , $optgroup='oui');
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_webmestre__publipostage">DOC : Lettre d'information (multi-structures)</a></span></p>

<div id="ajax_info" class="hide">
	<h2>Envoi de la lettre</h2>
	<label id="ajax_msg1"></label>
	<ul class="puce"><li id="ajax_msg2"></li></ul>
	<span id="ajax_num" class="hide"></span>
	<span id="ajax_max" class="hide"></span>
</div>

<form action="#" method="post" id="newsletter"><fieldset>
	<label class="tab" for="f_basic">Destinataire(s) <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo;&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo;&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /> :</label><select id="f_base" name="f_base[]" multiple size="10"><?php echo $select_structure ?></select><br />
	<label class="tab" for="f_titre">Titre :</label><input id="f_titre" name="f_titre" value="" size="50" /><br />
	<label class="tab" for="f_contenu">Contenu :</label><textarea id="f_contenu" name="f_contenu" rows="15" cols="100">message ici, sans bonjour ni au revoir, car l'en-tête et le pied du message sont automatiquement ajoutés</textarea><br />
	<span class="tab"></span><button id="bouton_valider" type="button" class="mail_envoyer">Envoyer la lettre.</button><label id="ajax_msg">&nbsp;</label>
	<hr />
</fieldset></form>

<form action="#" method="post" id="structures">
	<p id="zone_actions">
		Pour les structures sélectionnées : <input id="listing_ids" name="listing_ids" type="hidden" value="" />
		<button id="bouton_stats" type="button" class="stats">Calculer les statistiques.</button>
		<button id="bouton_transfert" type="button" class="fichier_export">Exporter données &amp; bases.</button>
		<button id="bouton_supprimer" type="button" class="supprimer">Supprimer.</button>
		<label id="ajax_supprimer">&nbsp;</label>
	</p>
</form>
