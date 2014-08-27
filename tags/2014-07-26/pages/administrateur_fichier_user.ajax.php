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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? $_POST['f_action']              : '';
$mode   = (isset($_POST['f_mode']))   ? $_POST['f_mode']                : '';
$step   = (isset($_POST['f_step']))   ? Clean::entier($_POST['f_step']) : 0;

$tab_actions = array(
  'sconet_professeurs_directeurs_oui' => array('sconet'     ,'professeur'),
  'sconet_eleves_oui'                 => array('sconet'     ,'eleve'     ),
  'sconet_parents_oui'                => array('sconet'     ,'parent'    ),
  'base_eleves_eleves'                => array('base_eleves','eleve'     ),
  'base_eleves_parents'               => array('base_eleves','parent'    ),
  'tableur_professeurs_directeurs'    => array('tableur'    ,'professeur'),
  'tableur_eleves'                    => array('tableur'    ,'eleve'     ),
  'tableur_parents'                   => array('tableur'    ,'parent'    ),
);

if( !isset($tab_actions[$action]) )
{
  exit('Erreur avec les données transmises !');
}

list( $import_origine , $import_profil ) = $tab_actions[$action];

$tab_etapes  = array();

$tab_extensions_autorisees = ($import_origine=='sconet') ? array('zip','xml') : array('txt','csv') ;
$extension_fichier_dest    = ($import_origine=='sconet') ? 'xml'              : 'txt' ;
$fichier_dest = 'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'.'.$extension_fichier_dest ;

function load_fichier($nom)
{
  global $import_origine,$import_profil;
  $fnom = CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_'.$nom.'.txt';
  if(!is_file($fnom))
  {
    exit('Erreur : le fichier contenant les données à traiter est introuvable !');
  }
  $contenu = file_get_contents($fnom);
  $tableau = @unserialize($contenu);
  if($tableau===FALSE)
  {
    exit('Erreur : le fichier contenant les données à traiter est syntaxiquement incorrect !');
  }
  return $tableau;
}

