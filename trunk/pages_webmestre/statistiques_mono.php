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

list($prof_nb,$prof_use,$eleve_nb,$eleve_use,$score_nb) = DB_STRUCTURE_recuperer_statistiques();

?>

<ul id="result_mono" class="puce">
	<li>Il y a <b id="prof_nb"><?php echo $prof_nb ?></b> professeurs enregistrés, dont <b id="prof_use"><?php echo $prof_use ?></b> professeurs connectés.</li>
	<li>Il y a <b id="eleve_nb"><?php echo $eleve_nb ?></b> élèves enregistrés, dont <b id="eleve_use"><?php echo $eleve_use ?></b> élèves connectés.</li>
	<li>Il y a <b id="score_nb"><?php echo $score_nb ?></b> saisies enregistrées.</li>
</ul>

<hr />

<p id="expli">
	<span class="astuce">Concernant les <b>utilisateurs enregistrés</b>, seuls sont comptés ceux au statut "actif".</span><br />
	<span class="astuce">Les <b>utilisateurs connectés</b> sont ceux s'étant identifiés au cours du dernier semestre.</span><br />
	<span class="astuce">La date de dernière connexion n'étant mémorisée que depuis juin 2010, les identifications antérieures ne sont pas comptabilisées.</span>
</p>
