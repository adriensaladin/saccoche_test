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
$TITRE = "Données en consultation";

// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = './pages/'.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
	// Cas d'une section : afficher les liens puis appeler la section
	echo'<div class="hc noprint">';
	echo'	<a href="./index.php?page='.$PAGE.'&amp;section=algorithme">Algorithme de calcul</a>	<br />';
	echo'	<a href="./index.php?page='.$PAGE.'&amp;section=referentiel_interne">Référentiels en place (dans l\'établissement)</a>	';
	if($_SESSION['USER_PROFIL']!='eleve')
	{
		echo'||	<a href="./index.php?page='.$PAGE.'&amp;section=referentiel_externe">Référentiels partagés (serveur communautaire)</a>';
	}
	echo'<hr />';
	echo'</div>';
	$PAGE = $PAGE.'_'.$SECTION ;
	require($fichier_section);
}
else
{
	// Sinon, page d'accueil de la section
	echo'<p>Voici, pour information, les éléments auxquels vous pouvez accéder :</p>';
	echo'<ul class="puce">';
	echo'	<li><a href="./index.php?page='.$PAGE.'&amp;section=algorithme"><img alt="" src="./_img/menu/consultation_algorithme.png" /> Algorithme de calcul.</a></li>';
	echo'	<li><a href="./index.php?page='.$PAGE.'&amp;section=referentiel_interne"><img alt="" src="./_img/menu/consultation_referentiel_interne.png" /> Référentiels en place (dans l\'établissement).</a></li>';
	if($_SESSION['USER_PROFIL']!='eleve')
	{
		echo'	<li><a href="./index.php?page='.$PAGE.'&amp;section=referentiel_externe"><img alt="" src="./_img/menu/consultation_referentiel_externe.png" /> Référentiels partagés (serveur communautaire).</a></li>';
	}
	echo'</ul>';
}
?>
