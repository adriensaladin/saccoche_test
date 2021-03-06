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
$TITRE = html(Lang::_("Menus et raccourcis"));

// récupère $tab_menu & $tab_sous_menu
require(CHEMIN_DOSSIER_MENUS.'menu_'.$_SESSION['USER_PROFIL_TYPE'].'.php');

$tab_memo_menu   = explode(',',$_SESSION['USER_PARAM_MENU']);
$tab_memo_favori = explode(',',$_SESSION['USER_PARAM_FAVORI']);

$form_menu = '<ul class="puce">'.NL;
foreach($tab_menu as $menu_id => $menu_titre)
{
  $checked_menu = !in_array($menu_id,$tab_memo_menu) ? ' checked' : '' ;
  $form_menu .= '<li><label for="menu_'.$menu_id.'"><input type="checkbox" id="menu_'.$menu_id.'" name="menu_'.$menu_id.'" value="1"'.$checked_menu.' /> '.html($menu_titre).'</label><ul>'.NL;
  foreach($tab_sous_menu[$menu_id] as $sous_menu_id => $tab)
  {
    $checked_sousmenu = ( $checked_menu && !in_array($sous_menu_id,$tab_memo_menu ) ) ? ' checked' : '' ;
    $checked_favori   = ( $checked_menu && in_array($sous_menu_id,$tab_memo_favori) ) ? ' checked' : '' ;
    $form_menu .= '<li><label for="sousmenu_'.$sous_menu_id.'"><input type="checkbox" id="sousmenu_'.$sous_menu_id.'" name="sousmenu_'.$sous_menu_id.'" value="1"'.$checked_sousmenu.' /> <input type="checkbox" id="favori_'.$sous_menu_id.'" name="favori_'.$sous_menu_id.'" value="1"'.$checked_favori.' /> '.html($tab['texte']).'</label></li>'.NL;
  }
  $form_menu .= '</ul></li>'.NL;
}
$form_menu .= '</ul>'.NL;

// Info sur l'origine du réglage en cours
$tab_info_origine = array(
  'aucun' => Lang::_("Aucun réglage n'a encore été enregistré."),
  'admin' => Lang::_("Le réglage actuel a été enregistré par un administrateur."),
  'prof'  => Lang::_("Le réglage actuel a été enregistré par vous-même."),
);
$info_origine = html($tab_info_origine[$_SESSION['USER_PARAM_ORIGINE']]);

?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__menus_raccourcis">DOC : Menus et raccourcis</a></span></li>
</ul>

<hr />

<div class="astuce">Consulter la documentation (lien ci-dessus) pour toute explication !</div>
<div class="astuce"><?php echo $info_origine ?></div>

<hr />

<form action="#" method="post" id="form_menu">
  <?php echo $form_menu ?>
  <p>
    <span class="tab"></span><input id="f_profil" name="f_profil" type="hidden" value="<?php echo $_SESSION['USER_PROFIL_TYPE'] ?>" /><button id="bouton_valider" type="button" class="valider">Valider.</button><label id="ajax_msg">&nbsp;</label>
  </p>
</form>
