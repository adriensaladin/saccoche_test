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
$TITRE = "Parents &amp; élèves";
$VERSION_JS_FILE += 1;
?>

<?php
// Fabrication des éléments select du formulaire
$select_f_groupes = afficher_select(DB_STRUCTURE_OPT_regroupements_etabl()         , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='oui');
$select_f_parents = afficher_select(DB_STRUCTURE_OPT_parents_etabl($user_statut=1) , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='non');

?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_parents">DOC : Gestion des parents</a></span></p>

<hr />

<form action="" method="post">
	<fieldset id="fieldset_eleves">
		<label class="tab" for="f_groupe">Élève :</label><select id="f_groupe" name="f_groupe"><?php echo $select_f_groupes ?></select> <select id="select_eleve" name="select_eleve"><option value=""></option></select> <label id="ajax_msg">&nbsp;</label>
	</fieldset>

	<hr />

	<fieldset id="fieldset_parents">
	</fieldset>
	<p id="p_valider" class="hide"><span class="tab"></span><button id="Enregistrer" type="button"><img alt="" src="./_img/bouton/valider.png" /> Enregistrer les modifications</button><label id="ajax_msg2">&nbsp;</label></p>

</form>

<script type="text/javascript">
	// <![CDATA[
	var select_parent="<?php echo str_replace('"','\"',$select_f_parents); ?>";
	// ]]>
</script>
