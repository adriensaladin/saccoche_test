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
$TITRE = "Changer son mot de passe";

if( !in_array($_SESSION['USER_PROFIL_TYPE'],array('administrateur','webmestre','partenaire')) && !test_user_droit_specifique($_SESSION['DROIT_MODIFIER_MDP']) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_MODIFIER_MDP'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

if($_SESSION['CONNEXION_MODE']!='normal')
{
  echo'<p class="astuce">Le mode de connexion est configuré pour utiliser une authentification externe.<br />Ce formulaire ne modifiera pas le mode de passe correspondant, il ne concerne que le mot de passe propre à l\'application.</p><hr />'.NL;
}

// Javascript
$GLOBALS['HEAD']['js']['inline'][] = 'var MDP_LONGUEUR_MINI = '.$_SESSION['USER_MDP_LONGUEUR_MINI'].';';
?>


<p>Entrer le mot de passe actuel, puis deux fois le nouveau mot de passe choisi.</p>
<form action="#" method="post"><fieldset>
  <label class="tab" for="f_password0">Actuel :</label><input id="f_password0" name="f_password0" size="20" type="password" value="" /><br />
  <label class="tab" for="f_password1"><img alt="" src="./_img/bulle_aide.png" title="La robustesse du mot de passe indiqué dans ce champ est estimée ci-dessous." /> Nouveau 1/2 :</label><input id="f_password1" name="f_password1" size="20" type="password" value="" /><br />
  <label class="tab" for="f_password2">Nouveau 2/2 :</label><input id="f_password2" name="f_password2" size="20" type="password" value="" /><br />
  <span class="tab"></span><button id="bouton_valider" type="submit" class="mdp_perso">Valider le changement.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
<hr />
<p><span class="astuce">Un mot de passe est considéré comme robuste s'il comporte de nombreux caractères, mélangeant des lettres minuscules et majuscules, des chiffres et d'autres symboles.</span></p>
<div id="robustesse">indicateur de robustesse : <span>0</span> / 12</div>
