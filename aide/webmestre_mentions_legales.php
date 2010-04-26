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
$TITRE = "Informations concernant l'hébergement";
?>

<h2>Introduction</h2>
<p>
	En cas d'installation sur un serveur personnel (mono-structure ou multi-structures), la page d'accueil présente quelques informations relatives à l'hébergement.<br />
	Le webmestre peut modifier ces informations. Celles-ci sont enregistrées dans le dossier <em>/__hebergeur_info/</em>.
</p>
<ul class="puce">
	<li>Se connecter avec son compte webmestre.</li>
	<li>Menu <em>[Mentions légales]</em>.</li>
</ul>

<h2>Informations disponibles</h2>
<ul class="puce">
	<li>Nom du service d'hébergement : "Collège de Trucville", "Rectorat du paradis", etc.</li>
	<li>Logo de l'hébergeur : un logo peut être accolé au logo SACoche en page d'accueil (voir ci-dessous).</li>
	<li>Numéro CNIL : une déclaration CNIL est à affectuer, dans la catégorie "<a class="lien_ext" href="http://www.cnil.fr/vos-responsabilites/declarer-a-la-cnil/declarer-un-fichier/declaration/mon-secteur-dactivite/mon-theme/je-dois-declarer/declaration-selectionnee/dec-mode/DISPLAYSINGLEFICHEDECL/dec-uid/30/">Espace numérique de travail</a>". Indiquez dans le champ "n°12345678" ou laissez "non renseignée".</li>
	<li>Nom du webmestre : n'est affiché que dans l'espace du webmestre.</li>
	<li>Prénom du webmestre : n'est affiché que dans l'espace du webmestre.</li>
	<li>Courriel du webmestre : l'adresse est affichée codée pour éviter une récupération par des robots fouineurs malveillants.</li>
</ul>

<h2>Ajouter un logo</h2>
<p>
	Pour ajouter un logo en page d'accueil, il faut le transférer sur le serveur puis le sélectionner. Le logo de <em>SACoche</em> a une hauteur d'environ 70 pixels ; c'est mieux si le logo de l'hébergeur a une hauteur similaire.
</p>
<ul class="puce">
	<li>Cliquer sur <em>"Parcourir"</em> et indiquer un fichier image à charger sur le serveur ; les extensions autorisées sont <em>bmp</em>, <em>gif</em>, <em>jpg</em>, <em>jpeg</em>, <em>png</em>, <em>svg</em>. On peut en charger plusieurs, puis en effacer en cliquant sur <img alt="niveau" src="./_img/action/action_supprimer.png" />.</li>
	<li>Ensuite sélectionner un logo dans la liste déroulante (qui se met à jour automatiquement) du premier formulaire et valider.</li>
</ul>
