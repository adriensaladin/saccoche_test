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
$TITRE = "Gestion de l'identité de l'établissement";
?>

<h2>Introduction</h2>
<p>
	Les administrateurs peuvent renseigner / modifier certaines informations concernant l'établissement.<br />
	<span class="astuce">Si les informations sont déjà renseignées et sont correctes, alors ne les modifiez pas !</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Identité de l'établissement]</em>.</li>
</ul>

<h2>Dénomination</h2>
<p>
	Typiquement, ce champ est de la forme "CLG Gaston Lagaffe".
</p>
<ul class="puce">
	<li>Sur un hébergement de type <em>mono-structure</em>, cette dénomination est affichée sur la page d'identification.</li>
	<li>Sur un hébergement de type <em>multi-structures</em>, cette dénomination est seulement utilisée une fois connecté, car celle affichée sur la page d'identification est celle figurant dans la base du webmestre.</li>
</ul>

<h2>Numéro UAI (ex-RNE)</h2>
<p>
	Un code <em>UAI</em> (<em>U</em>nité <em>A</em>dministrative <em>I</em>mmatriculée) est attribué à chaque établissement d'enseignement français.<br />
	Il correspond à l'ancien code <em>RNE</em> (<em>R</em>épertoire <em>N</em>ational des <em>E</em>tablissements) depuis 1996.<br />
	Pour le trouver, on peut rechercher dans <a class="lien_ext" href="http://www.infocentre.education.fr/bce/">la Base Centrale des Etablissements (BCE)</a>.
</p>
<ul class="puce">
	<li>Ce code doit être renseigné pour pouvoir importer un fichier issu de Sconet / STS-Web.</li>
</ul>

<h2>Identifiant Sésamath et clef de contrôle</h2>
<p>
	Les serveurs hébergeant<em>SACoche</em> ont la possibilité de se connecter au serveur central pour partager des référentiels, ou de récupérer des référentiels partagés par d'autres. Ceci nécessite de pouvoir référencer les établissements concernés dans une base unique, et de pouvoir joindre leur(s) responsable(s) en cas de problème.
</p>
<ul class="puce">
	<li>Pour récupérer si besoin ces valeurs, <a class="lien_ext" href="http://competences.sesamath.net">s'inscrire au projet SACoche</a> puis en faire la demande.</li>
</ul>
