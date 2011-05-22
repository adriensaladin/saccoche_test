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
$TITRE = "Traiter les comptes desactivés";
$VERSION_JS_FILE += 2;
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes              = afficher_select(DB_STRUCTURE_OPT_regroupements_etabl()                   , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
$select_professeurs_directeurs = afficher_select(DB_STRUCTURE_OPT_professeurs_directeurs_etabl($statut=0) , $select_nom=false , $option_first='non' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_statuts">DOC : Statuts : désactiver / réintégrer / supprimer</a></span></li>
	<li><span class="danger">Supprimer un compte est une action irréversible, effaçant en particulier pour les élèves tous les scores associés !</span></li>
</ul>

<hr />

<form action="">
	<table>
		<tr>
			<td style="width:55em" colspan="2">
				<p class="hc">
					<input id="f_profil_eleves" name="f_profil" type="radio" value="eleves" /><label for="f_profil_eleves"> Élèves</label>
					&nbsp;&nbsp;&nbsp;
					<input id="f_profil_professeurs_directeurs" name="f_profil" type="radio" value="professeurs_directeurs" /><label for="f_profil_professeurs_directeurs"> Professeurs / Directeurs</label>
				</p>
			</td>
		</tr>
		<tr>
			<td class="nu">
				<p id="p_eleves" class="hide">
					<b>Élèves au statut désactivé :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
					<select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select><br />
					<select id="select_eleves" name="select_eleves[]" multiple size="10" class="hide"><option value=""></option></select>
				</p>
				<p id="p_professeurs_directeurs" class="hide">
					<b>Professeurs / Directeurs au statut désactivé :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
					<select id="select_professeurs_directeurs" name="select_professeurs_directeurs[]" multiple size="10"><?php echo $select_professeurs_directeurs; ?></select>
				</p>
			</td>
			<td id="td_bouton" class="nu hide" style="width:25em">
				<p><button id="reintegrer" type="button"><img alt="" src="./_img/bouton/user_ajouter.png" /> Réintégrer ces comptes.</button></p>
				<p><button id="supprimer" type="button"><img alt="" src="./_img/bouton/supprimer.png" /> Supprimer définitivement ces comptes.</button></p>
				<p><label id="ajax_msg">&nbsp;</label></p>
			</td>
		</tr>
	</table>
</form>
