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
$TITRE = "Gestion des classes";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les classes et leurs affectations.<br />
	Chaque classe est rattachée à un niveau prédéfini.
</p>

<h2>Créer / modifier / supprimer des classes</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Classes]</em> puis <em>[Classes (gestion)]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer une nouvelle classe.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier une classe existante.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_supprimer.png" /> pour supprimer une classe existante.</li>
</ul>
<p>
	<span class="astuce">La <b>référence</b> d'une classe est le nom tel qu'il figure dans le fichier Sconet (ex : «&nbsp;6E&nbsp;1&nbsp;») : il est déconseillé d'y toucher.</span><br />
	<span class="astuce">Le <b>nom complet</b> est celui qui est utilisé dans les menus et lors de la génération de documents (ex : «&nbsp;6e&nbsp;Verdi&nbsp;»).</span><br />
	<span class="danger">Une classe comportant des élèves ne devrait pas être supprimée (sinon les professeurs n'auront plus accès à certaines saisies) !</span>
</p>

<h2>Affecter les élèves aux classes</h2>
<p>
	Un ou plusieurs élèves, issus de différentes classes ou différents groupes, peuvent être affectés à une classe.<br />
	<span class="astuce">Un élève ne peut appartenir qu'à une unique classe.</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Classes]</em> menu <em>[Élèves &amp; classes]</em>.</li>
	<li>Sélectionner les élèves, les classes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="danger">La composition d'une classe devrait rarement être modifiée en cours d'année (les professeurs n'auront plus accès à certaines saisies) !</span></p>

<h2>Affecter les professeurs aux classes</h2>
<p>
	Un ou plusieurs professeurs peuvent être affectés à une ou plusieurs classes.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Dans <em>[Classes]</em> menu <em>[Professeurs &amp; classes]</em>.</li>
	<li>Sélectionner les professeurs, les classes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="astuce">Les professeurs n'auront accès qu'aux élèves qui figurent dans les classes et groupes où ils sont affectés.</span></p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=gestion_groupes">DOC : Gestion des groupes.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=deplacer_eleve_durant_annee">DOC : Peut-on déplacer un élève en cours d'année ?</a></span></li>
</ul>
