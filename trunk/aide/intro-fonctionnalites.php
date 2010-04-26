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
$TITRE = "Fonctionnalités de SACoche";
?>

<p>
	On ne va pas ici entrer dans les détails, diverses documentations disponibles depuis les différents espaces permettant d'approfondir chaque fonctionnalité.
</p>

<p>
	A l'origine, <em>SACoche</em> est destiné à permettre au professeur d'évaluer ses élèves au sein de sa matière (quelle qu'elle soit), en utilisant les codes <img alt="" src="./_img/note/note_RR.gif" />, <img alt="" src="./_img/note/note_R.gif" />, <img alt="" src="./_img/note/note_V.gif" /> et <img alt="" src="./_img/note/note_VV.gif" />.<br />
	Pour cela, le programme d'une matière et d'un niveau donnés doit être scindé par les équipes disciplinaires en différents <b>items</b>, constituant <b>un référentiel</b>. L'élaboration de ce référentiel est libre ; elle peut se faire en ligne, mais on peut aussi partir d'un référentiel partagé par un autre établissement.<br />
	Remarque : bouleverser les pratiques d'évaluation n'est pas chose aisée, il est souhaitable que cela se fasse collectivement entre les professeurs d'une même discipline, et avec l'appui de la direction de l'établissement.
</p>

<p>
	En parallèle, les instructions officielles imposent désormais de compléter l'attestation de maîtrise du socle. <em>SACoche</em>, qui a en mémoire le contenu du socle commun, permet de relier les items des matières à ceux du socle commun, et ainsi de donner des indications de leur état de maîtrise par l'élève pour chaque rubrique.
</p>

<p>
	<em>SACoche</em> permet aussi d'associer à chaque item une ressource de remédiation, permettant alors à l'élève de travailler une notion non acquise.
</p>

<p>
	<em>SACoche</em> dispose de différents environnements :
</p>

<ul class="puce">
	<li>administrateur</li>
	<li>professeur</li>
	<li>élève</li>
	<li>personnel de direction</li>
</ul>

<p>
	L'<b>administrateur</b> gère les matières, les niveaux, les élèves, les professeurs, les classes, les groupes, les affectations, etc. Des procédures utilisant Sconet sont disponibles, et la connexion peut être établie avec certains ENT.<br />
	Le <b>professeur</b> peut gérer des groupes de besoin personnalisés (s'il le souhaite), créer des évaluations par compétences, en saisir les acquisitions, imprimer des cartouches associés, générer des grilles de compétences, établir des bilans de compétences, etc.<br />
	Le <b>professeur coordonnateur</b> peut en complément gérer les référentiels de compétences et leur contenu.<br />
	Le <b>professeur principal</b> peut en complément visualiser les bilans de toutes les matières de sa classe.<br />
	L'<b>élève</b> peut générer des grilles de compétences, établir des bilans de compétences, etc.
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=intro-pourquoi-competences">DOC : Pourquoi évaluer par compétences ?</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-origine">DOC : Origine du projet.</a></span></li>
	<li><span class="manuel">DOC : Fonctionnalités de <em>SACoche</em>.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-copies-ecran">DOC : Copies d'écran.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-documentations">DOC : Documentations spécifiques.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-avenir">DOC : Avenir de <em>SACoche</em>.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-pourquoi-nom">DOC : Pourquoi le nom <em>SACoche</em> ?</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-demonstration">DOC : Établissement de démonstration.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-inscription">DOC : Inscrire son établissement.</a></span></li>
</ul>
