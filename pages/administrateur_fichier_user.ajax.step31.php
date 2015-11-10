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
if(!isset($STEP))       {exit('Ce fichier ne peut être appelé directement !');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 31 - Analyse des données des classes (sconet_professeurs_directeurs | sconet_eleves | base_eleves_eleves | factos_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
$tab_liens_id_base = load_fichier('liens_id_base');
$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
// On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
$tab_classes_fichier = load_fichier('classes');
// On récupère le contenu de la base pour comparer : $tab_classes_base['ref'] : id -> ref ; $tab_classes_base['nom'] : id -> nom
$tab_classes_base        = array();
$tab_classes_base['ref'] = array();
$tab_classes_base['nom'] = array();
$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
foreach($DB_TAB as $DB_ROW)
{
  $tab_classes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
  $tab_classes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
}
// Contenu du fichier à conserver
$lignes_ras = '';
foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
{
  $id_base = array_search($ref,$tab_classes_base['ref']);
  if($id_base!==FALSE)
  {
    if($mode=='complet')
    {
      $lignes_ras .= '<tr><th>'.html($tab_classes_base['ref'][$id_base]).'</th><td>'.html($tab_classes_base['nom'][$id_base]).'</td></tr>'.NL;
    }
    $tab_i_classe_TO_id_base[$i_classe] = $id_base;
    unset($tab_classes_fichier['ref'][$i_classe] , $tab_classes_fichier['nom'][$i_classe] ,  $tab_classes_fichier['niveau'][$i_classe] , $tab_classes_base['ref'][$id_base] , $tab_classes_base['nom'][$id_base]);
  }
}
// Contenu du fichier à supprimer
$lignes_del = '';
if(count($tab_classes_base['ref']))
{
  foreach($tab_classes_base['ref'] as $id_base => $ref)
  {
    $lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" /> '.html($tab_classes_base['nom'][$id_base]).'</td></tr>'.NL;
  }
}
// Contenu du fichier à ajouter
$lignes_add = '';
if(count($tab_classes_fichier['ref']))
{
  $select_niveau = '<option value="">&nbsp;</option>';
  $tab_niveau_ref = array();
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(FALSE /*with_particuliers*/);
  foreach($DB_TAB as $DB_ROW)
  {
    $select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
    $key = ( ($import_origine=='sconet') && ($import_profil=='eleve') ) ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
    $tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
  }
  foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
  {
    // On préselectionne un niveau :
    // - pour sconet_eleves                 on compare avec un masque d'expression régulière
    // - pour base_eleves_eleves            on compare avec les niveaux de SACoche
    // - pour sconet_professeurs_directeurs on compare avec le début de la référence de la classe
    // - pour tableur_eleves                on compare avec le début de la référence de la classe
    $id_checked = '';
    foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
    {
      if( ($import_origine=='sconet') && ($import_profil=='eleve') )
      {
        $id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_classes_fichier['niveau'][$i_classe])) ? $niveau_id : '';
      }
      elseif( ($import_origine=='base_eleves') && ($import_profil=='eleve') )
      {
        $id_checked = (mb_strpos($tab_classes_fichier['niveau'][$i_classe],$masque_recherche)===0) ? $niveau_id : '';
      }
      else
      {
        $id_checked = (mb_strpos(str_replace(' ','',$ref),$masque_recherche)===0) ? $niveau_id : '';
      }
      if($id_checked)
      {
        break;
      }
    }
    $nom_classe = ($tab_classes_fichier['nom'][$i_classe]) ? $tab_classes_fichier['nom'][$i_classe] : $ref ;
    $lignes_add .= '<tr><th><input id="add_'.$i_classe.'" name="add_'.$i_classe.'" type="checkbox" checked /> '.html($ref).'<input id="add_ref_'.$i_classe.'" name="add_ref_'.$i_classe.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i_classe.'" name="add_niv_'.$i_classe.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i_classe.'" name="add_nom_'.$i_classe.'" size="15" type="text" value="'.html($nom_classe).'" maxlength="20" /></td></tr>'.NL;
  }
}
// On enregistre (tableau mis à jour)
$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
// On affiche
echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des classes.</label></p>'.NL;
// Pour sconet_professeurs_directeurs, les groupes ne figurent pas forcément dans le fichier si les services ne sont pas présents -> on ne procède qu'à des ajouts éventuels.
if($lignes_del)
{
  echo'<p class="danger">Des classes non trouvées sont proposées à la suppression. Il se peut que les services / affectations manquent dans le fichier. Veuillez cochez ces suppressions pour les confirmer.</p>'.NL;
}
echo'<table>'.NL;
if($mode=='complet')
{
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Classes actuelles à conserver</th></tr>'.NL;
  echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucune</td></tr>'.NL;
  echo  '</tbody>'.NL;
}
echo  '<tbody>'.NL;
echo    '<tr><th colspan="2">Classes nouvelles à ajouter<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
echo($lignes_add) ? $lignes_add : '<tr><td colspan="2">Aucune</td></tr>'.NL;
echo  '</tbody>'.NL;
echo  '<tbody>'.NL;
echo    '<tr><th colspan="2">Classes anciennes à supprimer<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
echo($lignes_del) ? $lignes_del : '<tr><td colspan="2">Aucune</td></tr>'.NL;
echo  '</tbody>'.NL;
echo'</table>'.NL;
echo'<ul class="puce p"><li><a href="#step32" id="envoyer_infos_regroupements">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;

?>
