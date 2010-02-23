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
$TITRE = "Établissement de démonstration";
?>

<p>
	Un établissement de démonstration est disponible. Il comporte 1 administrateur, 4 professeurs et 38 élèves affectés à deux classes de sixième. Des exemples de référentiels de compétences de niveau sixième ont été importés pour les matières français / mathématiques / histoire-géographie / anglais. Chaque professeur a créé une évaluation dont on peut simuler la saisie des résultats.<br />
	Voici les différents paramètres permettant de se connecter :
</p>

<ul class="puce">
	<li>administrateur : cocher la case / <b>admin</b></li>
	<li>professeur de français : <b>mpagnol</b> / <b>prof</b></li>
	<li>professeur de mathématiques : <b>bmandelbrot</b> / <b>prof</b></li>
	<li>professeur d'histoire-géographie  : <b>jmoulin</b> / <b>prof</b></li>
	<li>professeur d'anglais : <b>achristie</b> / <b>prof</b></li>
	<li>élève : noter en administrateur le nom d'utilisateur d'un élève, son mot de passe étant <b>eleve</b></li>
</ul>

<p>
	Toute action de nature à modifier la base de données est désactivée pour ces comptes de démonstration.<br />
	Par ailleurs, dans cette démonstration, il n'y a pas de notes saisies donc pas de bilan de compétences possible.
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=intro-pourquoi-competences">DOC : Pourquoi évaluer par compétences ?</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-origine">DOC : Origine du projet.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-fonctionnalites">DOC : Fonctionnalités de <em>SACoche</em>.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-copies-ecran">DOC : Copies d'écran.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-documentations">DOC : Documentations spécifiques.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-avenir">DOC : Avenir de <em>SACoche</em>.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-pourquoi-nom">DOC : Pourquoi le nom <em>SACoche</em> ?</a></span></li>
	<li><span class="manuel">DOC : Établissement de démonstration.</span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=intro-inscription">DOC : Inscrire son établissement.</a></span></li>
</ul>
