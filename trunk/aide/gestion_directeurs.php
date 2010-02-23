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
$TITRE = "Gestion des directeurs";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les directeurs et leurs affectations.<br />
	<span class="astuce">L'ajout d'un directeur peut se faire depuis la procédure d'importation Sconet / tableur ou manuellement.</span>
</p>

<h2>Créer / modifier / enlever des directeurs</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Directeurs]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouveau directeur.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un directeur présent.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_desactiver.png" /> pour enlever un directeur présent.</li>
</ul>
<p>Remarques sur les différents champs :</p>
<ul class="puce">
	<li>Les champs "<b>Id&nbsp;ENT</b>" et "<b>Id&nbsp;GEPI</b>" servent à réaliser des interconnexions (voir documentations correspondantes).</li>
	<li>Il est déconseillé de modifier les champs "<b>n°&nbsp;Sconet</b>" et "<b>Référence</b>", sauf en connaissance de cause (lors d'une inscription manuelle, ces champs peuvent être ignorés).</li>
	<li>Lors de l'ajout d'un nouveau directeur, un nom d'utilisateur et un mot de passe sont générés automatiquement : ne pas oublier de les noter.</li>
	<li>Un nom d'utilisateur peut être modifié sous réserve de disponibilité.</li>
	<li>Retirer un directeur ne le supprime pas de la base : son compte est simplement désactivé.</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_Sconet">DOC : Import professeurs / directeurs depuis Sconet</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_tableur">DOC : Import professeurs / directeurs avec un tableur</a></span></li>
</ul>
