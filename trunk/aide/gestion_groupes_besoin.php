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
$TITRE = "Gestion des groupes de besoin";
?>
<h2>Introduction</h2>
<p>
	L'administrateur de l'établissement doit affecter aux professeurs leurs classes et leurs groupes éventuels.<br />
	En complément, les professeurs peuvent se constituer des groupes de besoin supplémentaires, afin de faciliter l'évaluation d'un même ensemble d'élèves à plusieurs reprises.<br />
	<span class="astuce">L'utilisation de groupes de besoin n'est aucunement obligatoire ! Cette fonctionnalité est juste offerte à ceux qui en ont l'utilité...</span>
</p>

<h2>Créer / modifier / supprimer des groupes de besoin</h2>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Groupes de besoin]</em> menu <em>[Gérer les groupes de besoin]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouveau groupe de besoin.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un groupe de besoin existant.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_supprimer.png" /> pour supprimer un groupe de besoin existant.</li>
</ul>
<p><span class="danger">Un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être supprimé (sinon vous n'aurez plus accès à certaines saisies) !</span></p>

<h2>Affecter les élèves aux groupes de besoin</h2>
<p>
	Un ou plusieurs élèves, issus de différentes classes ou différents groupes, peuvent être affectés à un ou plusieurs groupes de besoin.
</p>
<ul class="puce">
	<li>Se connecter avec son compte professeur.</li>
	<li>Dans <em>[Groupes de besoin]</em> menu <em>[Élèves &amp; groupes de besoin]</em>.</li>
	<li>Sélectionner les élèves, les groupes, et choisir l'action correspondante.</li>
</ul>
<p>
	Appuyer sur la touche <em>Shift</em> tout en cliquant permet de sélectionner un intervalle d'éléments.<br />
	Appuyer sur la touche <em>Ctrl</em> tout en cliquant permet de sélectionner plusieurs éléments non contigus.
</p>
<p><span class="danger">La composition d'un groupe de besoin déjà utilisé lors d'une évaluation ne devrait pas être modifiée (sinon vous n'aurez plus accès à certaines saisies) : créer alors un nouveau groupe.</span></p>
