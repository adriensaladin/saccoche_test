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
$TITRE = "Consulter les référentiels d'un établissement";
?>

<?php
// Fabrication des éléments select du formulaire
$select_etabl   = afficher_select(DB_OPT_structures_partage($_SESSION['NIVEAUX']) , $select_nom='f_etabl'   , $option_first='oui' , $selection=false , $optgroup='oui');
$select_matiere = afficher_select(DB_OPT_matieres_communes($_SESSION['MATIERES']) , $select_nom='f_matiere' , $option_first='oui' , $selection=false , $optgroup='non');
?>

<form id="form_select" action="">
	<fieldset>
		<label class="tab" for="f_etabl">Établissement :</label><?php echo $select_etabl ?><br />
		<label class="tab" for="f_matiere">Matière <img alt="" src="./_img/bulle_aide.png" title="Seules les matières cochées par l'administrateur apparaissent." /> :</label><?php echo $select_matiere ?><br />
		<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
	</fieldset>
</form>

<div id="zone_compet">
</div>



