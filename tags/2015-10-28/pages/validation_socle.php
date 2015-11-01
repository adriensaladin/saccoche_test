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
$TITRE = html(Lang::_("Validation du socle"));

// Sous-Menu d'en-tête
if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
{
  $SOUS_MENU = '';
  $tab_sous_menu = array(
    'langue'         => Lang::_("Choisir la langue étrangère"),
    'item'           => Lang::_("Valider les items du socle"),
    'pilier'         => Lang::_("Valider les compétences (piliers) du socle"),
    'pilier_annuler' => Lang::_("Annuler une compétence validée du socle"),
    'fichier'        => Lang::_("Import / Export de validations du socle"),
  );
  $tab_class_differente = array(
    'pilier_annuler' => 'socle_annuler',
  );
  if($_SESSION['USER_PROFIL_TYPE']!='directeur')
  {
    unset($tab_sous_menu['fichier']);
  }
  foreach($tab_sous_menu as $sous_menu_section => $sous_menu_titre)
  {
    // Pour ne pas avoir à faire une requête sur la base à chaque fois pour chaque sous-menu, on se sert de la chaîne du menu mis en session
    $sous_menu_class = isset($tab_class_differente[$sous_menu_section]) ? $tab_class_differente[$sous_menu_section] : 'socle_'.$sous_menu_section ;
    // Les élèves et les parents n'ont pas accès à tous les sous-menus
    if( strpos( $_SESSION['MENU'] , 'class="'.$sous_menu_class ) )
    {
      if( strpos( $_SESSION['MENU'] , 'class="'.$sous_menu_class.'"' ) )
      {
        $class = ($sous_menu_section==$SECTION) ? ' class="actif"' : '' ;
      }
      else
      {
        $class = ' class="disabled"';
      }
      $SOUS_MENU .= '<a'.$class.' href="./index.php?page='.$PAGE.'&amp;section='.$sous_menu_section.'">'.html($sous_menu_titre).'</a>'.NL;
    }
  }
}

// Afficher la bonne page et appeler le bon js / ajax par la suite
if($SECTION=='langue')
{
  $PAGE = 'administrateur_eleve';
}
$fichier_section = CHEMIN_DOSSIER_PAGES.$PAGE.'_'.$SECTION.'.php';
if(is_file($fichier_section))
{
  $PAGE = $PAGE.'_'.$SECTION ;
  require($fichier_section);
}
else
{
  echo'<p><span class="astuce">Choisir une rubrique dans le menu déroulant&hellip;</span></p>'.NL;
}
?>
