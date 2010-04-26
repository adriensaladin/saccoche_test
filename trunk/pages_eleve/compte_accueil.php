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
$TITRE = "Bienvenue dans votre espace identifié !";
?>

<img id="look_menu" src="./_img/fleche_h1.gif" alt="look !" />

<ul class="puce">
	<li><span class="astuce">En haut à gauche, le bouton <img src="./_img/menu.gif" alt="Menu" /> se développe au survol de la souris et permet de naviguer dans son espace.</span></li>
	<li><span class="astuce">En haut à droite, le bouton <img src="./_img/action/action_deconnecter.png" alt="" /> permet de se déconnecter.</span></li>
</ul>

<hr />

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=ergonomie_generale">DOC : Ergonomie générale.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=environnement_eleve">DOC : L'environnement élève.</a></span></li>
</ul>

<hr />

<div>
	<span class="astuce">Pour que votre établissement soit automatiquement sélectionné depuis n'importe quel ordinateur, utilisez l'adresse <b><?php echo SERVEUR_ADRESSE.'?id='.$_SESSION['BASE'] ?></b></span><br />
</div>

