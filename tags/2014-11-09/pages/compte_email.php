<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Changer son adresse e-mail";

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>

<p><span class="astuce">Les adresses e-mail ne sont utilisées que par l'application et ne sont pas visibles des autres utilisateurs à l'exception des administrateurs.</span></p>
<p><span class="astuce">Si vous avez plusieurs comptes <em>SACoche</em> (profils d'accès multiples...), ils ne peuvent pas être associés à la même adresse de courriel.</span></p>
<hr />

<form action="#" method="post"><fieldset>
  <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="<?php echo html($_SESSION['USER_EMAIL']); ?>" size="50" maxlength="63" /><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="mdp_perso">Valider le changement.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
<div class="travaux">Fonctionnalité en développement ; finalisation et documentation à venir prochainement&hellip;</div>
