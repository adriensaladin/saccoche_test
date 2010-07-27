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
$TITRE = "Export listes des items par matière";

// Fabrication des éléments select du formulaire
if($_SESSION['USER_PROFIL']=='professeur')
{
	$select_matiere = afficher_select(DB_STRUCTURE_OPT_matieres_professeur($_SESSION['USER_ID']) , $select_nom='f_matiere' , $option_first='non' , $selection=false , $optgroup='non');
}
elseif($_SESSION['USER_PROFIL']=='directeur')
{
	$select_matiere = afficher_select(DB_STRUCTURE_OPT_matieres_etabl($_SESSION['MATIERES'],$transversal=true) , $select_nom='f_matiere' , $option_first='oui' , $selection=false , $optgroup='non');
}
?>

<div class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__export_listings">DOC : Export listings.</a></span></div>

<form action="" id="form_export"><fieldset>
	<label class="tab" for="f_matiere">Matière :</label><?php echo $select_matiere ?><input type="hidden" id="f_matiere_nom" name="f_matiere_nom" value="" /><br />
	<span class="tab"></span><button id="bouton_exporter" type="submit"><img alt="" src="./_img/bouton/fichier_export.png" /> Générer le listing</button><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>

<hr />

<div id="bilan">
</div>
