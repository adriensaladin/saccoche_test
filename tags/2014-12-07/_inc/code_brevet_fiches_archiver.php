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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$classe_id = (isset($_POST['f_classe'])) ? Clean::entier($_POST['f_classe']) : 0;
$groupe_id = (isset($_POST['f_groupe'])) ? Clean::entier($_POST['f_groupe']) : 0;

$is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;

$bilan_type = 'brevet';
$annee_session_brevet = annee_session_brevet();

// On vérifie les paramètres principaux

if(!$classe_id)
{
  exit('Erreur avec les données transmises !');
}

// On vérifie que la fiche brevet est bien accessible en impression et on récupère les infos associées (nom de la classe, id des élèves concernés avec lesquels l'intersection est faite ultérieurement).

$DB_ROW = DB_STRUCTURE_BREVET::DB_recuperer_brevet_classe_infos($classe_id);
if(empty($DB_ROW))
{
  exit('Classe sans élèves concernés !');
}
$BILAN_ETAT = $DB_ROW['fiche_brevet'];
$classe_nom = $DB_ROW['groupe_nom'];
$tab_id_eleves_avec_notes = explode(',',$DB_ROW['listing_user_id']);

if(!$BILAN_ETAT)
{
  exit('Fiche brevet introuvable !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister les élèves concernés : soit d'une classe (en général) soit d'une classe ET d'un sous-groupe pour un prof affecté à un groupe d'élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
if(empty($DB_TAB))
{
  exit('Aucun élève trouvé dans ce regroupement !');
}
$tab_eleve_id = array();
foreach($DB_TAB as $DB_ROW)
{
  if(in_array($DB_ROW['user_id'],$tab_id_eleves_avec_notes))
  {
    $tab_eleve_id[] = $DB_ROW['user_id'];
  }
}
if(empty($tab_eleve_id))
{
  exit('Aucun élève concerné dans ce regroupement !');
}
$liste_eleve_id = implode(',',$tab_eleve_id);
$nb_eleves = count($tab_eleve_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de l'identité des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_eleve = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve_id , 'alpha' /*eleves_ordre*/ , FALSE /*with_gepi*/ , FALSE /*with_langue*/ , TRUE /*with_brevet_serie*/ );

if(!is_array($tab_eleve_infos))
{
  exit('Aucun élève trouvé correspondant aux identifiants transmis !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des séries de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_brevet_serie = array();
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  $tab_brevet_serie[$tab_eleve['eleve_brevet_serie']] = $tab_eleve['eleve_brevet_serie']; // Sera remplacé par le nom de la série après
}
if( !count($tab_brevet_serie) || isset($tab_brevet_serie['X']) )
{
  exit('Élève(s) trouvé(s) sans association avec une série de brevet !');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des noms des épreuves par série de brevet (probablement une seule)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_brevet_epreuve = array();
foreach($tab_brevet_serie as $serie_ref)
{
  $tab_brevet_epreuve[$serie_ref] = array();
  $DB_TAB = DB_STRUCTURE_BREVET::DB_lister_brevet_epreuves( $serie_ref , TRUE /*with_serie_nom*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_brevet_serie[$serie_ref] = $DB_ROW['brevet_serie_nom'];
    $tab_brevet_epreuve[$serie_ref][$DB_ROW['brevet_epreuve_code']] = $DB_ROW['brevet_epreuve_nom'];
  }
  $tab_brevet_epreuve[$serie_ref][CODE_BREVET_EPREUVE_TOTAL] = 'Avis de synthèse';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function suppression_sauts_de_ligne($texte)
{
  $tab_bad = array( "\r\n" , "\r" , "\n" );
  $tab_bon = ' ';
  return str_replace( $tab_bad , $tab_bon , $texte );
}

function nombre_de_ligne($texte)
{
  return max( 1 , ceil(mb_strlen($texte)/125) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1/5 imprimer_donnees_eleves_epreuves : Appréciations par épreuve pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_epreuves')
{
  // Récupérer les saisies enregistrées pour tous les collègues, pour toutes les épreuves
  $tab_saisie = array(); // [eleve_id][epreuve_code] => array(note,appreciation);
  $nb_lignes_epreuves = 0; // On compte 1 ligne par élève par rubrique, il peut falloir plus si l'appréciation est longue
  $DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_eleves( $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_brevet_serie']==$DB_ROW['brevet_serie_ref'])
    {
      $note = is_numeric($DB_ROW['saisie_note']) ? number_format($DB_ROW['saisie_note'],1,',','') : $DB_ROW['saisie_note'] ;
      if( ($DB_ROW['brevet_epreuve_code']!=CODE_BREVET_EPREUVE_TOTAL) || !$DB_ROW['saisie_appreciation'] )
      {
        $appreciation = $DB_ROW['saisie_appreciation'];
      }
      else
      {
        $avis_conseil_classe = $DB_ROW['saisie_appreciation']{0};
        $txt_avis_conseil_classe = ($avis_conseil_classe=='F') ? 'Avis favorable - ' : 'Doit faire ses preuves - ' ;
        $appreciation = $txt_avis_conseil_classe.mb_substr($DB_ROW['saisie_appreciation'],2);
      }
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['brevet_epreuve_code']] = array( 'note'=>$note , 'appreciation'=>suppression_sauts_de_ligne($appreciation) );
      $nb_lignes_epreuves += nombre_de_ligne($DB_ROW['saisie_appreciation']);
    }
  }
  // Fabrication du PDF
  $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $releve_PDF->tableau_appreciation_initialiser_eleves_collegues( $nb_eleves , $nb_lignes_epreuves );
  $releve_PDF->tableau_appreciation_intitule( 'Fiches Brevet - '.$annee_session_brevet.' - '.$classe_nom.' - '.'Appréciations par élève' );
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  foreach($tab_eleve_id as $eleve_id)
  {
    extract($tab_eleve_infos[$eleve_id]);  // $eleve_nom $eleve_prenom $date_naissance $eleve_brevet_serie
    $releve_PDF->tableau_appreciation_epreuve_eleves_collegues_thead($eleve_nom,$eleve_prenom,$tab_brevet_serie[$eleve_brevet_serie]);
    if(isset($tab_saisie[$eleve_id]))
    {
      foreach($tab_saisie[$eleve_id] as $epreuve_code =>$tab)
      {
        extract($tab);  // $note $appreciation
        $releve_PDF->tableau_appreciation_epreuve_eleves_collegues_tbody( $tab_brevet_epreuve[$eleve_brevet_serie][$epreuve_code] , $note , $appreciation );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2/3 imprimer_donnees_eleves_syntheses : Avis de synthèse pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_syntheses')
{
  // Récupérer les saisies enregistrées pour l'avis de synthèse
  $tab_saisie = array(); // [eleve_id] => array(note,appreciation);
  $nb_lignes_epreuves = 0; // On compte 1 ligne par élève par rubrique, il peut falloir plus si l'appréciation est longue
  $DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_eleves( $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , TRUE /*only_total*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_brevet_serie']==$DB_ROW['brevet_serie_ref'])
    {
      $note = is_numeric($DB_ROW['saisie_note']) ? number_format($DB_ROW['saisie_note'],1,',','') : $DB_ROW['saisie_note'] ;
      if( ($DB_ROW['brevet_epreuve_code']!=CODE_BREVET_EPREUVE_TOTAL) || !$DB_ROW['saisie_appreciation'] )
      {
        $appreciation = $DB_ROW['saisie_appreciation'];
      }
      else
      {
        $avis_conseil_classe = $DB_ROW['saisie_appreciation']{0};
        $txt_avis_conseil_classe = ($avis_conseil_classe=='F') ? 'Avis favorable - ' : 'Doit faire ses preuves - ' ;
        $appreciation = $txt_avis_conseil_classe.mb_substr($DB_ROW['saisie_appreciation'],2);
      }
      $tab_saisie[$DB_ROW['eleve_id']] = array( 'note'=>$note , 'appreciation'=>suppression_sauts_de_ligne($appreciation) );
      $nb_lignes_epreuves += nombre_de_ligne($DB_ROW['saisie_appreciation']);
    }
  }
  // Fabrication du PDF
  $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $releve_PDF->tableau_appreciation_initialiser_eleves_syntheses( $nb_eleves , $nb_lignes_epreuves , TRUE /*with_moyenne*/ );
  $releve_PDF->tableau_appreciation_intitule( 'Fiches Brevet - '.$annee_session_brevet.' - '.$classe_nom.' - '.'Avis de synthèse' );
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  foreach($tab_eleve_id as $eleve_id)
  {
    extract($tab_eleve_infos[$eleve_id]);  // $eleve_nom $eleve_prenom $date_naissance $eleve_brevet_serie
    if(isset($tab_saisie[$eleve_id]))
    {
      extract($tab_saisie[$eleve_id]);  // $note $appreciation
    }
    else
    {
      $note = NULL;
      $appreciation = '';
    }
    $releve_PDF->tableau_appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , TRUE /*with_moyenne*/ , TRUE /*is_brevet*/ );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3/3 imprimer_donnees_eleves_moyennes : Tableau des notes pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_moyennes')
{
  // Récupérer les notes enregistrées pour tous les collègues, pour toutes les épreuves
  $tab_saisie = array(); // [eleve_id][epreuve_code] => note;
  $DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_eleves( $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_brevet_serie']==$DB_ROW['brevet_serie_ref'])
    {
      $note = is_numeric($DB_ROW['saisie_note']) ? number_format($DB_ROW['saisie_note'],1,',','') : $DB_ROW['saisie_note'] ;
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['brevet_serie_ref'].$DB_ROW['brevet_epreuve_code']] = $note;
    }
  }

  // Récupérer les notes enregistrées pour la classe
  $DB_TAB = DB_STRUCTURE_BREVET::DB_recuperer_brevet_saisies_classe( $classe_id , 0 /*prof_id*/ , FALSE /*with_epreuve_nom*/ , FALSE /*only_total*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $note = is_numeric($DB_ROW['saisie_note']) ? number_format($DB_ROW['saisie_note'],1,',','') : $DB_ROW['saisie_note'] ;
    $tab_saisie[0][$DB_ROW['brevet_serie_ref'].$DB_ROW['brevet_epreuve_code']] = $note;
  }

  // Pour insérer le groupe classe en dernier
  $tab_eleve_id[] = 0;
  $tab_eleve_infos[0] = array( 'eleve_nom' => $classe_nom ,  'eleve_prenom' => '' );

  // Fabrication du PDF ; on a besoin de tourner du texte à 90°
  // Fabrication d'un CSV en parallèle
  $tab_brevet_epreuve[$serie_ref][CODE_BREVET_EPREUVE_TOTAL] = 'Total des points';
  $nb_epreuves = count($tab_brevet_epreuve,COUNT_RECURSIVE) - count($tab_brevet_epreuve) ;
  $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $releve_PDF->tableau_moyennes_initialiser( $nb_eleves+1 , $nb_epreuves );
  $releve_CSV = '';
  $separateur = ';';
  // 1ère ligne : intitulés, noms rubriques
  $releve_PDF->tableau_moyennes_intitule( $classe_nom , 'Session '.$annee_session_brevet , TRUE /*is_brevet*/ );
  $releve_CSV .= '"'.$classe_nom.' | Session '.$annee_session_brevet.'"';
  foreach($tab_brevet_serie as $serie_ref => $serie_nom)
  {
    foreach($tab_brevet_epreuve[$serie_ref] as $epreuve_ref => $epreuve_nom)
    {
      $releve_PDF->tableau_moyennes_reference_rubrique( $epreuve_ref , $epreuve_nom );
      $releve_CSV .= $separateur.'"'.$epreuve_nom.'"';
    }
  }
  $releve_CSV .= "\r\n";
  // ligne suivantes : élèves, notes
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  $releve_PDF->SetXY( $releve_PDF->marge_gauche , $releve_PDF->marge_haut+$releve_PDF->etiquette_hauteur );
  foreach($tab_eleve_id as $eleve_id)
  {
    extract($tab_eleve_infos[$eleve_id]);  // $eleve_nom $eleve_prenom $date_naissance $eleve_brevet_serie
    $releve_PDF->tableau_moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
    $releve_CSV .= '"'.$eleve_nom.' '.$eleve_prenom.'"';
    foreach($tab_brevet_serie as $serie_ref => $serie_nom)
    {
      foreach($tab_brevet_epreuve[$serie_ref] as $epreuve_ref => $epreuve_nom)
      {
        $note = (isset($tab_saisie[$eleve_id][$serie_ref.$epreuve_ref])) ? $tab_saisie[$eleve_id][$serie_ref.$epreuve_ref] : NULL ;
        $releve_PDF->tableau_moyennes_note( $eleve_id , $epreuve_ref , $note , TRUE /*is_brevet*/ );
        $releve_CSV .= $separateur.'"'.str_replace('.',',',$note).'"'; // Remplacer le point décimal par une virgule pour le tableur.
      }
    }
    $releve_PDF->SetXY($releve_PDF->marge_gauche , $releve_PDF->GetY()+$releve_PDF->cases_hauteur);
    $releve_CSV .= "\r\n";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement et affichage du retour.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_export = 'saisies_'.$bilan_type.'_'.$annee_session_brevet.'_'.Clean::fichier($classe_nom).'_'.$action.'_'.fabriquer_fin_nom_fichier__date_et_alea();
FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.pdf' , $releve_PDF );
echo'<a target="_blank" href="'.URL_DIR_EXPORT.$fichier_export.'.pdf"><span class="file file_pdf">'.$tab_actions[$action].' (format <em>pdf</em>).</span></a>';
// Et le csv éventuel
if($action=='imprimer_donnees_eleves_moyennes')
{
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.csv' , To::csv($releve_CSV) );
  echo'<br />'.NL.'<a target="_blank" href="./force_download.php?fichier='.$fichier_export.'.csv"><span class="file file_txt">'.$tab_actions[$action].' (format <em>csv</em>).</span></a>';
}
exit();

?>
