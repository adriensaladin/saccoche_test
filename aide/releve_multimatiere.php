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
$TITRE = "Bilans transdisciplinaires (P.P.).";
?>
<h2>Introduction</h2>
<p>
	Les <b>professeurs principaux</b> et les personnels de direction, peuvent générer des bilans de compétences individuels, recensant les items travaillés dans toutes les matières de l'élève.<br />
	Ces bilans peuvent être utilisés comme synthèse trimestrielle, ou à l'occasion d'une rencontre parents / professeurs.<br />
	<em>SACoche</em> peut aussi générer une proposition de moyenne sur 20 (sauf compte élève).
</p>
<ul class="puce">
	<li>Se connecter avec son compte.</li>
	<li>Dans <em>[Relevés de compétences]</em> menu <em>[Bilans transdisciplinaires (P.P.)]</em>.</li>
</ul>

<h2>Réglages</h2>
<p>Voici différents paramètres accessibles :</p>
<ul class="puce">
	<li>coefficients associés aux items (affichage ou pas)</li>
	<li>appartenance au socle commun des items (affichage ou pas)</li>
	<li>liens de remédiation associés aux items (affichage ou pas)</li>
	<li>ligne de synthèse avec la moyenne des scores (affichage ou pas)</li>
	<li>ligne de synthèse avec le pourcentage d'acquisitions validées (affichage ou pas)</li>
	<li>proposition de note sur 20 (dans les lignes de synthèses)</li>
	<li>période considérée (les items pris en compte sont ceux qui sont évalués au moins une fois sur cette période, les dates limites étant incluses)</li>
	<li>prise en compte des évaluations antérieures (le bilan peut être établi uniquement sur la période considérée, ou en tenant compte d'évaluations antérieures des items concernés ; par exemple si un item a été testé 2 fois ce trimestre mais aussi 2 fois le précédent, on peut choisir de calculer le bilan à partir des deux seuls derniers devoirs ou des quatre derniers)</li>
	<li>le ou les élèves concernés</li>
</ul>
<p>Il existe des options de mise en page supplémentaires pour la sortie <em>pdf</em> :</p>
<ul class="puce">
	<li>format portrait ou paysage</li>
	<li>impression en couleur ou en noir et blanc</li>
	<li>marges minimales de 5mm / 10mm / 15mm</li>
	<li>nombre de cases pour y reporter les évaluations (de 1 à 10)</li>
	<li>largeur des cases (de 4mm à 16mm)</li>
	<li>hauteur des lignes (de 4mm à 16mm)</li>
</ul>
<p>
	<span class="astuce">Les évaluations prises en compte ne sont pas celles affichées mais celles qui satisfont aux critères de sélection. Par exemple si on choisit de n'afficher qu'une seule case mais qu'il y a trois évaluations concernées, les trois évaluations comptent pour le calcul du bilan.</span>
</p>

<h2>Relevés individuels générés</h2>
Pour chaque élève, pour chaque item travaillé, un score sur 100 est calculé, et un état d'acquisition est associé (acquis, non acquis, ou intermédiaire).
<ul class="puce">
	<li>Le format <em>HTML</em> permet de pouvoir cliquer sur les liens de remédiation, apporte des informations supplémentaires au survol d'un résultat avec la souris (nom et date de l'évaluation), et permet de trier les colonnes (cliquer sur les flèches).</li>
	<li>Le format <em>PDF</em> permet d'obtenir un fichier adapté à l'impression, proprement mis en page.</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
</ul>

