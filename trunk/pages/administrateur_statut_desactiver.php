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
$TITRE = "Désactiver des comptes utilisateurs";
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes              = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl()                   , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
$select_parents                = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_parents_etabl($statut=1)                , $select_nom=false , $option_first='non' , $selection=false , $optgroup='non');
$select_professeurs_directeurs = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_professeurs_directeurs_etabl($statut=1) , $select_nom=false , $option_first='non' , $selection=false , $optgroup='oui');
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_statuts">DOC : Statuts : désactiver / réintégrer / supprimer</a></span></li>
	<li><span class="astuce">Pour un traitement individuel on peut utiliser les pages [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Gérer les élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Gérer les parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Gérer les professeurs</a>] [<a href="./index.php?page=administrateur_directeur">Gérer les directeurs</a>].</span></li>
</ul>

<hr />

<form action="#" method="post">
	<table>
		<tr>
			<td style="width:55em" colspan="2">
				<p class="hc">
					<input id="f_profil_eleves" name="f_profil" type="radio" value="eleves" /><label for="f_profil_eleves"> Élèves</label>
					&nbsp;&nbsp;&nbsp;
					<input id="f_profil_parents" name="f_profil" type="radio" value="parents" /><label for="f_profil_parents"> Responsables légaux</label>
					&nbsp;&nbsp;&nbsp;
					<input id="f_profil_professeurs_directeurs" name="f_profil" type="radio" value="professeurs_directeurs" /><label for="f_profil_professeurs_directeurs"> Professeurs / Directeurs</label>
				</p>
			</td>
		</tr>
		<tr>
			<td class="nu">
				<p id="p_eleves" class="hide">
					<b>Élèves au statut activé :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
					<select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select><br />
					<select id="select_eleves" name="select_eleves[]" multiple size="10" class="hide"><option value=""></option></select>
				</p>
				<p id="p_parents" class="hide">
					<b>Responsables légaux au statut activé :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
					<select id="select_parents" name="select_parents[]" multiple size="10"><?php echo $select_parents; ?></select>
				</p>
				<p id="p_professeurs_directeurs" class="hide">
					<b>Professeurs / Directeurs au statut activé :</b> <img alt="" src="./_img/bulle_aide.png" title="Utiliser la touche &laquo&nbsp;Shift&nbsp;&raquo; pour une sélection multiple contiguë.<br />Utiliser la touche &laquo&nbsp;Ctrl&nbsp;&raquo; pour une sélection multiple non contiguë." /><br />
					<select id="select_professeurs_directeurs" name="select_professeurs_directeurs[]" multiple size="10"><?php echo $select_professeurs_directeurs; ?></select>
				</p>
			</td>
			<td id="td_bouton" class="nu hide" style="width:25em">
				<p><button id="desactiver" type="button" class="user_desactiver">Désactiver ces comptes.</button></p>
				<p><label id="ajax_msg">&nbsp;</label></p>
			</td>
		</tr>
	</table>
</form>
