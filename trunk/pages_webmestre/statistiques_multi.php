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
$TITRE = "Statistiques d'utilisation";

$select_structure = afficher_select(DB_OPT_structures_sacoche() , $select_nom=false , $option_first='non' , $selection=false , $optgroup='oui') ;
?>

<form id="structures" action=""><fieldset>
	<label class="tab" for="f_basic">Structure(s) :</label><select id="f_base" name="f_base" multiple="multiple" size="10"><?php echo $select_structure ?></select><br />
	<span class="tab"></span><input type="hidden" id="bases" name="bases" value="" /><input id="f_submit" type="submit" value="Envoyer." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<div id="ajax_info" class="hide">
	<h2>Calcul des statistiques en cours</h2>
	<label id="ajax_msg1"></label>
	<ul class="puce"><li id="ajax_msg2"></li></ul>
	<span id="ajax_num" class="hide"></span>
	<span id="ajax_max" class="hide"></span>
</div>

<table id="resultat" class="hide">
	<thead>
		<tr>
			<th>Id</th>
			<th>Structure</th>
			<th>Contact</th>
			<th>professeurs<br />enregistrés</th>
			<th>professeurs<br />connectés</th>
			<th>élèves<br />enregistrés</th>
			<th>élèves<br />connectés</th>
			<th>saisies<br />enregistrées</th>
		</tr>
	</thead>
	<tbody>
		<tr>
		</tr>
	</tbody>
</table>

<p id="expli" class="hide">
	<hr />
	<span class="astuce">Concernant les <b>utilisateurs enregistrés</b>, seuls sont comptés ceux au statut "actif".</span><br />
	<span class="astuce">Les <b>utilisateurs connectés</b> sont ceux s'étant identifiés au cours du dernier semestre.</span><br />
	<span class="astuce">La date de dernière connexion n'étant mémorisée que depuis juin 2010, les identifications antérieures ne sont pas comptabilisées.</span>
</p>
