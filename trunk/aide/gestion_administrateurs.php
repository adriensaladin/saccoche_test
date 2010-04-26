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
$TITRE = "Gestion des administrateurs";
?>

<h2>Introduction</h2>
<p>
	Les administrateurs gèrent les comptes administrateurs.<br />
	Le webmestre créé le premier compte administrateur, et peut aussi réinitialiser le mot de passe d'un administrateur.<br />
	<span class="astuce">Les comptent administrateurs ne sont pas reliables à une identification SSO, afin d'être toujours utilisables.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Administrateurs]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour ajouter un nouvel administrateur.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un administrateur présent.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_supprimer.png" /> pour enlever un administrateur présent.</li>
</ul>

<h2>Ajout d'administrateurs</h2>
<p>
	Lors de la mise en place de la base de l'établissement, un premier compte administrateur est automatiquement créé. Celui-ci a pour nom d'utilisateur <em>admin</em>.<br />
	<span class="astuce">Dans le cas d'une installation multi-structures, les identifiants de ce compte sont envoyés par courriel au référent de l'établissement.</span><br />
	Ensuite, le webmestre ne peut pas créer de nouveaux comptes ; ce sont les administrateurs qui gèrent ces comptes.
</p>

<h2>Modification de comptes administrateurs</h2>
<p>
	Les administrateurs peuvent modifier les informations des comptes, y compris les noms d'utilisateurs et les mots de passe.<br />
	<span class="astuce">Le webmestre peut aussi réinitialiser le mot de passe d'un administrateur.</span>
</p>

<h2>Suppression de comptes administrateurs</h2>
<p>
	Les administrateurs peuvent supprimer d'autres administrateurs, sauf leur propre compte. Cette restriction tend à éviter de se retrouver sans administrateur.<br />
	<span class="astuce">Pour changer d'administrateur unique, il suffit de lui transmettre les identifiants du compte.</span>
</p>
