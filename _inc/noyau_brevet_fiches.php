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

/**
 * Code inclus commun aux pages
 * [./_inc/code_brevet_fiches_***.php]
 */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Chemins d'enregistrement

$fichier_nom = 'fiche_brevet_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() ;

// Initialisation de tableaux

$tab_eleve_infos    = array();  // [eleve_id] => array(eleve_INE,eleve_nom,eleve_prenom,eleve_genre,date_naissance,eleve_brevet_serie)
$tab_matiere        = array();  // [matiere_id] => matiere_nom
$tab_brevet_serie   = array();  // [serie_ref] => serie_nom
$tab_brevet_epreuve = array();  // [serie_ref][epreuve_code] => epreuve_nom, epreuve_obligatoire, epreuve_note_chiffree, epreuve_point_sup_10, epreuve_note_comptee, epreuve_coefficient, choix_matieres
$tab_eleve_saisie   = array();  // [eleve_id][epreuve_code][prof_id] => array(prof_info,appreciation,note); avec eleve_id=0 pour note ou appréciation sur la classe

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de l'identité des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve , 'alpha' /*eleves_ordre*/ , FALSE /*with_gepi*/ , FALSE /*with_langue*/ , TRUE /*with_brevet_serie*/ );
if(!is_array($tab_eleve_infos))
{
  exit('Aucun élève trouvé correspondant aux identifiants transmis !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des matières utilisées dans l'établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_matieres_etabl();
if(is_string($DB_TAB))
{
  exit($DB_TAB);
}
foreach($DB_TAB as $DB_ROW)
{
  $tab_matiere[$DB_ROW['valeur']] = $DB_ROW['texte'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des séries de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  $tab_brevet_serie[$tab_eleve['eleve_brevet_serie']] = $tab_eleve['eleve_brevet_serie']; // Sera remplacé par le nom de la série après
}
if( !count($tab_brevet_serie) || isset($tab_brevet_serie['X']) )
{
  exit('Élève(s) trouvé(s) sans association avec une série de brevet !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des paramètres des épreuves par série de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

foreach($tab_brevet_serie as $serie_ref)
{
  $tab_brevet_epreuve[$serie_ref] = array();  // [serie_ref][epreuve_code] => epreuve_nom, epreuve_obligatoire, epreuve_note_chiffree, epreuve_point_sup_10, epreuve_note_comptee, epreuve_coefficient, choix_matieres
  $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves( $serie_ref , TRUE /*with_serie_nom*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_brevet_serie[$serie_ref] = $DB_ROW['brevet_serie_nom'];
    $tab_brevet_epreuve[$serie_ref][$DB_ROW['brevet_epreuve_code']] = 
       array(
        'epreuve_nom'             =>       $DB_ROW['brevet_epreuve_nom'],
        'epreuve_obligatoire'     => (bool)$DB_ROW['brevet_epreuve_obligatoire'],
        'epreuve_note_chiffree'   => (bool)$DB_ROW['brevet_epreuve_note_chiffree'],
        'epreuve_point_sup_10'    => (bool)$DB_ROW['brevet_epreuve_point_sup_10'],
        'epreuve_note_comptee'    => (bool)$DB_ROW['brevet_epreuve_note_comptee'],
        'epreuve_coefficient'     =>  (int)$DB_ROW['brevet_epreuve_coefficient'],
        'epreuve_choix_matieres'  =>       $DB_ROW['brevet_epreuve_choix_matieres'],
      );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des notes et appréciations des fiches brevet par élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Récupérer les saisies déjà effectuées pour le bilan officiel concerné

$DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_eleves( $liste_eleve , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
foreach($DB_TAB as $DB_ROW)
{
  $prof_info = ($DB_ROW['prof_id']) ? afficher_identite_initiale( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ) : '' ;
  $tab_eleve_saisie[$DB_ROW['eleve_id']][$DB_ROW['brevet_epreuve_code']] = array( 'matieres_id'=>$DB_ROW['matieres_id'] , 'prof_id'=>$DB_ROW['prof_id'] , 'prof_info'=>$prof_info , 'appreciation'=>$DB_ROW['saisie_appreciation'] , 'note'=>$DB_ROW['saisie_note'] );
}
$DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_classe( $classe_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
foreach($DB_TAB as $DB_ROW)
{
  $tab_eleve_saisie[0][$DB_ROW['brevet_epreuve_code']] = $DB_ROW['saisie_note'];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////

DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la fiche brevet, en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_graph_data = array();

// Nombre de boucles par élève (une 2e pour la copie archivée)
$nombre_tirages = ($ACTION!='imprimer') ? 1 : 2 ;
$page_numero = 0 ;

// Préparatifs
if( ($make_html) || ($make_graph) )
{
  $bouton_print_appr = (!$make_graph)                       ? ' <button id="archiver_imprimer" type="button" class="imprimer">Archiver / Imprimer des données</button>'           : '' ;
  $bouton_print_test = (!empty($is_bouton_test_impression)) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : '' ;
  $fiche_brevet_HTML = (!$make_graph) ? '<div>'.$bouton_print_appr.$bouton_print_test.'</div>'.NL : '<div id="div_graphique_brevet"></div>'.NL ;
  $width_col1 = 100 ;
  $width_col2 = 900 - $width_col1;
}
if($make_pdf)
{
  $fiche_brevet_PDF = new PDF_fiche_brevet( TRUE /*make_officiel*/ , 'portrait' /*orientation*/ , 16 /*marge_gauche*/ , 16 /*marge_droite*/ , 16 /*marge_haut*/ , 12 /*marge_bas*/ , 'oui' /*couleur*/ , 'oui' /*legende*/ , !empty($is_test_impression) /*filigrane*/ );
  // Tag date heure initiales
  $tag_date_heure_initiales = date('d/m/Y H:i').' '.afficher_identite_initiale($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);
  // Quelques valeurs de positionnement ...
  $pdf_coords_session       = array( 'G'=>array(111,18,15,4) , 'P'=>array(103,18,15,4) );
  $pdf_coords_academie      = array( 30  ,29.5,38,3);
  $pdf_coords_departement   = array( 34  ,33  ,35,3);
  $pdf_coords_etablissement = array( 15  ,42  ,55,3);
  $pdf_coords_eleve_nom     = array(127  ,28  ,63,3);
  $pdf_coords_eleve_prenom  = array(131.8,35.2,58,3);
  $pdf_coords_eleve_date    = array(147.6,42.5,42,3);
  $pdf_coords_classe_nom    = array(132.5,50  ,57,3);
  // Français
  $pdf_coords_epreuve_classe[101] = array( 'G'=>array(56,97.5, 15,13) , 'P'=>array(52,104, 14,13) );
  $pdf_coords_epreuve_eleve[101]  = array( 'G'=>array(71,97.5, 14,13) , 'P'=>array(66,104, 14,13) );
  $pdf_coords_epreuve_appr[101]   = array( 'G'=>array(85,97.5,108,13) , 'P'=>array(80,104,113,13) );
  // Mathématiques
  $pdf_coords_epreuve_classe[102] = array( 'G'=>array(56,111, 15,13) , 'P'=>array(52,117, 14,13) );
  $pdf_coords_epreuve_eleve[102]  = array( 'G'=>array(71,111, 14,13) , 'P'=>array(66,117, 14,13) );
  $pdf_coords_epreuve_appr[102]   = array( 'G'=>array(85,111,108,13) , 'P'=>array(80,117,113,13) );
  // Première langue vivante || Langues vivantes [pb pour P-Agri : mauvais intitulé sur la fiche]
  $pdf_coords_epreuve_classe[103] = array( 'G'=>array(56,124, 15,13) , 'P'=>array(52,131, 14,18) );
  $pdf_coords_epreuve_eleve[103]  = array( 'G'=>array(71,124, 14,13) , 'P'=>array(66,131, 14,18) );
  $pdf_coords_epreuve_appr[103]   = array( 'G'=>array(85,124,108,13) , 'P'=>array(80,131,113,18) );
  // Sciences de la vie et de la terre
  $pdf_coords_epreuve_classe[104] = array( 'G'=>array(56,151, 15,13) );
  $pdf_coords_epreuve_eleve[104]  = array( 'G'=>array(71,151, 14,13) );
  $pdf_coords_epreuve_appr[104]   = array( 'G'=>array(85,151,108,13) );
  // Physique-chimie || Prévention santé environnement
  $pdf_coords_epreuve_classe[105] = array( 'G'=>array(56,164, 15,13) , 'P'=>array(52,149, 14,13) );
  $pdf_coords_epreuve_eleve[105]  = array( 'G'=>array(71,164, 14,13) , 'P'=>array(66,149, 14,13) );
  $pdf_coords_epreuve_appr[105]   = array( 'G'=>array(85,164,108,13) , 'P'=>array(80,149,113,13) );
  // Éducation physique et sportive
  $pdf_coords_epreuve_classe[106] = array( 'G'=>array(56,177.5, 15,13) , 'P'=>array(52,162, 14,13) );
  $pdf_coords_epreuve_eleve[106]  = array( 'G'=>array(71,177.5, 14,13) , 'P'=>array(66,162, 14,13) );
  $pdf_coords_epreuve_appr[106]   = array( 'G'=>array(85,177.5,108,13) , 'P'=>array(80,162,113,13) );
  // Arts plastiques || Éducation artistique [pb pour P : "Enseignements artistiques" écrit sur la fiche] || Éducation socioculturelle [pb pour P-Agri : épreuve pas sur la fiche]
  $pdf_coords_epreuve_classe[107] = array( 'G'=>array(56,191, 15,8) , 'P'=>array(52,176, 14,13) );
  $pdf_coords_epreuve_eleve[107]  = array( 'G'=>array(71,191, 14,8) , 'P'=>array(66,176, 14,13) );
  $pdf_coords_epreuve_appr[107]   = array( 'G'=>array(85,191,108,8) , 'P'=>array(80,176,113,13) );
  // Éducation musicale || Sciences et technologie
  $pdf_coords_epreuve_classe[108] = array( 'G'=>array(56,199, 15,9) , 'P'=>array(52,189, 14,13) );
  $pdf_coords_epreuve_eleve[108]  = array( 'G'=>array(71,199, 14,9) , 'P'=>array(66,189, 14,13) );
  $pdf_coords_epreuve_appr[108]   = array( 'G'=>array(85,199,108,9) , 'P'=>array(80,189,113,13) );
  // Technologie || Technologie, sciences et découverte de la vie professionnelle et des métiers [pb pour P-Agri : épreuve pas sur la fiche]
  $pdf_coords_epreuve_classe[109] = array( 'G'=>array(56,208, 15,13) , 'P'=>array(52,202, 14,13) );
  $pdf_coords_epreuve_eleve[109]  = array( 'G'=>array(71,208, 14,13) , 'P'=>array(66,202, 14,13) );
  $pdf_coords_epreuve_appr[109]   = array( 'G'=>array(85,208,108,13) , 'P'=>array(80,202,113,13) );
  // Deuxième langue vivante || Découverte professionnelle
  $pdf_coords_epreuve_classe[110] = array( 'G'=>array(56,137.5, 15,13) , 'P'=>array(52,202, 14,13) );
  $pdf_coords_epreuve_eleve[110]  = array( 'G'=>array(71,137.5, 14,13) , 'P'=>array(66,202, 14,13) );
  $pdf_coords_epreuve_appr[110]   = array( 'G'=>array(85,137.5,108,13) , 'P'=>array(80,202,113,13) );
  // Vie scolaire
  // $pdf_coords_epreuve_classe[112] = array( 'G'=>array(56,242, 15,13) , 'P'=>array(52,237, 14,13) );
  // $pdf_coords_epreuve_eleve[112]  = array( 'G'=>array(71,242, 14,13) , 'P'=>array(66,237, 14,13) );
  // $pdf_coords_epreuve_appr[112]   = array( 'G'=>array(85,242,108,13) , 'P'=>array(80,237,113,13) );
  // Option facultative [pb pour P : anormalement présent sur la fiche]
  $pdf_coords_epreuve_classe[113] = array( 'G'=>array(56,221, 15,13) );
  $pdf_coords_epreuve_eleve[113]  = array( 'G'=>array(71,221, 14,13) );
  $pdf_coords_epreuve_appr[113]   = array( 'G'=>array(85,221,108,13) );
  // Histoire-géographie || Histoire-géographie éducation civique [pb pour P de partage de la case en 2 sur la fiche]
  $pdf_coords_epreuve_classe[121] = array( 'G'=>array(56,235, 15,7) , 'P'=>array(52,229, 14,13) );
  $pdf_coords_epreuve_eleve[121]  = array( 'G'=>array(71,235, 14,7) , 'P'=>array(66,229, 14,13) );
  $pdf_coords_epreuve_appr[121]   = array( 'G'=>array(85,235,108,7) , 'P'=>array(80,229,113,13) );
  // Éducation civique
  $pdf_coords_epreuve_classe[122] = array( 'G'=>array(56,242, 15,6) );
  $pdf_coords_epreuve_eleve[122]  = array( 'G'=>array(71,242, 14,6) );
  $pdf_coords_epreuve_appr[122]   = array( 'G'=>array(85,242,108,6) );
  // Niveau A2 de langue régionale
  // $pdf_coords_epreuve_classe[130] = array( 'G'=>array(56,134, 15,13) , 'P'=>array(52,228, 14,13) );
  // $pdf_coords_epreuve_eleve[130]  = array( 'G'=>array(71,134, 14,13) , 'P'=>array(66,228, 14,13) );
  // $pdf_coords_epreuve_appr[130]   = array( 'G'=>array(85,134,108,13) , 'P'=>array(80,228,113,13) );
  // Avis circonstancié du chef d’établissement
  $pdf_coords_epreuve_eleve[CODE_BREVET_EPREUVE_TOTAL] = array( 'G'=>array( 15,72,47,11) , 'P'=>array( 15,77,48,13) );
  $pdf_coords_epreuve_appr[ CODE_BREVET_EPREUVE_TOTAL] = array( 'G'=>array(106,72,86,11) , 'P'=>array(109,77,82,13) );
  // Avis du conseil de classe
  $pdf_coords_epreuve_case['F'] = array( 'G'=>array(65.5,78.5,2,2) , 'P'=>array(67,84,2,2) );
  $pdf_coords_epreuve_case['D'] = array( 'G'=>array(65.5,75.5,2,2) , 'P'=>array(67,81,2,2) );
  // Précision LV1 || Langues vivantes
  $pdf_coords_precision[103] = array( 'G'=>array(27,132,28,3) , 'P'=>array(22,138,28,3) );
  // Précision LV2
  $pdf_coords_precision[110] = array( 'G'=>array(27,144,28,3) );
  // Précision Option facultative
  $pdf_coords_precision[113] = array( 'G'=>array(27,228,28,3) );
}
// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  extract($tab_eleve);  // $eleve_INE $eleve_nom $eleve_prenom $eleve_genre $date_naissance $eleve_brevet_serie
  $date_naissance = ($date_naissance) ? convert_date_mysql_to_french($date_naissance) : '' ;
  $eleve_brevet_serie_initiale = $eleve_brevet_serie{0};
  // Initialisation / Intitulé
  if($make_pdf)
  {
    // indiquer le fichier source
    $source_pdf = ($eleve_brevet_serie_initiale=='G') ? 'DNB_Fiche_scolaire_pour_le_jury_Serie_generale_2014_01' : 'DNB_Fiche_scolaire_pour_le_jury_Serie_professionnelle_2014_01' ;
    $fiche_brevet_PDF->setSourceFile('./_pdf/'.$source_pdf.'.pdf');
  }
  for( $numero_tirage=0 ; $numero_tirage<$nombre_tirages ; $numero_tirage++ )
  {
    if($make_pdf)
    {
      $etablissement = ($numero_tirage==0) ? 'Établissement' : 'Avertissement' ;
      // ajouter une page ; y importer la page 1 ; l'utiliser comme support
      $fiche_brevet_PDF->AddPage();
      $tplIdx = $fiche_brevet_PDF->importPage(1);
      $fiche_brevet_PDF->useTemplate($tplIdx);
      if($eleve_brevet_serie_initiale=='G') { $fiche_brevet_PDF->SetFillColor(255,95,36); } else { $fiche_brevet_PDF->SetFillColor(35,153,249); }
      $fiche_brevet_PDF->information( $pdf_coords_session[$eleve_brevet_serie_initiale] , 'Session' , $annee_session_brevet );
      $fiche_brevet_PDF->SetFillColor(255,255,255);
      $fiche_brevet_PDF->information( $pdf_coords_academie      , 'Académie'          , ':   '.$geo_academie_nom    );
      $fiche_brevet_PDF->information( $pdf_coords_departement   , 'Département'       , ':   '.$geo_departement_nom );
      $fiche_brevet_PDF->information( $pdf_coords_eleve_nom     , 'Nom'               , ':   '.$eleve_nom           );
      $fiche_brevet_PDF->information( $pdf_coords_eleve_prenom  , 'Prénom'            , ':   '.$eleve_prenom        );
      $fiche_brevet_PDF->information( $pdf_coords_eleve_date    , 'Date de naissance' , ':   '.$date_naissance      );
      $fiche_brevet_PDF->information( $pdf_coords_classe_nom    , 'Division'          , ':   '.$classe_nom          );
      $fiche_brevet_PDF->information( $pdf_coords_etablissement , $etablissement      ,        $tab_etabl_coords    );
    }
    // On passe en revue les épreuves...
    foreach($tab_brevet_epreuve[$eleve_brevet_serie] as $epreuve_code => $tab)
    {
      extract($tab);  // $epreuve_nom $epreuve_obligatoire $epreuve_note_chiffree $epreuve_point_sup_10 $epreuve_note_comptee $epreuve_coefficient $choix_matieres
      $moyenne_classe = (isset($tab_eleve_saisie[0][$epreuve_code])) ? $tab_eleve_saisie[0][$epreuve_code] : '' ;
      if(isset($tab_eleve_saisie[$eleve_id][$epreuve_code]))
      {
        extract($tab_eleve_saisie[$eleve_id][$epreuve_code]); // $matieres_id $prof_id $prof_info $appreciation $note
        $tab_matieres_utilisees = explode(',',$matieres_id);
        if( ($make_action=='tamponner') || (($make_action=='modifier')&&(count(array_intersect($tab_matieres_utilisees,$tab_matiere_id)))) || (($make_action=='examiner')&&(in_array($eleve_brevet_serie.'_'.$epreuve_code,$tab_rubrique))) || ($make_action=='consulter') || ($make_action=='imprimer') )
        {
          // Fiche brevet - Interface graphique
          if( $make_graph && $epreuve_note_chiffree )
          {
            $tab_graph_data['categories'           ][$epreuve_code] = '"'.addcslashes($epreuve_nom,'"').'"';
            $tab_graph_data['series_data_MoyEleve' ][$epreuve_code] = is_numeric($note)           ? $note           : 'null' ;
            $tab_graph_data['series_data_MoyClasse'][$epreuve_code] = is_numeric($moyenne_classe) ? $moyenne_classe : 'null' ;
          }
          // Fiche brevet - Note & Appréciation par épreuve
          foreach($tab_matieres_utilisees as $key => $matiere_id)
          {
            $tab_matieres_utilisees[$key] = $tab_matiere[$matiere_id];
          }
          if($make_html)
          {
            $note           = is_numeric($note)           ? sprintf("%04.1f",$note)           : $note ;
            $moyenne_classe = is_numeric($moyenne_classe) ? sprintf("%04.1f",$moyenne_classe) : $moyenne_classe ;
            $fiche_brevet_HTML .= '<table class="bilan" style="width:900px;margin-bottom:0"><tbody>'.NL;
            $fiche_brevet_HTML .= '<tr><th colspan="2">'.html($epreuve_nom).' [ '.html(implode(' ; ',$tab_matieres_utilisees)).' ]</th></tr>'.NL;
            $fiche_brevet_HTML .= '<tr><td class="now moyenne" style="width:'.$width_col1.'px">'.$note.'</td><td class="now" style="width:'.$width_col2.'px">Moyenne de classe : '.$moyenne_classe.'</td></tr>'.NL;
            if($appreciation)
            {
              $actions = '';
              if($make_action=='modifier')
              {
                $actions .= ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
              }
              elseif(in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')))
              {
                if($prof_id!=$_SESSION['USER_ID']) { $actions .= ' <button type="button" class="signaler">Signaler une faute</button>'; }
                if($droit_corriger_appreciation)   { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
              }
              $fiche_brevet_HTML .= '<tr id="appr_'.$eleve_brevet_serie.'_'.$epreuve_code.'_'.$prof_id.'"><td colspan="2" class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'.NL;
            }
            if($make_action=='modifier')
            {
              if(!$appreciation)
              {
                $fiche_brevet_HTML .= '<tr id="appr_'.$eleve_brevet_serie.'_'.$epreuve_code.'_'.$_SESSION['USER_ID'].'"><td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'appréciation.</button></div></td></tr>'.NL;
              }
            }
            $fiche_brevet_HTML .= '</tbody></table>'.NL;
          }
          if($make_pdf)
          {
            $note           = is_numeric($note)           ? number_format($note,1,',','')           : $note ;
            $moyenne_classe = is_numeric($moyenne_classe) ? number_format($moyenne_classe,1,',','') : $moyenne_classe ;
            if(isset($pdf_coords_precision[$epreuve_code][$eleve_brevet_serie_initiale]))
            {
              $fiche_brevet_PDF->precision( $pdf_coords_precision[$epreuve_code][$eleve_brevet_serie_initiale] , $tab_matieres_utilisees );
            }
            $fiche_brevet_PDF->note( $pdf_coords_epreuve_classe[$epreuve_code][$eleve_brevet_serie_initiale] , 'classe' /*type*/ , $moyenne_classe );
            $fiche_brevet_PDF->note( $pdf_coords_epreuve_eleve[ $epreuve_code][$eleve_brevet_serie_initiale] , 'eleve'  /*type*/ , $note           );
            if($appreciation)
            {
              $fiche_brevet_PDF->appreciation( $pdf_coords_epreuve_appr[  $epreuve_code][$eleve_brevet_serie_initiale] , $appreciation );
            }
          }
        }
      }
      elseif($epreuve_obligatoire)
      {
        exit('Absence de données concernant '.html($eleve_nom.' '.$eleve_prenom).' pour l\'épreuve '.html($epreuve_nom). '!');
      }
    }
    // Fiche brevet - Total des points & Avis de synthèse
    if(!isset($tab_eleve_saisie[$eleve_id][CODE_BREVET_EPREUVE_TOTAL]))
    {
      exit('Absence de total des points concernant '.html($eleve_nom.' '.$eleve_prenom).' !');
    }
    extract($tab_eleve_saisie[$eleve_id][CODE_BREVET_EPREUVE_TOTAL]); // $matieres_id $prof_id $prof_info $appreciation $note
    $moyenne_classe = $tab_eleve_saisie[0][CODE_BREVET_EPREUVE_TOTAL];
    if($appreciation)
    {
      $avis_conseil_classe = $appreciation{0};
      $appreciation = mb_substr($appreciation,2);
    }
    if( ($make_action=='tamponner') || ($make_action=='consulter') )
    {
      if( ($make_html) || ($make_graph) )
      {
        $note           = is_numeric($note)           ? sprintf("%05.1f",$note)           : $note ;
        $moyenne_classe = is_numeric($moyenne_classe) ? sprintf("%05.1f",$moyenne_classe) : $moyenne_classe ;
        $fiche_brevet_HTML .= '<table class="bilan" style="width:900px"><tbody>'.NL;
        $fiche_brevet_HTML .= '<tr><th style="width:'.$width_col1.'px">Total des points</th><th style="width:'.$width_col2.'px">Avis de synthèse (conseil de classe / chef d\'établissement)</th></tr>'.NL;
        $fiche_brevet_HTML .= '<tr><td class="now moyenne" style="width:'.$width_col1.'px">'.$note.'</td><td class="now" style="width:'.$width_col2.'px">Moyenne de classe : '.$moyenne_classe.'</td></tr>'.NL;
        if($appreciation)
        {
          $actions = '';
          if($make_action=='tamponner')
          {
            $actions .= ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
          }
          elseif(in_array($BILAN_ETAT,array('2rubrique','3mixte','4synthese')))
          {
            if($prof_id!=$_SESSION['USER_ID']) { $actions .= ' <button type="button" class="signaler">Signaler une faute</button>'; }
            if($droit_corriger_appreciation)   { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
          }
          $txt_avis_conseil_classe = ($avis_conseil_classe=='F') ? 'Avis favorable' : 'Doit faire ses preuves' ;
          $fiche_brevet_HTML .= '<tr id="appr_'.$eleve_brevet_serie.'_'.CODE_BREVET_EPREUVE_TOTAL.'_'.$prof_id.'"><td colspan="2" class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div><div id="avis_conseil_classe" class="b">'.html($txt_avis_conseil_classe).'</div></td></tr>'.NL;
        }
        elseif($make_action=='tamponner')
        {
          $fiche_brevet_HTML .= '<tr id="appr_'.$eleve_brevet_serie.'_'.CODE_BREVET_EPREUVE_TOTAL.'_'.$_SESSION['USER_ID'].'"><td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'avis de synthèse.</button></div></td></tr>'.NL;
        }
        $fiche_brevet_HTML .= '</tbody></table>'.NL;
      }
    }
    if($make_pdf)
    {
      $note = str_replace('.',',',$note);
      $fiche_brevet_PDF->note( $pdf_coords_epreuve_eleve[CODE_BREVET_EPREUVE_TOTAL][$eleve_brevet_serie_initiale] , 'total' /*type*/ , $note );
      if($appreciation)
      {
        $fiche_brevet_PDF->appreciation( $pdf_coords_epreuve_appr[ CODE_BREVET_EPREUVE_TOTAL][$eleve_brevet_serie_initiale] , $appreciation );
        $fiche_brevet_PDF->rectangle( $pdf_coords_epreuve_case[$avis_conseil_classe][$eleve_brevet_serie_initiale] );
      }
      $fiche_brevet_PDF->ligne_tag($tag_date_heure_initiales);
    }
    // Fiche brevet - Date de naissance
    if( ($date_naissance) && ( ($make_html) || ($make_graph) ) )
    {
      $fiche_brevet_HTML .= '<div class="i">'.texte_ligne_naissance($date_naissance).'</div>'.NL;
    }
    // Mémorisation des pages de début et de fin pour chaque élève pour découpe et archivage ultérieur
    if($make_action=='imprimer')
    {
      $page_numero++;
      $tab_pages_decoupe_pdf[$eleve_id][$numero_tirage] = array( $eleve_nom.' '.$eleve_prenom , $page_numero );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On enregistre la sortie PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf' , $fiche_brevet_PDF ); }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On fabrique les options js pour le diagramme graphique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $make_graph && (count($tab_graph_data)) )
{
  $js_graph .= '<SCRIPT>';
  // Épreuves sur l'axe des abscisses
  $js_graph .= 'ChartOptions.title.text = null;';
  $js_graph .= 'ChartOptions.xAxis.categories = ['.implode(',',$tab_graph_data['categories']).'];';
  // Séries de valeurs
  $tab_graph_series = array();
  if(isset($tab_graph_data['series_data_MoyClasse']))
  {
    $tab_graph_series['MoyClasse'] = '{ type: "line", name: "Moyenne classe", data: ['.implode(',',$tab_graph_data['series_data_MoyClasse']).'], marker: {symbol: "circle"}, color: "#999" }';
  }
  if(isset($tab_graph_data['series_data_MoyEleve']))
  {
    $tab_graph_series['MoyEleve']  = '{ type: "line", name: "Moyenne élève", data: ['.implode(',',$tab_graph_data['series_data_MoyEleve']).'], marker: {symbol: "circle"}, color: "#139" }';
  }
  $js_graph .= 'ChartOptions.series = ['.implode(',',$tab_graph_series).'];';
  $js_graph .= 'graphique = new Highcharts.Chart(ChartOptions);';
}
?>