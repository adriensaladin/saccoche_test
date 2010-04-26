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
$TITRE = "Intégration ENT académie Nantes";
?>

<h2>Introduction</h2>
<p>
	Si un établissement est inscrit à l'ENT <em>e-lyco</em> mis en place sur l'académie de <em>Nantes</em>, les utilisateurs peuvent se connecter à <em>SACoche</em> en SSO avec leurs identifiants de l'ENT.
</p>

<h2>Préalable</h2>
<p>L'administrateur doit avoir importé les utilisateurs dans <em>SACoche</em></p>
<ul class="puce">
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer élèves & classes]</em></li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer professeurs & directeurs]</em></li>
</ul>
<p>Des noms d'utilisateurs et des mots de passe seront alors générés par <em>SACoche</em> ; il ne seront pas utilisés si on choisit ensuite un mode de connexion lié à un ENT.</p>

<h2>Indiquer le mode d'identification</h2>
<p>
	L'administrateur doit indiquer que la connexion s'effectuera avec l'authentification de l'ENT.<br />
	<ul class="puce">
		<li>En administrateur de <em>SACoche</em>, il faut passer par le menu <em>[Paramétrages]</em> puis <em>[Mode d'identification]</em>, cocher le bon bouton et valider.</li>
		<li>En administrateur de <em>e-lyco</em>, il faut ajouter dans la gestion du Portail de l'ENT un service avec comme type de SSO <em>[SSO standard]</em>.</li>
	</ul>
</p>

<h2>Importer l'identifiant de l'ENT</h2>
<p>
	Lorsque l'utilisateur est connecté à l'ENT, le serveur d'authentification revoie un identifiant (pour <em>e-lyco</em> il s'agit numéro interne "uid"). <em>SACoche</em> doit le connaître pour établir la liaison.
</p>
<ul class="puce">
	<li>En administrateur de <em>e-lyco</em>, récupérer le fichier au format csv contenant les colonnes suivantes :</li>
</ul>
<table>
	<tbody>
		<tr><th>rne</th><th>uid</th><th>profil</th><th>prenom</th><th>nom</th><th>login</th><th>mdp</th></tr>
	</tbody>
</table>
<p />
<ul class="puce">
	<li>Se connecter avec son compte administrateur à <em>SACoche</em>.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer identifiant ent]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et transférer le fichier précédent.</li>
</ul>
<p>La comparaison se fait sur les noms et prénoms ; comme c'est le même fichier Sconet qui est utilisé pour l'ENT et <em>SACoche</em>, ils devraient correspondre (il peut falloir traiter manuellement quelques cas d'homonymies).</p>

<h2>Mise à jour en cours d'année</h2>
<p>En cours d'année les données peuvent être mises à jour manuellement ou de la même façon :</p>
<ul class="puce">
	<li>Import des utilisateurs dans l'ENT.</li>
	<li>Import des utilisateurs dans <em>SACoche</em>.</li>
	<li>Récupération du csv de l'ENT.</li>
	<li>Import du fichier csv de l'ENT dans <em>SACoche</em>.</li>
</ul>

<h2>Remarques diverses</h2>
<ul class="puce">
	<li>Les deux modes de connexion ne sont pas compatibles, il faut choisir entre utiliser l'identification de l'ENT ou des identifiants <em>SACoche</em>.</li>
	<li>L'administrateur de <em>SACoche</em> est alors le seul utilisateur qui accède à <em>SACoche</em> sans passer par l'identification de l'ENT.</li>
	<li>Pour se connecter en tant qu'élève ou professeur ou directeur, il suffit en page d'accueil de cliquer sur <em>[Accéder à son espace]</em> : toute la suite est gérée automatiquement.</li>
	<li>Lors d'une déconnexion de <em>SACoche</em>, l'utilisateur n'est pas déconnecté de l'ENT (volontairement), ce qui fait que n'importe qui peut de nouveau entrer dans <em>SACoche</em> à sa place tant que le navigateur n'est pas entièrement fermé.</li>
</ul>
