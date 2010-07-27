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
$TITRE = "Imposer identifiants SACoche";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__force_login_mdp_tableur">DOC : Imposer identifiants SACoche avec un tableur</a></span></p>

<form action="">
	<fieldset>
		Vous pouvez <button id="user_export" type="button"><img alt="" src="./_img/bouton/fichier_export.png" /> récupérer un fichier csv avec les noms / prénoms / logins actuels</button> (le mot de passe, crypté, ne peut être restitué).<p />
		Modifiez les identifiants souhaités, puis indiquez ci-dessous le fichier <b>nom-du-fichier.csv</b> (ou <b>nom-du-fichier.txt</b>) obtenu que vous souhaitez importer.
		<p><label class="tab" for="user_import">Envoyer le fichier :</label><button id="user_import" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></p>
	</fieldset>
</form>

<p><span class="astuce">Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Gérer les élèves</a>" ou "<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Gérer les professeurs</a>" ou "<a href="./index.php?page=administrateur_directeur">Gérer les directeurs</a>".</span></p>

<hr />
<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>
