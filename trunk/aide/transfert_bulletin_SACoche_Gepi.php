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
$TITRE = "Transfert du bulletin de SACoche dans Gepi";
?>

<h2>Introduction</h2>
<p>
	Si vous utilisez le logiciel <a href="http://gepi.mutualibre.org/">GEPI</a> pour les bulletins trimestriels, <em>SACoche</em> permet d'y importer une note bilan chiffrée (sur 20) et un élément d'appréciation relatif à l'acquisition des compétences.<p />
	<b>L'administrateur doit avoir fait le nécessaire pour que les noms d'utilisateurs de GEPI coïncident avec ceux de <em>SACoche</em>.</b>
</p>

<p class="hc"><img alt="gepi_logo" src="./_img/aide/gepi_logo.png" /></p>

<h2>Démarche du professeur dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Menu <em>[Bilans sur une matière]</em>.</li>
	<li>Cocher <em>[Bulletin (moyenne & appréciation)]</em> et régler les autres paramètres.</li>
	<li>Cliquer sur <em>[Valider]</em>.</li>
	<li>Cliquer sur <em>[Bulletin au format CSV importable dans GEPI]</em>.</li>
</ul>
<p>On récupère un fichier avec l'extension <em>«&nbsp;csv&nbsp;»</em>.</p>

<h2>Démarche du professeur dans GEPI</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li><span class="u">Depuis un menu simplifié</span>, cliquer sur<img alt="gepi_bulletin" width="16" height="16" src="./_img/aide/gepi_bulletin.png" /> pour accéder aux moyennes d'un bulletin, puis <em>[Import/Export notes et appréciations]</em>.</li>
	<li><span class="u">Depuis un menu complet</span>, accéder aux bulletins, puis choisir son enseignement.</li>
	<li>Cliquer sur<img alt="gepi_import_notes_app" width="16" height="16" src="./_img/aide/gepi_import_notes_app.png" /> pour une importation d'un fichier CSV.</li>
	<li>Cliquer sur <em>[Parcourir...]</em>, indiquer le fichier récupéré de SACoche, puis cliquer sur <em>[Ouvrir]</em>.</li>
	<li>Si tout s'est bien passé, cliquer en bas du tableau sur <em>[Enregistrer les données]</em>.</li>
	<li>Si GEPI signale un ou plusieurs noms d'utilisateurs non concordants, signalez-le à l'administrateur (ou corrigez manuellement dans le fichier).</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=releve_matiere">DOC : Bilans sur une matière.</a></span></li>
</ul>
