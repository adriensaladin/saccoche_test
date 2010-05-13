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
$TITRE = "Identité de l'installation";
?>

<h2>Introduction</h2>
<p>
	La page d'accueil présente quelques informations relatives à l'hébergement.<br />
	Le webmestre peut modifier ces informations. Celles-ci sont enregistrées dans le dossier <em>/__hebergement_info/</em>.
</p>
<ul class="puce">
	<li>Se connecter avec son compte webmestre.</li>
	<li>Menu <em>[Identité de l'installation]</em>.</li>
</ul>

<h2>Dénomination</h2>
<p>
	Cette dénomination est en particulier affichée sur la page d'accueil / d'identification.
</p>
<ul class="puce">
	<li>Pour une installation de type <em>mono-structure</em>, c'est le nom de l'établissement (par exemple "Collège Gaston Lagaffe").</li>
	<li>Pour une installation de type <em>multi-structures</em>, c'est le nom du service d'hébergement (par exemple "Rectorat du paradis").</li>
</ul>

<h2>Numéro UAI (ex-RNE)</h2>
<p>
	<p class="astuce">Ce numéro est demandé ici uniquement pour une installation de type <em>mono-structure</em>.</p>
	Un code <em>UAI</em> (<em>U</em>nité <em>A</em>dministrative <em>I</em>mmatriculée) est attribué à chaque établissement d'enseignement français.<br />
	Il correspond à l'ancien code <em>RNE</em> (<em>R</em>épertoire <em>N</em>ational des <em>E</em>tablissements) depuis 1996.<br />
	Pour le trouver, on peut rechercher dans <a class="lien_ext" href="http://www.infocentre.education.fr/bce/">la Base Centrale des Etablissements (BCE)</a>.
</p>
<ul class="puce">
	<li>Ce code doit être renseigné pour pouvoir importer un fichier issu de Sconet / STS-Web.</li>
</ul>

<h2>Adresse web</h2>
<p>
	Ce champ est facultatif.<br />
	Par exemple "http://www.college-trucville.com" pour une installation de type <em>mono-structure</em>, ou "http://www.ac-paradis.fr" pour une installation de type <em>multi-structures</em>.
</p>

<h2>Logo de l'hébergeur</h2>
<p>
	Un logo peut être accolé au logo SACoche en page d'accueil.<br />
	Pour ajouter un logo, il faut le transférer sur le serveur puis le sélectionner. Le logo de <em>SACoche</em> a une hauteur d'environ 70 pixels ; c'est mieux si le logo de l'hébergeur a une hauteur similaire.
</p>
<ul class="puce">
	<li>Cliquer sur <em>"Parcourir"</em> et indiquer un fichier image à charger sur le serveur ; les extensions autorisées sont <em>bmp</em>, <em>gif</em>, <em>jpg</em>, <em>jpeg</em>, <em>png</em>, <em>svg</em>. On peut en charger plusieurs, puis en effacer en cliquant sur <img alt="niveau" src="./_img/action/action_supprimer.png" />.</li>
	<li>Ensuite sélectionner un logo dans la liste déroulante du premier formulaire (qui se met à jour automatiquement) et valider.</li>
</ul>

<h2>Numéro CNIL</h2>
<p>
	Une déclaration CNIL est à affectuer, dans la catégorie "<a class="lien_ext" href="http://www.cnil.fr/vos-responsabilites/declarer-a-la-cnil/declarer-un-fichier/declaration/mon-secteur-dactivite/mon-theme/je-dois-declarer/declaration-selectionnee/dec-mode/DISPLAYSINGLEFICHEDECL/dec-uid/30/">Espace numérique de travail</a>".<br />
	Une fois la déclaration effectuée, indiquez par exemple dans le champ "n°12345678", ou laissez "non renseignée" sinon.
</p>

<h2>Coordonnées du webmestre</h2>
<ul class="puce">
	<li><em>Nom</em> : n'est affiché que dans l'espace du webmestre.</li>
	<li><em>Prénom</em> : n'est affiché que dans l'espace du webmestre.</li>
	<li><em>Courriel</em> : l'adresse est affichée codée pour éviter une récupération par des robots fouineurs malveillants.</li>
</ul>
