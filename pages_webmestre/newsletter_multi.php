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
$TITRE = "Lettre d'information";

$selection = (isset($_POST['listing_ids'])) ? explode(',',$_POST['listing_ids']) : false ; // demande de newsletter depuis structure_multi.php ou statistiques_multi.php
$select_structure = afficher_select(DB_WEBMESTRE_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection , $optgroup='oui');
?>

<div id="ajax_info" class="hide">
	<h2>Envoi de la lettre</h2>
	<label id="ajax_msg1"></label>
	<ul class="puce"><li id="ajax_msg2"></li></ul>
	<span id="ajax_num" class="hide"></span>
	<span id="ajax_max" class="hide"></span>
</div>

<form id="newsletter" action=""><fieldset>
	<label class="tab" for="f_basic">Destinataire(s) :</label><select id="f_base" name="f_base" multiple="multiple" size="10"><?php echo $select_structure ?></select><br />
	<span class="tab"></span><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>" pour une sélection multiple.</span><br />
	<label class="tab" for="f_titre">Titre :</label><input id="f_titre" name="f_titre" value="" size="50" /><br />
	<label class="tab" for="f_contenu">Contenu :</label><textarea id="f_contenu" name="f_contenu" rows="15" cols="100">message ici, sans bonjour ni au revoir, car l'en-tête et le pied du message sont automatiquement ajoutés</textarea><br />
	<span class="tab"></span><input type="hidden" id="bases" name="bases" value="" /><input id="f_submit" type="submit" value="Envoyer." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