function afficher_etapes($import_origine,$import_profil)
{
  $puces = '<ul id="step">'.NL;
  switch($import_origine.'+'.$import_profil)
  {
    case  'sconet+professeur' :
    case 'tableur+professeur' :
    case  'sconet+eleve'      :
    case 'tableur+eleve'      :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step4">Étape 4 - Groupes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 5 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step6">Étape 6 - Affectations (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 7 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case      'sconet+parent' :
    case 'base_eleves+parent' :
    case     'tableur+parent' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step5">Étape 3 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step7">Étape 4 - Adresses (ajouts / modifications)</li>'.NL;
      $puces .= '<li id="step8">Étape 5 - Responsabilités (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 6 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case  'base_eleves+eleve' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 4 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 5 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
  }
  $puces .= '</ul>'.NL;
  return $puces;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 10 - Récupération du fichier (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==10 )
{
  // Nom du fichier à extraire si c'est un fichier zippé
  $alerte = '';
  if( ($import_origine=='sconet') && ($import_profil=='eleve') )
  {
    $nom_fichier_extrait = 'ElevesSansAdresses.xml';
    if( (isset($_FILES['userfile']['name'])) && (strpos($_FILES['userfile']['name'],'ElevesAvecAdresses')) )
    {
      $nom_fichier_extrait = 'ElevesAvecAdresses.xml';
      $alerte = '<p class="danger">Vous avez fourni le fichier <span class="u b">avec</span> adresses ! Vous pouvez toutefois poursuivre&hellip;</p>'.NL;
    }
  }
  else if( ($import_origine=='sconet') && ($import_profil=='parent') )
  {
    $nom_fichier_extrait = 'ResponsablesAvecAdresses.xml';
    if( (isset($_FILES['userfile']['name'])) && (strpos($_FILES['userfile']['name'],'ElevesSansAdresses')) )
    {
      $nom_fichier_extrait = 'ResponsablesSansAdresses.xml';
      $alerte = '<p class="danger">Vous avez fourni le fichier <span class="u b">sans</span> adresses ! Si vous poursuivez, sachez que les adresses ne seront pas trouvées&hellip;</p>'.NL;
    }
  }
  else
  {
    $annee_scolaire  = (date('n')>7) ? date('Y') : date('Y')-1 ;
    $nom_fichier_extrait = 'sts_emp_'.$_SESSION['WEBMESTRE_UAI'].'_'.$annee_scolaire.'.xml';
  }
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_dest /*fichier_nom*/ , $tab_extensions_autorisees , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , $nom_fichier_extrait /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  // On affiche le bilan et les puces des étapes
  echo'<hr />'.NL;
  echo ($mode=='complet') ? '<p class="astuce">Affichage complet des analyses et des comptes-rendus.</p>' : '<p class="astuce">Analyse restreinte aux seules différences trouvées et comptes-rendus non détaillés.</p>'.NL;
  echo afficher_etapes($import_origine,$import_profil);
  echo'<hr />'.NL;
  echo'<fieldset>'.NL;
  echo  '<div><label class="valide">Votre fichier a été correctement réceptionné.</label></div>'.NL;
  echo  $alerte;
  echo  '<ul class="puce p"><li><a href="#step20" id="passer_etape_suivante">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  echo'</fieldset>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 20 - Extraction des données (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==20 )
{
  if(!is_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest))
  {
    exit('Erreur : le fichier récupéré et enregistré n\'a pas été retrouvé !');
  }
  // Pour récupérer les données des utilisateurs ; on prend comme indice $sconet_id ou $reference suivant le mode d'import
  /*
   * On utilise la forme moins commode   ['nom'][i]=... ['prenom'][i]=...
   * au lieu de la forme plus habituelle [i]['nom']=... [i]['prenom']=...
   * parce qu'ensuite cela permet d'effectuer un tri multicolonnes.
   */
  $tab_users_fichier                 = array();
  $tab_users_fichier['sconet_id']    = array();
  $tab_users_fichier['sconet_num']   = array();
  $tab_users_fichier['reference']    = array();
  $tab_users_fichier['profil_sigle'] = array(); // Notamment pour distinguer les personnels
  $tab_users_fichier['nom']          = array();
  $tab_users_fichier['prenom']       = array();
  $tab_users_fichier['classe']       = array(); // Avec id sconet_id ou reference // Classe de l'élève || Classes du professeur, avec indication PP
  $tab_users_fichier['groupe']       = array(); // Avec id sconet_id // Groupes de l'élève || Groupes du professeur
  $tab_users_fichier['matiere']      = array(); // Avec id sconet_id // Matières du professeur, avec indication méthode récupération
  $tab_users_fichier['adresse']      = array(); // Avec id sconet_id // Adresse du responsable légal
  $tab_users_fichier['enfant']       = array(); // Avec id sconet_id // Liste des élèves rattachés
  $tab_users_fichier['birth_date']   = array();
  // Pour récupérer les données des classes et des groupes
  $tab_classes_fichier['ref']    = array();
  $tab_classes_fichier['nom']    = array();
  $tab_classes_fichier['niveau'] = array();
  $tab_groupes_fichier['ref']    = array();
  $tab_groupes_fichier['nom']    = array();
  $tab_groupes_fichier['niveau'] = array();
  // Pour retenir à part les dates de sortie Sconet des élèves
  $_SESSION['tmp']['date_sortie'] = array();
  // Procédures différentes suivant le mode d'import...
  if( ($import_origine=='sconet') && ($import_profil=='professeur') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2a - Extraction sconet_professeurs_directeurs
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    if($xml===FALSE)
    {
      exit('Erreur : le fichier transmis n\'est pas un XML valide !');
    }
    $editeur_prive_edt = (string)$xml->PARAMETRES->APPLICATION_SOURCE;
    if($editeur_prive_edt)
    {
      exit('Erreur : le fichier transmis est issu d\'un éditeur privé d\'emploi du temps, pas de STS !');
    }
    $uai = @(string)$xml->PARAMETRES->UAJ->attributes()->CODE;
    if(!$uai)
    {
      exit('Erreur : le contenu du fichier transmis ne correspond pas à ce qui est attendu !');
    }
    if($uai!=$_SESSION['WEBMESTRE_UAI'])
    {
      exit('Erreur : le fichier transmis est issu de l\'établissement '.$uai.' et non '.$_SESSION['WEBMESTRE_UAI'].' !');
    }
    /*
     * Les matières des profs peuvent être récupérées de 2 façons :
     * 1. $xml->DONNEES->INDIVIDUS->INDIVIDU->DISCIPLINES->DISCIPLINE->attributes()->CODE
     *    On récupère alors un code formé d'une lettre (L ou C) et de 4 chiffres (matières que le prof est apte à enseigner, son service peut préciser les choses...).
     *    Je n'ai pas trouvé de correspondance officielle &rarr; Le tableau $tab_discipline_code_discipline_TO_matiere_code_gestion donne les principales.
     */
    $tab_discipline_code_discipline_TO_matiere_code_gestion = array();
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0080'] = array('DOC');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0100'] = array('PHILO');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.02..'] = array('FRANC');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0421'] = array('ALL1','ALL2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0422'] = array('AGL1','AGL2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0424'] = array('CHI1','CHI2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0426'] = array('ESP1','ESP2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0429'] = array('ITA1','ITA2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0433'] = array('POR1','POR2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.0434'] = array('RUS1','RUS2');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1000'] = array('HIGEO','EDCIV');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1100'] = array('SES');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1300'] = array('MATHS');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1315'] = array('MATHS','PH-CH');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1400'] = array('TECHN');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1500'] = array('PH-CH');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1600'] = array('SVT');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1615'] = array('SVT','PH-CH');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1700'] = array('EDMUS');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1800'] = array('A-PLA');
    $tab_discipline_code_discipline_TO_matiere_code_gestion['.1900'] = array('EPS');
    /*
     * Les matières des profs peuvent être récupérées de 2 façons :
     * 2. $xml->DONNEES->STRUCTURES->DIVISIONS->DIVISION->SERVICES->SERVICE->attributes()->CODE_MATIERE
     *    On récupère alors, si l'emploi du temps est rensigné, un code expliqué dans $xml->NOMENCLATURES->MATIERES->MATIERE.
     *    &rarr; Le tableau $tab_matiere_code_matiere_TO_matiere_code_gestion liste ce contenu des nomenclatures.
     */
    $tab_matiere_code_matiere_TO_matiere_code_gestion = array();
    if( ($xml->NOMENCLATURES) && ($xml->NOMENCLATURES->MATIERES) && ($xml->NOMENCLATURES->MATIERES->MATIERE) )
    {
      foreach ($xml->NOMENCLATURES->MATIERES->MATIERE as $matiere)
      {
        $matiere_code_matiere = (string) $matiere->attributes()->CODE; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
        $matiere_code_gestion = (string) $matiere->CODE_GESTION; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
        $tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere] = $matiere_code_gestion;
      }
    }
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos, y compris les PP, d'éventuelles matières affectées, d'éventuelles classes présentes
    //
    $date_aujourdhui = date('Y-m-d');
    if( ($xml->DONNEES) && ($xml->DONNEES->INDIVIDUS) && ($xml->DONNEES->INDIVIDUS->INDIVIDU) )
    {
      foreach ($xml->DONNEES->INDIVIDUS->INDIVIDU as $individu)
      {
        $fonction = Clean::ref($individu->FONCTION) ;
        if( (isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$fonction])) && (in_array($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$fonction],array('professeur','directeur'))) )
        {
          $sconet_id = Clean::entier($individu->attributes()->ID);
          $i_fichier  = $sconet_id;
          $tab_users_fichier['sconet_id'   ][$i_fichier] = $sconet_id;
          $tab_users_fichier['sconet_num'  ][$i_fichier] = 0;
          $tab_users_fichier['reference'   ][$i_fichier] = '';
          $tab_users_fichier['profil_sigle'][$i_fichier] = $fonction;
          $tab_users_fichier['nom'         ][$i_fichier] = Clean::nom($individu->NOM_USAGE);
          $tab_users_fichier['prenom'      ][$i_fichier] = Clean::prenom($individu->PRENOM);
          $tab_users_fichier['classe'      ][$i_fichier] = array();
          $tab_users_fichier['groupe'      ][$i_fichier] = array();
          $tab_users_fichier['matiere'     ][$i_fichier] = array();
          // Indication éventuelle de professeur principal
          if( ($individu->PROFS_PRINC) && ($individu->PROFS_PRINC->PROF_PRINC) )
          {
            foreach ($individu->PROFS_PRINC->PROF_PRINC as $prof_princ)
            {
              $classe_ref = Clean::ref($prof_princ->CODE_STRUCTURE);
              $date_fin   = Clean::ref($prof_princ->DATE_FIN);
              $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              if($date_fin >= $date_aujourdhui)
              {
                $tab_users_fichier['classe'][$i_fichier][$i_classe] = 'PP';
              }
              // Au passage on ajoute la classe trouvée
              if(!isset($tab_classes_fichier['ref'][$i_classe]))
              {
                $tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
                $tab_classes_fichier['nom'][$i_classe]    = $classe_ref;
                $tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
              }
            }
          }
          // Indication éventuelle des matières du professeur (toujours renseigné pour les profs, mais matières potentielles et non effectivement enseignées, et usage d'un code discipline pas commode à décrypter)
          if( ($individu->DISCIPLINES) && ($individu->DISCIPLINES->DISCIPLINE) )
          {
            foreach ($individu->DISCIPLINES->DISCIPLINE as $discipline)
            {
              $discipline_code_discipline = (string) $discipline->attributes()->CODE;
              foreach ($tab_discipline_code_discipline_TO_matiere_code_gestion as $masque_recherche => $tab_matiere_code_gestion)
              {
                if(preg_match('/^'.$masque_recherche.'$/',$discipline_code_discipline))
                {
                  foreach ($tab_matiere_code_gestion as $matiere_code_gestion)
                  {
                    $tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'discipline';
                  }
                  break;
                }
              }
            }
          }
        }
      }
    }
    //
    // On passe les classes en revue : on mémorise leurs infos, y compris les profs rattachés éventuels, et les matières associées
    //
    if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURE) && ($xml->DONNEES->STRUCTURE->DIVISIONS) && ($xml->DONNEES->STRUCTURE->DIVISIONS->DIVISION) )
    {
      foreach ($xml->DONNEES->STRUCTURE->DIVISIONS->DIVISION as $division)
      {
        $classe_ref = Clean::ref($division->attributes()->CODE);
        $classe_nom = Clean::texte($division->LIBELLE_LONG);
        $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
        // Au passage on ajoute la classe trouvée
        if(!isset($tab_classes_fichier['ref'][$i_classe]))
        {
          $tab_classes_fichier['ref'   ][$i_classe] = $classe_ref;
          $tab_classes_fichier['nom'   ][$i_classe] = $classe_nom;
          $tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
        }
        else
        {
          $tab_classes_fichier['nom'][$i_classe]    = $classe_nom;
        }
        if( ($division->SERVICES) && ($division->SERVICES->SERVICE) )
        {
          foreach ($division->SERVICES->SERVICE as $service)
          {
            $matiere_code_matiere = (string) $service->attributes()->CODE_MATIERE; // (string) obligatoire sinon pb avec une clef commençant par 0...
            if( ($service->ENSEIGNANTS) && ($service->ENSEIGNANTS->ENSEIGNANT) )
            {
              foreach ($service->ENSEIGNANTS->ENSEIGNANT as $enseignant)
              {
                $i_fichier = Clean::entier($enseignant->attributes()->ID);
                // Il arrive que des individus soient présents dans le fichier mais sans fonction ($xml->DONNEES->INDIVIDUS->INDIVIDU->FONCTION)
                // Ce peut être un congé longue maladie, un congé maternité, une retraite en cours d'année...
                // Du coup, ils ne sont pas récupérés dans $tab_users_fichier[]
                // Par contre, il peut y avoir dans le fichier des reliquats de services (associations classes et matières)
                // Il faut les ignorer sous peine de récolter "array_multisort(): Array sizes are inconsistent" en fin d'étape 2.
                if(isset($tab_users_fichier['sconet_id'][$i_fichier]))
                {
                  // associer la classe au prof
                  if(!isset($tab_users_fichier['classe'][$i_fichier][$i_classe]))
                  {
                    $tab_users_fichier['classe'][$i_fichier][$i_classe] = 'prof';
                  }
                  // associer la matière au prof
                  if(isset($tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere]))
                  {
                    $matiere_code_gestion = $tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere];
                    $tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'service';
                  }
                }
              }
            }
          }
        }
      }
    }
    //
    // On passe les groupes en revue : on mémorise leurs infos, y compris les profs rattachés éventuels, et les matières associées
    //
    if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURE) && ($xml->DONNEES->STRUCTURE->GROUPES) && ($xml->DONNEES->STRUCTURE->GROUPES->GROUPE) )
    {
      foreach ($xml->DONNEES->STRUCTURE->GROUPES->GROUPE as $groupe)
      {
        $groupe_ref = Clean::ref($groupe->attributes()->CODE);
        $groupe_nom = Clean::texte($groupe->LIBELLE_LONG);
        $i_groupe   = 'i'.Clean::login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
        // Au passage on ajoute le groupe trouvé
        if(!isset($tab_groupes_fichier['ref'][$i_groupe]))
        {
          $tab_groupes_fichier['ref'   ][$i_groupe] = $groupe_ref;
          $tab_groupes_fichier['nom'   ][$i_groupe] = $groupe_nom;
          $tab_groupes_fichier['niveau'][$i_groupe] = $groupe_ref;
        }
        if( ($groupe->SERVICES) && ($groupe->SERVICES->SERVICE) )
        {
          foreach ($groupe->SERVICES->SERVICE as $service)
          {
            $matiere_code_matiere = (string) $service->attributes()->CODE_MATIERE; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
            if( ($service->ENSEIGNANTS) && ($service->ENSEIGNANTS->ENSEIGNANT) )
            {
              foreach ($service->ENSEIGNANTS->ENSEIGNANT as $enseignant)
              {
                $i_fichier = Clean::entier($enseignant->attributes()->ID);
                // Il arrive que des individus soient présents dans le fichier mais sans fonction ($xml->DONNEES->INDIVIDUS->INDIVIDU->FONCTION)
                // Ce peut être un congé longue maladie, un congé maternité, une retraite en cours d'année...
                // Du coup, ils ne sont pas récupérés dans $tab_users_fichier[]
                // Par contre, il peut y avoir dans le fichier des reliquats de services (associations classes et matières)
                // Il faut les ignorer sous peine de récolter "array_multisort(): Array sizes are inconsistent" en fin d'étape 2.
                if(isset($tab_users_fichier['sconet_id'][$i_fichier]))
                {
                  // associer le groupe au prof
                  if(!isset($tab_users_fichier['groupe'][$i_fichier][$i_groupe]))
                  {
                    $tab_users_fichier['groupe'][$i_fichier][$i_groupe] = 'prof';
                  }
                  // associer la matière au prof
                  if(isset($tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere]))
                  {
                    $matiere_code_gestion = $tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere];
                    $tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'service';
                  }
                }
              }
            }
          }
        }
      }
    }
  }
  if( ($import_origine=='sconet') && ($import_profil=='eleve') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2b - Extraction sconet_eleves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    if($xml===FALSE)
    {
      exit('Erreur : le fichier transmis n\'est pas un XML valide !');
    }
    $uai = $xml->PARAMETRES->UAJ;
    if($uai===FALSE)
    {
      exit('Erreur : le fichier transmis n\'est pas correct (erreur de numéro UAI) !');
    }
    // tableau temporaire qui sera effacé, servant à retenir le niveau de l'élève en attendant de connaître sa classe.
    $tab_users_fichier['niveau'] = array();
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos, plus leur niveau
    //
    if( ($xml->DONNEES) && ($xml->DONNEES->ELEVES) && ($xml->DONNEES->ELEVES->ELEVE) )
    {
      foreach ($xml->DONNEES->ELEVES->ELEVE as $eleve)
      {
        $i_fichier = Clean::entier($eleve->attributes()->ELEVE_ID);
        if($eleve->DATE_SORTIE)
        {
          $_SESSION['tmp']['date_sortie'][$i_fichier] = (string) $eleve->DATE_SORTIE; // format fr (jj/mm/aaaa)
        }
        else
        {
          $tab_users_fichier['sconet_id'   ][$i_fichier] = $i_fichier;
          $tab_users_fichier['sconet_num'  ][$i_fichier] = Clean::entier($eleve->attributes()->ELENOET);
          $tab_users_fichier['reference'   ][$i_fichier] = Clean::ref($eleve->ID_NATIONAL);
          $tab_users_fichier['profil_sigle'][$i_fichier] = 'ELV' ;
          $tab_users_fichier['nom'         ][$i_fichier] = Clean::nom($eleve->NOM);
          $tab_users_fichier['prenom'      ][$i_fichier] = Clean::prenom($eleve->PRENOM);
          $tab_users_fichier['birth_date'  ][$i_fichier] = Clean::texte($eleve->DATE_NAISS);
          $tab_users_fichier['classe'      ][$i_fichier] = '';
          $tab_users_fichier['groupe'      ][$i_fichier] = array();
          $tab_users_fichier['niveau'      ][$i_fichier] = Clean::ref($eleve->CODE_MEF);
        }
      }
    }
    //
    // On passe les liaisons élèves/classes-groupes en revue : on mémorise leurs infos, et les élèves rattachés
    //
    if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURES) && ($xml->DONNEES->STRUCTURES->STRUCTURES_ELEVE) )
    {
      foreach ($xml->DONNEES->STRUCTURES->STRUCTURES_ELEVE as $structures_eleve)
      {
        $i_fichier = Clean::entier($structures_eleve->attributes()->ELEVE_ID);
        if(!isset($_SESSION['tmp']['date_sortie'][$i_fichier]))  // les élèves marqués comme sortis de l'établissement sont encore dans le fichier reliés à une classe et d'autres bricoles...
        {
          foreach ($structures_eleve->STRUCTURE as $structure)
          {
            if($structure->TYPE_STRUCTURE == 'D')
            {
              $classe_ref = Clean::ref($structure->CODE_STRUCTURE);
              $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              $tab_users_fichier['classe'][$i_fichier] = $i_classe;
              if(!isset($tab_classes_fichier['ref'][$i_classe]))
              {
                $tab_classes_fichier['ref'   ][$i_classe] = $classe_ref;
                $tab_classes_fichier['nom'   ][$i_classe] = $classe_ref;
                $tab_classes_fichier['niveau'][$i_classe] = '';
              }
              if($tab_users_fichier['niveau'][$i_fichier])
              {
                $tab_classes_fichier['niveau'][$i_classe] = $tab_users_fichier['niveau'][$i_fichier];
              }
            }
            elseif($structure->TYPE_STRUCTURE == 'G')
            {
              $groupe_ref = Clean::ref($structure->CODE_STRUCTURE);
              $i_groupe   = 'i'.Clean::login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              if(!isset($tab_users_fichier['groupe'][$i_fichier][$i_groupe]))
              {
                $tab_users_fichier['groupe'][$i_fichier][$i_groupe] = $groupe_ref;
              }
              if(!isset($tab_groupes_fichier['ref'][$i_groupe]))
              {
                $tab_groupes_fichier['ref'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['nom'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['niveau'][$i_groupe] = '';
              }
              if($tab_users_fichier['niveau'][$i_fichier])
              {
                $tab_groupes_fichier['niveau'][$i_groupe] = $tab_users_fichier['niveau'][$i_fichier];
              }
            }
          }
        }
      }
    }
    // suppression du tableau temporaire
    unset($tab_users_fichier['niveau']);
  }
  if( ($import_origine=='sconet') && ($import_profil=='parent') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2c - Extraction sconet_parents
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    if($xml===FALSE)
    {
      exit('Erreur : le fichier transmis n\'est pas un XML valide !');
    }
    $uai = (string)$xml->PARAMETRES->UAJ;
    if(!$uai)
    {
      exit('Erreur : le fichier transmis ne comporte pas de numéro UAI !');
    }
    if($uai!=$_SESSION['WEBMESTRE_UAI'])
    {
      exit('Erreur : le fichier transmis est issu de l\'établissement '.$uai.' et non '.$_SESSION['WEBMESTRE_UAI'].' !');
    }
    //
    // On recense les adresses dans un tableau temporaire.
    //
    $tab_adresses = array();
    if( ($xml->DONNEES) && ($xml->DONNEES->ADRESSES) && ($xml->DONNEES->ADRESSES->ADRESSE) )
    {
      foreach ($xml->DONNEES->ADRESSES->ADRESSE as $adresse)
      {
        $tab_adresses[Clean::entier($adresse->attributes()->ADRESSE_ID)] = array( Clean::adresse($adresse->LIGNE1_ADRESSE) , Clean::adresse($adresse->LIGNE2_ADRESSE) , Clean::adresse($adresse->LIGNE3_ADRESSE) , Clean::adresse($adresse->LIGNE4_ADRESSE) , Clean::entier($adresse->CODE_POSTAL) , Clean::commune($adresse->LIBELLE_POSTAL) , Clean::pays($adresse->LL_PAYS) );
      }
    }
    $nb_adresses = count($tab_adresses);
    // L'import Sconet peut apporter beaucoup de parents rattachés à des élèves sortis de l'établissement et encore présents dans le fichier.
    // Alors on récupère la liste des id_sconet des élèves actuels et on contrôle par la suite qu'il est dans la liste des enfants du parent.
    // Par ailleurs, l'import de base-élèves n'utilise pas les id sconet : il est donc plus facile de prendre les id de la base comme indices du tableau des enfants pour harmoniser les procédures.
    $tab_eleves_actuels = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' /*profil_type*/ , 1 /*only_actuels*/ , 'user_id,user_sconet_id' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleves_actuels[$DB_ROW['user_sconet_id']] = $DB_ROW['user_id'];
    }
    //
    // On recense les liens de responsabilités dans un tableau temporaire.
    // On ne garde que les resp. légaux, les contacts n'ont pas à avoir accès aux notes ou à un éventuel bulletin.
    //
    $tab_enfants = array();
    $nb_lien_responsabilite = 0;
    if( ($xml->DONNEES) && ($xml->DONNEES->RESPONSABLES) && ($xml->DONNEES->RESPONSABLES->RESPONSABLE_ELEVE) )
    {
      foreach ($xml->DONNEES->RESPONSABLES->RESPONSABLE_ELEVE as $responsable)
      {
        $num_responsable = Clean::entier($responsable->RESP_LEGAL);
        if($num_responsable)
        {
          $eleve_sconet_id = Clean::entier($responsable->ELEVE_ID);
          if(isset($tab_eleves_actuels[$eleve_sconet_id]))
          {
            $tab_enfants[Clean::entier($responsable->PERSONNE_ID)][$tab_eleves_actuels[$eleve_sconet_id]] = $num_responsable;
            $nb_lien_responsabilite++;
          }
        }
      }
    }
    //
    // On passe les parents en revue : on mémorise leurs infos (dont adresses et enfants)
    // Si pas d'enfant trouvé, on laisse tomber, c'est en effet le choix par défaut de Gepi qui indique : "ne pas proposer d'ajouter les responsables non associés à des élèves (de telles entrées peuvent subsister en très grand nombre dans Sconet)".
    //
    if( ($xml->DONNEES) && ($xml->DONNEES->PERSONNES) && ($xml->DONNEES->PERSONNES->PERSONNE) )
    {
      foreach ($xml->DONNEES->PERSONNES->PERSONNE as $personne)
      {
        $i_fichier = Clean::entier($personne->attributes()->PERSONNE_ID);
        if(isset($tab_enfants[$i_fichier]))
        {
          $i_adresse = Clean::entier($personne->ADRESSE_ID);
          $tab_users_fichier['sconet_id'   ][$i_fichier] = $i_fichier;
          $tab_users_fichier['sconet_num'  ][$i_fichier] = 0;
          $tab_users_fichier['reference'   ][$i_fichier] = '';
          $tab_users_fichier['profil_sigle'][$i_fichier] = 'TUT' ;
          $tab_users_fichier['nom'         ][$i_fichier] = Clean::nom($personne->NOM);
          $tab_users_fichier['prenom'      ][$i_fichier] = Clean::prenom($personne->PRENOM);
          $tab_users_fichier['adresse'     ][$i_fichier] = isset($tab_adresses[$i_adresse]) ? $tab_adresses[$i_adresse] : array('','','','',0,'','') ;
          $tab_users_fichier['enfant'      ][$i_fichier] = $tab_enfants[$i_fichier];
        }
      }
    }
  }
  if( ($import_origine=='tableur') && ($import_profil=='professeur') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2d - Extraction tableur_professeurs_directeurs
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
    $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
    unset($tab_lignes[0]); // Supprimer la 1e ligne
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos
    //
    foreach ($tab_lignes as $ligne_contenu)
    {
      $tab_elements = str_getcsv($ligne_contenu,$separateur);
      $tab_elements = array_slice($tab_elements,0,6);
      if(count($tab_elements)>=4)
      {
        list($reference,$nom,$prenom,$profil,$classes,$groupes) = $tab_elements + array_fill(4,2,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
        $profil = Clean::ref($profil);
        if( ($nom!='') && ($prenom!='') && isset($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil]) && in_array($_SESSION['TAB_PROFILS_ADMIN']['TYPE'][$profil],array('professeur','directeur')) )
        {
          $tab_users_fichier['sconet_id'   ][] = 0;
          $tab_users_fichier['sconet_num'  ][] = 0;
          $tab_users_fichier['reference'   ][] = Clean::ref($reference);
          $tab_users_fichier['profil_sigle'][] = $profil;
          $tab_users_fichier['nom'         ][] = Clean::nom($nom);
          $tab_users_fichier['prenom'      ][] = Clean::prenom($prenom);
          // classes
          $tab_user_classes = array();
          if(strlen($classes))
          {
            $tab_classes = explode('|',$classes);
            foreach ($tab_classes as $classe)
            {
              $classe_ref = mb_substr(Clean::ref($classe),0,8);
              $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              if( ($classe_ref) && (!isset($tab_classes_fichier['ref'][$i_classe])) )
              {
                $tab_classes_fichier['ref'   ][$i_classe] = $classe_ref;
                $tab_classes_fichier['nom'   ][$i_classe] = $classe_ref;
                $tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
              }
              if(!isset($tab_user_classes[$i_classe]))
              {
                $tab_user_classes[$i_classe] = $classe_ref;
              }
            }
          }
          $tab_users_fichier['classe'][] = $tab_user_classes;
          // groupes
          $tab_user_groupes = array();
          if(strlen($groupes))
          {
            $tab_groupes = explode('|',$groupes);
            foreach ($tab_groupes as $groupe)
            {
              $groupe_ref = mb_substr(Clean::ref($groupe),0,8);
              $i_groupe   = 'i'.Clean::login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              if( ($groupe_ref) && (!isset($tab_groupes_fichier['ref'][$i_groupe])) )
              {
                $tab_groupes_fichier['ref'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['nom'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['niveau'][$i_groupe] = $groupe_ref;
              }
              if(!isset($tab_user_groupes[$i_groupe]))
              {
                $tab_user_groupes[$i_groupe] = $groupe_ref;
              }
            }
          }
          $tab_users_fichier['groupe'][] = $tab_user_groupes;
        }
      }
    }
  }
  if( ($import_origine=='tableur') && ($import_profil=='eleve') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2e - Extraction tableur_eleves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
    $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
    unset($tab_lignes[0]); // Supprimer la 1e ligne
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos et les classes trouvées
    //
    foreach ($tab_lignes as $ligne_contenu)
    {
      $tab_elements = str_getcsv($ligne_contenu,$separateur);
      $tab_elements = array_slice($tab_elements,0,6);
      if(count($tab_elements)>=5)
      {
        list($reference,$nom,$prenom,$birth_date,$classe,$groupes) = $tab_elements + array(5=>NULL); // Evite des NOTICE en initialisant les valeurs manquantes
        if( ($nom!='') && ($prenom!='') )
        {
          $tab_users_fichier['sconet_id'   ][] = 0;
          $tab_users_fichier['sconet_num'  ][] = 0;
          $tab_users_fichier['reference'   ][] = Clean::ref($reference);
          $tab_users_fichier['profil_sigle'][] = 'ELV';
          $tab_users_fichier['nom'         ][] = Clean::nom($nom);
          $tab_users_fichier['prenom'      ][] = Clean::prenom($prenom);
          $tab_users_fichier['birth_date'  ][] = Clean::texte($birth_date);
          // classe
          $classe_ref = mb_substr(Clean::ref($classe),0,8);
          $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
          $tab_users_fichier['classe'][]     = $i_classe;
          if( ($classe_ref) && (!isset($tab_classes_fichier['ref'][$i_classe])) )
          {
            $tab_classes_fichier['ref'   ][$i_classe] = $classe_ref;
            $tab_classes_fichier['nom'   ][$i_classe] = $classe_ref;
            $tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
          }
          // groupes
          $tab_user_groupes = array();
          if(strlen($groupes))
          {
            $tab_groupes = explode('|',$groupes);
            foreach ($tab_groupes as $groupe)
            {
              $groupe_ref = mb_substr(Clean::ref($groupe),0,8);
              $i_groupe   = 'i'.Clean::login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
              if( ($groupe_ref) && (!isset($tab_groupes_fichier['ref'][$i_groupe])) )
              {
                $tab_groupes_fichier['ref'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['nom'   ][$i_groupe] = $groupe_ref;
                $tab_groupes_fichier['niveau'][$i_groupe] = $groupe_ref;
              }
              if(!isset($tab_user_groupes[$i_groupe]))
              {
                $tab_user_groupes[$i_groupe] = $groupe_ref;
              }
            }
          }
          $tab_users_fichier['groupe'][] = $tab_user_groupes;
        }
      }
    }
  }
  if( ($import_origine=='tableur') && ($import_profil=='parent') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2f - Extraction tableur_parents
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
    $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
    unset($tab_lignes[0]); // Supprimer la 1e ligne
    // L'import ne contient aucun id parent ni enfant.
    // On récupère la liste des références des élèves actuels pour comparer au contenu du fichier.
    $tab_eleves_actuels  = array();
    $tab_responsabilites = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' /*profil_type*/ , 1 /*only_actuels*/ , 'user_id,user_reference' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleves_actuels[ $DB_ROW['user_id']] = $DB_ROW['user_reference'];
      $tab_responsabilites[$DB_ROW['user_id']] = 0;
    }
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos et les classes trouvées
    //
    $tab_adresses_uniques = array();
    foreach ($tab_lignes as $ligne_contenu)
    {
      $tab_elements = str_getcsv($ligne_contenu,$separateur);
      $tab_elements = array_slice($tab_elements,0,19);
      if(count($tab_elements)>=11)
      {
        list($reference,$nom,$prenom,$adresse_ligne1,$adresse_ligne2,$adresse_ligne3,$adresse_ligne4,$codepostal,$commune,$pays,$enfant1,$enfant2,$enfant3,$enfant4,$enfant5,$enfant6,$enfant7,$enfant8,$enfant9) = $tab_elements + array_fill(3,16,NULL); // Evite des NOTICE en initialisant les valeurs manquantes
        if( ($nom!='') && ($prenom!='') && ($enfant1!='') )
        {
          // enfants
          $tab_enfants = array();
          for( $num_enfant=1 ; $num_enfant<10 ; $num_enfant++ )
          {
            $enfant_ref = Clean::ref(${'enfant'.$num_enfant});
            if(!$enfant_ref) break;
            $enfant_id = array_search( $enfant_ref , $tab_eleves_actuels );
            if($enfant_id)
            {
              $tab_responsabilites[$enfant_id]++;
              $tab_enfants[$enfant_id] = $tab_responsabilites[$enfant_id];
            }
          }
          //
          // Si pas d'enfant trouvé, on laisse tomber, comme pour Sconet.
          //
          if( count($tab_enfants) )
          {
            $tab_users_fichier['sconet_id'   ][] = 0;
            $tab_users_fichier['sconet_num'  ][] = 0;
            $tab_users_fichier['reference'   ][] = Clean::ref($reference);
            $tab_users_fichier['profil_sigle'][] = 'TUT';
            $tab_users_fichier['nom'         ][] = Clean::nom($nom);
            $tab_users_fichier['prenom'      ][] = Clean::prenom($prenom);
            $tab_users_fichier['adresse'     ][] = array( Clean::adresse($adresse_ligne1) , Clean::adresse($adresse_ligne2) , Clean::adresse($adresse_ligne3) , Clean::adresse($adresse_ligne4) , Clean::codepostal($codepostal) , Clean::commune($commune) , Clean::pays($pays) ) ;
            $tab_users_fichier['enfant'      ][] = $tab_enfants;
            $tab_adresses_uniques[$adresse_ligne1.'#'.$adresse_ligne2.'#'.$adresse_ligne3.'#'.$adresse_ligne4.'#'.$codepostal.'#'.$commune.'#'.$pays] = TRUE;
          }
        }
      }
    }
    $nb_lien_responsabilite = array_sum($tab_responsabilites);
    $nb_adresses = count($tab_adresses_uniques);
  }
  if( ($import_origine=='base_eleves') && ($import_profil=='eleve') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2g - Extraction base_eleves_eleves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
    $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
    // Utiliser la 1e ligne pour repérer les colonnes intéressantes
    $tab_numero_colonne = array('nom'=>-100,'prenom'=>-100,'niveau'=>-100,'classe'=>-100);
    $tab_elements = str_getcsv($tab_lignes[0],$separateur);
    $numero_max = 0;
    foreach ($tab_elements as $numero=>$element)
    {
      switch($element)
      {
        case 'Nom Elève'      : $tab_numero_colonne['nom'   ]     = $numero; $numero_max = max($numero_max,$numero); break; // normalement 0
        case 'Prénom Elève'   : $tab_numero_colonne['prenom']     = $numero; $numero_max = max($numero_max,$numero); break; // normalement 2
        case 'Date naissance' : $tab_numero_colonne['birth_date'] = $numero; $numero_max = max($numero_max,$numero); break; // normalement 3
        case 'Niveau'         : $tab_numero_colonne['niveau']     = $numero; $numero_max = max($numero_max,$numero); break; // normalement 14
        case 'Classe'         : $tab_numero_colonne['classe']     = $numero; $numero_max = max($numero_max,$numero); break; // normalement 15
      }
    }
    if(array_sum($tab_numero_colonne)<0)
    {
      exit('Erreur : les champs nécessaires n\'ont pas pu être repérés !');
    }
    unset($tab_lignes[0]); // Supprimer la 1e ligne
    /*
     * Des difficultés se posent.
     * D'une part, les noms des niveaux et des classes ne semblent pas soumis à un format particulier ; on peut facilement dépasser les 20 caractères maxi autorisés par SACoche
     * D'autre part il n'existe pas de référence courte pour une classe.
     * Enfin, des classes sont sur plusieurs niveaux, donc comportent plusieurs groupes !
     */
    $tab_bon = array(); $tab_bad = array();
    $tab_bon[] = 'T';   $tab_bad[] = array('Toute ','toute ','TOUTE ');
    $tab_bon[] = 'P';   $tab_bad[] = array('Petite ','petite ','PETITE ');
    $tab_bon[] = 'M';   $tab_bad[] = array('Moyenne ','moyenne ','MOYENNE ');
    $tab_bon[] = 'G';   $tab_bad[] = array('Grande ','grande ','GRANDE ');
    $tab_bon[] = 'S';   $tab_bad[] = array('Section ','section ','SECTION ');
    $tab_bon[] = 'C';   $tab_bad[] = array('Cours ','cours ','COURS ');
    $tab_bon[] = 'P';   $tab_bad[] = array('Préparatoire','préparatoire','PRÉPARATOIRE','Preparatoire','preparatoire','PREPARATOIRE');
    $tab_bon[] = 'E';   $tab_bad[] = array('Élémentaire ','élémentaire ','ÉLÉMENTAIRE ','Elementaire ','elementaire ','ELEMENTAIRE ','Elémentaire ','elémentaire ','ELÉMENTAIRE ');
    $tab_bon[] = 'M';   $tab_bad[] = array('Moyen ','moyen ','MOYEN ');
    $tab_bon[] = '1';   $tab_bad[] = array('1er ','1ER ','1ere ','1ERE ','1ère ','1ÈRE ','premier ','PREMIER ','première ','PREMIÈRE ','premiere ','PREMIERE ');
    $tab_bon[] = '2';   $tab_bad[] = array('2e ','2E ','2eme ','2EME ','2ème ','2ÈME ','deuxième ','DEUXIÈME ','deuxieme ','DEUXIEME ','seconde ','SECONDE ');
    $tab_bon[] = '-';   $tab_bad[] = '- ';
    $tab_bon[] = '';    $tab_bad[] = array('Classe ','classe ','CLASSE ');
    $tab_bon[] = '';    $tab_bad[] = array('De ','de ','DE ');
    $tab_bon[] = '';    $tab_bad[] = array('Maternelle','maternelle','MATERNELLE');
    $tab_bon[] = '';    $tab_bad[] = array('Année','année','ANNÉE','Annee','annee','ANNEE');
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos, les classes trouvées, les groupes trouvés
    //
    foreach ($tab_lignes as $ligne_contenu)
    {
      $tab_elements = str_getcsv($ligne_contenu,$separateur);
      if(count($tab_elements)>$numero_max)
      {
        $nom        = $tab_elements[$tab_numero_colonne['nom']   ];
        $prenom     = $tab_elements[$tab_numero_colonne['prenom']];
        $birth_date = strpos($tab_elements[$tab_numero_colonne['birth_date']],'-') ? convert_date_mysql_to_french($tab_elements[$tab_numero_colonne['birth_date']]) : $tab_elements[$tab_numero_colonne['birth_date']] ; // Selon les fichiers, trouvé au format français ou mysql
        $niveau     = $tab_elements[$tab_numero_colonne['niveau']];
        $classe     = $tab_elements[$tab_numero_colonne['classe']];
        if( ($nom!='') && ($prenom!='') && ($niveau!='') && ($classe!='') )
        {
          // Réduire la longueur du niveau et de la classe
          foreach ($tab_bon as $i=>$bon)
          {
            $niveau = str_replace($tab_bad[$i],$bon,$niveau);
            $classe = str_replace($tab_bad[$i],$bon,$classe);
          }
          $niveau_ref = mb_substr(Clean::ref($niveau),0,8);
          $classe_nom = mb_substr('['.$niveau_ref.'] '.$classe,0,20); // On fait autant de classes que de groupes de niveaux par classes.
          $classe_ref = mb_substr(Clean::ref($niveau_ref.'_'.md5($niveau_ref.$classe)),0,8);
          $i_classe   = 'i'.Clean::login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
          $tab_users_fichier['sconet_id'   ][] = 0;
          $tab_users_fichier['sconet_num'  ][] = 0;
          $tab_users_fichier['reference'   ][] = '';
          $tab_users_fichier['profil_sigle'][] = 'ELV';
          $tab_users_fichier['nom'         ][] = Clean::nom($nom);
          $tab_users_fichier['prenom'      ][] = Clean::prenom($prenom);
          $tab_users_fichier['birth_date'  ][] = Clean::texte($birth_date);
          $tab_users_fichier['classe'      ][] = $i_classe;
          if( ($classe_ref) && (!isset($tab_classes_fichier['ref'][$i_classe])) )
          {
            $tab_classes_fichier['ref'   ][$i_classe] = $classe_ref;
            $tab_classes_fichier['nom'   ][$i_classe] = $classe_nom;
            $tab_classes_fichier['niveau'][$i_classe] = $niveau_ref;
          }
        }
      }
    }
  }
  if( ($import_origine=='base_eleves') && ($import_profil=='parent') )
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Étape 2h - Extraction base_eleves_parents
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
    $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
    $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
    $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
    // Utiliser la 1e ligne pour repérer les colonnes intéressantes
    $tab_numero_colonne = array('nom'=>-200,'prenom'=>-200,'adresse'=>-200,'codepostal'=>-200,'commune'=>-200,'pays'=>-200,'enfant_nom'=>array(),'enfant_prenom'=>array());
    $tab_elements = str_getcsv($tab_lignes[0],$separateur);
    $numero_max = 0;
    foreach ($tab_elements as $numero=>$element)
    {
      switch($element)
      {
        case 'Nom responsable'       : $tab_numero_colonne['nom']             = $numero; $numero_max = max($numero_max,$numero); break; // normalement 2
        case 'Prénom responsable'    : $tab_numero_colonne['prenom']          = $numero; $numero_max = max($numero_max,$numero); break; // normalement 3
        case 'Adresse responsable'   : $tab_numero_colonne['adresse']         = $numero; $numero_max = max($numero_max,$numero); break; // normalement 4
        case 'CP responsable'        : $tab_numero_colonne['codepostal']      = $numero; $numero_max = max($numero_max,$numero); break; // normalement 5
        case 'Commune responsable'   : $tab_numero_colonne['commune']         = $numero; $numero_max = max($numero_max,$numero); break; // normalement 6
        case 'Pays'                  : $tab_numero_colonne['pays']            = $numero; $numero_max = max($numero_max,$numero); break; // normalement 7
        case 'Nom de famille enfant' : $tab_numero_colonne['enfant_nom'][]    = $numero;                                         break; // normalement 14 18 22 ...
        case 'Prénom enfant'         : $tab_numero_colonne['enfant_prenom'][] = $numero;                                         break; // normalement 15 19 23 ...
      }
    }
    $nb_enfants_maxi = min( count($tab_numero_colonne['enfant_nom']) , count($tab_numero_colonne['enfant_prenom']) );
    if( (array_sum($tab_numero_colonne)<0) || ($nb_enfants_maxi==0) )
    {
      exit('Erreur : les champs nécessaires n\'ont pas pu être repérés !');
    }
    $numero_max = max( $numero_max , $tab_numero_colonne['enfant_nom'][0] , $tab_numero_colonne['enfant_prenom'][0] );
    unset($tab_lignes[0]); // Supprimer la 1e ligne
    // L'import ne contient aucun id parent ni enfant.
    // On récupère la liste des noms prénoms des élèves actuels pour comparer au contenu du fichier.
    $tab_eleves_actuels  = array();
    $tab_responsabilites = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' /*profil_type*/ , 1 /*only_actuels*/ , 'user_id,user_nom,user_prenom' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_eleves_actuels[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
      $tab_responsabilites[$DB_ROW['user_id']] = 0;
    }
    //
    // On passe les utilisateurs en revue : on mémorise leurs infos, les adresses trouvées, les enfants trouvés
    //
    $tab_adresses_uniques = array();
    foreach ($tab_lignes as $ligne_contenu)
    {
      $tab_elements = str_getcsv($ligne_contenu,$separateur);
      if(count($tab_elements)>$numero_max)
      {
        $nom        = Clean::nom(       $tab_elements[$tab_numero_colonne['nom']       ]);
        $prenom     = Clean::prenom(    $tab_elements[$tab_numero_colonne['prenom']    ]);
        $adresse    = Clean::adresse(   $tab_elements[$tab_numero_colonne['adresse']   ]);
        $codepostal = Clean::codepostal($tab_elements[$tab_numero_colonne['codepostal']]);
        $commune    = Clean::commune(   $tab_elements[$tab_numero_colonne['commune']   ]);
        $pays       = Clean::pays(      $tab_elements[$tab_numero_colonne['pays']      ]);
        if( ($nom!='') && ($prenom!='') )
        {
          $tab_enfants = array();
          for( $num_enfant=0 ; $num_enfant<$nb_enfants_maxi ; $num_enfant++ )
          {
            if ( !isset($tab_elements[$tab_numero_colonne['enfant_nom'][$num_enfant]]) || !isset($tab_elements[$tab_numero_colonne['enfant_prenom'][$num_enfant]]) )
            {
              break;
            }
            $enfant_nom    = Clean::nom(   $tab_elements[$tab_numero_colonne['enfant_nom'   ][$num_enfant]]);
            $enfant_prenom = Clean::prenom($tab_elements[$tab_numero_colonne['enfant_prenom'][$num_enfant]]);
            $enfant_id     = array_search( $enfant_nom.' '.$enfant_prenom , $tab_eleves_actuels );
            if($enfant_id)
            {
              $tab_responsabilites[$enfant_id]++;
              $tab_enfants[$enfant_id] = $tab_responsabilites[$enfant_id];
            }
          }
          //
          // Si pas d'enfant trouvé, on laisse tomber, comme pour Sconet.
          //
          if( count($tab_enfants) )
          {
            $tab_users_fichier['sconet_id'   ][] = 0;
            $tab_users_fichier['sconet_num'  ][] = 0;
            $tab_users_fichier['reference'   ][] = '';
            $tab_users_fichier['profil_sigle'][] = 'TUT';
            $tab_users_fichier['nom'         ][] = $nom;
            $tab_users_fichier['prenom'      ][] = $prenom;
            $tab_users_fichier['adresse'     ][] = array( $adresse , '' , '' , '' , $codepostal , $commune , $pays );
            $tab_users_fichier['enfant'      ][] = $tab_enfants;
            $tab_adresses_uniques[$adresse.'#'.$codepostal.'#'.$commune.'#'.$pays] = TRUE;
          }
        }
      }
    }
    $nb_lien_responsabilite = array_sum($tab_responsabilites);
    $nb_adresses = count($tab_adresses_uniques);
  }
  //
  // Fin des différents cas possibles
  //
  // Tableaux pour les étapes 61/62/71/72
  $tab_i_classe_TO_id_base  = array();
  $tab_i_groupe_TO_id_base  = array();
  $tab_i_fichier_TO_id_base = array();
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  // On trie
  switch($import_origine.'+'.$import_profil)
  {
    case 'sconet+professeur' :
      $test1 = array_multisort(
        $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
        $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
        $tab_users_fichier['sconet_id'],
        $tab_users_fichier['sconet_num'],
        $tab_users_fichier['reference'],
        $tab_users_fichier['profil_sigle'],
        $tab_users_fichier['classe'],
        $tab_users_fichier['groupe'],
        $tab_users_fichier['matiere']
      );
      $test2 = array_multisort(
        $tab_classes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_classes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_classes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      $test3 = array_multisort(
        $tab_groupes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_groupes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_groupes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      break;
    case 'tableur+professeur' :
      $test1 = array_multisort(
        $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
        $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
        $tab_users_fichier['sconet_id'],
        $tab_users_fichier['sconet_num'],
        $tab_users_fichier['reference'],
        $tab_users_fichier['profil_sigle'],
        $tab_users_fichier['classe'],
        $tab_users_fichier['groupe']
      );
      $test2 = array_multisort(
        $tab_classes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_classes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_classes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      $test3 = array_multisort(
        $tab_groupes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_groupes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_groupes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      break;
    case  'sconet+eleve' :
    case 'tableur+eleve' :
      $test1 = array_multisort(
        $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
        $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
        $tab_users_fichier['birth_date'],
        $tab_users_fichier['sconet_id'],
        $tab_users_fichier['sconet_num'],
        $tab_users_fichier['reference'],
        $tab_users_fichier['profil_sigle'],
        $tab_users_fichier['classe'],
        $tab_users_fichier['groupe']
      );
      $test2 = array_multisort(
        $tab_classes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_classes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_classes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      $test3 = array_multisort(
        $tab_groupes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_groupes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_groupes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      break;
    case 'base_eleves+eleve' :
      $test1 = array_multisort(
        $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
        $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
        $tab_users_fichier['birth_date'],
        $tab_users_fichier['sconet_id'],
        $tab_users_fichier['sconet_num'],
        $tab_users_fichier['reference'],
        $tab_users_fichier['profil_sigle'],
        $tab_users_fichier['classe']
      );
      $test2 = array_multisort(
        $tab_classes_fichier['niveau'], SORT_DESC,SORT_STRING,
        $tab_classes_fichier['ref']   , SORT_ASC,SORT_STRING,
        $tab_classes_fichier['nom']   , SORT_ASC,SORT_STRING
      );
      break;
    case      'sconet+parent' :
    case 'base_eleves+parent' :
    case     'tableur+parent' :
      $test1 = array_multisort(
        $tab_users_fichier['nom']   , SORT_ASC,SORT_STRING,
        $tab_users_fichier['prenom'], SORT_ASC,SORT_STRING,
        $tab_users_fichier['sconet_id'],
        $tab_users_fichier['sconet_num'],
        $tab_users_fichier['reference'],
        $tab_users_fichier['profil_sigle'],
        $tab_users_fichier['adresse'],
        $tab_users_fichier['enfant']
      );
      break;
  }
  // Outil de résolution de bug ; le test1 provoque parfois l'erreur "Array sizes are inconsistent".
  // Edit au 11/05/2012 : a priori c'est corrigé, mais je laisse quand même le test au cas où, ça ne coûte rien...
  if(!$test1)
  {
    ajouter_log_PHP( 'Import fichier '.$import_origine.' '.$import_profil /*log_objet*/ , serialize($tab_users_fichier) /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , TRUE /*only_sesamath*/ );
  }
  // On enregistre
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_users.txt',serialize($tab_users_fichier));
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_classes.txt',serialize($tab_classes_fichier));
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_groupes.txt',serialize($tab_groupes_fichier));
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // On affiche le bilan des utilisateurs trouvés
  if(count($tab_users_fichier['profil_sigle']))
  {
    // Nom des profils
    $tab_profils_libelles = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_profils_parametres( 'user_profil_nom_long_singulier,user_profil_nom_long_pluriel' /*listing_champs*/ , FALSE /*only_actif*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_profils_libelles[$DB_ROW['user_profil_sigle']] = array( 1=>$DB_ROW['user_profil_nom_long_singulier'] , 2=>$DB_ROW['user_profil_nom_long_pluriel'] );
    }
    // Boucle pour l'affichage
    $tab_profil_nombre = array_count_values($tab_users_fichier['profil_sigle']);
    foreach ($tab_profil_nombre as $profil=>$nombre)
    {
      $s = ($nombre>1) ? 's' : '' ;
      echo'<p><label class="valide">'.$nombre.' '.$tab_profils_libelles[$profil][min(2,$nombre)].' trouvé'.$s.'.</label></p>'.NL;
    }
  }
  else if($import_profil=='parent')
  {
    exit('<p><label class="alerte">Aucun parent trouvé ayant un enfant dans l\'établissement : importer d\'abord les élèves !</label></p>');
  }
  else
  {
    exit('<p><label class="alerte">Aucun utilisateur trouvé !</label></p>');
  }
  // On affiche le bilan des classes trouvées
   if($import_profil!='parent')
  {
    $nombre = count($tab_classes_fichier['ref']);
    if($nombre)
    {
      $s = ($nombre>1) ? 's' : '' ;
      echo'<p><label class="valide">'.$nombre.' classe'.$s.' trouvée'.$s.'.</label></p>'.NL;
    }
    else
    {
      echo'<p><label class="alerte">Aucune classe trouvée !</label></p>'.NL;
    }
  }
  // On affiche le bilan des groupes trouvés
  if( ($import_profil!='parent') && ($import_origine!='base_eleves') )
  {
    $nombre = count($tab_groupes_fichier['ref']);
    if($nombre)
    {
      $s = ($nombre>1) ? 's' : '' ;
      echo'<p><label class="valide">'.$nombre.' groupe'.$s.' trouvé'.$s.'.</label></p>'.NL;
    }
    else
    {
      echo'<p><label class="alerte">Aucun groupe trouvé !</label></p>'.NL;
    }
  }
  // On affiche le bilan des parents trouvés
  if($import_profil=='parent')
  {
    if($nb_adresses)
    {
      $s = ($nb_adresses>1) ? 's' : '' ;
      echo'<p><label class="valide">'.$nb_adresses.' adresse'.$s.' trouvée'.$s.'.</label></p>'.NL;
    }
    else
    {
      echo'<p><label class="alerte">Aucune adresse trouvée !</label></p>'.NL;
    }
    if($nb_lien_responsabilite)
    {
      $s = ($nb_lien_responsabilite>1) ? 's' : '' ;
      echo'<p><label class="valide">'.$nb_lien_responsabilite.' lien'.$s.' de responsabilité'.$s.' trouvé'.$s.'.</label></p>'.NL;
    }
    else
    {
      echo'<p><label class="alerte">Aucun lien de responsabilité trouvé !</label></p>'.NL;
    }
  }
  // Fin de l'extraction
  $step = ($import_profil=='parent') ? '5' : '3' ;
  echo'<ul class="puce p"><li><a href="#step'.$step.'1" id="passer_etape_suivante">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 31 - Analyse des données des classes (sconet_professeurs_directeurs | sconet_eleves | base_eleves_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==31 )
{
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
    $select_niveau = '<option value=""></option>';
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
          $id_checked = (mb_strpos($ref,$masque_recherche)===0) ? $niveau_id : '';
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
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 32 - Traitement des actions à effectuer sur les classes (sconet_professeurs_directeurs | sconet_eleves | base_eleves_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==32 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
  // Récupérer les éléments postés
  $tab_add = array();
  $tab_del = array();
  foreach($_POST as $key => $val)
  {
    if( (substr($key,0,4)=='add_') && (!in_array(substr($key,0,8),array('add_ref_','add_nom_','add_niv_'))) )
    {
      $i = substr($key,4);
      $tab_add[$i]['ref'] = Clean::ref($_POST['add_ref_'.$i]);
      $tab_add[$i]['nom'] = Clean::ref($_POST['add_nom_'.$i]);
      $tab_add[$i]['niv'] = Clean::ref($_POST['add_niv_'.$i]);
    }
    elseif(substr($key,0,4)=='del_')
    {
      $id = substr($key,4);
      $tab_del[] = Clean::entier($id);
    }
  }
  // Ajouter des classes éventuelles
  $nb_add = 0;
  if(count($tab_add))
  {
    foreach($tab_add as $i => $tab)
    {
      if( (count($tab)==3) && $tab['ref'] && $tab['nom'] && $tab['niv'] )
      {
        $classe_id = DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_groupe_par_admin('classe',$tab['ref'],$tab['nom'],$tab['niv']);
        $nb_add++;
        $tab_i_classe_TO_id_base[$i] = (int) $classe_id;
      }
    }
  }
  // Supprimer des classes éventuelles
  $nb_del = 0;
  if(count($tab_del))
  {
    foreach($tab_del as $groupe_id)
    {
      if( $groupe_id )
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_groupe_par_admin( $groupe_id , 'classe' , TRUE /*with_devoir*/ );
        $nb_del++;
        // Log de l'action
        SACocheLog::ajouter('Suppression d\'un regroupement (classe '.$groupe_id.'), avec les devoirs associés.');
      }
    }
  }
  // On enregistre (tableau mis à jour)
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // Afficher le bilan
  $lignes = '';
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes_avec_niveaux();
  if($mode=='complet')
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $lignes .= '<tr><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td>'.html($DB_ROW['groupe_nom']).'</td></tr>'.NL;
    }
  }
  $nb_fin = count($DB_TAB);
  $nb_ras = $nb_fin - $nb_add + $nb_del;
  $s_ras = ($nb_ras>1) ? 's' : '';
  $s_add = ($nb_add>1) ? 's' : '';
  $s_del = ($nb_del>1) ? 's' : '';
  $s_fin = ($nb_fin>1) ? 's' : '';
  echo'<p><label class="valide">'.$nb_ras.' classe'.$s_ras.' présente'.$s_ras.' + '.$nb_add.' classe'.$s_add.' ajoutée'.$s_add.' &minus; '.$nb_del.' classe'.$s_del.' supprimée'.$s_del.' = '.$nb_fin.' classe'.$s_fin.' résultante'.$s_fin.'.</label></p>'.NL;
  if($mode=='complet')
  {
    echo'<table>'.NL;
    echo  '<thead>'.NL;
    echo    '<tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>'.NL;
    echo  '</thead>'.NL;
    echo  '<tbody>'.NL;
    echo    $lignes;
    echo  '</tbody>'.NL;
    echo'</table>'.NL;
  }
  $step = ( ($import_origine=='base_eleves') && ($import_profil=='eleve') ) ? '5' : '4' ;
  echo'<ul class="puce p"><li><a href="#step'.$step.'1" id="passer_etape_suivante">Passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 41 - Analyse des données des groupes (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==41 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
  // On récupère le fichier avec les groupes : $tab_groupes_fichier['ref'] : i -> ref ; $tab_groupes_fichier['nom'] : i -> nom ; $tab_groupes_fichier['niveau'] : i -> niveau
  $tab_groupes_fichier = load_fichier('groupes');
  // On récupère le contenu de la base pour comparer : $tab_groupes_base['ref'] : id -> ref ; $tab_groupes_base['nom'] : id -> nom
  $tab_groupes_base        = array();
  $tab_groupes_base['ref'] = array();
  $tab_groupes_base['nom'] = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_groupes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_groupes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
    $tab_groupes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
  }
  // Contenu du fichier à conserver
  $lignes_ras = '';
  foreach($tab_groupes_fichier['ref'] as $i_groupe => $ref)
  {
    $id_base = array_search($ref,$tab_groupes_base['ref']);
    if($id_base!==FALSE)
    {
      if($mode=='complet')
      {
        $lignes_ras .= '<tr><th>'.html($tab_groupes_base['ref'][$id_base]).'</th><td>'.html($tab_groupes_base['nom'][$id_base]).'</td></tr>'.NL;
      }
      $tab_i_groupe_TO_id_base[$i_groupe] = $id_base;
      unset($tab_groupes_fichier['ref'][$i_groupe] , $tab_groupes_fichier['nom'][$i_groupe] ,  $tab_groupes_fichier['niveau'][$i_groupe] , $tab_groupes_base['ref'][$id_base] , $tab_groupes_base['nom'][$id_base]);
    }
  }
  // Contenu du fichier à supprimer
  $lignes_del = '';
  if(count($tab_groupes_base['ref']))
  {
    foreach($tab_groupes_base['ref'] as $id_base => $ref)
    {
      $lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" /> '.html($tab_groupes_base['nom'][$id_base]).'</td></tr>'.NL;
    }
  }
  // Contenu du fichier à ajouter
  $lignes_add = '';
  if(count($tab_groupes_fichier['ref']))
  {
    $select_niveau = '<option value=""></option>';
    $tab_niveau_ref = array();
    $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_niveaux_etablissement(FALSE /*with_particuliers*/);
    foreach($DB_TAB as $DB_ROW)
    {
      $select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
      $key = ( ($import_origine=='sconet') && ($import_profil=='eleve') ) ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
      $tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
    }
    foreach($tab_groupes_fichier['ref'] as $i_groupe => $ref)
    {
      // On préselectionne un niveau :
      // - pour sconet_eleves                 on compare avec un masque d'expression régulière
      // - pour base_eleves_eleves            on compare avec les niveaux de SACoche
      // - pour sconet_professeurs_directeurs on compare avec le début de la référence du groupe
      // - pour tableur_eleves                on compare avec le début de la référence du groupe
      $id_checked = '';
      foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
      {
        if( ($import_origine=='sconet') && ($import_profil=='eleve') )
        {
          $id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_groupes_fichier['niveau'][$i_groupe])) ? $niveau_id : '';
        }
        else
        {
          $id_checked = (mb_strpos($ref,$masque_recherche)===0) ? $niveau_id : '';
        }
        if($id_checked)
        {
          break;
        }
      }
      $nom_groupe = ($tab_groupes_fichier['nom'][$i_groupe]) ? $tab_groupes_fichier['nom'][$i_groupe] : $ref ;
      $lignes_add .= '<tr><th><input id="add_'.$i_groupe.'" name="add_'.$i_groupe.'" type="checkbox" checked /> '.html($ref).'<input id="add_ref_'.$i_groupe.'" name="add_ref_'.$i_groupe.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i_groupe.'" name="add_niv_'.$i_groupe.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i_groupe.'" name="add_nom_'.$i_groupe.'" size="15" type="text" value="'.html($nom_groupe).'" maxlength="20" /></td></tr>'.NL;
    }
  }
  // On enregistre (tableau mis à jour)
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // On affiche
  echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des groupes.</label></p>'.NL;
  // Pour sconet_professeurs_directeurs, les groupes ne figurent pas forcément dans le fichier si les services ne sont pas présents -> on ne procède qu'à des ajouts éventuels.
  if($lignes_del)
  {
    echo'<p class="danger">Des groupes non trouvés sont proposés à la suppression. Il se peut que les services / affectations manquent dans le fichier. Veuillez cochez ces suppressions pour les confirmer.</p>'.NL;
  }
  echo'<table>'.NL;
  if($mode=='complet')
  {
    echo  '<tbody>'.NL;
    echo    '<tr><th colspan="2">Groupes actuels à conserver</th></tr>'.NL;
    echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>'.NL;
    echo  '</tbody>'.NL;
  }
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Groupes nouveaux à ajouter<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_add) ? $lignes_add : '<tr><td colspan="2">Aucun</td></tr>'.NL;
  echo  '</tbody>'.NL;
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Groupes anciens à supprimer<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_del) ? $lignes_del : '<tr><td colspan="2">Aucun</td></tr>'.NL;
  echo  '</tbody>'.NL;
  echo'</table>'.NL;
  echo'<ul class="puce p"><li><a href="#step42" id="envoyer_infos_regroupements">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 42 - Traitement des actions à effectuer sur les groupes (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==42 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
  // Récupérer les éléments postés
  $tab_add = array();
  $tab_del = array();
  foreach($_POST as $key => $val)
  {
    if( (substr($key,0,4)=='add_') && (!in_array(substr($key,0,8),array('add_ref_','add_nom_','add_niv_'))) )
    {
      $i = substr($key,4);
      $tab_add[$i]['ref'] = Clean::ref($_POST['add_ref_'.$i]);
      $tab_add[$i]['nom'] = Clean::ref($_POST['add_nom_'.$i]);
      $tab_add[$i]['niv'] = Clean::ref($_POST['add_niv_'.$i]);
    }
    elseif(substr($key,0,4)=='del_')
    {
      $id = substr($key,4);
      $tab_del[] = Clean::entier($id);
    }
  }
  // Ajouter des groupes éventuels
  $nb_add = 0;
  if(count($tab_add))
  {
    foreach($tab_add as $i => $tab)
    {
      if( (count($tab)==3) && $tab['ref'] && $tab['nom'] && $tab['niv'] )
      {
        $groupe_id = DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_groupe_par_admin('groupe',$tab['ref'],$tab['nom'],$tab['niv']);
        $nb_add++;
        $tab_i_groupe_TO_id_base[$i] = (int) $groupe_id;
      }
    }
  }
  // Supprimer des groupes éventuels
  $nb_del = 0;
  if(count($tab_del))
  {
    foreach($tab_del as $groupe_id)
    {
      if( $groupe_id )
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_groupe_par_admin( $groupe_id , 'groupe' , TRUE /*with_devoir*/ );
        $nb_del++;
        // Log de l'action
        SACocheLog::ajouter('Suppression d\'un regroupement (groupe '.$groupe_id.'), avec les devoirs associés.');
      }
    }
  }
  // On enregistre (tableau mis à jour)
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // Afficher le bilan
  $lignes = '';
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_groupes_avec_niveaux();
  if($mode=='complet')
  {
    foreach($DB_TAB as $DB_ROW)
    {
      $lignes .= '<tr><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td>'.html($DB_ROW['groupe_nom']).'</td></tr>'.NL;
    }
  }
  $nb_fin = count($DB_TAB);
  $nb_ras = $nb_fin - $nb_add + $nb_del;
  $s_ras = ($nb_ras>1) ? 's' : '';
  $s_add = ($nb_add>1) ? 's' : '';
  $s_del = ($nb_del>1) ? 's' : '';
  $s_fin = ($nb_fin>1) ? 's' : '';
  echo'<p><label class="valide">'.$nb_ras.' groupe'.$s_ras.' présent'.$s_ras.' + '.$nb_add.' groupe'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' groupe'.$s_del.' supprimé'.$s_del.' = '.$nb_fin.' groupe'.$s_fin.' résultant'.$s_fin.'.</label></p>'.NL;
  if($mode=='complet')
  {
    echo'<table>'.NL;
    echo  '<thead>'.NL;
    echo    '<tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>'.NL;
    echo  '</thead>'.NL;
    echo  '<tbody>'.NL;
    echo    $lignes;
    echo  '</tbody>'.NL;
    echo'</table>'.NL;
  }
  echo'<ul class="puce p"><li><a href="#step51" id="passer_etape_suivante">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 51 - Analyse des données des utilisateurs (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==51 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
  // On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
  $tab_users_fichier = load_fichier('users');
  // On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
  $tab_classes_fichier = load_fichier('classes');
  // On récupère le contenu de la base pour comparer : $tab_users_base['champ'] : id -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / birth_date / statut / classe / adresse
  $tab_users_base                 = array();
  $tab_users_base['sconet_id'   ] = array();
  $tab_users_base['sconet_num'  ] = array();
  $tab_users_base['reference'   ] = array();
  $tab_users_base['profil_sigle'] = array();
  $tab_users_base['nom'         ] = array();
  $tab_users_base['prenom'      ] = array();
  $tab_users_base['birth_date'  ] = array();
  $tab_users_base['sortie'      ] = array();
  $tab_users_base['classe'      ] = array();
  $tab_users_base['adresse'     ] = array();
  $profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
  $with_classe = ($import_profil=='eleve') ? TRUE : FALSE ;
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil_sigle,user_nom,user_prenom,user_naissance_date,user_sortie_date' /*liste_champs*/ , $with_classe , FALSE /*tri_statut*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base['sconet_id'   ][$DB_ROW['user_id']] = $DB_ROW['user_sconet_id'];
    $tab_users_base['sconet_num'  ][$DB_ROW['user_id']] = $DB_ROW['user_sconet_elenoet'];
    $tab_users_base['reference'   ][$DB_ROW['user_id']] = $DB_ROW['user_reference'];
    $tab_users_base['profil_sigle'][$DB_ROW['user_id']] = $DB_ROW['user_profil_sigle'];
    $tab_users_base['nom'         ][$DB_ROW['user_id']] = $DB_ROW['user_nom'];
    $tab_users_base['birth_date'  ][$DB_ROW['user_id']] = convert_date_mysql_to_french($DB_ROW['user_naissance_date']);
    $tab_users_base['prenom'      ][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
    $tab_users_base['sortie'      ][$DB_ROW['user_id']] = $DB_ROW['user_sortie_date'] ;
    $tab_users_base['classe'      ][$DB_ROW['user_id']] = ($import_profil=='eleve') ? $DB_ROW['groupe_ref'] : '' ;
  }
  // Pour préparer l'affichage
  $lignes_ignorer   = '';
  $lignes_ajouter   = '';
  $lignes_retirer   = '';
  $lignes_modifier  = '';
  $lignes_conserver = '';
  $lignes_inchanger = '';
  // Pour préparer l'enregistrement des données
  $tab_users_ajouter  = array();
  $tab_users_modifier = array();
  $tab_users_retirer  = array();
  // Comparer fichier et base : c'est parti !
  $tab_indices_fichier = array_keys($tab_users_fichier['sconet_id']);
  // Parcourir chaque entrée du fichier
  foreach($tab_indices_fichier as $i_fichier)
  {
    $id_base = FALSE;
    // Recherche sur sconet_id
    if( (!$id_base) && ($tab_users_fichier['sconet_id'][$i_fichier]) )
    {
      $id_base = array_search($tab_users_fichier['sconet_id'][$i_fichier],$tab_users_base['sconet_id']);
    }
    // Recherche sur sconet_num
    if( (!$id_base) && ($tab_users_fichier['sconet_num'][$i_fichier]) )
    {
      $id_base = array_search($tab_users_fichier['sconet_num'][$i_fichier],$tab_users_base['sconet_num']);
    }
    // Si pas trouvé, recherche sur reference
    if( (!$id_base) && ($tab_users_fichier['reference'][$i_fichier]) )
    {
      $id_base = array_search($tab_users_fichier['reference'][$i_fichier],$tab_users_base['reference']);
    }
    // Si pas trouvé, recherche sur nom prénom
    if(!$id_base)
    {
      $tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
      $tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
      $tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
      $nb_homonymes  = count($tab_id_commun);
      if($nb_homonymes>0)
      {
        list($inutile,$id_base) = each($tab_id_commun);
      }
    }
    // Cas [1] : présent dans le fichier, absent de la base, pas de classe dans le fichier (élèves uniquements) : contenu à ignorer (probablement des anciens élèves, ou des élèves jamais venus, qu'il est inutile d'importer)
    if( ($import_profil=='eleve') && (!$id_base) && (!$tab_users_fichier['classe'][$i_fichier]) )
    {
      $indication = ($import_profil=='eleve') ? substr($tab_users_fichier['classe'][$i_fichier],1) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
      $lignes_ignorer .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
    }
    // Cas [2] : présent dans le fichier, absent de la base, prof ou classe indiquée dans le fichier si élève : contenu à ajouter (nouvel élève ou nouveau professeur / directeur)
    elseif( (!$id_base) && ( ($import_profil!='eleve') || ($tab_users_fichier['classe'][$i_fichier]) ) )
    {
      $indication = ($import_profil=='eleve') ? substr($tab_users_fichier['classe'][$i_fichier],1) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
      $lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
      $id_classe = ( ($import_profil=='eleve') && isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]]) ) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
      $tab_users_ajouter[$i_fichier] = array( 'sconet_id'=>$tab_users_fichier['sconet_id'][$i_fichier] , 'sconet_num'=>$tab_users_fichier['sconet_num'][$i_fichier] , 'reference'=>$tab_users_fichier['reference'][$i_fichier] , 'nom'=>$tab_users_fichier['nom'][$i_fichier] , 'prenom'=>$tab_users_fichier['prenom'][$i_fichier] , 'profil_sigle'=>$tab_users_fichier['profil_sigle'][$i_fichier] , 'classe'=>$id_classe );
      if($import_profil=='eleve')
      {
        $tab_users_ajouter[$i_fichier]['birth_date'] = $tab_users_fichier['birth_date'][$i_fichier];
      }
    }
    // Cas [3] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), actuel dans la base : contenu à retirer (probablement des élèves nouvellement sortants)
    elseif( ($import_profil=='eleve') && (!$tab_users_fichier['classe'][$i_fichier]) && ($tab_users_base['sortie'][$id_base]==SORTIE_DEFAUT_MYSQL) )
    {
      $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
      $date_sortie_fr = TODAY_FR;
      $lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Sortie : non &rarr; '.$date_sortie_fr.'</b></td></tr>'.NL;
      $tab_users_retirer[$id_base] = convert_date_french_to_mysql($date_sortie_fr);
    }
    // Cas [4] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), ancien dans la base : contenu inchangé (probablement des anciens élèves déjà écartés)
    elseif( ($import_profil=='eleve') && (!$tab_users_fichier['classe'][$i_fichier]) && ($tab_users_base['sortie'][$id_base]!=SORTIE_DEFAUT_MYSQL) )
    {
      $indication = ($import_profil=='eleve') ? substr($tab_users_fichier['classe'][$i_fichier],1) : $tab_users_fichier['profil_sigle'][$i_fichier] ;
      $lignes_inchanger .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>'.NL;
    }
    else
    {
      // On compare les données de 2 enregistrements pour voir si des choses ont été modifiées
      $td_modif = '';
      $nb_modif = 0;
      $tab_champs = ($import_profil=='eleve') ? array( 'sconet_id'=>'Id Sconet' , 'sconet_num'=>'n° Sconet' , 'reference'=>'Référence' , 'nom'=>'Nom' , 'prenom'=>'Prénom' , 'birth_date'=>'Date Naiss.' , 'classe'=>'Classe' ) : array( 'sconet_id'=>'Id Sconet' , 'reference'=>'Référence' , 'profil_sigle'=>'Profil' , 'nom'=>'Nom' , 'prenom'=>'Prénom' ) ;
      foreach($tab_champs as $champ_ref => $champ_aff)
      {
        if($champ_ref=='classe')
        {
          $id_classe = (isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]])) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
          $tab_users_fichier[$champ_ref][$i_fichier] = ($id_classe) ? $tab_classes_fichier['ref'][$tab_users_fichier['classe'][$i_fichier]] : '' ;
        }
        if($tab_users_base[$champ_ref][$id_base]!=$tab_users_fichier[$champ_ref][$i_fichier])
        {
          $td_modif .= ' || <b>'.$champ_aff.' : '.html($tab_users_base[$champ_ref][$id_base]).' &rarr; '.html($tab_users_fichier[$champ_ref][$i_fichier]).'</b>';
          $tab_users_modifier[$id_base][$champ_ref] = ($champ_ref!='classe') ? $tab_users_fichier[$champ_ref][$i_fichier] : $id_classe ;
          $nb_modif++;
        }
        else
        {
          $td_modif .= ' || '.$champ_aff.' : '.html($tab_users_base[$champ_ref][$id_base]);
          $tab_users_modifier[$id_base][$champ_ref] = FALSE;
        }
      }
      if($tab_users_base['sortie'][$id_base]!=SORTIE_DEFAUT_MYSQL)
      {
        $td_modif .= ' || <b>Sortie : '.convert_date_mysql_to_french($tab_users_base['sortie'][$id_base]).' &rarr; non</b>';
        $tab_users_modifier[$id_base]['entree'] = SORTIE_DEFAUT_MYSQL ;
        $nb_modif++;
      }
      else
      {
        $tab_users_modifier[$id_base]['entree'] = FALSE ;
      }
      // Cas [5] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, ancien dans la base et/ou différence constatée : contenu à modifier (user revenant ou mise à jour)
      if($nb_modif)
      {
        $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$id_base.'" name="mod_'.$id_base.'" type="checkbox" checked /></th><td>'.mb_substr($td_modif,4).'</td></tr>'.NL;
      }
      // Cas [6] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, actuel dans la base et aucune différence constatée : contenu à conserver (contenu identique)
      else
      {
        if($mode=='complet')
        {
          $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
          $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>'.NL;
        }
      }
    }
    // Supprimer l'entrée du fichier et celle de la base éventuelle
    unset( $tab_users_fichier['sconet_id'][$i_fichier] , $tab_users_fichier['sconet_num'][$i_fichier] , $tab_users_fichier['reference'][$i_fichier] , $tab_users_fichier['nom'][$i_fichier] , $tab_users_fichier['prenom'][$i_fichier] , $tab_users_fichier['classe'][$i_fichier] );
    if($id_base)
    {
      $tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
      unset( $tab_users_base['sconet_id'][$id_base] , $tab_users_base['sconet_num'][$id_base] , $tab_users_base['reference'][$id_base] , $tab_users_base['nom'][$id_base] , $tab_users_base['prenom'][$id_base] , $tab_users_base['classe'][$id_base] , $tab_users_base['sortie'][$id_base] );
    }
  }
  // Parcourir chaque entrée de la base
  if(count($tab_users_base['sconet_id']))
  {
    $tab_indices_base = array_keys($tab_users_base['sconet_id']);
    foreach($tab_indices_base as $id_base)
    {
      // Cas [7] : absent dans le fichier, présent dans la base, actuel : contenu à retirer (probablement un user nouvellement sortant)
      if($tab_users_base['sortie'][$id_base]==SORTIE_DEFAUT_MYSQL)
      {
        $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
        $date_sortie_fr = isset($_SESSION['tmp']['date_sortie'][$tab_users_base['sconet_id'][$id_base]]) ? $_SESSION['tmp']['date_sortie'][$tab_users_base['sconet_id'][$id_base]] : TODAY_FR ;
        $lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Sortie : non &rarr; '.$date_sortie_fr.'</b></td></tr>'.NL;
        $tab_users_retirer[$id_base] = convert_date_french_to_mysql($date_sortie_fr);
      }
      // Cas [8] : absent dans le fichier, présent dans la base, ancien : contenu inchangé (restant ancien)
      else
      {
        if($mode=='complet')
        {
          $indication = ($import_profil=='eleve') ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil_sigle'][$id_base] ;
          $lignes_inchanger .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>'.NL;
        }
      }
      unset( $tab_users_base['sconet_id'][$id_base] , $tab_users_base['sconet_num'][$id_base] , $tab_users_base['reference'][$id_base] , $tab_users_base['nom'][$id_base] , $tab_users_base['prenom'][$id_base] , $tab_users_base['classe'][$id_base] , $tab_users_base['sortie'][$id_base] );
    }
  }
  unset($_SESSION['tmp']['date_sortie']);
  // On enregistre
  $tab_memo_analyse = array('modifier'=>$tab_users_modifier,'ajouter'=>$tab_users_ajouter,'retirer'=>$tab_users_retirer);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_memo_analyse.txt',serialize($tab_memo_analyse));
  // On enregistre (tableau mis à jour)
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // On affiche
  echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des utilisateurs.</label></p>'.NL;
  if( $lignes_ajouter && $lignes_retirer )
  {
    echo'<p class="danger">Si des utilisateurs sont à la fois proposés pour être retirés et ajoutés, alors allez modifier leurs noms/prénoms puis reprenez l\'import au début.</p>'.NL;
  }
  echo'<table>'.NL;
  // Cas [2]
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Utilisateurs à ajouter (absents de la base, nouveaux dans le fichier).<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="2">Aucun</td></tr>'.NL;
  echo  '</tbody>'.NL;
  // Cas [3] et [7]
  $texte = ($import_profil=='eleve') ? ' ou sans classe affectée' : ( ($import_profil=='parent') ? ' ou sans enfant actuel' : '' ) ;
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Utilisateurs à retirer (absents du fichier'.$texte.')<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_retirer) ? $lignes_retirer : '<tr><td colspan="2">Aucun</td></tr>'.NL;
  echo  '</tbody>'.NL;
  // Cas [5]
  echo  '<tbody>'.NL;
  echo    '<tr><th colspan="2">Utilisateurs à modifier (ou à réintégrer)<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="2">Aucun</td></tr>'.NL;
  echo  '</tbody>'.NL;
  // Cas [6]
  if($mode=='complet')
  {
    echo  '<tbody>'.NL;
    echo    '<tr><th colspan="2">Utilisateurs à conserver (actuels)</th></tr>'.NL;
    echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="2">Aucun</td></tr>'.NL;
    echo  '</tbody>'.NL;
  }
  // Cas [4] et [8]
  if($mode=='complet')
  {
    echo  '<tbody>'.NL;
    echo    '<tr><th colspan="2">Utilisateurs inchangés (anciens)</th></tr>'.NL;
    echo($lignes_inchanger) ? $lignes_inchanger : '<tr><td colspan="2">Aucun</td></tr>'.NL;
    echo  '</tbody>'.NL;
  }
  // Cas [1]
  if($import_profil=='eleve')
  {
    echo  '<tbody>'.NL;
    echo    '<tr><th colspan="2">Utilisateurs ignorés (sans classe affectée).</th></tr>'.NL;
    echo($lignes_ignorer) ? $lignes_ignorer : '<tr><td colspan="2">Aucun</td></tr>'.NL;
    echo  '</tbody>'.NL;
  }
  echo'</table>'.NL;
  echo'<ul class="puce p"><li><a href="#step52" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 52 - Traitement des actions à effectuer sur les utilisateurs (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==52 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base   = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base   = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
  // On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse['modifier'] : id -> array ; $tab_memo_analyse['ajouter'] : i -> array ; $tab_memo_analyse['retirer'] : i -> array
  $tab_memo_analyse = load_fichier('memo_analyse');
  // Récupérer les éléments postés
  $tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
  $tab_mod = array();  // id à modifier
  $tab_add = array();  // i à ajouter
  $tab_del = array();  // id à supprimer
  foreach($tab_check as $check_infos)
  {
    if(substr($check_infos,0,4)=='mod_')
    {
      $tab_mod[] = Clean::entier( substr($check_infos,4) );
    }
    elseif(substr($check_infos,0,4)=='add_')
    {
      $tab_add[] = Clean::entier( substr($check_infos,4) );
    }
    elseif(substr($check_infos,0,4)=='del_')
    {
      $tab_del[] = Clean::entier( substr($check_infos,4) );
    }
  }
  // Dénombrer combien d'actuels et d'anciens au départ
  $profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
  list($nb_debut_actuel,$nb_debut_ancien) = DB_STRUCTURE_ADMINISTRATEUR::DB_compter_users_suivant_statut($profil_type);
  // Retirer des users éventuels
  $nb_del = 0;
  if(count($tab_del))
  {
    foreach($tab_del as $id_base)
    {
      if( isset($tab_memo_analyse['retirer'][$id_base]) )
      {
        // Mettre à jour l'enregistrement
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , array(':sortie_date'=>$tab_memo_analyse['retirer'][$id_base]) );
        $nb_del++;
      }
    }
  }
  // Ajouter des users éventuels
  $nb_add = 0;
  $tab_password = array();
  $separateur = ';';
  $classe_ou_profil = ($import_profil=='eleve') ? 'CLASSE' : 'PROFIL' ;
  $fcontenu_csv = 'SCONET_Id'.$separateur.'SCONET_N°'.$separateur.'REFERENCE'.$separateur.$classe_ou_profil.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'LOGIN'.$separateur.'MOT DE PASSE'."\r\n\r\n";
  $fcontenu_pdf_tab = array();
  if(count($tab_add))
  {
    // Récupérer les noms de classes pour le fichier avec les logins/mdp
    $tab_nom_classe = array();
    if($import_profil=='eleve')
    {
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_nom_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
      }
    }
    foreach($tab_add as $i_fichier)
    {
      if( isset($tab_memo_analyse['ajouter'][$i_fichier]) )
      {
        // Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement...
        // Construire le login
        $login = fabriquer_login($tab_memo_analyse['ajouter'][$i_fichier]['prenom'] , $tab_memo_analyse['ajouter'][$i_fichier]['nom'] , $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle']);
        // Puis tester le login (parmi tout le personnel de l'établissement)
        if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_utilisateur_identifiant('login',$login) )
        {
          // Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
          $login = DB_STRUCTURE_ADMINISTRATEUR::DB_rechercher_login_disponible($login);
        }
        // Construire le password
        if( ($import_profil!='eleve') || (!$_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI']['ELV']) || (empty($tab_memo_analyse['ajouter'][$i_fichier]['birth_date'])) )
        {
          $password = fabriquer_mdp($tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle']);
        }
        else
        {
          $password = str_replace('/','',$tab_memo_analyse['ajouter'][$i_fichier]['birth_date']);
        }
        // Attention à la date de naissance, définie seulement pour les élèves
        $birth_date = empty($tab_memo_analyse['ajouter'][$i_fichier]['birth_date']) ? NULL : convert_date_french_to_mysql($tab_memo_analyse['ajouter'][$i_fichier]['birth_date']) ;
        // Ajouter l'utilisateur
        $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur(
          $tab_memo_analyse['ajouter'][$i_fichier]['sconet_id'],
          $tab_memo_analyse['ajouter'][$i_fichier]['sconet_num'],
          $tab_memo_analyse['ajouter'][$i_fichier]['reference'],
          $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle'],
          $tab_memo_analyse['ajouter'][$i_fichier]['nom'],
          $tab_memo_analyse['ajouter'][$i_fichier]['prenom'],
          $birth_date,
          '', /* user_email */
          $login,
          crypter_mdp($password),
          $tab_memo_analyse['ajouter'][$i_fichier]['classe']
        );
        $tab_i_fichier_TO_id_base[$i_fichier] = (int) $user_id;
        $nb_add++;
        $tab_password[$user_id] = $password;
        $classe_ou_profil = ($import_profil=='eleve') ? $tab_nom_classe[$tab_memo_analyse['ajouter'][$i_fichier]['classe']] : $tab_memo_analyse['ajouter'][$i_fichier]['profil_sigle'] ;
        $fcontenu_csv .= '"'.$tab_memo_analyse['ajouter'][$i_fichier]['sconet_id'].'"'.$separateur.'"'.$tab_memo_analyse['ajouter'][$i_fichier]['sconet_num'].'"'.$separateur.'"'.$tab_memo_analyse['ajouter'][$i_fichier]['reference'].'"'.$separateur.$classe_ou_profil.$separateur.$tab_memo_analyse['ajouter'][$i_fichier]['nom'].$separateur.$tab_memo_analyse['ajouter'][$i_fichier]['prenom'].$separateur.$login.$separateur.'"'.$password.'"'."\r\n";
        $ligne1 = $classe_ou_profil;
        $ligne2 = $tab_memo_analyse['ajouter'][$i_fichier]['nom'].' '.$tab_memo_analyse['ajouter'][$i_fichier]['prenom'];
        $ligne3 = 'Utilisateur : '.$login;
        $ligne4 = 'Mot de passe : '.$password;
        $fcontenu_pdf_tab[] = $ligne1."\r\n".$ligne2."\r\n".$ligne3."\r\n".$ligne4;
      }
    }
  }
  // Modifier des users éventuels
  $nb_mod = 0;
  if(count($tab_mod))
  {
    foreach($tab_mod as $id_base)
    {
      // Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement...
      $tab_champs = ($import_profil=='eleve') ? array( 'sconet_id' , 'sconet_num' , 'reference' , 'classe' , 'nom' , 'prenom' , 'birth_date' ) : array( 'sconet_id' , 'reference' , 'profil_sigle' , 'nom' , 'prenom' ) ;
      $DB_VAR  = array();
      foreach($tab_champs as $champ_ref)
      {
        if($tab_memo_analyse['modifier'][$id_base][$champ_ref] !== FALSE)
        {
          $DB_VAR[':'.$champ_ref] = ($champ_ref!='birth_date') ? $tab_memo_analyse['modifier'][$id_base][$champ_ref] : convert_date_french_to_mysql($tab_memo_analyse['modifier'][$id_base][$champ_ref]) ;
        }
      }
      if($tab_memo_analyse['modifier'][$id_base]['entree'] !== FALSE)
      {
        $DB_VAR[':sortie_date'] = $tab_memo_analyse['modifier'][$id_base]['entree'];
      }
      // bilan
      if( count($DB_VAR) )
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_user( $id_base , $DB_VAR );
      }
      $nb_mod++;
    }
  }
  // On enregistre (tableau mis à jour)
  $tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt',serialize($tab_liens_id_base));
  // Afficher le bilan
  $lignes        = '';
  $nb_fin_actuel = 0;
  $nb_fin_ancien = 0;
  $profil_type = ($import_profil!='professeur') ? $import_profil : array('professeur','directeur') ;
  $with_classe = ($import_profil=='eleve') ? TRUE : FALSE ;
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( $profil_type , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil_nom_court_singulier,user_nom,user_prenom,user_prenom,user_login,user_sortie_date' /*liste_champs*/ , $with_classe , TRUE /*tri_statut*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if(TODAY_MYSQL<$DB_ROW['user_sortie_date']) {$nb_fin_actuel++;} else {$nb_fin_ancien++;}
    if($mode=='complet')
    {
      $class       = (isset($tab_password[$DB_ROW['user_id']])) ? ' class="new"' : '' ;
      $td_password = (isset($tab_password[$DB_ROW['user_id']])) ? '<td class="new">'.html($tab_password[$DB_ROW['user_id']]).'</td>' : '<td class="i">champ crypté</td>' ;
      $champ = ($import_profil=='eleve') ? $DB_ROW['groupe_ref'] : $DB_ROW['user_profil_nom_court_singulier'] ;
      $date_affich = ($DB_ROW['user_sortie_date']!=SORTIE_DEFAUT_MYSQL) ? convert_date_mysql_to_french($DB_ROW['user_sortie_date']) : '-' ;
      $lignes .= '<tr'.$class.'><td>'.html($DB_ROW['user_sconet_id']).'</td><td>'.html($DB_ROW['user_sconet_elenoet']).'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($champ).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td'.$class.'>'.html($DB_ROW['user_login']).'</td>'.$td_password.'<td>'.$date_affich.'</td></tr>'.NL;
    }
  }
  $s_debut_actuel = ($nb_debut_actuel>1) ? 's' : '';
  $s_debut_ancien = ($nb_debut_ancien>1) ? 's' : '';
  $s_fin_actuel   = ($nb_fin_actuel>1)   ? 's' : '';
  $s_fin_ancien   = ($nb_fin_ancien>1)   ? 's' : '';
  $s_mod = ($nb_mod>1) ? 's' : '';
  $s_add = ($nb_add>1) ? 's' : '';
  $s_del = ($nb_del>1) ? 's' : '';
  if($nb_add)
  {
    // On archive les nouveaux identifiants dans un fichier tableur (csv tabulé)
    $profil = ($import_profil=='eleve') ? 'eleve' : ( ($import_profil=='parent') ? 'parent' : 'personnel' ) ;
    $fnom = 'identifiants_'.$_SESSION['BASE'].'_'.$profil.'_'.fabriquer_fin_nom_fichier__date_et_alea();
    FileSystem::ecrire_fichier( CHEMIN_DOSSIER_LOGINPASS.$fnom.'.csv' , To::csv($fcontenu_csv) );
    // On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
    $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
    $pdf -> AddFont('Arial','' ,'arial.php');
    $pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
    $pdf -> AddPage();
    $pdf -> SetFillColor(245,245,245);
    $pdf -> SetDrawColor(145,145,145);
    sort($fcontenu_pdf_tab);
    foreach($fcontenu_pdf_tab as $text)
    {
      $pdf -> Add_Label(To::pdf($text));
    }
    $pdf->Output(CHEMIN_DOSSIER_LOGINPASS.$fnom.'.pdf','F');
  }
  $champ = ($import_profil=='eleve') ? 'Classe' : 'Profil' ;
  echo'<p><label class="valide">'.$nb_debut_actuel.' utilisateur'.$s_debut_actuel.' actuel'.$s_debut_actuel.' et '.$nb_debut_ancien.' utilisateur'.$s_debut_ancien.' ancien'.$s_debut_ancien.' &rarr; '.$nb_mod.' utilisateur'.$s_mod.' modifié'.$s_mod.' + '.$nb_add.' utilisateur'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' utilisateur'.$s_del.' retiré'.$s_del.' &rarr; '.$nb_fin_actuel.' utilisateur'.$s_fin_actuel.' actuel'.$s_fin_actuel.' et '.$nb_fin_ancien.' utilisateur'.$s_fin_ancien.' ancien'.$s_fin_ancien.'.</label></p>'.NL;
  if($mode=='complet')
  {
    echo'<table>'.NL;
    echo  '<thead>'.NL;
    echo    '<tr><th>Id Sconet</th><th>N° Sconet</th><th>Référence</th><th>'.$champ.'</th><th>Nom</th><th>Prénom</th><th>Login</th><th>Mot de passe</th><th>Sortie</th></tr>'.NL;
    echo  '</thead>'.NL;
    echo  '<tbody>'.NL;
    echo    $lignes;
    echo  '</tbody>'.NL;
    echo'</table>'.NL;
  }
  if($nb_add)
  {
    echo'<ul class="puce p"><li><a href="#" class="step53">Récupérer les identifiants de tout nouvel utilisateur inscrit.</a><input id="archive" name="archive" type="hidden" value="'.$fnom.'" /><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  }
  else
  {
    echo'<p class="astuce">Il n\'y a aucun nouvel utilisateur inscrit, donc pas d\'identifiants à récupérer.</p>'.NL;
    switch($import_origine.'+'.$import_profil)
    {
      case 'sconet+eleve'       : $etape = 6; $step = 61; break;
      case 'sconet+professeur'  : $etape = 6; $step = 61; break;
      case 'tableur+eleve'      : $etape = 6; $step = 61; break;
      case 'tableur+professeur' : $etape = 6; $step = 61; break;
      case 'sconet+parent'      : $etape = 4; $step = 71; break;
      case 'tableur+parent'     : $etape = 4; $step = 71; break;
      case 'base_eleves+parent' : $etape = 4; $step = 71; break;
      case 'base_eleves+eleve'  : $etape = 5; $step = 90; break;
    }
    echo'<ul class="puce p"><li><a href="#step'.$step.'" id="passer_etape_suivante">Passer à l\'étape '.$etape.'.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 53 - Récupérer les identifiants des nouveaux utilisateurs (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==53 )
{
  $archive = (isset($_POST['archive'])) ? $_POST['archive'] : '';
  if(!$archive)
  {
    exit('Erreur : le nom du fichier contenant les identifiants est manquant !');
  }
  echo'<p><label class="alerte">Voici les identifiants des nouveaux inscrits :</label></p>'.NL;
  echo'<ul class="puce">'.NL;
  echo  '<li><a target="_blank" href="'.URL_DIR_LOGINPASS.$archive.'.pdf"><span class="file file_pdf">Archiver / Imprimer (étiquettes <em>pdf</em>).</span></a></li>'.NL;
  echo  '<li><a target="_blank" href="./force_download.php?auth&amp;fichier='.$archive.'.csv"><span class="file file_txt">Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</span></a></li>'.NL;
  echo'</ul>'.NL;
  echo'<p class="danger">Les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</p>'.NL;
  switch($import_origine.'+'.$import_profil)
  {
    case 'sconet+eleve'       : $etape = 6; $step = 61; break;
    case 'sconet+professeur'  : $etape = 6; $step = 61; break;
    case 'tableur+eleve'      : $etape = 5; $step = 61; break;
    case 'tableur+professeur' : $etape = 4; $step = 61; break;
    case 'sconet+parent'      : $etape = 4; $step = 71; break;
    case 'tableur+parent'     : $etape = 4; $step = 71; break;
    case 'base_eleves+parent' : $etape = 4; $step = 71; break;
    case 'base_eleves+eleve'  : $etape = 5; $step = 90; break;
  }
  echo'<ul class="puce p"><li><a href="#step'.$step.'" id="passer_etape_suivante">Passer à l\'étape '.$etape.'.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 61 - Modification d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==61 )
{
  $lignes_classes_ras   = '';
  $lignes_classes_add   = '';
  $lignes_classes_del   = '';
  $lignes_principal_ras = '';
  $lignes_principal_add = '';
  $lignes_principal_del = '';
  $lignes_matieres_ras  = '';
  $lignes_matieres_add  = '';
  $lignes_matieres_del  = '';
  $lignes_groupes_ras   = '';
  $lignes_groupes_add   = '';
  $lignes_groupes_del   = '';
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
  $tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
  $tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
  // On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
  $tab_users_fichier = load_fichier('users');
  //
  // Pour sconet_professeurs_directeurs, il faut regarder les associations profs/classes & profs/PP + profs/matières + profs/groupes.
  // Pour tableur_professeurs_directeurs, il faut regarder les associations profs/classes & profs/groupes.
  // Pour sconet_eleves & tableur_eleves, il faut juste à regarder les associations élèves/groupes.
  //
  if($import_profil=='professeur')
  {
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // associations profs/classes
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Garder trace des associations profs/classes pour faire le lien avec les propositions d'ajouts profs/pp
    $tab_asso_prof_classe = array();
    // Garder trace des identités des profs de la base
    $tab_base_prof_identite = array();
    // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE $tab_base_classe[groupe_id]=groupe_nom
    // En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
    $tab_base_classe = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
    }
    $tab_base_affectation = array();
    $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_professeurs_avec_classes();
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
      $tab_base_prof_identite[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
    }
    // Parcourir chaque entrée du fichier à la recherche d'affectations profs/classes
    foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
    {
      if(count($tab_classes))
      {
        foreach( $tab_classes as $i_classe => $classe_pp )
        {
          // On a trouvé une telle affectation ; comparer avec ce que contient la base
          if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
          {
            $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
            $groupe_id = $tab_i_classe_TO_id_base[$i_classe];
            if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
            {
              $tab_asso_prof_classe[$user_id.'_'.$groupe_id] = TRUE;
              if($mode=='complet')
              {
                $lignes_classes_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
              }
              unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
            }
            else
            {
              $tab_asso_prof_classe[$user_id.'_'.$groupe_id] = TRUE;
              $lignes_classes_add .= '<tr><th>Ajouter <input id="classe_'.$user_id.'_'.$groupe_id.'_1" name="classe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
            }
          }
        }
      }
    }
    // Associations à retirer
    if(count($tab_base_affectation))
    {
      foreach($tab_base_affectation as $key => $bool)
      {
        list($user_id,$groupe_id) = explode('_',$key);
        $lignes_classes_del .= '<tr><th>Supprimer <input id="classe_'.$user_id.'_'.$groupe_id.'_0" name="classe_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_prof_identite[$user_id]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
      }
    }
    if($import_origine=='sconet')
    {
      // ////////////////////////////////////////////////////////////////////////////////////////////////////
      // associations profs/PP
      // ////////////////////////////////////////////////////////////////////////////////////////////////////
      // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE ($tab_base_classe déjà renseigné)
      $tab_base_affectation = array();
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_jointure_professeurs_principaux();
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
      }
      // Parcourir chaque entrée du fichier à la recherche d'affectations profs/PP
      foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
      {
        if(count($tab_classes))
        {
          foreach( $tab_classes as $i_classe => $classe_pp )
          {
            if($classe_pp=='PP')
            {
              // On a trouvé une telle affectation ; comparer avec ce que contient la base
              if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
              {
                $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
                $groupe_id = $tab_i_classe_TO_id_base[$i_classe];
                if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
                {
                  if($mode=='complet')
                  {
                    $lignes_principal_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
                  }
                  unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
                }
                elseif(isset($tab_asso_prof_classe[$user_id.'_'.$groupe_id]))
                {
                  $lignes_principal_add .= '<tr><th>Ajouter <input id="pp_'.$user_id.'_'.$groupe_id.'_1" name="pp_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
                }
              }
            }
          }
        }
      }
      // Associations à retirer
      if(count($tab_base_affectation))
      {
        foreach($tab_base_affectation as $key => $bool)
        {
          list($user_id,$groupe_id) = explode('_',$key);
          $lignes_principal_del .= '<tr><th>Supprimer <input id="pp_'.$user_id.'_'.$groupe_id.'_0" name="pp_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_prof_identite[$user_id]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>'.NL;
        }
      }
      // ////////////////////////////////////////////////////////////////////////////////////////////////////
      // associations profs/matières
      // ////////////////////////////////////////////////////////////////////////////////////////////////////
      // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_matiere_id]=TRUE + $tab_base_matiere[matiere_id]=matiere_nom + $tab_matiere_ref_TO_id_base[matiere_ref]=id_base
      // En deux requêtes sinon on ne récupère pas les matieres sans utilisateurs affectés.
      $tab_base_matiere = array();
      $tab_matiere_ref_TO_id_base = array();
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_base_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
        $tab_matiere_ref_TO_id_base[$DB_ROW['matiere_ref']] = $DB_ROW['matiere_id'];
      }
      $tab_base_affectation = array();
      $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_jointure_professeurs_matieres();
      foreach($DB_TAB as $DB_ROW)
      {
        $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['matiere_id']] = TRUE;
      }
      // Parcourir chaque entrée du fichier à la recherche d'affectations profs/matières
      foreach( $tab_users_fichier['matiere'] as $i_fichier => $tab_matieres )
      {
        if(count($tab_matieres))
        {
          foreach( $tab_matieres as $matiere_code => $type_rattachement ) // $type_rattachement vaut 'discipline' ou 'service'
          {
            // On a trouvé une telle affectation ; comparer avec ce que contient la base
            if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_matiere_ref_TO_id_base[$matiere_code])) )
            {
              $user_id    = $tab_i_fichier_TO_id_base[$i_fichier];
              $matiere_id = $tab_matiere_ref_TO_id_base[$matiere_code];
              if(isset($tab_base_affectation[$user_id.'_'.$matiere_id]))
              {
                if($mode=='complet')
                {
                  $lignes_matieres_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>'.NL;
                }
                unset($tab_base_affectation[$user_id.'_'.$matiere_id]);
              }
              else
              {
                $lignes_matieres_add .= '<tr><th>Ajouter <input id="matiere_'.$user_id.'_'.$matiere_id.'_1" name="matiere_'.$user_id.'_'.$matiere_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>'.NL;
              }
            }
          }
        }
      }
      // Retirer des matières semble sans intérêt.
    }
  }
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // associations profs/groupes ou élèves/groupes
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Garder trace des identités des utilisateurs de la base
  $tab_base_user_identite = array();
  // On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=TRUE et $tab_base_groupe[groupe_id]=groupe_nom
  // En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
  $tab_base_groupe = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_groupes();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_base_groupe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
  }
  $tab_base_affectation = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users_avec_groupe( $import_profil , TRUE /*only_actuels*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = TRUE;
    $tab_base_user_identite[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
  }
  // Parcourir chaque entrée du fichier à la recherche d'affectations utilisateurs/groupes
  foreach( $tab_users_fichier['groupe'] as $i_fichier => $tab_groupes )
  {
    if(count($tab_groupes))
    {
      foreach( $tab_groupes as $i_groupe => $groupe_ref )
      {
        // On a trouvé une telle affectation ; comparer avec ce que contient la base
        if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_groupe_TO_id_base[$i_groupe])) )
        {
          $user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
          $groupe_id = $tab_i_groupe_TO_id_base[$i_groupe];
          if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
          {
            if($mode=='complet')
            {
              $lignes_groupes_ras .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
            }
            unset($tab_base_affectation[$user_id.'_'.$groupe_id]);
          }
          else
          {
            $lignes_groupes_add .= '<tr><th>Ajouter <input id="groupe_'.$user_id.'_'.$groupe_id.'_1" name="groupe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
          }
        }
      }
    }
  }
  // Associations à retirer
  if(count($tab_base_affectation))
  {
    foreach($tab_base_affectation as $key => $bool)
    {
      list($user_id,$groupe_id) = explode('_',$key);
      $lignes_groupes_del .= '<tr><th>Supprimer <input id="groupe_'.$user_id.'_'.$groupe_id.'_0" name="groupe_'.$user_id.'_'.$groupe_id.'_0" type="checkbox" checked /></th><td>'.html($tab_base_user_identite[$user_id]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>'.NL;
    }
  }
  // On affiche
  echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des affectations éventuelles.</label></p>'.NL;
  if( $lignes_classes_del || $lignes_principal_del || $lignes_groupes_del )
  {
    echo'<p class="danger">Des suppressions sont proposées. Elles peuvent provenir d\'un fichier incomplet ou d\'ajouts manuels antérieurs dans SACoche. Décochez-les si besoin !</p>'.NL;
  }
  echo'<table>'.NL;
  if($import_profil=='professeur')
  {
    if($mode=='complet')
    {
      echo    '<tbody>'.NL;
      echo      '<tr><th colspan="3">Associations utilisateurs / classes à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
      echo($lignes_classes_ras) ? $lignes_classes_ras : '<tr><td colspan="3">Aucune</td></tr>'.NL;
      echo    '</tbody>'.NL;
    }
    echo    '<tbody>'.NL;
    echo      '<tr><th colspan="3">Associations utilisateurs / classes à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
    echo($lignes_classes_add) ? $lignes_classes_add : '<tr><td colspan="3">Aucune</td></tr>'.NL;
    echo    '</tbody>'.NL;
    echo    '<tbody>'.NL;
    echo      '<tr><th colspan="3">Associations utilisateurs / classes à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
    echo($lignes_classes_del) ? $lignes_classes_del : '<tr><td colspan="3">Aucune</td></tr>'.NL;
    echo    '</tbody>'.NL;
    if($import_origine=='sconet')
    {
      if($mode=='complet')
      {
        echo    '<tbody>'.NL;
        echo      '<tr><th colspan="3">Associations utilisateurs / p.principal à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
        echo($lignes_principal_ras) ? $lignes_principal_ras : '<tr><td colspan="3">Aucune</td></tr>'.NL;
        echo    '</tbody>'.NL;
      }
      echo    '<tbody>'.NL;
      echo      '<tr><th colspan="3">Associations utilisateurs / p.principal à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
      echo($lignes_principal_add) ? $lignes_principal_add : '<tr><td colspan="3">Aucune</td></tr>'.NL;
      echo    '</tbody>'.NL;
      echo    '<tbody>'.NL;
      echo      '<tr><th colspan="3">Associations utilisateurs / p.principal à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
      echo($lignes_principal_del) ? $lignes_principal_del : '<tr><td colspan="3">Aucune</td></tr>'.NL;
      echo    '</tbody>'.NL;
      if($mode=='complet')
      {
        echo    '<tbody>'.NL;
        echo      '<tr><th colspan="3">Associations utilisateurs / matières à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
        echo($lignes_matieres_ras) ? $lignes_matieres_ras : '<tr><td colspan="3">Aucune</td></tr>'.NL;
        echo    '</tbody>'.NL;
      }
      echo    '<tbody>'.NL;
      echo      '<tr><th colspan="3">Associations utilisateurs / matières à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
      echo($lignes_matieres_add) ? $lignes_matieres_add : '<tr><td colspan="3">Aucune</td></tr>'.NL;
      // echo    '</tbody>'.NL;
      // echo    '<tbody>'.NL;
      // echo      '<tr><th colspan="3">Associations utilisateurs / matières à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
      // echo($lignes_matieres_del) ? $lignes_matieres_del : '<tr><td colspan="3">Aucune</td></tr>'.NL;
      echo    '</tbody>'.NL;
    }
  }
  if($mode=='complet')
  {
    echo    '<tbody>';
    echo      '<tr><th colspan="3">Associations utilisateurs / groupes à conserver.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
    echo($lignes_groupes_ras) ? $lignes_groupes_ras : '<tr><td colspan="3">Aucune</td></tr>'.NL;
    echo    '</tbody>'.NL;
  }
  echo    '<tbody>'.NL;
  echo      '<tr><th colspan="3">Associations utilisateurs / groupes à ajouter.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_groupes_add) ? $lignes_groupes_add : '<tr><td colspan="3">Aucune</td></tr>'.NL;
  echo    '</tbody>'.NL;
  echo    '<tbody>'.NL;
  echo      '<tr><th colspan="3">Associations utilisateurs / groupes à supprimer.<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_groupes_del) ? $lignes_groupes_del : '<tr><td colspan="3">Aucune</td></tr>'.NL;
  echo    '</tbody>'.NL;
  echo'</table>'.NL;
  echo'<ul class="puce p"><li><a href="#step62" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 62 - Traitement des ajouts d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==62 )
{
  // Récupérer les éléments postés
  $tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
  $tab_post = array( 'classe'=>array() , 'pp'=>array() , 'matiere'=>array() , 'groupe'=>array() );
  foreach($tab_check as $check_infos)
  {
    if( (substr($check_infos,0,7)=='classe_') || (substr($check_infos,0,3)=='pp_') || (substr($check_infos,0,8)=='matiere_') || (substr($check_infos,0,7)=='groupe_') )
    {
      list($obj,$id1,$id2,$etat) = explode('_',$check_infos);
      $tab_post[$obj][$id1][$id2] = (bool)$etat;
    }
  }
  // Modifier des associations users/classes (profs uniquements, pour les élèves c'est fait à l'étape 52)
  $nb_asso_classes = count($tab_post['classe']);
  if($nb_asso_classes)
  {
    foreach($tab_post['classe'] as $user_id => $tab_id2)
    {
      foreach($tab_id2 as $classe_id => $etat)
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_user_groupe_par_admin($user_id,$import_profil,$classe_id,'classe',$etat);
      }
    }
  }
  // Ajouter des associations users/pp (profs uniquements)
  $nb_asso_pps = count($tab_post['pp']);
  if($nb_asso_pps)
  {
    foreach($tab_post['pp'] as $user_id => $tab_id2)
    {
      foreach($tab_id2 as $classe_id => $etat)
      {
        // En espérant qu'on ne fasse pas une association de PP avec une classe à laquelle le prof n'est pas associée
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_professeur_principal($user_id,$classe_id,$etat);
      }
    }
  }
  // Ajouter des associations users/matières (profs uniquements)
  $nb_asso_matieres = count($tab_post['matiere']);
  if($nb_asso_matieres)
  {
    foreach($tab_post['matiere'] as $user_id => $tab_id2)
    {
      foreach($tab_id2 as $matiere_id => $etat)
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_professeur_matiere($user_id,$matiere_id,$etat);
      }
    }
  }
  // Ajouter des associations users/groupes (profs ou élèves)
  $nb_asso_groupes = count($tab_post['groupe']);
  if($nb_asso_groupes)
  {
    foreach($tab_post['groupe'] as $user_id => $tab_id2)
    {
      foreach($tab_id2 as $groupe_id => $etat)
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_liaison_user_groupe_par_admin($user_id,$import_profil,$groupe_id,'groupe',$etat);
      }
    }
  }
  // Afficher le résultat
  if($import_profil=='professeur')
  {
    echo'<p><label class="valide">Modifications associations utilisateurs / classes effectuées : '.$nb_asso_classes.'</label></p>'.NL;
    if($import_origine=='sconet')
    {
      echo'<p><label class="valide">Modifications associations utilisateurs / p.principal effectuées : '.$nb_asso_pps.'</label></p>'.NL;
      echo'<p><label class="valide">Modifications associations utilisateurs / matières effectuées : '.$nb_asso_matieres.'</label></p>'.NL;
    }
  }
  echo'<p><label class="valide">Modifications associations utilisateurs / groupes effectuées : '.$nb_asso_groupes.'</label></p>'.NL;
  echo'<ul class="puce p"><li><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 7.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 71 - Adresses des parents (sconet_parents | base_eleves_parents | tableur_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==71 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
  // On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
  $fnom = CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_users.txt';
  if(!is_file($fnom))
  {
    exit('Erreur : le fichier contenant les utilisateurs est introuvable !');
  }
  $contenu = file_get_contents($fnom);
  $tab_users_fichier = @unserialize($contenu);
  if($tab_users_fichier===FALSE)
  {
    exit('Erreur : le fichier contenant les utilisateurs est syntaxiquement incorrect !');
  }
  // On récupère le contenu de la base pour comparer : $tab_base_adresse[user_id]=array()
  $tab_base_adresse = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_adresses_parents();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_base_adresse[$DB_ROW['parent_id']] = array( $DB_ROW['adresse_ligne1'] , $DB_ROW['adresse_ligne2'] , $DB_ROW['adresse_ligne3'] , $DB_ROW['adresse_ligne4'] , (int)$DB_ROW['adresse_postal_code'] , $DB_ROW['adresse_postal_libelle'] , $DB_ROW['adresse_pays_nom'] );
  }
  // Pour préparer l'affichage
  $lignes_ajouter   = '';
  $lignes_modifier  = '';
  $lignes_conserver = '';
  // Pour préparer l'enregistrement des données
  $tab_users_ajouter = array();
  $tab_users_modifier = array();
  // Parcourir chaque entrée du fichier
  foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
  {
    // Cas [1] : parent présent dans le fichier, adresse absente de la base : il vient d'être ajouté, on ajoute aussi son adresse, sauf si elle est vide (on ne teste pas le pays qui vaut FRANCE par défaut dans l'export Sconet).
    if(!isset($tab_base_adresse[$id_base]))
    {
      if( $tab_users_fichier['adresse'][$i_fichier][0] || $tab_users_fichier['adresse'][$i_fichier][1] || $tab_users_fichier['adresse'][$i_fichier][2] || $tab_users_fichier['adresse'][$i_fichier][3] || $tab_users_fichier['adresse'][$i_fichier][4] || $tab_users_fichier['adresse'][$i_fichier][5] )
      {
        $lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_users_fichier['adresse'][$i_fichier][0].' / '.$tab_users_fichier['adresse'][$i_fichier][1].' / '.$tab_users_fichier['adresse'][$i_fichier][2].' / '.$tab_users_fichier['adresse'][$i_fichier][3].' / '.$tab_users_fichier['adresse'][$i_fichier][4].' / '.$tab_users_fichier['adresse'][$i_fichier][5].' / '.$tab_users_fichier['adresse'][$i_fichier][6]).'</td></tr>';
      }
    }
    // Cas [2] : parent présent dans le fichier, adresse présente de la base
    else
    {
      $nb_differences = 0;
      $td_contenu = array();
      for($indice=0 ; $indice<7 ; $indice++)
      {
        if($tab_users_fichier['adresse'][$i_fichier][$indice]==$tab_base_adresse[$id_base][$indice])
        {
          $td_contenu[] = html($tab_base_adresse[$id_base][$indice]);
        }
        else
        {
          $td_contenu[] = '<b>'.html($tab_base_adresse[$id_base][$indice]).' &rarr; '.html($tab_users_fichier['adresse'][$i_fichier][$indice]).'</b>';
          $nb_differences++;
        }
      }
      if($nb_differences==0)
      {
        // Cas [2a] : adresses identiques &rarr; conserver
        if($mode=='complet')
        {
          $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>'.NL;
        }
      }
      else
      {
        // Cas [2b] : adresses différentes &rarr; modifier
        $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$i_fichier.'" name="mod_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>'.NL;
      }
    }
  }
  // On affiche
  echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des adresses.</label></p>'.NL;
  echo'<table>'.NL;
  // Cas [1]
  echo    '<tbody>'.NL;
  echo      '<tr><th colspan="3">Adresses à ajouter<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="3">Aucune</td></tr>'.NL;
  echo    '</tbody>'.NL;
  // Cas [2b]
  echo    '<tbody>'.NL;
  echo      '<tr><th colspan="3">Adresses à modifier<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="3">Aucune</td></tr>'.NL;
  echo    '</tbody>'.NL;
  // Cas [2a]
  if($mode=='complet')
  {
    echo    '<tbody>'.NL;
    echo      '<tr><th colspan="3">Adresses à conserver</th></tr>'.NL;
    echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="3">Aucune</td></tr>'.NL;
    echo    '</tbody>'.NL;
  }
  echo'</table>'.NL;
  echo'<ul class="puce p"><li><a href="#step72" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 72 - Traitement des ajouts/modifications d'adresses éventuelles (sconet_parents | base_eleves_parents | tableur_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==72 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
  // On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
  $tab_users_fichier = load_fichier('users');
  // Récupérer les éléments postés et ajouter/modifier les adresses
  $tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
  $nb_add = 0;
  $nb_mod = 0;
  foreach($tab_check as $check_infos)
  {
    if(substr($check_infos,0,4)=='mod_')
    {
      $i_fichier = Clean::entier( substr($check_infos,4) );
      if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
        $nb_mod++;
      }
    }
    elseif(substr($check_infos,0,4)=='add_')
    {
      $i_fichier = Clean::entier( substr($check_infos,4) );
      if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
        $nb_add++;
      }
    }
  }
  // Afficher le résultat
  echo'<p><label class="valide">Nouvelles adresses ajoutées : '.$nb_add.'</label></p>'.NL;
  echo'<p><label class="valide">Anciennes adresses modifiées : '.$nb_mod.'</label></p>'.NL;
  echo'<ul class="puce p"><li><a href="#step81" id="passer_etape_suivante">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 81 - Liens de responsabilités des parents (sconet_parents | base_eleves_parents | tableur_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==81 )
{
  // On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
  $tab_liens_id_base = load_fichier('liens_id_base');
  $tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
  // On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil_sigle / nom / prenom / classe / groupes / matieres / adresse / enfant
  $tab_users_fichier = load_fichier('users');
  // On convertit les données du fichier parent=>enfant dans un tableau enfant=>parent
  $tab_fichier_parents_par_eleve = array();
  foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
  {
    if( (isset($tab_users_fichier['enfant'][$i_fichier])) && (count($tab_users_fichier['enfant'][$i_fichier])) )
    {
      foreach($tab_users_fichier['enfant'][$i_fichier] as $eleve_id => $resp_legal_num)
      {
        $tab_fichier_parents_par_eleve[$eleve_id][$i_fichier] = $resp_legal_num;
      }
    }
  }
  // On récupère le contenu de la base pour comparer : $tab_base_parents_par_eleve[eleve_id]=array( 'eleve'=>(eleve_nom,eleve_prenom) , 'parent'=>array(num=>(parent_id,parent_nom,parent_prenom,)) )
  $tab_base_parents_par_eleve = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_parents_par_eleve();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_infos_eleve = array( 'nom'=>$DB_ROW['eleve_nom'] , 'prenom'=>$DB_ROW['eleve_prenom'] );
    if( ($DB_ROW['parent_id']) && ( ( $DB_ROW['parent_sconet_id'] && $DB_ROW['eleve_sconet_id'] ) || ($import_origine=='base_eleves') ) )
    {
      $tab_infos_parent = array( 'id'=>(int)$DB_ROW['parent_id'] , 'nom'=>$DB_ROW['parent_nom'] , 'prenom'=>$DB_ROW['parent_prenom'] );
      if(!isset($tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']]))
      {
        $tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array((int)$DB_ROW['resp_legal_num']=>$tab_infos_parent) );
      }
      else
      {
        $tab_base_parents_par_eleve[(int)$DB_ROW['eleve_id']]['parent'][$DB_ROW['resp_legal_num']] = $tab_infos_parent;
      }
    }
    else
    {
      // Cas d'un élève sans parent affecté ou un élève/parent sans id Sconet avec import Sconet
      if(!isset($tab_base_parents_par_eleve[$DB_ROW['eleve_id']]))
      {
        $tab_base_parents_par_eleve[$DB_ROW['eleve_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array() );
      }
    }
  }
  // On enregistre une copie du tableau $tab_fichier_parents_par_eleve (on partira de celui-ci pour récupérer les identifiants à ajouter / modifier).
  // Il faut le faire maintenant car ensuite tab_base_parents_par_eleve est ensuite peu à peu vidé.
  $tab_memo_analyse = array();
  foreach($tab_fichier_parents_par_eleve as $eleve_id_base => $tab_parent)
  {
    foreach($tab_parent as $i_fichier => $resp_legal_num)
    {
      $parent_id_base = $tab_i_fichier_TO_id_base[$i_fichier];
      $tab_memo_analyse[$eleve_id_base][$parent_id_base] = $resp_legal_num;
    }
  }
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_memo_analyse.txt',serialize($tab_memo_analyse));
  // Pour préparer l'affichage
  $lignes_modifier  = '';
  $lignes_conserver = '';
  // Pour préparer l'enregistrement des données
  $tab_users_modifier = array();
  // Parcourir chaque élève de la base
  foreach($tab_base_parents_par_eleve as $eleve_id_base => $tab_base_eleve_infos)
  {
    if(isset($tab_fichier_parents_par_eleve[$eleve_id_base])) // Si on ne trouve aucun parent dans le fichier, on laisse tomber, c'est peut être un vieux compte
    {
      $nb_differences = 0;
      $td_contenu = array();
      // On fait des modifs s'il n'y a pas le même nombre de responsables ou si un responsable est différent ou si l'ordre des responsables est différent
      $num = 1;
      while( count($tab_fichier_parents_par_eleve[$eleve_id_base]) || count($tab_base_eleve_infos['parent']) )
      {
        $parent_i_fichier      = array_search( $num, $tab_fichier_parents_par_eleve[$eleve_id_base] );
        $parent_id_base        = (isset($tab_base_eleve_infos['parent'][$num])) ? $tab_base_eleve_infos['parent'][$num]['id'] : FALSE ;
        $parent_affich_fichier = ($parent_i_fichier===FALSE)  ? 'X' : $tab_users_fichier['nom'][$parent_i_fichier].' '.$tab_users_fichier['prenom'][$parent_i_fichier] ;
        $parent_affich_base    = ($parent_id_base===FALSE)    ? 'X' : $tab_base_eleve_infos['parent'][$num]['nom'].' '.$tab_base_eleve_infos['parent'][$num]['prenom'] ;
        if($tab_i_fichier_TO_id_base[$parent_i_fichier]===$parent_id_base)
        {
          if($parent_affich_base!='X')
          {
            $td_contenu[] = 'Responsable n°'.$num.' : '.html($parent_affich_base);
          }
        }
        else
        {
          $td_contenu[] = 'Responsable n°'.$num.' : <b>'.html($parent_affich_base).' &rarr; '.html($parent_affich_fichier).'</b>';
          $nb_differences++;
        }
        if($parent_i_fichier!==FALSE)
        {
          unset($tab_fichier_parents_par_eleve[$eleve_id_base][$parent_i_fichier]);
        }
        if($tab_i_fichier_TO_id_base[$parent_i_fichier])
        {
          unset($tab_base_eleve_infos['parent'][$num]);
        }
        $num++;
        if($num==5)
        {
          // Il arrive que certains fichiers Sconet soient mal renseignés, avec par exemple plusieurs responsables n°1 (un vieux compte et un nouveau).
          // Si on ne met pas une sortie à ce niveau alors ça boucle à l'infini.
          break;
        }
      }
      if($nb_differences==0)
      {
        // Cas [1] : responsables identiques &rarr; conserver
        if($mode=='complet')
        {
          $lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>'.NL;
        }
      }
      else
      {
        // Cas [2] : au moins une différence  &rarr; modifier
        $lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$eleve_id_base.'" name="mod_'.$eleve_id_base.'" type="checkbox" checked /></th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>'.NL;
      }
    }
  }
  // On affiche
  echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des liens de responsabilité.</label></p>'.NL;
  echo'<table>'.NL;
  // Cas [2]
  echo    '<tbody>'.NL;
  echo      '<tr><th colspan="3">Liens de responsabilité à modifier<q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th></tr>'.NL;
  echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="3">Aucun</td></tr>'.NL;
  echo    '</tbody>'.NL;
  // Cas [1]
  if($mode=='complet')
  {
    echo    '<tbody>'.NL;
    echo      '<tr><th colspan="3">Liens de responsabilité à conserver</th></tr>'.NL;
    echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="3">Aucun</td></tr>'.NL;
    echo    '</tbody>'.NL;
  }
  echo'</table>'.NL;
  echo'<ul class="puce p"><li><a href="#step82" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 82 - Traitement des liens de responsabilités des parents (sconet_parents | base_eleves_parents | tableur_parents)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==82 )
{
  // On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse[$eleve_id][$parent_id] = $resp_legal_num;
  $tab_memo_analyse = load_fichier('memo_analyse');
  // Récupérer les éléments postés
  $tab_eleve_id = array() ;
  $tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
  foreach($tab_check as $check_infos)
  {
    if(substr($check_infos,0,4)=='mod_')
    {
      $eleve_id = Clean::entier( substr($check_infos,4) );
      if( isset($tab_memo_analyse[$eleve_id]) )
      {
        $tab_eleve_id[] = $eleve_id;
      }
    }
  }
  $nb_modifs_eleves = count($tab_eleve_id);
  if($nb_modifs_eleves)
  {
    // supprimer les liens de responsabilité des élèves concernés (il est plus simple de réinitialiser que de traiter les resp un par un puis de vérifier s'il n'en reste pas à supprimer...)
    DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_jointures_parents_for_eleves(implode(',',$tab_eleve_id));
    // modifier les liens de responsabilité
    foreach($tab_eleve_id as $eleve_id)
    {
      foreach($tab_memo_analyse[$eleve_id] as $parent_id => $resp_legal_num)
      {
        DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num);
      }
    }
  }
  // Afficher le résultat
  $s = ($nb_modifs_eleves>1) ? 's' : '' ;
  echo'<p><label class="valide">Liens de responsabilités modifiés pour '.$nb_modifs_eleves.' élève'.$s.'</label></p>'.NL;
  echo'<ul class="puce p"><li><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 90 - Nettoyage des fichiers temporaires (tous les cas)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==90 )
{
  // Il est arrivé que ces fichiers n'existent plus (bizarre...) d'où le test d'existence.
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.$fichier_dest                                                                                            , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_users.txt'         , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_classes.txt'       , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_groupes.txt'       , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_memo_analyse.txt'  , TRUE /*verif_exist*/ );
  FileSystem::supprimer_fichier( CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_liens_id_base.txt' , TRUE /*verif_exist*/ );
  // Retenir qu'un import a été effectué
  $nom_variable = 'date_last_import_'.$import_profil.'s';
  DB_STRUCTURE_COMMUN::DB_modifier_parametres( array( $nom_variable => TODAY_MYSQL ) );
  $_SESSION[strtoupper($nom_variable)] = TODAY_MYSQL;
  // Game over
  echo'<p><label class="valide">Fichiers temporaires effacés, procédure d\'import terminée !</label></p>'.NL;
  echo'<ul class="puce p"><li><a href="#" id="retourner_depart">Retour au départ.</a><label id="ajax_msg">&nbsp;</label></li></ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  exit('Erreur : aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload());
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
