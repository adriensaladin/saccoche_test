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
$TITRE = "Importer les professeurs et les directeurs";
?>

<?php
$nom_fin_fichier = (date('n')>7) ? date('Y') : date('Y')-1 ;
$nom_fin_fichier = $_SESSION['STRUCTURE_UAI'].'_'.$nom_fin_fichier;
?>

<p><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></p>

<ul id="step">
	<li id="step1">Étape 1 - fichier Sconet / STS-Web ou tableur : récupération</li>
	<li id="step2">Étape 2 - fichier Sconet / STS-Web ou tableur : traitement</li>
	<li id="step3">Étape 3 - importation des professeurs et des directeurs : paramétrage</li>
	<li id="step4">Étape 4 - importation des professeurs et des directeurs : résultat</li>
	<li id="step5">Étape 5 - confirmation / impression</li>
</ul>

<hr />

<form action="">
	<div id="ajax">
		<h2>Première méthode : fichier issu de Sconet / STS-Web</h2>
		<?php
		// Si le numéro UAI n'est pas renseigné, cette procédure ne peut pas être utilisée.
		if($_SESSION['STRUCTURE_UAI'])
		{
			echo'Cette méthode est fortement recommandée.<br />'."\r\n";
			echo'<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_professeurs_directeurs_Sconet">DOC : Import professeurs / directeurs depuis Sconet</a></span><br />'."\r\n";
			echo'Indiquez ci-dessous le fichier <b>sts_emp_'.$nom_fin_fichier.'.xml</b> (ou <b>sts_emp_'.$nom_fin_fichier.'.zip</b>) obtenu.'."\r\n";
		}
		else
		{
			echo'<label class="alerte">Le numéro UAI de l\'établissement n\'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>'."\r\n";
			echo'<ul class="puce">'."\r\n";
			echo'	<li><a href="./index.php?dossier='.$DOSSIER.'&amp;fichier=etabl&amp;section=identite">Compléter l\'identification de l\'établissement.</a></li>'."\r\n";
			echo'</ul>'."\r\n";
		}
		?>
		<p />
		<h2>Seconde méthode : fichier tableur</h2>
		Cette méthode n'est à utiliser que si l'établissement n'utilise pas SCONET (à l'étranger...).<br />
		<span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_professeurs_directeurs_tableur">DOC : Import professeurs / directeurs avec un tableur</a></span><br />
		Indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu.
		<p />
		<h2>Démarrer la procédure</h2>
		<label class="tab" for="f_submit_1">Fichier à importer :</label><input id="f_submit_1" type="button" value="Parcourir..." /><label id="ajax_msg">&nbsp;</label>
	</div>
</form>
