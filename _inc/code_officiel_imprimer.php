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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$objet       = (isset($_POST['f_objet']))       ? Clean::texte($_POST['f_objet'])       : '';
$ACTION      = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])      : '';
$BILAN_TYPE  = (isset($_POST['f_bilan_type']))  ? Clean::texte($_POST['f_bilan_type'])  : '';
$periode_id  = (isset($_POST['f_periode']))     ? Clean::entier($_POST['f_periode'])    : 0;
$classe_id   = (isset($_POST['f_classe']))      ? Clean::entier($_POST['f_classe'])     : 0;
$groupe_id   = (isset($_POST['f_groupe']))      ? Clean::entier($_POST['f_groupe'])     : 0;
$etape       = (isset($_POST['f_etape']))       ? Clean::entier($_POST['f_etape'])      : 0;
$page_parite = (isset($_POST['f_parite']))      ? Clean::entier($_POST['f_parite'])     : 0;
// Autres chaines spécifiques...
$listing_piliers  = (isset($_POST['f_listing_piliers']))  ? $_POST['f_listing_piliers']  : '' ;
$tab_pilier_id  = array_filter( Clean::map_entier( explode(',',$listing_piliers) ) , 'positif' );
$liste_pilier_id  = implode(',',$tab_pilier_id);
$listing_eleves = (isset($_POST['f_listing_eleves']))  ? $_POST['f_listing_eleves']  : '' ;
$tab_eleve_id   = array_filter( Clean::map_entier( explode(',',$listing_eleves) )  , 'positif' );
$liste_eleve_id = implode(',',$tab_eleve_id);

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$tab_objet  = array('imprimer','voir_archive');
$tab_action = array('initialiser','imprimer');

$tab_types = array
(
  'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
  'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
  'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1'  ) ,
  'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2'  ) ,
  'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3'  )
);

// On vérifie les paramètres principaux

if( (!in_array($ACTION,$tab_action)) || (!isset($tab_types[$BILAN_TYPE])) || (!in_array($objet,$tab_objet)) || !$periode_id || !$classe_id || ( (!$liste_eleve_id)&&($ACTION!='initialiser') ) )
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que le bilan est bien accessible en impression et on récupère les infos associées

$DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
if(empty($DB_ROW))
{
  exit('Association classe / période introuvable !');
}
$date_debut  = $DB_ROW['jointure_date_debut'];
$date_fin    = $DB_ROW['jointure_date_fin'];
$BILAN_ETAT  = $DB_ROW['officiel_'.$BILAN_TYPE];
$periode_nom = $DB_ROW['periode_nom'];
$classe_nom  = $DB_ROW['groupe_nom'];
if(!$BILAN_ETAT)
{
  exit('Bilan introuvable !');
}
if( ($BILAN_ETAT!='4complet') && empty($is_test_impression) )
{
  exit('Bilan interdit d\'accès pour cette action !');
}
if( !empty($is_test_impression) && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') && !test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ ) )
{
  exit('Droits insuffisants pour cette action !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage de la liste des élèves + recalcul des moyennes dans le cas d'impression d'un bulletin (sans incidence tant qu'on n'imprime pas, sauf pour la visualisation graphique)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($ACTION=='initialiser')
{
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
  if(empty($DB_TAB))
  {
    exit('Aucun élève trouvé dans ce regroupement !');
  }
  $tab_eleve_id = array();
  $tab_eleve_td = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_id[] = $DB_ROW['user_id'];
    $tab_eleve_td[$DB_ROW['user_id']] = html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom']);
  }
  // (re)calculer les moyennes des élèves (matières et générales), ainsi que les moyennes de classe (matières et générales).
  if( ($objet=='imprimer') && ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] )
  {
    // Attention ! On doit calculer des moyennes de classe, pas de groupe !
    if(!$is_sous_groupe)
    {
      $liste_eleve_id = implode(',',$tab_eleve_id);
    }
    else
    {
      $tab_eleve_id_tmp = array();
      $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_eleve_id_tmp[] = $DB_ROW['user_id'];
      }
      $liste_eleve_id = implode(',',$tab_eleve_id_tmp);
    }
    calculer_et_enregistrer_moyennes_eleves_bulletin( $periode_id , $classe_id , $liste_eleve_id , '' /*liste_matiere_id*/ , $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'] , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] , $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] );
  }
  // lister les bilans officiels archivés de l'année courante, affichage du retour
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_bilan_officiel_fichiers( $BILAN_TYPE , $periode_id , $tab_eleve_id );
  $_SESSION['tmp_droit_voir_archive'] = array(); // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et à donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
  foreach($tab_eleve_id as $eleve_id)
  {
    if($objet=='imprimer')
    {
      $checked    = (isset($DB_TAB[$eleve_id])) ? '' : ' checked' ;
      $archive_td = (isset($DB_TAB[$eleve_id])) ? 'Oui, le '.convert_date_mysql_to_french($DB_TAB[$eleve_id][0]['fichier_date']) : 'Non' ;
      echo'<tr id="id_'.$eleve_id.'">';
      echo'<td class="nu"><input type="checkbox" name="f_ids" value="'.$eleve_id.'"'.$checked.' /></td>';
      echo'<td class="label">'.$tab_eleve_td[$eleve_id].'</td>';
      echo'<td class="label">'.$archive_td.'</td>';
      echo'</tr>';
    }
    elseif($objet=='voir_archive')
    {
      if(!isset($DB_TAB[$eleve_id]))
      {
        $archive_td = 'Non, pas encore imprimé' ;
      }
      elseif(is_file(CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $BILAN_TYPE , $periode_id )))
      {
        $_SESSION['tmp_droit_voir_archive'][$eleve_id.$BILAN_TYPE] = TRUE; // marqueur mis en session pour vérifier que c'est bien cet utilisateur qui veut voir (et a donc le droit de voir) le fichier, car il n'y a pas d'autre vérification de droit ensuite
        $archive_td = '<a href="releve_pdf.php?fichier='.$eleve_id.'_'.$BILAN_TYPE.'_'.$periode_id.'" class="lien_ext">Oui, le '.convert_date_mysql_to_french($DB_TAB[$eleve_id][0]['fichier_date']).'</a>' ;
      }
      else
      {
        $archive_td = 'Oui, mais archive non présente sur ce serveur' ;
      }
      echo'<tr>';
      echo'<td>'.$tab_eleve_td[$eleve_id].'</td>';
      echo'<td>'.$archive_td.'</td>';
      echo'</tr>';
    }
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 2/4 - Le PDF complet est généré ; on archive individuellement les bilans anonymes (qui vont y rester une année scolaire)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==2) )
{
  prevention_et_gestion_erreurs_fatales( FALSE /*memory*/ , TRUE /*time*/ );
  foreach($_SESSION['tmp']['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    list( $eleve_identite , $page_plage ) = $tab_tirages[0];
    DB_STRUCTURE_OFFICIEL::DB_modifier_bilan_officiel_fichier( $eleve_id , $BILAN_TYPE , $periode_id );
    $fichier_extraction_chemin = CHEMIN_DOSSIER_OFFICIEL.$_SESSION['BASE'].DS.fabriquer_nom_fichier_bilan_officiel( $eleve_id , $BILAN_TYPE , $periode_id );
    unset($_SESSION['tmp']['tab_pages_decoupe_pdf'][$eleve_id][0]);
    $releve_pdf = new PDFMerger;
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $page_plage ) -> merge( 'file' , $fichier_extraction_chemin );
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 3/4 - Le PDF complet est généré ; on découpe individuellement les bilans par responsables puis on zippe l'ensemble
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==3) )
{
  prevention_et_gestion_erreurs_fatales( FALSE /*memory*/ , TRUE /*time*/ );
  $date = date('Y-m-d');
  $tab_pages_non_anonymes     = array();
  $tab_pages_nombre_par_bilan = array();
  $chemin_temp_pdf = CHEMIN_DOSSIER_EXPORT.'pdf_'.mt_rand().DS;
  FileSystem::creer_ou_vider_dossier($chemin_temp_pdf);
  foreach($_SESSION['tmp']['tab_pages_decoupe_pdf'] as $eleve_id => $tab_tirages)
  {
    foreach($tab_tirages as $numero_tirage => $tab)
    {
      list( $eleve_identite , $page_plage , $page_nombre ) = $tab;
      $tab_pages_non_anonymes[]     = $page_plage;
      $tab_pages_nombre_par_bilan[] = $page_nombre;
      $fichier_extraction_chemin = $chemin_temp_pdf.'officiel_'.$BILAN_TYPE.'_'.Clean::fichier($eleve_identite).'_'.$date.'_resp'.$numero_tirage.'.pdf';
      $releve_pdf = new PDFMerger;
      $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $page_plage ) -> merge( 'file' , $fichier_extraction_chemin );
    }
  }
  FileSystem::zipper_fichiers( $chemin_temp_pdf , CHEMIN_DOSSIER_EXPORT , $_SESSION['tmp']['fichier_nom'].'.zip' );
  FileSystem::supprimer_dossier($chemin_temp_pdf);
  $_SESSION['tmp']['pages_non_anonymes']     = implode(',',$tab_pages_non_anonymes);
  $_SESSION['tmp']['pages_nombre_par_bilan'] = implode(' ; ',$tab_pages_nombre_par_bilan);
  unset($_SESSION['tmp']['tab_pages_decoupe_pdf']);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 4/4 - Le PDF complet est généré ; on n'en garde que les bilans non anonymes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION=='imprimer') && ($etape==4) )
{
  $releve_pdf = new PDFMerger;
  if($_SESSION['tmp']['pages_non_anonymes']!='') // Potentiellement possible si on veut imprimer un ou plusieurs bulletins d'élèves sans aucune donnée, ce qui provoque l'erreur "FPDF error: Pagenumber is wrong!"
  {
    $pdf_string = $releve_pdf -> addPDF( CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' , $_SESSION['tmp']['pages_non_anonymes'] ) -> merge( 'file' , CHEMIN_DOSSIER_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf' );
  }
  echo'<ul class="puce">';
  echo'<li><a class="lien_ext" href="'.URL_DIR_EXPORT.$_SESSION['tmp']['fichier_nom'].'.pdf"><span class="file file_pdf">Récupérer, <span class="u">pour impression</span>, l\'ensemble des bilans officiels en un seul document <b>[x]</b>.</span></a></li>';
  echo'<li><a class="lien_ext" href="'.URL_DIR_EXPORT.$_SESSION['tmp']['fichier_nom'].'.zip"><span class="file file_zip">Récupérer, <span class="u">pour archivage</span>, les bilans officiels dans des documents individuels.</span></a></li>';
  echo'</ul>';
  echo'<p class="astuce"><b>[x]</b> Nombre de pages par bilan (y prêter attention avant de lancer une impression recto-verso en série) :<br />'.$_SESSION['tmp']['pages_nombre_par_bilan'].'</p>';
  unset( $_SESSION['tmp']['fichier_nom'] , $_SESSION['tmp']['pages_non_anonymes'] , $_SESSION['tmp']['pages_nombre_par_bilan'] );
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// IMPRIMER ETAPE 1/4 - Génération de l'impression PDF (archive + responsables)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($ACTION!='imprimer') || ($etape!=1) )
{
  exit('Erreur avec les données transmises !');
}

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné
// Initialiser les signatures numériques

$tab_saisie = array();  // [eleve_id][rubrique_id][prof_id] => array(prof_info,appreciation,note,info);
$tab_signature = array(0=>NULL);  // [prof_id] => array(contenu,format,largeur,hauteur);
$tab_assiduite = array_fill_keys( $tab_eleve_id , array( 'absence' => NULL , 'non_justifie' => NULL , 'retard' => NULL ) );  // [eleve_id] => array(absence,non_justifie,retard);
$tab_prof_id = array();
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$DB_ROW['prof_info'] , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
  $tab_signature[$DB_ROW['prof_id']] = NULL ; // Initialisé
  $tab_prof_id[$DB_ROW['prof_id']] = $DB_ROW['prof_id']; // Pour savoir ensuite la liste des profs à chercher
}
$DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $periode_id , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_saisie[0][$DB_ROW['rubrique_id']][$DB_ROW['prof_id']] = array( 'prof_info'=>$DB_ROW['prof_info'] , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
}

