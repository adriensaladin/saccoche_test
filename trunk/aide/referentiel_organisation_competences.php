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
$TITRE = "Organisation des compétences dans les référentiels";
?>
<h2>Vocabulaire</h2>
On distingue différents niveaux et types de <i>"compétences"</i> :
<ul class="puce">
	<li>les <b>savoirs</b> ou <b>connaissances</b> (exemple : réciter une propriété)</li>
	<li>les <b>savoirs-faire</b> ou <b>capacités</b> (exemple : utiliser une propriété, application directe ou situation un peu habillée)</li>
	<li>les <b>savoirs-être</b> ou <b>attitudes</b> (exemple : manifester curiosité, créativité, motivation)</li>
</ul>
<p>
	Une <b>compétence</b> est <i>«&nbsp;l'aptitude à mobiliser un ensemble de ressources (connaissances, capacités et attitudes) adaptées dans une situation complexe et authentique.&nbsp;»</i>. C'est donc une notion complexe...
</p>

<h2>Que mettre dans les référentiels ?</h2>
<p>
	Tout dépend de l'usage envisagé !
</p>
Une grille de savoirs et savoirs-faire a ses avantages :
<ul class="puce">
	<li>possibilité d'un bilan par matière, avec suppression de la note chiffrée intermédiaire</li>
	<li>permet un suivi des acquisitions des élèves au quotidien</li>
	<li>plus concret, plus facile, plus motivant pour l'élève</li>
	<li>possibilité de remédiation fine, item par item</li>
</ul>
<p>
	Mais une telle grille ne doit pas se suffire à elle-même : aujourd'hui la demande institutionnelle est plus orientée en terme de compétences qu'en grand nombre de savoirs et savoirs-faire ; estimer l'acquisition du socle ne se fait pas par accumulation de savoirs et savoirs-faire, mais à la capacité de mobiliser des ressources notamment pour résoudre des problèmes ou de mener à bien des tâches complexes.<br />
	À l'opposé, une grille de compétences a ses défauts ; l'attestation de maîtrise du socle n'est pas un outil opérationnel de suivi de l'élève au cours de sa scolarité.
</p>

<h2>Comment organiser les référentiels ?</h2>
<p>
	SACoche a été conçu pour suivre les acquisitions des élèves au quotidien au sein de chaque matière et chaque niveau.<br />
	Afin de prendre en compte des items transversaux ou interdisciplinaires, il existe aussi un niveau global (correspondant à un palier) et une matière transversale (automatiquement affectée à tous les professeurs).
</p>
<ul class="puce">
	<li>Les référentiels <b>par matière</b> et <b>par niveau</b> sont adaptés pour des savoirs et/ou savoirs-faire précis, attachés à un programme donné. Par exemple l'item "savoir mesurer un angle".</li>
	<li>Les référentiels <b>par matière</b> et rattachés à <b>un palier</b> sont adaptés pour des capacités génériques et/ou des attitudes, attachées à une matière donnée. Par exemple l'item "savoir lire une carte géographique" ne doit pas être mis sur chaque niveau (items déconnectés non réévalués), mais référencé une unique fois sur un palier.</li>
	<li>Les référentiels de la <b>matière "transversale"</b>, obligatoirement rattachés à <b>un palier</b> sont adaptés pour des capacités génériques et/ou des attitudes interdisciplinaires. Par exemple l'item "s'impliquer dans un projet individuel ou collectif.".</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
	<li><span class="manuel">DOC : Organisation des compétences dans les référentiels.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_structure">DOC : Structure d'un référentiel.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_gerer">DOC : Gérer les référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_modifier">DOC : Modifier le contenu des référentiels.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
</ul>
