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
$TITRE = "Zones géographiques";
?>

<h2>Introduction</h2>
<p>
	<u>En cas d'installation de type multi-structures</u>, les établissements sont classés dans un formulaire en fonction de leur dénomination, et ils sont éventuellement regroupés par zones géographiques.<br />
	Le webmestre peut définir ces zones et leur ordre d'affichage.
</p>
<ul class="puce">
	<li>Se connecter avec son compte webmestre.</li>
	<li>Menu <em>[Zones géographiques]</em>.</li>
</ul>

<h2>Fonctionnement</h2>
<ul class="puce">
	<li>Les zones peuvent être créées, renommées, réordonnées et supprimées.</li>
	<li>L'identifiant est utilisé lors d'un import multiple de structures avec un fichier csv.</li>
	<li>Par défaut, il existe une unique zone (sans nom), à laquelle appartiennent toutes les structures. Cette zone, d'identifiant 1, peut être renommée et réordonnée mais ne peut pas être supprimée.</li>
</ul>