// Récupérer les signatures numériques

if($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']!='sans')
{
  if( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='tampon') || (!count($tab_prof_id)) )
  {
    $listing_prof_id = '0';
  }
  elseif( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') || (isset($tab_prof_id[0])) )
  {
    $listing_prof_id = implode(',',$tab_prof_id);
  }
  else
  {
    $listing_prof_id = '0,'.implode(',',$tab_prof_id);
  }
  $DB_TAB = DB_STRUCTURE_IMAGE::DB_lister_images( $listing_prof_id , 'signature' );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_signature[$DB_ROW['user_id']] = array( base64_decode($DB_ROW['image_contenu']) , $DB_ROW['image_format'] , $DB_ROW['image_largeur'] , $DB_ROW['image_hauteur'] );
  }
}

// Récupérer les absences / retards

$affichage_assiduite = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_ASSIDUITE']) ? TRUE : FALSE ;

if($affichage_assiduite)
{
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , $tab_eleve_id );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_assiduite[$DB_ROW['user_id']] = array( 'absence' => $DB_ROW['assiduite_absence'] , 'non_justifie' => $DB_ROW['assiduite_non_justifie'] , 'retard' => $DB_ROW['assiduite_retard'] );
  }
}

// Récupérer les noms et coordonnées des responsables, ou simplement l'info de savoir si leurs adresses sont différentes

