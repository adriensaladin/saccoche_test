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
$TITRE = "L'environnement coordonnateur";
?>
<h2>Introduction</h2>
<p>
	Le professeur coordonnateur se connecte avec son compte professeur normal (voir la documentation correspondante).<br />
	Il a dans son menu la possibilité supplémentaire de gérer les référentiels de compétences, et leur contenu.
</p>

<h2>Préalable</h2>
<p>Pour que le professeur coordonnateur puisse travailler, il faut auparavant que l'administrateur ait effectué les réglages nécessaires :</p>
<ul class="puce">
	<li>choix des matières utilisées</li>
	<li>choix des paliers du socle commun utilisés</li>
	<li>importation des professeurs</li>
	<li>affectation des professeurs aux matières</li>
	<li>désignation des professeurs coordonnateurs</li>
</ul>

<h2>Avertissement</h2>
<p>
	<span class="danger">Le compte de professeur coordonnateur est sensible</span>, puisqu'il permet d'effacer des référentiels, avec tous les scores des élèves associés aux items concernés !<br />
	Il doit donc être utilisé avec sagesse et prudence...
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel">DOC : L'environnement professeur coordonnateur.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation des compétences dans les référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_structure">DOC : Structure d'un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_gerer">DOC : Gérer les référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_modifier">DOC : Modifier le contenu des référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
