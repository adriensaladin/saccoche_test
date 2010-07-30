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
?>

<div class="hc">
	<?php
	$tab_menu = array();
	// Menu variable suivant le profil
	if(in_array($_SESSION['USER_PROFIL'],array('professeur','directeur','eleve')))
	{
		$tab_menu[] = '<a href="./index.php?page='.$PAGE.'&amp;section=grille">Grilles sur un niveau.</a>';
	}
	if(in_array($_SESSION['USER_PROFIL'],array('professeur','directeur','eleve')))
	{
		$tab_menu[] = '<a href="./index.php?page='.$PAGE.'&amp;section=matiere">Bilans sur une matière.</a>';
	}
	if($_SESSION['USER_PROFIL']=='professeur')
	{
		$tab_menu[] = '<a href="./index.php?page='.$PAGE.'&amp;section=selection">Bilans sur une sélection d\'items.</a>';
	}
	if(in_array($_SESSION['USER_PROFIL'],array('professeur','directeur')))
	{
		$tab_menu[] = '<a href="./index.php?page='.$PAGE.'&amp;section=multimatiere">Bilans transdisciplinaires (P.P.).</a>';
	}
	if(in_array($_SESSION['USER_PROFIL'],array('professeur','directeur','eleve')))
	{
		$tab_menu[] = '<a href="./index.php?page='.$PAGE.'&amp;section=socle">État de maîtrise du socle.</a>';
	}
	echo implode(' || ',$tab_menu);
	?>
</div>

<hr />

<?php
// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = './pages/'.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
	require($fichier_section);
	$PAGE = $PAGE.'_'.$SECTION ;
}
else
{
	echo'<p><span class="astuce">Choisissez une rubrique ci-dessus...</span></p>';
}
?>
