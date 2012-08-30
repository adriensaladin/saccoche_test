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
$TITRE = "Niveaux";

// Formulaire des familles de niveaux, en 2 catégories
$select_niveau_famille = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_niveaux() , $select_nom='f_famille' , $option_first='oui' , $selection=FALSE , $optgroup='oui');
?>

<div><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_niveaux">DOC : Niveaux</a></span></div>

<form action="#" method="post" id="niveaux">
	<hr />
	<table class="form">
		<thead>
			<tr>
				<th>Référence</th>
				<th>Nom complet</th>
				<th class="nu"><q class="ajouter" title="Ajouter un niveau."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les niveaux
			$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(TRUE /*with_specifiques*/);
			foreach($DB_TAB as $DB_ROW)
			{
				echo'<tr id="id_'.$DB_ROW['niveau_id'].'">';
				echo'	<td class="label">'.html($DB_ROW['niveau_ref']).'</td>';
				echo'	<td class="label">'.html($DB_ROW['niveau_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="supprimer" title="Supprimer ce niveau."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<form action="#" method="post" id="zone_ajout_form" onsubmit="return false" class="hide">
	<hr />
	<h2>Rechercher un niveau</h2>
	<p><span class="tab"></span><button id="ajout_annuler" type="button" class="annuler">Annuler / Retour.</button></p>
	<fieldset id="f_recherche_famille">
		<label class="tab" for="f_famille">Famille :</label><?php echo $select_niveau_famille ?><br />
	</fieldset>
	<span class="tab"></span><label id="ajax_msg_recherche">&nbsp;</label>
	<ul id="f_recherche_resultat" class="puce hide">
		<li></li>
	</ul>
</form>

<p>&nbsp;</p>
