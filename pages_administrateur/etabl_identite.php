<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Identité de l'établissement";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_informations_structure">DOC : Gestion de l'identité de l'établissement</a></span></p>

<hr />

<form id="form" action=""><fieldset>
	<label class="tab" for="f_denomination">Dénomination :</label><input id="f_denomination" name="f_denomination" size="55" type="text" value="<?php echo html($_SESSION['DENOMINATION']); ?>" /><br />
	<label class="tab" for="f_structure_uai">Code UAI (ex-RNE) :</label><input id="f_structure_uai" name="f_structure_uai" size="8" type="text" value="<?php echo html($_SESSION['STRUCTURE_UAI']); ?>" /><br />
	<label class="tab" for="f_structure_id">Id. Sésamath :</label><input id="f_structure_id" name="f_structure_id" size="5" type="text" value="<?php echo html($_SESSION['STRUCTURE_ID']); ?>" /><br />
	<label class="tab" for="f_structure_key">Clef de contrôle :</label><input id="f_structure_key" name="f_structure_key" size="35" type="text" value="<?php echo html($_SESSION['STRUCTURE_KEY']); ?>" /><br />
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
