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

// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_famille = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_familles_matieres() , $select_nom='f_famille' , $option_first='oui' , $selection=false , $optgroup='oui');
$select_niveau  = Formulaire::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_niveaux()           , $select_nom='f_niveau'  , $option_first='val' , $selection=false , $optgroup='non');
?>

<script type="text/javascript">
	var listing_id_niveaux_cycles = "<?php echo LISTING_ID_NIVEAUX_CYCLES ?>";
	var id_matiere_transversale   = "<?php echo ID_MATIERE_TRANSVERSALE ?>";
</script>

<form action="#" method="post">
	<fieldset>
		<label class="tab" for="f_famille">Famille de matières :</label><?php echo $select_famille ?><label id="ajax_maj">&nbsp;</label><br />
		<label class="tab" for="f_matiere">Matières :</label><select id="f_matiere" name="f_matiere"><option value="0">Toutes les matières</option></select><br />
		<label class="tab" for="f_niveau">Niveau :</label><?php echo $select_niveau ?><br />
		<label class="tab" for="f_structure"><img alt="" src="./_img/bulle_aide.png" title="Seules les structures partageant au moins un référentiel apparaissent." /> Structure :</label><select id="f_structure" name="f_structure"><option></option></select><br />
		<span class="tab"></span><button id="rechercher" type="button" class="rechercher" disabled>Lancer / Actualiser la recherche.</button><label id="ajax_msg">&nbsp;</label>
	</fieldset>
</form>

<hr />

<div id="choisir_referentiel_communautaire" class="hide">
	<h2>Liste des référentiels trouvés</h2>
		<p>
			<span class="danger">Les référentiels partagés ne sont pas des modèles exemplaires à suivre ! Ils peuvent être améliorables, voir inadaptés&hellip;</span><br />
			<span class="astuce">Le nombre jouxtant l'étoile indique combien de fois un référentiel a été repris, mais ceci ne présage pas de son intérêt / sa pertinence.</span>
		</p>
	<ul>
		<li></li>
	</ul>
</div>

