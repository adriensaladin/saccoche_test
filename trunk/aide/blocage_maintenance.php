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
$TITRE = "Blocage de l'application";
?>

<h2>Introduction</h2>
<p>
	Le webmestre et les administrateurs peuvent bloquer l'accès à l'application, ou le rétablir.<br />
	En cas de blocage, un fichier est créé dans le dossier <em>/__hebergement_info/</em>.
</p>
<ul class="puce">
	<li>Se connecter avec son compte webmestre ou administrateur.</li>
	<li>Menu <em>[Blocage de l'application]</em>.</li>
</ul>

<h2>Blocage de l'application par le webmestre</h2>
<ul class="puce">
	<li>En cas de blocage de l'application par le webmestre, seul le webmestre peut se connecter, et les autres personnes déjà connectées ne peuvent plus naviguer.</li>
	<li>Cette fonctionnalité est notamment utile lors d'une mise à jour des fichiers.</li>
	<li>Cette fonctionnalité est utilisée automatiquement lors d'une mise à jour de la base, elle-même lancée automatiquement si besoin lorsqu'un utilisateur se connecte.</li>
</ul>

<h2>Blocage de l'application par un administrateur</h2>
<ul class="puce">
	<li>En cas de blocage de l'application par un administrateur, seul le webmestre et les administrateurs peuvent se connecter, et les autres personnes déjà connectées ne peuvent plus naviguer.</li>
	<li>Cette fonctionnalité est utilisée automatiquement lors d'une sauvegarde, d'une restauration, ou d'une mise à jour de la base.</li>
</ul>
