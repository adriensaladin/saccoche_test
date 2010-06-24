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
$TITRE = "Importer les élèves et les classes";
?>

<p><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></p>

<ul id="step">
	<li id="step1">Étape 1 - fichier Sconet ou tableur : récupération</li>
	<li id="step2">Étape 2 - fichier Sconet ou tableur : traitement</li>
	<li id="step3">Étape 3 - importation des classes : paramétrage</li>
	<li id="step4">Étape 4 - importation des classes : résultat</li>
	<li id="step5">Étape 5 - importation des élèves : paramétrage</li>
	<li id="step6">Étape 6 - importation des élèves : résultat</li>
	<li id="step7">Étape 7 - confirmation / impression</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<h2>Première méthode : fichier issu de Sconet</h2>
		Cette méthode est fortement recommandée.<br />
		<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_classes_eleves_Sconet">DOC : Import classes / élèves depuis Sconet</a></span><br />
		Indiquez ci-dessous le fichier <b>ExportXML_ElevesSansAdresses.zip</b> (ou <b>ElevesSansAdresses.xml</b>) obtenu.
		<h2>Seconde méthode : fichier tableur</h2>
		Cette méthode n'est à utiliser que si l'établissement n'utilise pas SCONET (à l'étranger...).<br />
		<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_classes_eleves_tableur">DOC : Import classes / élèves avec un tableur</a></span><br />
		Indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu.
		<h2>Démarrer la procédure</h2>
		<label class="tab" for="f_submit_1">Fichier à importer :</label><input id="f_submit_1" type="button" value="Parcourir..." /><label id="ajax_msg">&nbsp;</label>
	</div>
</form>
