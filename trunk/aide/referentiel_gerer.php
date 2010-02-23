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
$TITRE = "Gérer les référentiels";
?>
<h2>Introduction</h2>
<p>
	Seuls <b>les professeurs coordonnateurs</b> ont accès à cette gestion.<br />
	Les autres professeurs peuvent uniquement consulter les référentiels de leurs disciplines.<br />
	Les personnels de direction peuvent consulter les référentiels de toutes les disciplines ; un tableau récapitulatif leur indique les absences de référentiels, ou les disciplines sans coordonnateur attribué.
</p>

<h2>Créer / importer un référentiel</h2>
<p>
	Si un référentiel de compétence est manquant, il est possible soit de créer un référentiel vierge, soit de partir d'un référentiel existant qu'un autre établissement aurait accepté de partager (c'est à dire de le rendre visible par les professeurs des autres établissements).
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Référentiels de compétences]</em> menu <em>[Gérer les référentiels]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> (si un référentiel est manquant).</li>
	<li><em>SACoche</em> va afficher la liste des référentiels partagés disponibles pour la matière et le niveau donnés.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_voir.png" /> pour voir le détail d'un référentiel.</li>
	<li>Il est possible de chercher un référentiel partagé parmi un autre niveau ou une autre matière.</li>
	<li>Sélectionner le référentiel de votre choix, ou un référentiel vierge, et valider.</li>
</ul>

<h2>Partager un référentiel</h2>
<p>
	On peut choisir de rendre publiques ou non les référentiels de compétences en cliquant sur <img alt="partager" src="./_img/action/action_referentiel_partager.png" />. Il y a plusieurs statuts possibles :
</p>
<ul class="puce">
	<li><img alt="niveau" src="./_img/partage1.gif" /> référentiel partagé : à utiliser pour tout référentiel novateur que l'on souhaite rendre visible.</li>
	<li><img alt="niveau" src="./_img/partage0.gif" /> référentiel non partagé car sans intérêt : à utiliser si on s'est inspiré d'un référentiel existant, peu ou pas modifié.</li>
	<li><img alt="niveau" src="./_img/partage0.gif" /> référentiel non partagé : soit par choix, soit parce qu'on ne le considère pas abouti.</li>
</ul>
<p>
	Seuls les référentiels d'une matière 'générale' peuvent être partagés. Dans le cas de la création d'une matière spécifique à l'établissement, la notion de partage est hors-sujet et ne peut pas être modifiée (icône <img alt="partager" src="./_img/action/action_referentiel_partager_non.png" />).
</p>

<h2>Mode de calcul associé à un référentiel</h2>
<p>
	L'administrateur a fixé les réglages par défaut, mais ceux-ci peuvent-être modifiés pour chaque référentiel par les coordonnateurs (icône <img alt="partager" src="./_img/action/action_referentiel_calculer.png" />).
</p>
<ul class="puce">
	<li>Concernant le mode de calcul, on peut soit <b>favoriser les évaluations récentes</b> (coefficients en progression arithmétique), soit <b>comptabiliser autant chaque évaluation</b> (moyenne classique non pondérée). Par défaut, quand un item est évalué plusieurs fois, les évaluations les plus récentes sont celles qui ont le plus d'importance. On autorise ainsi le droit à l'erreur en cours d'apprentissage, l'essentiel étant de valoriser l'acquisition finale (mais inversement, un élève qui régresse sera davantage pénalisé).</li>
	<li>Concernant les évaluations prises en compte, on peut soit les considérer toutes (par défaut), soit se restreindre à la dernière évaluation, soit se restreindre aux 2 ; 3 ; 4 ; 5 ; 6 ; 7 ; 8 ; 9 ; 10 ; 15 ; 20 ; 30 ; 40 ; 50 dernières évaluations.</li>
</ul>

<h2>Détruire un référentiel</h2>
<p>
	On peut supprimer un référentiel existant.<br />
	<span class="danger">Cette action supprime tout son contenu, ainsi que les résultats associés de tous les élèves. Un référentiel en cours d'utilisation ne devrait donc jamais être effacé.</span>
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation des compétences dans les référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_structure">DOC : Structure d'un référentiel.</a></span></li>
	<li><span class="manuel">DOC : Gérer les référentiels.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_modifier">DOC : Modifier le contenu des référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
