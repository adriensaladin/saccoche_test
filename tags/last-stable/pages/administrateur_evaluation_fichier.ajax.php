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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {Json::end( FALSE , 'Action désactivée pour la démo.' );}

$action    = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$tab_eleve = (isset($_POST['f_eleve']))  ? explode(',',$_POST['f_eleve'])   : array() ;
$tab_eleve = array_filter( Clean::map_entier($tab_eleve) , 'positif' );

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , TRUE /*time*/ );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Exporter un fichier d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='export') && count($tab_eleve) )
{
  $listing_eleve_id = implode(',',$tab_eleve);
  $dossier_temp = CHEMIN_DOSSIER_EXPORT.$_SESSION['BASE'].DS;
  // Récupérer les saisies, donc aussi les identifiants des élèves et des items
  $tab_item = array();
  $tab_user = array();
  $tab_code = array();
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves( $listing_eleve_id );
  if(empty($DB_TAB))
  {
    Json::end( FALSE , 'Aucune saisie d\'évaluation trouvée !');
  }
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp);
  // Infos concernant les saisies
  $nb_saisies = count($DB_TAB);
  $xml_saisie = '<saisies>'."\r\n";
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_user[$DB_ROW['user_id']] = $DB_ROW['user_id'];
    $tab_item[$DB_ROW['item_id']] = $DB_ROW['item_id'];
    if(is_numeric($DB_ROW['note']))
    {
      $tab_code[$DB_ROW['note']] = $DB_ROW['note'];
    }
    $xml_saisie.= '  <saisie user="'.$DB_ROW['user_id'].'" item="'.$DB_ROW['item_id'].'" date="'.$DB_ROW['date'].'" note="'.$DB_ROW['note'].'" info="'.html($DB_ROW['info']).'" visu="'.$DB_ROW['visible'].'" />'."\r\n";
  }
  $xml_saisie.= '</saisies>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp.'saisies.xml' , $xml_saisie );
  unset($xml_saisie);
  // Infos concernant les codes d'évaluation
  sort($tab_code);
  $xml_code = '<codes>'."\r\n";
  foreach($tab_code as $code)
  {
    $xml_code.= '  <code id="'.$code.'" valeur="'.$_SESSION['NOTE'][$code]['VALEUR'].'" legende="'.html($_SESSION['NOTE'][$code]['LEGENDE']).'" />'."\r\n";
  }
  $xml_code.= '</codes>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp.'codes.xml' , $xml_code );
  unset($xml_code);
  // Infos concernant les élèves
  $xml_user = '<users>'."\r\n";
  $listing_user_id = implode(',',$tab_user);
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_cibles( $listing_user_id , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_nom,user_prenom' );
  foreach($DB_TAB as $DB_ROW)
  {
    $xml_user.= '  <user id="'.$DB_ROW['user_id'].'" sconet="'.$DB_ROW['user_sconet_id'].'" elenoet="'.$DB_ROW['user_sconet_elenoet'].'" reference="'.html($DB_ROW['user_reference']).'" nom="'.html($DB_ROW['user_nom']).'" prenom="'.html($DB_ROW['user_prenom']).'" />'."\r\n";
  }
  $xml_user.= '</users>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp.'users.xml' , $xml_user );
  unset($xml_user);
  // Infos concernant les items (et les arborescences associées)
  $tab_matiere = array();
  $tab_niveau  = array();
  $tab_domaine = array();
  $tab_theme   = array();
  $xml_item    = '<items>'."\r\n";
  $listing_item_id = implode(',',$tab_item);
  $DB_TAB = DB_STRUCTURE_BILAN::recuperer_arborescence_items( $listing_item_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_matiere[$DB_ROW['matiere_id']] = '  <matiere id="'.$DB_ROW['matiere_id'].'" nom="'.html($DB_ROW['matiere_nom']).'" />'."\r\n";
    $tab_niveau[$DB_ROW['niveau_id']]   = '  <niveau id="'.$DB_ROW['niveau_id'].'" nom="'.html($DB_ROW['niveau_nom']).'" />'."\r\n";
    $tab_domaine[$DB_ROW['domaine_id']] = '  <domaine id="'.$DB_ROW['domaine_id'].'" nom="'.html($DB_ROW['domaine_nom']).'" matiere="'.$DB_ROW['matiere_id'].'" niveau="'.$DB_ROW['niveau_id'].'" />'."\r\n";
    $tab_theme[$DB_ROW['theme_id']]     = '  <theme id="'.$DB_ROW['theme_id'].'" nom="'.html($DB_ROW['theme_nom']).'" domaine="'.$DB_ROW['domaine_id'].'" />'."\r\n";
    $xml_item.= '  <item id="'.$DB_ROW['item_id'].'" nom="'.html($DB_ROW['item_nom']).'" theme="'.$DB_ROW['theme_id'].'" />'."\r\n";
  }
  $xml_item.= '</items>'."\r\n";
  FileSystem::ecrire_fichier( $dossier_temp.'items.xml' , $xml_item );
  unset($xml_item);
  // Infos concernant les éléments de référentiel associés
  FileSystem::ecrire_fichier( $dossier_temp.'matieres.xml' , '<matieres>'."\r\n".implode('',$tab_matiere).'  </matieres>'."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp.'niveaux.xml'  , '<niveaux>' ."\r\n".implode('',$tab_niveau ).'  </niveaux>' ."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp.'domaines.xml' , '<domaines>'."\r\n".implode('',$tab_domaine).'  </domaines>'."\r\n" );
  FileSystem::ecrire_fichier( $dossier_temp.'themes.xml'   , '<themes>'  ."\r\n".implode('',$tab_theme  ).'  </themes>'  ."\r\n" );
  unset( $tab_matiere , $tab_niveau , $tab_domaine , $tab_theme );
  // On zippe (gain significatif de facteur 15 à 20)
  $fichier_zip_nom = 'evaluations_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::zip_fichiers( $dossier_temp , CHEMIN_DOSSIER_EXPORT , $fichier_zip_nom );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Supprimer le dossier temporaire
  FileSystem::supprimer_dossier($dossier_temp);
  // Afficher le retour
  $fichier_lien = URL_DIR_EXPORT.$fichier_zip_nom;
  $nb_eleves = count($tab_user);
  $nb_items = count($tab_item);
  $ss = ($nb_saisies>1) ? 's' : '' ;
  $se = ($nb_eleves>1)  ? 's' : '' ;
  $si = ($nb_items>1)   ? 's' : '' ;
  Json::add_str('<li><label class="valide">Fichier d\'export généré : '.number_format($nb_saisies,0,'',' ').' saisie'.$ss.' d\'évaluations trouvées concernant '.number_format($nb_eleves,0,'',' ').' élève'.$se.' et '.number_format($nb_items,0,'',' ').' item'.$si.'.</label></li>'.NL);
  Json::add_str('<li><a target="_blank" href="'.$fichier_lien.'"><span class="file file_zip">Récupérer le fichier au format <em>zip</em>.</span></a></li>'.NL);
  Json::add_str('<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>'.NL);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Importer un fichier d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='import')
{
  $dossier_temp = CHEMIN_DOSSIER_IMPORT.$_SESSION['BASE'].DS;
  // Récupération du fichier
  $fichier_upload_nom = 'evaluations_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.zip';
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_upload_nom /*fichier_nom*/ , array('zip') /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , NULL /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    Json::end( FALSE , $result );
  }
  // Créer ou vider le dossier temporaire
  FileSystem::creer_ou_vider_dossier($dossier_temp);
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( CHEMIN_DOSSIER_IMPORT.$fichier_upload_nom , $dossier_temp , FALSE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    FileSystem::supprimer_dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , 'Cette archive ZIP n\'a pas pu être ouverte ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }

  // Vérifier le contenu : noms des fichiers
  $tab_fichiers_archive = array( 'saisies.xml' , 'codes.xml' , 'users.xml' , 'items.xml' , 'matieres.xml' , 'niveaux.xml'  , 'domaines.xml' , 'themes.xml' ); // ordre important
  $tab_fichiers_cherches = array_fill_keys( $tab_fichiers_attendus , TRUE );
  $tab_fichiers_trouves  = FileSystem::lister_contenu_dossier($dossier_temp);
  foreach($tab_fichiers_trouves as $fichier_nom)
  {
    unset($tab_fichiers_cherches[$fichier_nom]);
  }
  if(count($tab_fichiers_cherches))
  {
    FileSystem::supprimer_dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
    Json::end( FALSE , 'Cette archive ZIP ne semble pas contenir les fichiers d\'un export d\'évaluations effectué par SACoche !' );
  }
  // On passe au contenu
  foreach($tab_fichiers_archive as $fichier_nom)
  {
    $fichier_contenu = file_get_contents($dossier_temp.$fichier_nom);
    $fichier_contenu = To::deleteBOM(To::utf8($fichier_contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $xml = @simplexml_load_string($fichier_contenu);
    if($xml===FALSE)
    {
      Json::end( FALSE , 'Le fichier extrait "'.$fichier_nom.'" n\'est pas un XML valide !');
    }
    
    
    
  }

  // On extrait les infos du XML
  $tab_eleve_fichier = array();
  if( ($xml->donnees) && ($xml->donnees->eleve) )
  {
    foreach ($xml->donnees->eleve as $eleve)
    {
      $tab_eleve_fichier['sconet_id'][] = Clean::entier($eleve->attributes()->id);
      $tab_eleve_fichier['nom'][]       = Clean::nom($eleve->attributes()->nom);
      $tab_eleve_fichier['prenom'][]    = Clean::prenom($eleve->attributes()->prenom);
      // Indication des (in-)validations
      $tab_validations = array();
      if($eleve->palier)
      {
        foreach ($eleve->palier as $palier)
        {
          $palier_id = Clean::entier($palier->attributes()->id);
          if($palier->competence)
          {
            foreach ($palier->competence as $competence)
            {
              $pilier_id = Clean::entier($competence->attributes()->id);
              if( ($competence->validation) && ($competence->validation->date) )
              {
                $date = Clean::texte($competence->validation->date) ;
                $etat = ($competence->validation->etat) ? Clean::entier($competence->validation->etat) : 1 ;
                $info = ($competence->validation->info) ? html_decode($competence->validation->info) : $action ;
                $tab_validations['pilier'][$pilier_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
              }
              if( ($competence->item) && ($competence->item->renseignement) && ($competence->item->renseignement->date) )
              {
                foreach ($competence->item as $item)
                {
                  if( ($item->renseignement) && ($item->renseignement->date) )
                  {
                    $item_id = Clean::entier($item->attributes()->id);
                    $date = Clean::texte($item->renseignement->date) ;
                    $etat = ($item->renseignement->etat) ? Clean::entier($item->renseignement->etat) : 1 ;
                    $info = ($item->renseignement->info) ? html_decode($item->renseignement->info) : $action ;
                    $tab_validations['entree'][$item_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
                  }
                }
              }
            }
          }
        }
      }
      $tab_eleve_fichier['validations'][] = $tab_validations;
    }
  }
  // On récupère les infos de la base pour les comparer ; on commence par les identités des élèves
  $tab_eleve_base                = array();
  $tab_eleve_base['sconet_id']   = array();
  $tab_eleve_base['nom']         = array();
  $tab_eleve_base['prenom']      = array();
  $tab_eleve_base['validations'] = array();
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_eleves_identite_et_sconet();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_base['sconet_id'][$DB_ROW['user_id']]  = (int)$DB_ROW['user_sconet_id'];
    $tab_eleve_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
    $tab_eleve_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
  }
  // Voyons donc si on trouve les élèves du fichier dans la base
  $tab_i_fichier_TO_id_base = array();
  // Pour préparer l'affichage
  $lignes_ignorer   = '';
  $lignes_modifier  = '';
  $lignes_inchanger = '';
  $tab_indices_fichier = (isset($tab_eleve_fichier['sconet_id'])) ? array_keys($tab_eleve_fichier['sconet_id']) : array() ;
  // Parcourir chaque entrée du fichier
  foreach($tab_indices_fichier as $i_fichier)
  {
    $id_base = FALSE;
    // Recherche sur sconet_id
    if( (!$id_base) && ($tab_eleve_fichier['sconet_id'][$i_fichier]) )
    {
      $id_base = array_search($tab_eleve_fichier['sconet_id'][$i_fichier],$tab_eleve_base['sconet_id']);
    }
    // Si pas trouvé, recherche sur nom prénom
    if(!$id_base)
    {
      $tab_id_nom    = array_keys($tab_eleve_base['nom'],$tab_eleve_fichier['nom'][$i_fichier]);
      $tab_id_prenom = array_keys($tab_eleve_base['prenom'],$tab_eleve_fichier['prenom'][$i_fichier]);
      $tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
      $nb_homonymes  = count($tab_id_commun);
      if($nb_homonymes==1)
      {
        list($inutile,$id_base) = each($tab_id_commun);
      }
    }
    // Cas [1] : non trouvé dans la base : contenu à ignorer
    if(!$id_base)
    {
      $lignes_ignorer .= '<li><em>Ignoré</em> (non trouvé dans la base) : '.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).' ('.$tab_users_fichier['sconet_id'][$i_fichier].')</li>'.NL;
      unset( $tab_eleve_fichier['validations'][$i_fichier] );
    }
    // Cas [2] : trouvé dans la base : contenu à étudier par la suite
    else
    {
      $tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
    }
  }
  unset( $tab_eleve_fichier['sconet_id'] , $tab_eleve_fichier['nom'] , $tab_eleve_fichier['prenom'] );
  if(count($tab_i_fichier_TO_id_base))
  {
    // On récupère les infos de la base pour les comparer ; on poursuit par les validations
    $tab_validations  = array();
    $listing_eleve_id = implode(',',$tab_i_fichier_TO_id_base);
    $only_positives   = FALSE ;
    // Validations des items
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_items($listing_eleve_id,$only_positives);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_validations[$DB_ROW['user_id']]['entree'][$DB_ROW['entree_id']] = $DB_ROW['validation_entree_date'] ; // Pas besoin d'autre chose que la date
    }
    // Validations des compétences
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_competences($listing_eleve_id,$only_positives);
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_validations[$DB_ROW['user_id']]['pilier'][$DB_ROW['pilier_id']] = $DB_ROW['validation_pilier_date'] ; // Pas besoin d'autre chose que la date
    }
    // Parcourir chaque entrée du fichier
    foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
    {
      $nb_modifs = 0;
      // les validations de piliers
      if(isset($tab_eleve_fichier['validations'][$i_fichier]['pilier']))
      {
        foreach($tab_eleve_fichier['validations'][$i_fichier]['pilier'] as $pilier_id => $tab_infos_fichier)
        {
          if(!isset($tab_validations[$id_base]['pilier'][$pilier_id]))
          {
            DB_STRUCTURE_SOCLE::DB_ajouter_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
          elseif($tab_validations[$id_base]['pilier'][$pilier_id]<$tab_infos_fichier['date'])
          {
            DB_STRUCTURE_SOCLE::DB_modifier_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
        }
      }
      // les validations d'items
      if(isset($tab_eleve_fichier['validations'][$i_fichier]['entree']))
      {
        foreach($tab_eleve_fichier['validations'][$i_fichier]['entree'] as $entree_id => $tab_infos_fichier)
        {
          if(!isset($tab_validations[$id_base]['entree'][$entree_id]))
          {
            DB_STRUCTURE_SOCLE::DB_ajouter_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
          elseif($tab_validations[$id_base]['entree'][$entree_id]<$tab_infos_fichier['date'])
          {
            DB_STRUCTURE_SOCLE::DB_modifier_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
            $nb_modifs++;
          }
        }
      }
      if($nb_modifs)
      {
        $s = ($nb_modifs>1) ? 's' : '' ;
        $lignes_modifier .= '<li><em>Modifié</em> ('.$nb_modifs.' import'.$s.' de validation'.$s.' ) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>'.NL;
      }
      else
      {
        $lignes_inchanger .= '<li><em>Inchangé</em> (pas de validations nouvelles) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>'.NL;
      }
    }
  }
  // On complète et on affiche le bilan
  Json::add_str('<li><label class="valide">Fichier d\'import traité.</label></li>'.NL);
  Json::add_str($lignes_modifier);
  Json::add_str($lignes_inchanger);
  Json::add_str($lignes_ignorer);
  Json::end( TRUE );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  Json::end( FALSE , 'Aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload() );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
