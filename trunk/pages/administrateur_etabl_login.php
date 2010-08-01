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
$TITRE = "Format des noms d'utilisateurs";
?>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_format_logins">DOC : Format des noms d'utilisateurs</a></div>

<hr />

<form action=""><fieldset>
	<label class="tab" for="f_login_professeur">Format professeur :</label><input id="f_login_professeur" name="f_login_professeur" value="<?php echo $_SESSION['MODELE_PROF']; ?>" size="20" maxlength="20" /><br />
	<label class="tab" for="f_login_eleve">Format élève :</label><input id="f_login_eleve" name="f_login_eleve" value="<?php echo $_SESSION['MODELE_ELEVE']; ?>" size="20" maxlength="20" /><br />
	<span class="tab"></span><button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ces formats.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