$tab_destinataires = array();  // [eleve_id][i] => array(...) | 'archive' | NULL ;
$pays_majoritaire = DB_STRUCTURE_OFFICIEL::DB_recuperer_pays_majoritaire();
$DB_TAB = ( ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']!='non') || ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') ) ? DB_STRUCTURE_OFFICIEL::DB_lister_adresses_parents_for_enfants($liste_eleve_id) : array() ;
foreach($tab_eleve_id as $eleve_id)
{
  if( (isset($DB_TAB[$eleve_id][0]['adresse_pays_nom'])) && ($DB_TAB[$eleve_id][0]['adresse_pays_nom']==$pays_majoritaire) ) {$DB_TAB[$eleve_id][0]['adresse_pays_nom']='';}
  if( (isset($DB_TAB[$eleve_id][1]['adresse_pays_nom'])) && ($DB_TAB[$eleve_id][1]['adresse_pays_nom']==$pays_majoritaire) ) {$DB_TAB[$eleve_id][1]['adresse_pays_nom']='';}
  $tab_coords_resp1 = (isset($DB_TAB[$eleve_id][0])) ? array_filter(array($DB_TAB[$eleve_id][0]['user_nom'].' '.$DB_TAB[$eleve_id][0]['user_prenom'],$DB_TAB[$eleve_id][0]['adresse_ligne1'],$DB_TAB[$eleve_id][0]['adresse_ligne2'],$DB_TAB[$eleve_id][0]['adresse_ligne3'],$DB_TAB[$eleve_id][0]['adresse_ligne4'],$DB_TAB[$eleve_id][0]['adresse_postal_code'].' '.$DB_TAB[$eleve_id][0]['adresse_postal_libelle'],$DB_TAB[$eleve_id][0]['adresse_pays_nom'])) : NULL ;
  $tab_coords_resp2 = (isset($DB_TAB[$eleve_id][1])) ? array_filter(array($DB_TAB[$eleve_id][1]['user_nom'].' '.$DB_TAB[$eleve_id][1]['user_prenom'],$DB_TAB[$eleve_id][1]['adresse_ligne1'],$DB_TAB[$eleve_id][1]['adresse_ligne2'],$DB_TAB[$eleve_id][1]['adresse_ligne3'],$DB_TAB[$eleve_id][1]['adresse_ligne4'],$DB_TAB[$eleve_id][1]['adresse_postal_code'].' '.$DB_TAB[$eleve_id][1]['adresse_postal_libelle'],$DB_TAB[$eleve_id][1]['adresse_pays_nom'])) : NULL ;
  // La copie du bilan qui sera 'archivée' jusqu'à la fin de l'année scolaire.
  $tab_destinataires[$eleve_id][0] = 'archive' ;
  // Tirage pour le 1er responsable
  $tab_destinataires[$eleve_id][1] = ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='non') ? NULL : $tab_coords_resp1 ;
  // Tirage pour le 2e responsable
  if( ( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_de_force') && ($tab_coords_resp2!=NULL) ) || ( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') && ($tab_coords_resp2!=NULL) && ( ($tab_coords_resp1==NULL) || (array_slice($tab_coords_resp2,1)!=array_slice($tab_coords_resp1,1)) ) ) )
  {
    $tab_destinataires[$eleve_id][2] = ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='non') ? NULL : $tab_coords_resp2 ;
  }
  // Ajouter sur le 1er tirage le nom du 2e responsable si les adresses sont identiques et que le 2e bilan n'est imprimé qu'en cas d'adresses différentes
  if( ($_SESSION['OFFICIEL']['NOMBRE_EXEMPLAIRES']=='deux_si_besoin') && ($tab_destinataires[$eleve_id][1]!=NULL) && (!isset($tab_destinataires[$eleve_id][2])) )
  {
    array_unshift($tab_destinataires[$eleve_id][1], $tab_coords_resp2[0]);
  }
}

