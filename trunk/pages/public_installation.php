<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Procédure d'installation";
$VERSION_JS_FILE += 1;
?>

<ul id="step">
	<li id="step1">Étape 1 - Vérification des droits d'accès en écriture des dossiers</li>
	<li id="step2">Étape 2 - Création de dossiers supplémentaires et de leurs droits</li>
	<li id="step3">Étape 3 - Remplissage de ces dossiers avec le contenu approprié</li>
	<li id="step4">Étape 4 - Choix du type d'installation</li>
	<li id="step5">Étape 5 - Informations concernant l'hébergement et le webmestre</li>
	<li id="step6">Étape 6 - Indication des paramètres de connexion MySQL</li>
	<li id="step7">Étape 7 - Installation des tables de la base de données</li>
</ul>

<hr />

<form action="" id="form0">
	<h2>Bienvenue dans la procédure d'installation de <em>SACoche</em> !</h2>
	<p class="astuce"><em>SACoche</em> est une web-application distribuée gratuitement dans l’espoir qu’elle vous sera utile, mais sans aucune garantie, conformément à la <a class="lien_ext" href="http://www.rodage.org/gpl-3.0.fr.html">licence libre GNU GPL3</a>.</p>
	<p class="danger">De plus, le webmestre et les administrateurs sont responsables de toute conséquence d'une mauvaise manipulation ou négligence de leur part.</p>
	<p><span class="tab"><a href="#" class="step1">Passer à l'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>
</form>
<form action="" id="form4">
</form>
<form action="" id="form5">
</form>
<form action="" id="form6">
</form>
