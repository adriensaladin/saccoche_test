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
$TITRE = "Bienvenue dans votre espace identifié";

// user + messages + demandes + help + ecolo peuvent être masqués
// alert est obligatoire
$tab_accueil = array( 'user'=>'' , 'alert'=>'' , 'messages'=>array() , 'demandes'=>'' , 'help'=>'' , 'ecolo'=>'' );

// Le temps de la mise à jour [2012-06-08], pour éviter tout souci ; [TODO] peut être retiré dans un an environ.
if(!(isset($_SESSION['USER_PARAM_ACCUEIL'])))
{
  $_SESSION['USER_PARAM_ACCUEIL'] = 'user,alert,messages,demandes,help,ecolo';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alertes (pour l'administrateur) ; affiché après mais à définir avant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  $alerte_novice = FALSE ;
  $info_rentree  = FALSE ;
  if(!DB_STRUCTURE_ADMINISTRATEUR::compter_matieres_etabl())
  {
    $tab_accueil['alert'] .= '<p class="danger">Aucune matière n\'est choisie pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_matiere">Gestion des matières.</a></p>';
    $alerte_novice = TRUE ;
  }
  if(!DB_STRUCTURE_ADMINISTRATEUR::compter_niveaux_etabl( TRUE /*with_specifiques*/ ))
  {
    $tab_accueil['alert'] .= '<p class="danger">Aucun niveau n\'est choisi pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
    $alerte_novice = TRUE ;
  }
  elseif(!DB_STRUCTURE_ADMINISTRATEUR::compter_niveaux_etabl( FALSE /*with_specifiques*/ ))
  {
    $tab_accueil['alert'] .= '<p class="danger">Aucun niveau de classe n\'est choisi pour l\'établissement ! <a href="./index.php?page=administrateur_etabl_niveau">Gestion des niveaux.</a></p>';
    $alerte_novice = TRUE ;
  }
  if(DB_STRUCTURE_ADMINISTRATEUR::DB_compter_devoirs_annee_scolaire_precedente())
  {
    $tab_accueil['alert'] .= '<p class="danger">Année scolaire précédente non archivée ! Au changement d\'année scolaire il faut <a href="./index.php?page=administrateur_nettoyage">lancer l\'initialisation annuelle des données</a>.</p>';
    $info_rentree  = TRUE ;
  }
  if($alerte_novice)
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['alert'] .= '<p><span class="manuel"><a class="lien_ext" href="'.SERVEUR_GUIDE_ADMIN.'">Guide de démarrage d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Message de bienvenue (informations utilisateur : infos profil, infos selon profil, infos adresse de connexion)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_accueil['user'] = '';
// infos connexion (pas si webmestre ni partenaire)
if(isset($_SESSION['DELAI_CONNEXION']))
{
  $tab_accueil['user'] .= '<p class="i"><TG> Bonjour <b>'.html($_SESSION['USER_PRENOM']).'</b>. ';
  if($_SESSION['FIRST_CONNEXION'])                             { $tab_accueil['user'] .= 'Heureux de faire votre connaissance&nbsp;; bonne découverte de <em>SACoche</em>&nbsp;!</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']<  43200 /*0.5*24*3600*/) { $tab_accueil['user'] .= 'Déjà de retour&nbsp;? Décidément on ne se quitte plus&nbsp;!</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']< 108000 /*  2*24*3600*/) { $tab_accueil['user'] .= 'Bonne navigation, et merci de votre fidélité&nbsp;!</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']< 604800 /*  7*24*3600*/) { $tab_accueil['user'] .= 'Content de vous retrouver après cette pause de quelques jours&nbsp;!</p>'; }
  elseif($_SESSION['DELAI_CONNEXION']<3024000 /* 35*24*3600*/) { $tab_accueil['user'] .= 'Quel plaisir de vous revoir&nbsp;: le temps semble long sans vous&nbsp;!</p>'; }
  else                                                         { $tab_accueil['user'] .= 'On ne s\'était pas vu depuis trop longtemps&nbsp;: vous nous avez manqué&nbsp;!</p>'; }
  unset( $_SESSION['FIRST_CONNEXION'] , $_SESSION['DELAI_CONNEXION'] );
  $_SESSION['DEUXIEME_PASSAGE'] = TRUE;
}
elseif(isset($_SESSION['DEUXIEME_PASSAGE']))
{
  $tab_accueil['user'] .= '<p class="i"><TG> Encore là <b>'.html($_SESSION['USER_PRENOM']).'</b>&nbsp;? Vous avez raison, faîtes comme chez vous&nbsp;!';
  unset($_SESSION['DEUXIEME_PASSAGE']);
  $_SESSION['PASSAGES_SUIVANTS'] = TRUE;
}
elseif(isset($_SESSION['PASSAGES_SUIVANTS']))
{
  $tab_accueil['user'] .= '<p class="i"><TG> Toujours là <b>'.html($_SESSION['USER_PRENOM']).'</b>&nbsp;? Pas de souci, restez le temps que vous voulez&nbsp;!';
}
// infos profil
$tab_accueil['user'] .= '<p>Vous êtes dans l\'environnement <b>'.$_SESSION['USER_PROFIL_NOM_LONG'].'</b>.</p>';
// infos selon profil
if($_SESSION['USER_PROFIL_TYPE']=='parent')
{
  if($_SESSION['NB_ENFANTS'])
  {
    $tab_nom_enfants = array();
    foreach($_SESSION['OPT_PARENT_ENFANTS'] as $DB_ROW)
    {
      $tab_nom_enfants[] =html($DB_ROW['texte']);
    }
    $tab_accueil['user'] .= '<p>Élève(s) associé(s) à votre compte&nbsp;: <b>'.implode('</b> ; <b>',$tab_nom_enfants).'</b></p>';
  }
  else
  {
    $tab_accueil['user'] .= '<p class="danger">'.$_SESSION['OPT_PARENT_ENFANTS'].'</p>';
  }
}
elseif($_SESSION['USER_PROFIL_TYPE']=='administrateur')
{
  // Indication connexions SSO existantes si non choisies
  $uai_departement = (int)substr($_SESSION['WEBMESTRE_UAI'],0,3);
  if( $uai_departement && ($_SESSION['CONNEXION_MODE']=='normal') )
  {
    require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');
    $tab_memo_ent_possible = array();
    foreach($tab_connexion_mode as $connexion_mode => $mode_texte)
    {
      foreach($tab_connexion_info[$connexion_mode] as $connexion_ref => $tab_infos)
      {
        list($departement,$connexion_nom) = explode('|',$connexion_ref);
        if( ($uai_departement==$departement) && $tab_infos['etat'] )
        {
          $tab_memo_ent_possible[$connexion_ref] = $connexion_nom;
        }
      }
    }
    $nb_ent_possibles = count($tab_memo_ent_possible);
    if($nb_ent_possibles)
    {
      $mot_ent = ($nb_ent_possibles>1) ? 'des ENT' : 'de l\'ENT' ;
      $texte_ent_possibles = 'Sur votre département <em>SACoche</em> peut utiliser l\'authentification '.$mot_ent.' <b>'.implode(' - ',$tab_memo_ent_possible).'</b> &rarr; <a href="./index.php?page=administrateur_etabl_connexion">Gestion du mode d\'identification.</a>';
      if( IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE && is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT) )
      {
        require(CHEMIN_FICHIER_WS_SESAMATH_ENT);
        foreach($tab_memo_ent_possible as $connexion_ref => $connexion_nom)
        {
          list($departement,$connexion_nom) = explode('|',$connexion_ref);
          if(isset($tab_connecteurs_convention[$connexion_ref]))
          {
            $texte_ent_possibles .= '<br /><a class="lien_ext" href="'.SERVEUR_CARTE_ENT.'">'.$tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['texte'].'</a>';
          }
        }
      }
      $tab_accueil['user'] .= '<p class="astuce">'.$texte_ent_possibles.'</p>';
    }
  }
  if(!$tab_accueil['alert'])
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['user'] .= '<p><span class="manuel"><a class="lien_ext" href="'.SERVEUR_GUIDE_ADMIN.'">Guide de démarrage d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
  if( $info_rentree || test_periode_rentree() )
  {
    // volontairement pas en pop-up mais dans un nouvel onglet
    $tab_accueil['user'] .= '<p><span class="manuel"><a class="lien_ext" href="'.SERVEUR_GUIDE_RENTREE.'">Guide de changement d\'année d\'un administrateur de <em>SACoche</em>.</a></span></p>';
  }
}
// infos adresse de connexion
if(in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','partenaire')))
{
  $tab_accueil['user'] .= '<div>Pour vous connecter à cet espace, utilisez l\'adresse <b>'.URL_DIR_SACOCHE.'?'.$_SESSION['USER_PROFIL_TYPE'].'</b></div>';
}
else
{
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    $tab_accueil['user'] .= '<div>Adresse à utiliser pour une sélection automatique de l\'établissement&nbsp;: <b>'.URL_DIR_SACOCHE.'?base='.$_SESSION['BASE'].'</b></div>';
  }
  if($_SESSION['CONNEXION_MODE']!='normal')
  {
    $get_base = ($_SESSION['BASE']) ? '&amp;base='.$_SESSION['BASE'] : '' ;
    $tab_accueil['user'] .= '<div>Adresse à utiliser pour une connexion automatique avec l\'authentification externe&nbsp;: <b>'.URL_DIR_SACOCHE.'?sso'.$get_base.'</b></div>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Panneau d'informations ou message écolo
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(!in_array($_SESSION['USER_PROFIL_TYPE'],array('webmestre','partenaire')))
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_messages_user_destinataire($_SESSION['USER_ID']);
  if(!empty($DB_TAB))
  {
    // En attendant un éventuel textarea enrichi pour la saisie des messages (mais est-ce que ça ne risquerait pas de faire une page d'accueil folklorique ?), une petite fonction pour fabriquer des liens...
    // Format attendu : [desciptif|adresse|target]
    function make_lien($texte)
    {
      $masque_recherche = '#\[([^\|]+)\|([^\|]+)\|([^\|]*)\]#' ;
      $masque_remplacement = '<a href="$2" target="$3">$1</a>';
      return str_replace( array('target="_blank"','target=""') , array('class="lien_ext"','') , preg_replace( $masque_recherche , $masque_remplacement , $texte ) );
    }
    foreach($DB_TAB as $key => $DB_ROW)
    {
      $findme = ','.$_SESSION['USER_ID'].',';
      $tab_accueil['messages'][$DB_ROW['message_id']] = array(
        'titre'   => 'Communication ('.html($DB_ROW['user_prenom']{0}.'. '.$DB_ROW['user_nom']).')',
        'message' => make_lien(nl2br(html($DB_ROW['message_contenu']))),
        'visible' => (strpos($DB_ROW['message_dests_cache'],$findme)===FALSE),
      );
    }
  }
  if( (!count($tab_accueil['messages'])) && ($_SESSION['USER_PROFIL_TYPE']!='administrateur') )
  {
    $tab_accueil['ecolo'] = '<p class="b"><TG> Afin de préserver l\'environnement, n\'imprimer qu\'en cas de nécessité !</p><div>Enregistrer la version numérique d\'un document (grille, relevé, bilan) suffit pour le consulter, l\'archiver, le partager, &hellip;</div>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Info Demandes d'évaluations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='professeur')
{
  $nb_demandes = DB_STRUCTURE_PROFESSEUR::DB_compter_demandes_eleves_en_attente($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  if($nb_demandes)
  {
    $s = ($nb_demandes>1) ? 's' : '' ;
    $tab_accueil['demandes'] = '<p class="b i"><TG> Demandes d\'évaluations</p><p>Vous avez <a href="./index.php?page=evaluation_demande_professeur"><span class="b">'.$nb_demandes.' demande'.$s.' d\'évaluation'.$s.' d\'élève'.$s.'</span></a> en attente.</p>';
  }
}
elseif($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $nb_demandes = DB_STRUCTURE_ELEVE::DB_compter_reponses_professeur_en_attente($_SESSION['USER_ID']);
  if($nb_demandes)
  {
    $s = ($nb_demandes>1) ? 's' : '' ;
    $tab_accueil['demandes'] = '<p class="b i"><TG> Demandes d\'évaluations</p><p>Vous avez <a href="./index.php?page=evaluation_demande_eleve"><span class="b">'.$nb_demandes.' demande'.$s.' d\'évaluation'.$s.'</span></a> en cours de préparation.</p>';
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Astuce du jour
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*
$nombre_indices = 10;
$coef_distorsion = 2;
$tab_indices = array();
for( $i=0 ; $i<100 ; $i++ )
{
  $i_alea = mt_rand(0,99) / 100; // nombre aléatoire entre 0,00 et 0,99
  $i_dist = pow($i_alea,$coef_distorsion) ; // distorsion pour accentuer le nombre de résultats proches de 0
  $indice = (int)floor($nombre_indices*$i_dist);
  $tab_indices[] = (int)$indice;
}
$tab_trie = array_count_values($tab_indices);
ksort($tab_trie);
var_dump( $tab_trie );
*/
require(CHEMIN_DOSSIER_INCLUDE.'tableau_astuces.php'); // Charge $tab_astuces[$profil][]
$astuce_nombre = (isset($tab_astuces[$_SESSION['USER_PROFIL_TYPE']])) ? count($tab_astuces[$_SESSION['USER_PROFIL_TYPE']]) : 0 ;
if($astuce_nombre)
{
  $coef_distorsion = 2;
  $i_alea = mt_rand(0,99) / 100; // nombre aléatoire entre 0,00 et 0,99
  $i_dist = pow($i_alea,$coef_distorsion) ; // distorsion pour accentuer le nombre de résultats proches de 0
  $indice = (int)floor($astuce_nombre*$i_dist);
  $tab_accueil['help'] .= '<p class="b i"><TG> Le saviez-vous ?</p>'.$tab_astuces[$_SESSION['USER_PROFIL_TYPE']][$indice];
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_msg_rubrique_masquee = array( 'user'=>'Informations d\'accueil' , 'demandes'=>'Demandes d\'évaluations' , 'help'=>'Astuce du jour' , 'ecolo'=>'Protégeons l\'environnement' );

foreach($tab_accueil as $type => $contenu)
{
  if( is_string($contenu) && ($contenu!='') )
  {
    if(isset($tab_msg_rubrique_masquee[$type]))
    {
      $class_moins = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)!==FALSE) ? '' : ' hide' ;
      $class_plus  = (strpos($_SESSION['USER_PARAM_ACCUEIL'],$type)===FALSE) ? '' : ' hide' ;
      $toggle_moins = '<a href="#toggle_accueil" class="to_'.$type.'"><img src="./_img/toggle_moins.gif" alt="" title="Masquer" /></a>';
      $toggle_plus  = '<div id="'.$type.'_plus" class="rien64'.$class_plus.'"><a href="#toggle_accueil" class="to_'.$type.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir" /> '.$tab_msg_rubrique_masquee[$type].'</a></div>';
    }
    else
    {
      $class_moins = $class_plus = $toggle_moins = $toggle_plus = '' ;
    }
    echo $toggle_plus.'<div id="'.$type.'_moins" class="p '.$type.'64'.$class_moins.'">'.str_replace('<TG>',$toggle_moins,$contenu).'</div>'.NL.'<hr />'.NL;
  }
  elseif( is_array($contenu) && count($contenu) ) // Seul 'messages' actuellement
  {
    foreach($contenu as $message_id => $tab_donnees_rubrique)
    {
      $class_moins = ( $tab_donnees_rubrique['visible']) ? '' : ' hide' ;
      $class_plus  = (!$tab_donnees_rubrique['visible']) ? '' : ' hide' ;
      $toggle_moins = '<a href="#toggle_accueil" class="to_'.$type.$message_id.'"><img src="./_img/toggle_moins.gif" alt="" title="Masquer" /></a>';
      $toggle_plus  = '<div id="'.$type.$message_id.'_plus" class="rien64'.$class_plus.'"><span class="fluo"><a href="#toggle_accueil" class="to_'.$type.$message_id.'"><img src="./_img/toggle_plus.gif" alt="" title="Voir" /> '.$tab_donnees_rubrique['titre'].'</a></span></div>';
      echo $toggle_plus.'<div id="'.$type.$message_id.'_moins" class="p '.$type.'64'.$class_moins.'">'.'<p><span class="b fluo">'.$toggle_moins.' '.$tab_donnees_rubrique['titre'].'</span></p>'.'<p>'.$tab_donnees_rubrique['message'].'</p>'.'</div>'.NL.'<hr />'.NL;
    }
  }
}

// Affichage communication si convention signée par un partenaire ENT
if(isset($_SESSION['CONVENTION_PARTENAIRE_ENT_COMMUNICATION']))
{
  echo $_SESSION['CONVENTION_PARTENAIRE_ENT_COMMUNICATION'];
}
?>