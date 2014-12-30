<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = Lang::_("Choisir sa langue");

// Charger $tab_langues_traduction
require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_traduction.php');
// Formulaire SELECT du choix de la langue
// On commence par une première option qui correspond au choix de l'établissement
$selected = (empty($_SESSION['USER_LANGUE'])) ? ' selected' : '' ;
$langue_nom = array_search( $_SESSION['ETABLISSEMENT']['LANGUE'] , $tab_langues_traduction );
$options_langue  = '<optgroup label="Langue par défaut dans l\'établissement">';
$options_langue .=   '<option value="defaut"'.$selected.'>'.$langue_nom.' ['.$_SESSION['ETABLISSEMENT']['LANGUE'].']</option>';
$options_langue .= '</optgroup>';
// On continue avec les langues sélectionnables
$options_langue .= '<optgroup label="Autres langues sélectionnables">';
foreach($tab_langues_traduction as $langue_nom => $langue_code)
{
  $selected = ($langue_code==$_SESSION['USER_LANGUE']) ? ' selected' : '' ;
  $options_langue .= '<option value="'.$langue_code.'"'.$selected.'>'.$langue_nom.' ['.$langue_code.']</option>';
}
$options_langue .= '</optgroup>';
?>

<form action="#" method="post"><fieldset>
  <label class="tab" for="f_langue">Langue :</label><select id="f_langue" name="f_langue"><?php echo $options_langue; ?></select><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="parametre">Valider.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
<div class="travaux">Fonctionnalité en développement ; finalisation et documentation à venir prochainement&hellip;</div>
