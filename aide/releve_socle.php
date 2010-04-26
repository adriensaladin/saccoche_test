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
$TITRE = "Attestation de maîtrise du socle commun";
?>
<h2>Introduction</h2>
<p>
	Les utilisateurs peuvent estimer des attestations de maîtrise du socle commun (l'accès peut être restreint pour les élèves).<br />
	Ces bilans peuvent être utilisés pour remplir le document officiel figurant dans le dossier de l'élève.
</p>
<ul class="puce">
	<li>Se connecter avec son compte.</li>
	<li>Dans <em>[Relevés de compétences]</em> menu <em>[Attestation de maîtrise du socle]</em>.</li>
</ul>

<h2>Préalable</h2>
<p>
	L'administrateur doit sélectionner le ou les paliers concernés, et les coordonnateurs de disciplines doivent relier, lorsqu'il y a lieu, les items de leur arborescence aux items du socle.
</p>

<h2>Réglages</h2>
<p>Voici différents paramètres accessibles :</p>
<ul class="puce">
	<li>attestation complète (3 pages) ou uniquement les intitulés (1 page)</li>
	<li>attestation vierge ou avec les états de validation</li>
	<li>le palier souhaité</li>
	<li>le ou les élèves concernés (sauf espace élève)</li>
</ul>

<h2>Attestations générées</h2>
Pour chaque item du socle, <em>SACoche</em> recherche tous les items associés des différentes matières qui y sont rattachés, il évalue leur état d'acquisition, et il fait la moyenne des scores obtenus. Puis <em>SACoche</em> compte le nombre d'items validés et il indique le pourcentage correspondant.
<ul class="puce">
	<li>Le format <em>HTML</em> permet d'avoir le détail des items disciplinaires évalués en cliquant sur l'icône <img alt="" src="./_img/toggle_plus.gif" /> à côté d'un item du socle.</li>
	<li>Le format <em>PDF</em> permet d'obtenir un fichier adapté à l'impression, proprement mis en page.</li>
</ul>

<h2>Documents officiels</h2>
<p>
	Voici des publications officielles au format <em>pdf</em> en rapport avec le socle commun :</span>
</p>
<ul class="puce">
	<li><a class="lien_ext" href="./_doc/socle_attestation_paliers1-2.pdf">Paliers 1&amp;2 - Attestation de maîtrise des connaissances et compétences du socle commun.</a></li>
	<li><a class="lien_ext" href="./_doc/socle_attestation_palier3_2009_12_03.pdf">Palier 3 - Attestation de maîtrise des connaissances et compétences du socle commun.</a></li>
	<li><a class="lien_ext" href="./_doc/socle_palier3_grilles_references.pdf">Palier 3 - Grilles de référence pour l'évaluation de la maîtrise des connaissances et compétences du socle commun.</a></li>
	<li><a class="lien_ext" href="./_doc/socle_palier3_bo_2007.pdf">Palier 3 - Bulletin Officiel avec le contenu de chacun des 7 piliers remis en page.</a></li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=referentiel_liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
</ul>

