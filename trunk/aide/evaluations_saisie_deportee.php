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
$TITRE = "Saisie déportée";
?>

<h2>Introduction</h2>
<p>
	Cette procédure peut être utilisée pour préparer une saisie de résultats sans être connecté, ou pour importer des scores qui aurait été obtenus avec un logiciel associé.<p />
</p>

<h2>Présentation</h2>
<p>
	Pour commencer il faut créer une évaluation, puis cliquer sur l'icône afin d'en saisir les résultats (<span class="manuel"><a href="./aide.php?fichier=evaluations_saisie_resultats">DOC : Saisie des résultats.</a></span>).<br />
	En dessous du tableau de saisie, cliquer sur <img src="./_img/toggle_plus.gif" alt="plus" /> pour faire apparaître le module.<br />
	Un lien permet de récupérer un fichier <em>csv</em> vierge à compléter, un bouton permet d'envoyer ce même fichier complété (il ne faut pas oublier de cliquer ensuite sur le lien pour enregistrer).
</p>

<h2>Format du fichier associé</h2>
<p>
	Les données respectent cette disposition :
</p>
<table id="simulation">
	<tbody>
		<tr><th class="nu"></th><th>Elève_id</th><th>Elève_id</th><th>Elève_id</th><th>...</th></tr>
		<tr><th>Item_id</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><th>Item_id</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><th>Item_id</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><th>...</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
	</tbody>
</table>
<p />
<ul class="puce">
	<li>Les dénominations des items et des élèves sont ajoutés en bout de lignes et de colonnes pour savoir à quoi correspondent les identifiants.</li>
	<li>Les codages des réponses suivent les conventions de <em>SACoche</em> (1 2 3 4 A N D).</li>
	<li>On peut ne saisir que certains résultats : seules les cases complétées seront prises en compte.</li>
	<li>L'ordre des colonnes ou des lignes peut être modifié, seuls les identifiants sont importants.</li>
</ul>

<h2>Créer le fichier soi-même</h2>
<ul class="puce">
	<li>Pour élaborer son fichier <em>csv</em> sans le logiciel, il faut connaitre les identifiants des élèves et des items. Ceux-ci peuvent être récupérés depuis le menu <em>[Export listings]</em>. Mais ceci ne dispensera pas de devoir créer l'évaluation.</li>
	<li>Dans son tableur, utiliser ensuite le menu <em>[Enregistrer sous...]</em> pour enregistrer le fichier au format <em>csv</em> (peu importe le séparateur).</li>
	<li><span class="danger">Le format <em>csv</em> n'enregistre qu'une feuille (la première, ou la feuille courante...) ; il faut donc avoir la liste de tous les élèves sur la même feuille avant d'exporter en <em>csv</em>.</span></li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=evaluations_gestion">DOC : Gestion des évaluations.</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=evaluations_saisie_resultats">DOC : Saisie des résultats.</a></span></li>
</ul>
