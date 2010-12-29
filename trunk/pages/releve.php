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
$TITRE = "Relevés &amp; Bilans";

$menu_items_selection = ($_SESSION['USER_PROFIL']=='professeur') ? '<a href="./index.php?page='.$PAGE.'&amp;section=items_selection">Bilan d\'items sélectionnés.</a> || ' : '' ;
$menu_synthese_socle  = ($_SESSION['USER_PROFIL']!='eleve') ? ' || <a href="./index.php?page='.$PAGE.'&amp;section=synthese_socle">Synthèse des validations du socle.</a>' : '' ;
?>

<div class="hc">
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=grille_referentiel">Grille d'items d'un référentiel.</a> || 
	<?php echo $menu_items_selection ?>
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=items_matiere">Bilan d'items d'une matière.</a> || 
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=items_multimatiere">Bilan d'items pluridisciplinaire.</a>
</div>
<div class="hc">
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=synthese_matiere">Synthèse d'une matière.</a> || 
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=synthese_multimatiere">Synthèse pluridisciplinaire.</a> || 
	<a href="./index.php?page=<?php echo $PAGE ?>&amp;section=socle">État de maîtrise du socle.</a>
	<?php echo $menu_synthese_socle ?>
</div>

<hr />

<?php
// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = './pages/'.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
	$PAGE = $PAGE.'_'.$SECTION ;
	require($fichier_section);
}
else
{
	echo'<p><span class="astuce">Choisissez une rubrique ci-dessus...</span></p>';
}
?>
