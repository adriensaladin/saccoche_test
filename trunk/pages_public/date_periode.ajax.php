<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {}

$champ = (isset($_GET['champ'])) ? $_GET['champ'] : '' ;
$debut = strpos($champ,'debut');
$fin   = strpos($champ,'fin');

$tab_periodes = DB_OPT_periodes_etabl();
$calendrier_affichage = '';
if(is_array($tab_periodes))
{
	foreach($tab_periodes as $tab_infos)
	{
		list($periode_debut,$periode_fin) = explode(' ',$tab_infos['optgroup']);
		$calendrier_affichage .= $debut ? '<a class="actu" href="'.convert_date_mysql_to_french($periode_debut).'">'.html($tab_infos['texte']).' [ debut ]</a><br />' : '' ;
		$calendrier_affichage .= $fin   ? '<a class="actu" href="'.convert_date_mysql_to_french($periode_fin).'">'.html($tab_infos['texte']).' [ fin ]</a><br />' : '' ;
	}
}
else
{
	$calendrier_affichage .= $tab_periodes;
}
echo'<h5>Périodes</h5>';
echo'<form id="form_calque" action="">';
echo'	<h6>Cliquer sur un lien :</h6>';
echo'	<p>'.$calendrier_affichage.'</p>';
echo'	<div><input class="but" type="button" name="fermer" value="Annuler et Fermer" /></div>';
echo'</form>';

?>