// Récupérer le logo de l'établissement

$tab_etabl_logo = NULL;
$logo_hauteur = 0;
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'logo'))
{
  $DB_ROW = DB_STRUCTURE_IMAGE::DB_recuperer_image( 0 /*user_id*/ , 'logo' );
  if(!empty($DB_ROW))
  {
    $tab_etabl_logo = array( base64_decode($DB_ROW['image_contenu']) , $DB_ROW['image_format'] , $DB_ROW['image_largeur'] , $DB_ROW['image_hauteur'] );
    $logo_hauteur = 5;
  }
}

// Bloc des coordonnées de l'établissement

$tab_etabl_coords = array( 0 => $_SESSION['ETABLISSEMENT']['DENOMINATION'] );
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'adresse'))
{
  if($_SESSION['ETABLISSEMENT']['ADRESSE1']) { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE2']) { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE3']) { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
}
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'telephone'))
{
  if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords[] = 'Tel : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
}
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'fax'))
{
  if($_SESSION['ETABLISSEMENT']['FAX']) { $tab_etabl_coords[] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
}
if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'] ,'courriel'))
{
  if($_SESSION['ETABLISSEMENT']['COURRIEL']) { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['COURRIEL']; }
}
$etabl_coords__bloc_hauteur = 0.75 + ( max( count($tab_etabl_coords) , $logo_hauteur ) * 0.75 ) ;

// Bloc des titres du document

$mois_actuel    = date('n');
$annee_actuelle = date('Y');
$mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'];
if($mois_bascule==1)
{
  $annee_affichee = $annee_actuelle;
}
else if($mois_actuel < $mois_bascule)
{
  $annee_affichee = ($annee_actuelle-1).'/'.$annee_actuelle;
}
else
{
  $annee_affichee = $annee_actuelle.'/'.($annee_actuelle+1);
}
$tab_bloc_titres = array( 0 => $tab_types[$BILAN_TYPE]['titre'] , 1 => 'Année scolaire '.$annee_affichee.' - '.$periode_nom , 2 =>$classe_nom );

// Tag date heure initiales

$tag_date_heure_initiales = date('d/m/Y H:i').' '.$_SESSION['USER_PRENOM']{0}.'.'.$_SESSION['USER_NOM']{0}.'.';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation de variables supplémentaires
// INCLUSION DU CODE COMMUN À PLUSIEURS PAGES
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_pages_decoupe_pdf = array();
$make_officiel = TRUE;
$make_action   = 'imprimer';
$make_html     = FALSE;
$make_pdf      = TRUE;
$make_graph    = FALSE;

if($BILAN_TYPE=='releve')
{
  $format                 = 'multimatiere';
  $aff_etat_acquisition   = $_SESSION['OFFICIEL']['RELEVE_ETAT_ACQUISITION'];
  $aff_moyenne_scores     = $_SESSION['OFFICIEL']['RELEVE_MOYENNE_SCORES'];
  $aff_pourcentage_acquis = $_SESSION['OFFICIEL']['RELEVE_POURCENTAGE_ACQUIS'];
  $conversion_sur_20      = $_SESSION['OFFICIEL']['RELEVE_CONVERSION_SUR_20'];
  $with_coef              = 1; // Il n'y a que des relevés par matière et pas de synthèse commune : on prend en compte les coefficients pour chaque relevé matière.
  $matiere_id             = TRUE;
  $matiere_nom            = '';
  $groupe_id              = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom             = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $date_debut             = '';
  $date_fin               = '';
  $retroactif             = $_SESSION['OFFICIEL']['RELEVE_RETROACTIF']; // C'est un relevé de notes sur une période donnée : aller chercher les notes antérieures serait curieux !
  $only_socle             = $_SESSION['OFFICIEL']['RELEVE_ONLY_SOCLE'];
  $aff_coef               = $_SESSION['OFFICIEL']['RELEVE_AFF_COEF'];
  $aff_socle              = $_SESSION['OFFICIEL']['RELEVE_AFF_SOCLE'];
  $aff_lien               = 0; // Sans intérêt, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_domaine            = $_SESSION['OFFICIEL']['RELEVE_AFF_DOMAINE'];
  $aff_theme              = $_SESSION['OFFICIEL']['RELEVE_AFF_THEME'];
  $orientation            = 'portrait'; // pas jugé utile de le mettre en option...
  $couleur                = $_SESSION['OFFICIEL']['RELEVE_COULEUR'];
  $legende                = $_SESSION['OFFICIEL']['RELEVE_LEGENDE'];
  $marge_gauche           = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
  $marge_droite           = $_SESSION['OFFICIEL']['MARGE_DROITE'];
  $marge_haut             = $_SESSION['OFFICIEL']['MARGE_HAUT'];
  $marge_bas              = $_SESSION['OFFICIEL']['MARGE_BAS'];
  $pages_nb               = 'optimise'; // pas jugé utile de le mettre en option... à revoir ?
  $cases_nb               = $_SESSION['OFFICIEL']['RELEVE_CASES_NB'];
  $cases_largeur          = 5; // pas jugé utile de le mettre en option...
  $tab_eleve              = $tab_eleve_id;
  $liste_eleve            = $liste_eleve_id;
  $tab_type[]             = 'individuel';
  $type_individuel        = 1;
  $type_synthese          = 0;
  $type_bulletin          = 0;
  $tab_matiere_id         = array();
  require(CHEMIN_DOSSIER_INCLUDE.'code_items_releve.php');
  $nom_bilan_html         = 'releve_HTML_individuel';
}
elseif($BILAN_TYPE=='bulletin')
{
  $format         = 'multimatiere' ;
  $groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $date_debut     = '';
  $date_fin       = '';
  $retroactif     = $_SESSION['OFFICIEL']['BULLETIN_RETROACTIF'];
  $fusion_niveaux = $_SESSION['OFFICIEL']['BULLETIN_FUSION_NIVEAUX'];
  $niveau_id      = 0; // Niveau transmis uniquement si on restreint sur un niveau : pas jugé utile de le mettre en option...
  $aff_coef       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_socle      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_lien       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_start      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $only_socle     = $_SESSION['OFFICIEL']['BULLETIN_ONLY_SOCLE'];
  $only_niveau    = 0; // pas jugé utile de le mettre en option...
  $couleur        = $_SESSION['OFFICIEL']['BULLETIN_COULEUR'];
  $legende        = $_SESSION['OFFICIEL']['BULLETIN_LEGENDE'];
  $marge_gauche   = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
  $marge_droite   = $_SESSION['OFFICIEL']['MARGE_DROITE'];
  $marge_haut     = $_SESSION['OFFICIEL']['MARGE_HAUT'];
  $marge_bas      = $_SESSION['OFFICIEL']['MARGE_BAS'];
  $tab_eleve      = $tab_eleve_id;
  $liste_eleve    = $liste_eleve_id;
  $tab_matiere_id = array();
  require(CHEMIN_DOSSIER_INCLUDE.'code_items_synthese.php');
  $nom_bilan_html = 'releve_HTML';
}
elseif(in_array($BILAN_TYPE,array('palier1','palier2','palier3')))
{
  $palier_id      = (int)substr($BILAN_TYPE,-1);
  $palier_nom     = 'Palier '.$palier_id;
  $only_presence  = $_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE'];
  $aff_socle_PA   = $_SESSION['OFFICIEL']['SOCLE_POURCENTAGE_ACQUIS'];
  $aff_socle_EV   = $_SESSION['OFFICIEL']['SOCLE_ETAT_VALIDATION'];
  $groupe_id      = (!$is_sous_groupe) ? $classe_id  : $groupe_id ; // Le groupe = la classe (par défaut) ou le groupe transmis
  $groupe_nom     = (!$is_sous_groupe) ? $classe_nom : $classe_nom.' - '.DB_STRUCTURE_COMMUN::DB_recuperer_groupe_nom($groupe_id) ;
  $mode           = 'auto';
  $aff_coef       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_socle      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_lien       = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $aff_start      = 0; // Sans objet, l'élève & sa famille n'ayant accès qu'à l'archive pdf
  $couleur        = $_SESSION['OFFICIEL']['SOCLE_COULEUR'];
  $legende        = $_SESSION['OFFICIEL']['SOCLE_LEGENDE'];
  $marge_gauche   = $_SESSION['OFFICIEL']['MARGE_GAUCHE'];
  $marge_droite   = $_SESSION['OFFICIEL']['MARGE_DROITE'];
  $marge_haut     = $_SESSION['OFFICIEL']['MARGE_HAUT'];
  $marge_bas      = $_SESSION['OFFICIEL']['MARGE_BAS'];
  $tab_pilier_id  = $tab_pilier_id;
  $tab_eleve_id   = $tab_eleve_id;
  $tab_matiere_id = array();
  require(CHEMIN_DOSSIER_INCLUDE.'code_socle_releve.php');
  $nom_bilan_html = 'releve_HTML';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du résultat (pas grand chose, car la découpe du PDF intervient lors d'appels ajax ultérieurs, sauf s'il s'agissait d'un test d'impression auquel cas on ajoute un filigrane et on s'arrête là)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($is_test_impression))
{
  $_SESSION['tmp']['fichier_nom'] = $fichier_nom;
  $_SESSION['tmp']['tab_pages_decoupe_pdf'] = $tab_pages_decoupe_pdf;
  exit('ok');
}
else
{
  exit('ok;'.URL_DIR_EXPORT.$fichier_nom.'.pdf');
}

?>
