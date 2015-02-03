<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
$TITRE = Lang::_("Adresse e-mail &amp; Notifications");

$info_origine = '';
$info_edition = '';
$disabled = '';

if( $_SESSION['USER_EMAIL'] && $_SESSION['USER_EMAIL_ORIGINE'] )
{
  if($_SESSION['USER_EMAIL_ORIGINE']=='user')
  {
    $info_origine = '<span class="astuce">L\'adresse enregistrée a été saisie par vous-même.</span>';
  }
  else
  {
    $info_origine = '<span class="astuce">L\'adresse enregistrée a été importée ou saisie par un administrateur.</span>';
    if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_MODIFIER_EMAIL']) )
    {
      $info_edition = '<span class="astuce">Vous êtes habilité à modifier cette adresse si vous le souhaitez.</span>';
    }
    else
    {
      $info_edition = '<span class="danger">Vous n\'êtes pas habilité à modifier l\'adresse vous-même ! Veuillez contacter un administrateur.</span>';
      $disabled = ' disabled';
    }
  }
}
else
{
  $info_origine = '<span class="astuce">Il n\y a pas d\'adresse actuellement enregistrée.</span>';
}

?>

<p>
  <span class="astuce">Les adresses e-mail ne sont utilisées que par l'application et ne sont pas visibles des autres utilisateurs à l'exception des administrateurs.</span><br />
  <span class="astuce">Si vous avez plusieurs comptes <em>SACoche</em> (profils d'accès multiples...), ils ne peuvent pas être associés à la même adresse de courriel.</span>
</p>

<hr />

<h2>Adresse associée à votre compte</h2>

<p id="info_adresse">
  <?php echo $info_origine ?><br />
  <?php echo $info_edition ?>
</p>
<form action="#" method="post"><fieldset>
  <label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" type="text" value="<?php echo html($_SESSION['USER_EMAIL']); ?>" size="50" maxlength="63" /><br />
  <span class="tab"></span><input name="f_action" type="hidden" value="courriel" /><button id="bouton_valider" type="submit" class="mdp_perso"<?php echo $disabled ?>>Valider le changement.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>

<hr />

<h2>Abonnement aux notifications</h2>

<div class="travaux">Fonctionnalité en développement ; finalisation et documentation à venir prochainement&hellip;</div>
