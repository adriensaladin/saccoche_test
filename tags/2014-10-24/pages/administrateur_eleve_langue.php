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
$TITRE = "Choisir la langue étrangère pour le socle commun";

if( ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique( $_SESSION['DROIT_AFFECTER_LANGUE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  echo'<p class="danger">Vous n\'êtes pas habilité à accéder à cette fonctionnalité !</p>'.NL;
  echo'<div class="astuce">Profils autorisés (par les administrateurs) :</div>'.NL;
  echo afficher_profils_droit_specifique($_SESSION['DROIT_AFFECTER_LANGUE'],'li');
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues.php');
// Fonction adaptant le tableau pour un affichage dans un formulaire de type select avec des groupes d'options suivant que ces langues soient ou non enseignées dans l'établissement
function OPT_langues($tab_langues)
{
  $tab_optgroup = $tab_matiere_nom = array(); // pour array_multisort()
  $tab_matieres_enseignées = explode(',',DB_STRUCTURE_COMMUN::DB_recuperer_matieres_etabl());
  foreach($tab_langues as $id =>$tab)
  {
    $tab_langues[$id]['optgroup'] = ($id==100) ? 0 : ( count(array_intersect($tab_langues[$id]['tab_matiere_id'],$tab_matieres_enseignées)) ? 1 : 2 ) ;
    $tab_optgroup[$id]    = $tab_langues[$id]['optgroup'];
    $tab_matiere_nom[$id] = $tab_langues[$id]['texte'];
  }
  array_multisort(
    $tab_optgroup   , SORT_ASC,
    $tab_matiere_nom, SORT_ASC,
    $tab_langues
  );
  return $tab_langues;
}

// Fabrication des éléments select du formulaire
if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $tab_groupes = ($_SESSION['USER_JOIN_GROUPES']=='config') ? DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']) : DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl() ;
}
else // directeur ou administrateur
{
  $tab_groupes = DB_STRUCTURE_COMMUN::DB_OPT_regroupements_etabl();
}
$select_eleve  = Form::afficher_select($tab_groupes              , 'select_groupe' /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , 'regroupements' /*optgroup*/ );
$select_langue = Form::afficher_select(OPT_langues($tab_langues) , 'f_langue'      /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ ,       'langues' /*optgroup*/ );
?>

<p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__socle_choisir_langue">DOC : Choisir la langue étrangère pour le socle commun</a></span></p>

<hr />

<form action="#" method="post" id="form_select">
  <table><tr>
    <td class="nu" style="width:25em">
      <b>Élèves :</b><span class="check_multiple"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q><q class="cocher_inverse" title="Tout échanger."></q></span><br />
      <?php echo $select_eleve ?><br />
      <span id="f_eleve" class="select_multiple"></span>
    </td>
    <td class="nu" style="width:20em">
      <b>Langue :</b><br />
      <?php echo $select_langue; ?>
    </td>
    <td class="nu" style="width:25em">
      <button id="associer" type="button" class="parametre">Effectuer ces associations.</button>
      <p><label id="ajax_msg">&nbsp;</label></p>
    </td>
  </tr></table>
</form>

<div id="bilan">
</div>
