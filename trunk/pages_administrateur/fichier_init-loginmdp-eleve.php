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
$TITRE = "Initialiser identifiants élèves";
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes = afficher_select(DB_STRUCTURE_OPT_regroupements_etabl() , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
?>

<p class="astuce">Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?dossier=administrateur&amp;fichier=eleve&amp;section=gestion">Gérer les élèves</a>".</p>
<p class="danger">Valider entraîne la génération de nouveaux identifiants pour tous les utilisateurs sélectionnés.</p>
<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des élèves :</b><br />
			<select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select><br />
			<select id="select_eleves" name="select_eleves[]" multiple="multiple" size="10" class="hide"><option value=""></option></select>
		</td>
		<td class="nu" style="width:25em">
			<p><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>"<br />pour une sélection multiple.</span></p>
			<p><button id="eleve_login" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les noms d'utilisateurs</button> de ces élèves.</p>
			<p><button id="eleve_mdp" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les mots de passe</button> de ces élèves.</p>
		</td>
	</tr></table>
</form>
<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>