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
$TITRE = "Procédure d'installation";
?>

<ul id="step">
	<li id="step1">Étape 1 - vérification / création des dossiers supplémentaires et de leurs droits</li>
	<li id="step2">Étape 2 - vérification / remplissage de ces dossiers avec le contenu approprié</li>
	<li id="step3">Étape 3 - vérification / indication des paramètres de connexion MySQL</li>
	<li id="step4">Étape 4 - vérification / installation de la base de données</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<div>
			<span class="astuce">Ce logiciel en phase de développement est mis à disposition à titre expérimental.</span><br />
			Une distribution sous licence libre de ce logiciel est prévue (échance à déterminer).
		</div>
		<p><span class="tab"><a href="#" class="step1">Passer à l'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>
	</div>
</form>
