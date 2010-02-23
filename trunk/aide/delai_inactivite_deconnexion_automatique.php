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
$TITRE = "Délai d'inactivité et déconnexion automatique";
?>

<h2>Fonctionnement</h2>
<p>
	Dans chaque espace identifié figure en haut à droite un compteur dynamique <img alt="réveil" width="16" height="16" src="./_img/clock_fixe.png" /> indiquant le temps restant avant une deconnexion automatique pour inactivité.<br />
	Un changement de page, ou une validation quelconque d'un formulaire, est considéré comme une activité et remet le compteur au maximum. Ainsi, par exemple, valider régulièrement une saisie partielle des résultats d'une évaluation permet d'éviter toute déconnexion.<br />
	Lorsque le compteur arrive à moins de 5 minutes, il se met à clignoter <img alt="réveil" width="16" height="16" src="./_img/clock_anim.gif" /> et emet un léger signal sonore chaque minute.
</p>

<h2>Réglage par l'administrateur</h2>
<p>
	Par défaut, le compteur est initialisé pour 30 minutes ; l'administrateur peut paramétrer ce délai dans une fourchette allant de 10min à 90min.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Délai avant déconnexion]</em>.</li>
	<li>Sélectionner le délai souhaité et <em>[Valider]</em>.</li>
</ul>
