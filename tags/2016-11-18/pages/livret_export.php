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
$TITRE = html(Lang::_("Livret Scolaire")).' &rarr; '.html(Lang::_("Export LSU"));

echo'<p class="travaux">Fonctionnalité en cours de développement (mise à disposition envisagée fin novembre 2016).</p>'.NL;
return; // Ne pas exécuter la suite de ce fichier inclus.

// Vérif UAI

if(!$_SESSION['WEBMESTRE_UAI'])
{
  $webmestre_menu_uai  = (HEBERGEUR_INSTALLATION=='multi-structures') ? '[Gestion des inscriptions] [Gestion des établissements]' : '[Paramétrages installation] [Identité de l\'installation]' ;
  echo'<p><label class="erreur">Référence non renseignée par le webmestre.</label> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_webmestre__identite_installation">DOC</a></span>&nbsp;&nbsp;&nbsp;'.HtmlMail::to(WEBMESTRE_COURRIEL,'SACoche - référence UAI','contact','Bonjour. La référence UAI de notre établissement (base n°'.$_SESSION['BASE'].') n\'est pas renseignée. Pouvez-vous faire le nécessaire depuis votre menu '.$webmestre_menu_uai.' ?').'</p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Tableaux utiles

$tab_puce_info = array();

$tab_periode_livret = array(
  'periodeT1' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 1/3' ),
  'periodeT2' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 2/3' ),
  'periodeT3' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Trimestre 3/3' ),
  'periodeS1' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 1/2'  ),
  'periodeS2' => array( 'used' => FALSE , 'defined' => FALSE , 'dates' => FALSE , 'nom' => 'Semestre 2/2'  ),
  'cycle'     => array( 'used' => FALSE , 'defined' => TRUE  , 'dates' => TRUE  , 'nom' => 'Fin de cycle'  ),
  // On n'affiche pas le bilan "Fin de collège" qui n'aura probablement pas lieu d'être (autre modif plus bas)
  // 'college'   => array( 'used' => FALSE , 'defined' => TRUE  , 'dates' => TRUE  , 'nom' => 'Fin du collège'),
);

$tab_etats = array
(
  '1vide'     => 'Vide (fermé)',
  '2rubrique' => 'Saisies Profs',
  '3mixte'    => 'Saisies Mixtes',
  '4synthese' => 'Saisie Synthèse',
  '5complet'  => 'Complet (fermé)',
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des jointures livret / classes / périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_LIVRET::DB_lister_jointures_classes_livret();
if(empty($DB_TAB))
{
  echo'<p><label class="erreur">Aucune association de classe au livret scolaire enregistrée !</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
$tab_page_ref = array();
$tab_join_classe_periode = array();
foreach($DB_TAB as $DB_ROW)
{
  // On n'affiche pas le bilan "Fin de collège" qui n'aura probablement pas lieu d'être (autre modif plus haut)
  if( $DB_ROW['livret_page_periodicite'] != 'college' )
  {
    $periode = $DB_ROW['livret_page_periodicite'].$DB_ROW['jointure_periode'];
    $tab_periode_livret[$periode]['used'] = TRUE;
    if($DB_ROW['periode_id'])
    {
      $tab_periode_livret[$periode]['defined'] = TRUE;
    }
    if( $DB_ROW['jointure_date_debut'] && $DB_ROW['jointure_date_fin'] )
    {
      $tab_periode_livret[$periode]['dates'] = TRUE;
    }
    $tab_join_classe_periode[$DB_ROW['groupe_id']][$periode] = array(
      'page_ref'      => $DB_ROW['livret_page_ref'],
      'etat'          => $DB_ROW['jointure_etat'],
      'rubrique_type' => $DB_ROW['livret_page_rubrique_type'],
      'periode_id'    => $DB_ROW['periode_id'],
      'date_debut'    => $DB_ROW['jointure_date_debut'],
      'date_fin'      => $DB_ROW['jointure_date_fin'],
      'date_verrou'   => $DB_ROW['jointure_date_verrou'],
      'date_export'   => $DB_ROW['jointure_date_export'],
    );
    $tab_page_ref[] = $DB_ROW['livret_page_ref'];
  }
}
$tab_periode_pb = array( 'undefined' => array() , 'pbdates' => 0 );
foreach($tab_periode_livret as $periode => $tab)
{
  if(!$tab['used'])
  {
    unset($tab_periode_livret[$periode]);
  }
  else if(!$tab['defined'])
  {
    $tab_periode_pb['undefined'][] = $tab['nom'];
  }
  else if(!$tab['dates'])
  {
    $tab_periode_pb['pbdates']++;
  }
}
if(!empty($tab_periode_pb['undefined']))
{
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode">Paramétrer les périodes.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_gestion_periodes">paramétrer les périodes</a></span>.' ;
  echo'<p><label class="erreur">Désignation des périodes pour le livret scolaire non effectuée pour "'.implode(' + ',$tab_periode_pb['undefined']).'" !'.$consigne.'</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}
if($tab_periode_pb['pbdates'])
{
  $s = ( $tab_periode_pb['pbdates'] > 1 ) ? 's' : '' ;
  $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_periode&section=classe_groupe">Effectuer les associations.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_periodes#toggle_affecter_periodes">effectuer les associations</a></span>.' ;
  echo'<p><label class="erreur">Association datée des périodes aux classes non effectuée pour '.$tab_periode_pb['pbdates'].' période'.$s.' !'.$consigne.'</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// Besoin de connaitre le chef d'établissement
if( count( array_intersect( $tab_page_ref , array('6e','5e','4e','3e','cycle1','cycle2','cycle3','cycle4') ) ) )
{
  $DB_ROW = DB_STRUCTURE_LIVRET::DB_recuperer_chef_etabl_infos($_SESSION['ETABLISSEMENT']['CHEF_ID']);
  if(empty($DB_ROW))
  {
    $consigne = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') ? ' <a href="./index.php?page=administrateur_etabl_identite">Identité de l\'établissement.</a>' : '<br />Un administrateur doit <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure#toggle_chef_etablissement">désigner cette personne</a></span>.' ;
    echo'<p><label class="erreur">Chef d\'établissement ou directeur d\'école non désigné !'.$consigne.'</label></p>'.NL;
    return; // Ne pas exécuter la suite de ce fichier inclus.
  }
  else
  {
    $tab_puce_info[] = '<li><span class="astuce">Le chef d\'établissement ou directeur d\'école désigné est '.To::texte_identite( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ).' (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_informations_structure#toggle_chef_etablissement">DOC</a></span>).</span></li>';
  }
}

// Vérif présence Id SIECLE
if( count( array_intersect( $tab_page_ref , array('6e','5e','4e','3e','cycle3','cycle4') ) ) )
{
  $nb_eleves_sans_sconet = DB_STRUCTURE_SOCLE::DB_compter_eleves_actuels_sans_id_sconet();
  if($nb_eleves_sans_sconet)
  {
    $s = ($nb_eleves_sans_sconet>1) ? 's' : '' ;
    $tab_puce_info[] = '<li><span class="danger">'.$nb_eleves_sans_sconet.' élève'.$s.' trouvé'.$s.' sans identifiant Sconet.</span> <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__import_users_sconet">DOC</a></span></li>';
  }
}

?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__reglages_livret_scolaire#toggle_export">DOC : Administration du Livret Scolaire &rarr; Export LSU</a></span></li>
  <li><span class="astuce">Numéro UAI d'établissement : <b><?php echo $_SESSION['WEBMESTRE_UAI']?></b></span></li>
  <?php echo implode('',$tab_puce_info); ?>
</ul>

<hr />

<h2>1/2 Récolter les données</h2>

<?php

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des classes de l'établissement.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);

$tab_classe = array();
foreach($DB_TAB as $DB_ROW)
{
  $tab_classe[$DB_ROW['valeur']] = $DB_ROW['texte'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Renseigner le contenu des jointures classes / périodes.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_affich = array();
$listing_classes_id = implode(',',array_keys($tab_classe));
$DB_TAB = DB_STRUCTURE_PERIODE::DB_lister_jointure_groupe_periode($listing_classes_id);
foreach($tab_periode_livret as $periode => $tab)
{
  foreach($tab_classe as $classe_id => $classe_nom)
  {
    $tab_join = isset($tab_join_classe_periode[$classe_id][$periode]) ? $tab_join_classe_periode[$classe_id][$periode] : NULL ;
    if(!$tab_join)
    {
      $tab_affich[$classe_id][$periode] = '<td class="hc notnow"">-</td>';
    }
    else
    {
      $etat = $tab_join['etat'];
      if($etat!='5complet')
      {
        $tab_affich[$classe_id][$periode] = '<td class="hc notnow">'.$tab_etats[$etat].'</td>';
      }
      elseif(!$tab_join['date_verrou'])
      {
        $tab_affich[$classe_id][$periode] = '<td class="hc notnow">Impression PDF non générée.</td>';
      }
      else
      {
        $info_export = ($tab_join['date_export']) ? 'Données récoltées le '.To::date_mysql_to_french($tab_join['date_export']).'.' : '<span class="danger">Données non récoltées.</span>' ;
        $page_ref = $tab_join['page_ref'];
        $id = '_'.$classe_id.'_'.$page_ref.'_'.$periode;
        $bouton_recolte = '<button id="ids'.$id.'" type="button" class="generer">Récolter les données</button>';
        $tab_affich[$classe_id][$periode] = '<td class="hc">'.$info_export.'<br />'.$bouton_recolte.'</td>';
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du tableau.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'<table id="table_accueil">'.NL;
echo'<thead><tr><td class="nu"></td>';
foreach($tab_periode_livret as $periode => $tab)
{
  echo'<th class="hc">'.$tab['nom'].'</th>';
}
echo'</tr></thead>'.NL;
echo'<tbody>'.NL;
foreach($tab_classe as $classe_id => $classe_nom)
{
  echo'<tr><th>'.html($classe_nom).'</th>'.implode('',$tab_affich[$classe_id]).'</tr>'.NL;
}
echo'</tbody></table>'.NL;

// Javascript
Layout::add( 'js_inline_before' , 'var TODAY_FR = "'.TODAY_FR.'";' );
?>
