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

$type_export = (isset($_POST['f_type']))        ? Clean::texte($_POST['f_type'])        : '';
$groupe_type = (isset($_POST['f_groupe_type'])) ? Clean::texte($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? Clean::texte($_POST['f_groupe_nom'])  : '';
$groupe_id   = (isset($_POST['f_groupe_id']))   ? Clean::entier($_POST['f_groupe_id'])  : 0;
$matiere_id  = (isset($_POST['f_matiere']))     ? Clean::entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? Clean::texte($_POST['f_matiere_nom']) : '';
$palier_id   = (isset($_POST['f_palier']))      ? Clean::entier($_POST['f_palier'])     : 0;
$palier_nom  = (isset($_POST['f_palier_nom']))  ? Clean::texte($_POST['f_palier_nom'])  : '';

$tab_types   = array('d'=>'all' , 'n'=>'niveau' , 'c'=>'classe' , 'g'=>'groupe' , 'b'=>'besoin');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données des élèves d'une classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='listing_eleves') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'GROUPE'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead><tr><th>Id</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Groupe</th></tr></thead><tbody>'."\r\n";
  // Récupérer les élèves de la classe ou du groupe
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'user_id,user_login,user_nom,user_prenom' );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $export_csv  .= $DB_ROW['user_id'].$separateur.$DB_ROW['user_login'].$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$groupe_nom."\r\n";
      $export_html .= '<tr><td>'.$DB_ROW['user_id'].'</td><td>'.html($DB_ROW['user_login']).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td>'.html($groupe_nom).'</td></tr>'."\r\n";
    }
  }

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_listing-eleves_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'."\r\n";

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les données (fichier <em>csv</em> zippé.</span>)</a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données des items d'une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='listing_matiere') && $matiere_id && $matiere_nom )
{
  Form::save_choix('export_fichier');
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'ITEM_ID'.$separateur.'MATIERE'.$separateur.'NIVEAU'.$separateur.'REFERENCE'.$separateur.'NOM'.$separateur.'COEF'.$separateur.'DEMANDE_EVAL'.$separateur.'LIEN'.$separateur.'SOCLE'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead><tr><th>Id</th><th>Matière</th><th>Niveau</th><th>Référence</th><th>Nom</th><th>Coef</th><th>Demande</th><th>Lien</th><th>Socle</th></tr></thead><tbody>'."\r\n";

  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , TRUE /*only_item*/ , TRUE /*socle_nom*/ );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $item_ref = $DB_ROW['matiere_ref'].'.'.$DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'];
      $demande_eval = ($DB_ROW['item_cart']) ? 'oui' : 'non' ;
      $export_csv .= $DB_ROW['item_id'].$separateur.$matiere_nom.$separateur.$DB_ROW['niveau_nom'].$separateur.$item_ref.$separateur.'"'.$DB_ROW['item_nom'].'"'.$separateur.$DB_ROW['item_coef'].$separateur.$demande_eval.$separateur.'"'.$DB_ROW['item_lien'].'"'.$separateur.'"'.$DB_ROW['entree_nom'].'"'."\r\n";
      $export_html .= '<tr><td>'.$DB_ROW['item_id'].'</td><td>'.html($matiere_nom).'</td><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($item_ref).'</td><td>'.html($DB_ROW['item_nom']).'</td><td>'.html($DB_ROW['item_coef']).'</td><td>'.html($demande_eval).'</td><td>'.html($DB_ROW['item_lien']).'</td><td>'.html($DB_ROW['entree_nom']).'</td></tr>'."\r\n";
    }
  }
  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_listing-items_'.Clean::fichier($matiere_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'."\r\n";

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les données (fichier <em>csv</em> zippé).</span></a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV de l'arborescence des items d'une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='arbre_matiere') && $matiere_id && $matiere_nom )
{
  Form::save_choix('matiere');
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'MATIERE'.$separateur.'NIVEAU'.$separateur.'DOMAINE'.$separateur.'THEME'.$separateur.'ITEM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<div id="zone_matieres_items" class="arbre_dynamique p">';

  $tab_niveau  = array();
  $tab_domaine = array();
  $tab_theme   = array();
  $tab_item    = array();
  $niveau_id = 0;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , FALSE /*socle_nom*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['niveau_id']!=$niveau_id)
    {
      $niveau_id = $DB_ROW['niveau_id'];
      $tab_niveau[$niveau_id] = $DB_ROW['niveau_ref'].' - '.$DB_ROW['niveau_nom'];
      $domaine_id = 0;
      $theme_id   = 0;
      $item_id    = 0;
    }
    if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
    {
      $domaine_id = $DB_ROW['domaine_id'];
      $tab_domaine[$niveau_id][$domaine_id] = $DB_ROW['domaine_ref'].' - '.$DB_ROW['domaine_nom'];
    }
    if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
    {
      $theme_id = $DB_ROW['theme_id'];
      $tab_theme[$niveau_id][$domaine_id][$theme_id] = $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - '.$DB_ROW['theme_nom'];
    }
    if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
    {
      $item_id = $DB_ROW['item_id'];
      $tab_item[$niveau_id][$domaine_id][$theme_id][$item_id] = $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'];
    }
  }
  $export_csv .= $DB_ROW['matiere_ref'].' - '.$matiere_nom."\r\n";
  $export_html .= '<ul class="ul_m1">'."\r\n";
  $export_html .= '  <li class="li_m1"><span>'.html($DB_ROW['matiere_ref'].' - '.$matiere_nom).'</span>'."\r\n";
  $export_html .= '    <ul class="ul_m2">'."\r\n";
  foreach($tab_niveau as $niveau_id => $niveau_nom)
  {
    $export_csv .= $separateur.$niveau_nom."\r\n";
    $export_html .= '      <li class="li_m2"><span>'.html($niveau_nom).'</span>'."\r\n";
    $export_html .= '        <ul class="ul_n1">'."\r\n";
    if(isset($tab_domaine[$niveau_id]))
    {
      foreach($tab_domaine[$niveau_id] as $domaine_id => $domaine_nom)
      {
        $export_csv .= $separateur.$separateur.$domaine_nom."\r\n";
        $export_html .= '          <li class="li_n1"><span>'.html($domaine_nom).'</span>'."\r\n";
        $export_html .= '            <ul class="ul_n2">'."\r\n";
        if(isset($tab_theme[$niveau_id][$domaine_id]))
        {
          foreach($tab_theme[$niveau_id][$domaine_id] as $theme_id => $theme_nom)
          {
            $export_csv .= $separateur.$separateur.$separateur.$theme_nom."\r\n";
            $export_html .= '              <li class="li_n2"><span>'.html($theme_nom).'</span>'."\r\n";
            $export_html .= '                <ul class="ul_n3">'."\r\n";
            if(isset($tab_item[$niveau_id][$domaine_id][$theme_id]))
            {
              foreach($tab_item[$niveau_id][$domaine_id][$theme_id] as $item_id => $item_nom)
              {
                $export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$item_nom.'"'."\r\n";
                $export_html .= '                  <li class="li_n3">'.html($item_nom).'</li>'."\r\n";
              }
            }
            $export_html .= '                </ul>'."\r\n";
            $export_html .= '              </li>'."\r\n";
          }
        }
        $export_html .= '            </ul>'."\r\n";
        $export_html .= '          </li>'."\r\n";
      }
    }
    $export_html .= '        </ul>'."\r\n";
    $export_html .= '      </li>'."\r\n";
  }
  $export_html .= '    </ul>'."\r\n";
  $export_html .= '  </li>'."\r\n";
  $export_html .= '</ul>'."\r\n";

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_arbre-matiere_'.Clean::fichier($matiere_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html.= '</div>';

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez l\'arborescence (fichier <em>csv</em> zippé).</span></a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV de l'arborescence du socle
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='arbre_socle') && $palier_id && $palier_nom )
{
  Form::save_choix('palier');
  // Préparation de l'export CSV
  $separateur = ';';
  $export_csv  = 'PALIER'.$separateur.'PILIER'.$separateur.'SECTION'.$separateur.'ITEM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<div id="zone_paliers" class="arbre_dynamique p">';

  $tab_pilier  = array();
  $tab_section = array();
  $tab_entree  = array();
  $pilier_id = 0;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier($palier_id);
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['pilier_id']!=$pilier_id)
    {
      $pilier_id = $DB_ROW['pilier_id'];
      $tab_pilier[$pilier_id] = $DB_ROW['pilier_nom'];
      $section_id = 0;
      $entree_id  = 0;
    }
    if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
    {
      $section_id = $DB_ROW['section_id'];
      $tab_section[$pilier_id][$section_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'];
    }
    if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
    {
      $entree_id = $DB_ROW['entree_id'];
      $tab_entree[$pilier_id][$section_id][$entree_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'];
    }
  }
  $export_csv .= $palier_nom."\r\n";
  $export_html .= '<ul class="ul_m1">'."\r\n";
  $export_html .= '  <li class="li_m1"><span>'.html($palier_nom).'</span>'."\r\n";
  $export_html .= '    <ul class="ul_n1">'."\r\n";
  foreach($tab_pilier as $pilier_id => $pilier_nom)
  {
    $export_csv .= $separateur.$pilier_nom."\r\n";
    $export_html .= '      <li class="li_n1"><span>'.html($pilier_nom).'</span>'."\r\n";
    $export_html .= '        <ul class="ul_n2">'."\r\n";
    if(isset($tab_section[$pilier_id]))
    {
      foreach($tab_section[$pilier_id] as $section_id => $section_nom)
      {
        $export_csv .= $separateur.$separateur.$section_nom."\r\n";
        $export_html .= '          <li class="li_n2"><span>'.html($section_nom).'</span>'."\r\n";
        $export_html .= '            <ul class="ul_n3">'."\r\n";
        if(isset($tab_entree[$pilier_id][$section_id]))
        {
          foreach($tab_entree[$pilier_id][$section_id] as $entree_id => $socle_nom)
          {
            $export_csv .= $separateur.$separateur.$separateur.'"'.$socle_nom.'"'."\r\n";
            $export_html .= '              <li class="li_n3">'.html($socle_nom).'</li>'."\r\n";
          }
        }
        $export_html .= '            </ul>'."\r\n";
        $export_html .= '          </li>'."\r\n";
      }
    }
    $export_html .= '        </ul>'."\r\n";
    $export_html .= '      </li>'."\r\n";
  }
  $export_html .= '    </ul>'."\r\n";
  $export_html .= '  </li>'."\r\n";
  $export_html .= '</ul>'."\r\n";

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_arbre-socle_'.Clean::fichier(substr($palier_nom,0,strpos($palier_nom,' ('))).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html.= '</div>';

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez l\'arborescence (fichier <em>csv</em> zippé).</span></a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des liens des matières rattachés aux liens du socle
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($type_export=='jointure_socle_matiere') && $palier_id && $palier_nom )
{
  Form::save_choix('palier');
  // Préparation de l'export CSV
  $separateur = ';';
  $export_csv  = 'PALIER SOCLE'.$separateur.'PILIER SOCLE'.$separateur.'SECTION SOCLE'.$separateur.'ITEM SOCLE'.$separateur.'ITEM MATIERE'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<div id="zone_paliers" class="arbre_dynamique p">';

  // Récupération des données du socle
  $tab_pilier  = array();
  $tab_section = array();
  $tab_socle   = array();
  $pilier_id = 0;
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier($palier_id);
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['pilier_id']!=$pilier_id)
    {
      $pilier_id = $DB_ROW['pilier_id'];
      $tab_pilier[$pilier_id] = $DB_ROW['pilier_nom'];
      $section_id = 0;
      $socle_id   = 0;
    }
    if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
    {
      $section_id = $DB_ROW['section_id'];
      $tab_section[$pilier_id][$section_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'];
    }
    if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
    {
      $socle_id = $DB_ROW['entree_id'];
      $tab_socle[$pilier_id][$section_id][$socle_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'];
    }
  }

  // Récupération des données des référentiels liés au socle
  $tab_jointure = array();
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_recuperer_associations_entrees_socle();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_jointure[$DB_ROW['entree_id']][] = $DB_ROW['matiere_ref'].'.'.$DB_ROW['niveau_ref'].'.'.$DB_ROW['item_ref'].' - '.$DB_ROW['item_nom'];
  }

  // Elaboration de la sortie
  $export_csv .= $palier_nom."\r\n";
  $export_html .= '<ul class="ul_m1">'."\r\n";
  $export_html .= '  <li class="li_m1"><span>'.html($palier_nom).'</span>'."\r\n";
  $export_html .= '    <ul class="ul_n1">'."\r\n";
  foreach($tab_pilier as $pilier_id => $pilier_nom)
  {
    $export_csv .= $separateur.$pilier_nom."\r\n";
    $export_html .= '      <li class="li_n1"><span>'.html($pilier_nom).'</span>'."\r\n";
    $export_html .= '        <ul class="ul_n2">'."\r\n";
    if(isset($tab_section[$pilier_id]))
    {
      foreach($tab_section[$pilier_id] as $section_id => $section_nom)
      {
        $export_csv .= $separateur.$separateur.$section_nom."\r\n";
        $export_html .= '          <li class="li_n2"><span>'.html($section_nom).'</span>'."\r\n";
        $export_html .= '            <ul class="ul_n3">'."\r\n";
        if(isset($tab_socle[$pilier_id][$section_id]))
        {
          foreach($tab_socle[$pilier_id][$section_id] as $socle_id => $socle_nom)
          {
            $export_csv .= $separateur.$separateur.$separateur.'"'.$socle_nom.'"'."\r\n";
            $export_html .= '              <li class="li_n3"><span>'.html($socle_nom).'</span>'."\r\n";
            if(isset($tab_jointure[$socle_id]))
            {
              $export_html .= '                <ul class="ul_m2">'."\r\n";
              foreach($tab_jointure[$socle_id] as $item_descriptif)
              {
                $export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$item_descriptif.'"'."\r\n";
                $export_html .= '                  <li class="li_m2">'.html($item_descriptif).'</li>'."\r\n";
              }
              $export_html .= '                </ul>'."\r\n";
            }
            else
            {
              $export_csv .= $separateur.$separateur.$separateur.$separateur.'"AUCUN ITEM ASSOCIÉ"'."\r\n";
              $export_html .= '                  <br /><label class="alerte"><span style="background-color:#EE7">Aucun item associé.</span></label>'."\r\n";
            }
            $export_html .= '              </li>'."\r\n";
          }
        }
        $export_html .= '            </ul>'."\r\n";
        $export_html .= '          </li>'."\r\n";
      }
    }
    $export_html .= '        </ul>'."\r\n";
    $export_html .= '      </li>'."\r\n";
  }
  $export_html .= '    </ul>'."\r\n";
  $export_html .= '  </li>'."\r\n";
  $export_html .= '</ul>'."\r\n";

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_jointures_'.Clean::fichier(substr($palier_nom,0,strpos($palier_nom,' ('))).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html.= '</div>';

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les associations (fichier <em>csv</em> zippé).</span></a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données d'élèves (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_eleves') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'.$separateur.'ID_ENT'.$separateur.'ID_GEPI'.$separateur.'SCONET_ID'.$separateur.'SCONET_NUM'.$separateur.'REFERENCE'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'CLASSE_REF'.$separateur.'CLASSE_NOM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead><tr><th>Id</th><th>Id. ENT</th><th>Id. GEPI</th><th>Id. Sconet</th><th>Num. Sconet</th><th>Référence</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Classe Ref.</th><th>Classe Nom</th></tr></thead><tbody>'."\r\n";

  // Récupérer la liste des classes
  $tab_groupe = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupe[$DB_ROW['groupe_id']] = array( 'ref'=>$DB_ROW['groupe_ref'] , 'nom'=>$DB_ROW['groupe_nom'] );
  }
  // Récupérer les données des élèves
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'user_id,user_id_ent,user_id_gepi,user_sconet_id,user_sconet_elenoet,user_reference,user_nom,user_prenom,user_login,eleve_classe_id' );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $export_csv  .= $DB_ROW['user_id'].$separateur.$DB_ROW['user_id_ent'].$separateur.$DB_ROW['user_id_gepi'].$separateur.$DB_ROW['user_sconet_id'].$separateur.$DB_ROW['user_sconet_elenoet'].$separateur.$DB_ROW['user_reference'].$separateur.$DB_ROW['user_login'].$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$tab_groupe[$DB_ROW['eleve_classe_id']]['ref'].$separateur.$tab_groupe[$DB_ROW['eleve_classe_id']]['nom']."\r\n";
      $export_html .= '<tr><td>'.$DB_ROW['user_id'].'</td><td>'.html($DB_ROW['user_id_ent']).'</td><td>'.html($DB_ROW['user_id_gepi']).'</td><td>'.$DB_ROW['user_sconet_id'].'</td><td>'.$DB_ROW['user_sconet_elenoet'].'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($DB_ROW['user_login']).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td>'.html($tab_groupe[$DB_ROW['eleve_classe_id']]['ref']).'</td><td>'.html($tab_groupe[$DB_ROW['eleve_classe_id']]['nom']).'</td></tr>'."\r\n";
    }
  }

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_infos-eleves_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'."\r\n";

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les données (fichier <em>csv</em> zippé.</span>)</a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données de responsables légaux (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_parents') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'.$separateur.'ID_ENT'.$separateur.'ID_GEPI'.$separateur.'SCONET_ID'.$separateur.'SCONET_NUM'.$separateur.'REFERENCE'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'ENFANT_ID'.$separateur.'ENFANT_NOM'.$separateur.'ENFANT_PRENOM'.$separateur.'ENFANT_CLASSE_REF'.$separateur.'ENFANT_CLASSE_NOM'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead><tr><th>Id</th><th>Id. ENT</th><th>Id. GEPI</th><th>Id. Sconet</th><th>Num. Sconet</th><th>Référence</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Enfant Id.</th><th>Enfant Nom</th><th>Enfant Prénom</th><th>Enfant Classe Ref.</th><th>Enfant Classe Nom</th></tr></thead><tbody>'."\r\n";

  // Récupérer la liste des classes
  $tab_groupe = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupe[$DB_ROW['groupe_id']] = array( 'ref'=>$DB_ROW['groupe_ref'] , 'nom'=>$DB_ROW['groupe_nom'] );
  }
  // Récupérer les données des responsables
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'parent' /*profil*/ , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'parent.user_id AS parent_id,parent.user_id_ent AS parent_id_ent,parent.user_id_gepi AS parent_id_gepi,parent.user_sconet_id AS parent_sconet_id,parent.user_sconet_elenoet AS parent_sconet_elenoet,parent.user_reference AS parent_reference,parent.user_nom AS parent_nom,parent.user_prenom AS parent_prenom,parent.user_login AS parent_login,enfant.user_id AS enfant_id,enfant.user_nom AS enfant_nom,enfant.user_prenom AS enfant_prenom,enfant.eleve_classe_id AS enfant_classe_id' );
  if(!empty($DB_TAB))
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $export_csv  .= $DB_ROW['parent_id'].$separateur.$DB_ROW['parent_id_ent'].$separateur.$DB_ROW['parent_id_gepi'].$separateur.$DB_ROW['parent_sconet_id'].$separateur.$DB_ROW['parent_sconet_elenoet'].$separateur.$DB_ROW['parent_reference'].$separateur.$DB_ROW['parent_login'].$separateur.$DB_ROW['parent_nom'].$separateur.$DB_ROW['parent_prenom'].$separateur.$DB_ROW['enfant_id'].$separateur.$DB_ROW['enfant_nom'].$separateur.$DB_ROW['enfant_prenom'].$separateur.$tab_groupe[$DB_ROW['enfant_classe_id']]['ref'].$separateur.$tab_groupe[$DB_ROW['enfant_classe_id']]['nom']."\r\n";
      $export_html .= '<tr><td>'.$DB_ROW['parent_id'].'</td><td>'.html($DB_ROW['parent_id_ent']).'</td><td>'.html($DB_ROW['parent_id_gepi']).'</td><td>'.$DB_ROW['parent_sconet_id'].'</td><td>'.$DB_ROW['parent_sconet_elenoet'].'</td><td>'.html($DB_ROW['parent_reference']).'</td><td>'.html($DB_ROW['parent_login']).'</td><td>'.html($DB_ROW['parent_nom']).'</td><td>'.html($DB_ROW['parent_prenom']).'</td><td>'.$DB_ROW['enfant_id'].'</td><td>'.html($DB_ROW['enfant_nom']).'</td><td>'.html($DB_ROW['enfant_prenom']).'</td><td>'.html($tab_groupe[$DB_ROW['enfant_classe_id']]['ref']).'</td><td>'.html($tab_groupe[$DB_ROW['enfant_classe_id']]['nom']).'</td></tr>'."\r\n";
    }
  }

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_infos-parents_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'."\r\n";

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les données (fichier <em>csv</em> zippé.</span>)</a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Export CSV des données de professeurs (mode administrateur)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') && ($type_export=='infos_professeurs') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
  // Préparation de l'export CSV
  $separateur = ';';
  // ajout du préfixe 'SACOCHE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
  $export_csv  = 'SACOCHE_ID'.$separateur.'ID_ENT'.$separateur.'ID_GEPI'.$separateur.'SCONET_ID'.$separateur.'SCONET_NUM'.$separateur.'REFERENCE'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'PROFIL'."\r\n\r\n";
  // Préparation de l'export HTML
  $export_html = '<table class="p"><thead><tr><th>Id</th><th>Id. ENT</th><th>Id. GEPI</th><th>Id. Sconet</th><th>Num. Sconet</th><th>Référence</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Profil</th></tr></thead><tbody>'."\r\n";

  // Récupérer les données des professeurs et des personnels
  $tab_profil = array('professeur','personnel');
  foreach($tab_profil as $profil)
  {
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( $profil  , TRUE /*statut*/ , $tab_types[$groupe_type] , $groupe_id , 'user_id,user_id_ent,user_id_gepi,user_sconet_id,user_sconet_elenoet,user_reference,user_nom,user_prenom,user_login,user_profil_sigle' );
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        $export_csv  .= $DB_ROW['user_id'].$separateur.$DB_ROW['user_id_ent'].$separateur.$DB_ROW['user_id_gepi'].$separateur.$DB_ROW['user_sconet_id'].$separateur.$DB_ROW['user_sconet_elenoet'].$separateur.$DB_ROW['user_reference'].$separateur.$DB_ROW['user_login'].$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$DB_ROW['user_profil_sigle']."\r\n";
        $export_html .= '<tr><td>'.$DB_ROW['user_id'].'</td><td>'.html($DB_ROW['user_id_ent']).'</td><td>'.html($DB_ROW['user_id_gepi']).'</td><td>'.$DB_ROW['user_sconet_id'].'</td><td>'.$DB_ROW['user_sconet_elenoet'].'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($DB_ROW['user_login']).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td>'.$DB_ROW['user_profil_sigle'].'</td></tr>'."\r\n";
      }
    }
  }

  // Finalisation de l'export CSV (archivage dans un fichier zippé)
  $fnom = 'export_infos-professeurs_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea();
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fnom.'.zip' , $fnom.'.csv' , To::csv($export_csv) );
  // Finalisation de l'export HTML
  $export_html .= '</tbody></table>'."\r\n";

  // Affichage
  echo'<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fnom.'.zip"><span class="file file_zip">Récupérez les données (fichier <em>csv</em> zippé.</span>)</a></li></ul>';
  echo $export_html;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas arriver jusque là.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
