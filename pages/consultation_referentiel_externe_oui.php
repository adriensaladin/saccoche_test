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
$VERSION_JS_FILE += 8;

// Fabrication des éléments select du formulaire, pour pouvoir prendre un référentiel d'une autre matière ou d'un autre niveau (demandé...).
$select_matiere = afficher_select(DB_STRUCTURE_OPT_matieres_communes() , $select_nom='f_matiere' , $option_first='val' , $selection=false , $optgroup='non');
$select_niveau  = afficher_select(DB_STRUCTURE_OPT_niveaux()           , $select_nom='f_niveau'  , $option_first='val' , $selection=false , $optgroup='non');
?>

<script type="text/javascript">
	var listing_id_niveaux_cycles = "<?php echo LISTING_ID_NIVEAUX_CYCLES ?>";
	var id_matiere_transversale   = "<?php echo ID_MATIERE_TRANSVERSALE ?>";
</script>

<form action="" class="noprint">
	<fieldset>
		<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><br />
		<label class="tab" for="f_niveau">Niveau :</label><?php echo $select_niveau ?><br />
		<label class="tab" for="f_structure"><img alt="" src="./_img/bulle_aide.png" title="Seules les structures partageant au moins un référentiel apparaissent." /> Structure :</label><select id="f_structure" name="f_structure"><option></option></select><br />
		<span class="tab"></span><button id="rechercher" type="button" class="hide"><img alt="" src="./_img/bouton/rechercher.png" /> Lancer / Actualiser la recherche.</button><label id="ajax_msg">&nbsp;</label>
	</fieldset>
</form>

<div class="noprint">
	<hr />
	<div id="choisir_referentiel_communautaire" class="hide">
		<h2>Liste des référentiels trouvés</h2>
		<div class="danger">Les référentiels partagés ne sont pas des modèles exemplaires à suivre ! Ils peuvent être améliorables, voir inadaptés...</div>
		<ul>
			<li></li>
		</ul>
	</div>
</div>

<div id="voir_referentiel_communautaire" class="hide">
	<hr />
	<ul class="ul_m1">
		<li class="li_m1">
		</li>
	</ul>
</div>


