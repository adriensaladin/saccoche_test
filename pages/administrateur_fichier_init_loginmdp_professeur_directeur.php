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
$TITRE = "Initialiser identifiants professeurs directeurs";
?>

<?php
// Fabrication des éléments select du formulaire
$select_professeurs_directeurs = afficher_select(DB_STRUCTURE_OPT_professeurs_directeurs_etabl($statut=1) , $select_nom=false , $option_first='non' , $selection=true , $optgroup='oui');
?>

<p class="astuce">Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Gérer les professeurs</a>" ou "<a href="./index.php?page=administrateur_directeur">Gérer les directeurs</a>".</p>
<p class="danger">Valider entraîne la génération de nouveaux identifiants pour tous les utilisateurs sélectionnés.</p>
<form action="">
	<table><tr>
		<td class="nu" style="width:25em">
			<b>Liste des professeurs et directeurs :</b><br />
			<select id="select_professeurs_directeurs" name="select_professeurs_directeurs[]" multiple="multiple" size="10"><?php echo $select_professeurs_directeurs; ?></select>
		</td>
		<td class="nu" style="width:25em">
			<p><span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>"<br />pour une sélection multiple.</span></p>
			<p><button id="prof_login" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les noms d'utilisateurs</button> de ces professeurs / directeurs.</p>
			<p><button id="prof_mdp" type="button"><img alt="" src="./_img/bouton/mdp_groupe.png" /> Initialiser les mots de passe</button> de ces professeurs / directeurs.</p>
		</td>
	</tr></table>
</form>
<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>
