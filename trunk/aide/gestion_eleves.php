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
$TITRE = "Gestion des élèves";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les élèves et leurs affectations.<br />
	<span class="astuce">L'ajout d'un élève peut se faire depuis la procédure d'importation Sconet / tableur ou manuellement.</span>
</p>

<h2>Créer / modifier / enlever des élèves</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Élèves]</em> puis <em>[Élèves (gestion)]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouvel élève.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un élève présent.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_desactiver.png" /> pour enlever un élève présent.</li>
</ul>
<p>Remarques sur les différents champs :</p>
<ul class="puce">
	<li>Les champs "<b>Id&nbsp;ENT</b>" et "<b>Id&nbsp;GEPI</b>" servent à réaliser des interconnexions (voir documentations correspondantes).</li>
	<li>Il est déconseillé de modifier les champs "<b>n°&nbsp;Sconet</b>" et "<b>Référence</b>", sauf en connaissance de cause (lors d'une inscription manuelle, ces champs peuvent être ignorés).</li>
	<li>Lors de l'ajout d'un nouvel élève, un nom d'utilisateur et un mot de passe sont générés automatiquement : ne pas oublier de les noter.</li>
	<li>Un nom d'utilisateur peut être modifié sous réserve de disponibilité.</li>
	<li>Retirer un élève ne le supprime pas de la base : son compte est simplement désactivé.</li>
</ul>

<h2>Affecter les élèves aux classes</h2>
<p>
	Un ou plusieurs élèves, issus de différentes classes ou différents groupes, peuvent être affectés à une classe.<br />
	<span class="astuce">Un élève ne peut appartenir qu'à une unique classe.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Élèves]</em> menu <em>[Élèves &amp; classes]</em>.</li>
	<li>Sélectionner les élèves, les classes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="danger">La composition d'une classe devrait rarement être modifiée en cours d'année (les professeurs n'auront plus accès à certaines saisies) !</span></p>

<h2>Affecter les élèves aux groupes</h2>
<p>
	Un ou plusieurs élèves, issus de différentes classes ou différents groupes, peuvent être affectés à un ou plusieurs groupes.<br />
	<span class="astuce">Un élève peut être affecté à plusieurs groupes, mais à une seule classe.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Élèves]</em> menu <em>[Élèves &amp; groupes]</em>.</li>
	<li>Sélectionner les élèves, les groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="danger">La composition d'un groupe devrait rarement être modifiée en cours d'année (les professeurs n'auront plus accès à certaines saisies) !</span></p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=import_classes_eleves_Sconet">DOC : Import classes / élèves depuis Sconet</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=import_classes_eleves_tableur">DOC : Import classes / élèves avec un tableur</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=deplacer_eleve_durant_annee">DOC : Peut-on déplacer un élève en cours d'année ?</a></span></li>
</ul>
