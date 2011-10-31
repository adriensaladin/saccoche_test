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
$TITRE = "Date de dernière connexion";
$VERSION_JS_FILE += 1;
?>

<?php
// Fabrication des éléments select du formulaire
$tab_groupes = ($_SESSION['USER_PROFIL']=='professeur') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl(FALSE/*sans*/) ;
$select_groupe = Formulaire::afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<hr />

<form action="" method="post" id="form_select"><fieldset>
	<label class="tab" for="f_rien">Profil :</label>
		<?php if($_SESSION['USER_PROFIL']=='administrateur'): ?>
		<input id="f_profil_directeurs" name="f_profil" type="radio" value="directeur" /><label for="f_profil_directeurs"> Directeurs</label>
		&nbsp;&nbsp;&nbsp;
		<?php endif ?>
		<?php if(in_array($_SESSION['USER_PROFIL'],array('administrateur','directeur'))): ?>
		<input id="f_profil_professeurs" name="f_profil" type="radio" value="professeur" /><label for="f_profil_professeurs"> Professeurs</label>
		&nbsp;&nbsp;&nbsp;
		<?php endif ?>
		<input id="f_profil_eleves" name="f_profil" type="radio" value="eleve" /><label for="f_profil_eleves"> Élèves</label>
		&nbsp;&nbsp;&nbsp;
		<input id="f_profil_parents" name="f_profil" type="radio" value="parent" /><label for="f_profil_parents"> Responsables légaux</label><br />
	<label class="tab" for="f_groupe">Regroupement :</label><?php echo $select_groupe ?> <label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<hr />

<table id="bilan" class="hide">
	<thead>
		<tr>
			<th>Utilisateur</th>
			<th>Date</th>
		</tr>
	</thead>
	<tbody>
		<tr><td class="nu" colspan="2"></td></tr>
	</tbody>
</table>

