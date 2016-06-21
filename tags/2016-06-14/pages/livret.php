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
$TITRE = html(Lang::_("Livret Scolaire"));
?>

<div class="travaux">Interfaces en prévision de la réforme entrant en vigueur en septembre 2016. Les données seront ré-initialisées au changement d'année scolaire.</div>

<?php
// Sous-Menu d'en-tête
if($_SESSION['USER_PROFIL_TYPE']!='professeur')
{
  $SOUS_MENU = '';
  $tab_sous_menu = array(
    'accueil'   =>       Lang::_("Accueil"),
    'classes'   => '1) '.Lang::_("Classes"),
    'rubriques' => '2) '.Lang::_("Rubriques"),
    'seuils'    => '3) '.Lang::_("Seuils"),
    'epi'       => '4) '.Lang::_("E.P.I."),
    'ap'        => '5) '.Lang::_("A.P."),
    'parcours'  => '6) '.Lang::_("Parcours"),
    'modaccomp' => '7) '.Lang::_("Mod. accomp."),
    'edition'   => '8) '.Lang::_("Edition du livret"),
    'export'    => '9) '.Lang::_("Export LSUN"),
  );
  foreach($tab_sous_menu as $sous_menu_section => $sous_menu_titre)
  {
    $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
    $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.html($sous_menu_titre).'</a>'.NL;
  }
}

/*
// TODO : ENVIRONNEMENT EN TRAVAUX POUR QUELQUES MOIS...
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $SECTION = 'fiches';
}
*/

// Afficher la bonne page et appeler le bon js / ajax par la suite
$fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
  $PAGE = $PAGE.'_'.$SECTION ;
  require($fichier_section);
}
else
{
  echo'<p class="danger">Page disponible ultérieurement&hellip;</p>'.NL;
  // echo'<p class="danger">Page introuvable (paramètre manquant ou incorrect) !</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
?>
