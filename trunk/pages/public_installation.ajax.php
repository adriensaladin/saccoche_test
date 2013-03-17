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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$step = (isset($_POST['f_step'])) ? Clean::entier($_POST['f_step']) : '';
$affichage = '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 1 - Création de dossiers supplémentaires et de leurs droits
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==1 )
{
  $poursuivre = TRUE;
  // Création des deux dossiers principaux, et vérification de leur accès en écriture
  $tab_dossier = array(
    CHEMIN_DOSSIER_PRIVATE,
    CHEMIN_DOSSIER_TMP
  );
  foreach($tab_dossier as $dossier)
  {
    $poursuivre = $poursuivre && FileSystem::creer_dossier($dossier,$affichage);
  }
  // Création des sous-dossiers, et vérification de leur accès en éciture
  if($poursuivre)
  {
    $tab_dossier = array(
      CHEMIN_DOSSIER_CONFIG,
      CHEMIN_DOSSIER_LOG,
      CHEMIN_DOSSIER_MYSQL,
      CHEMIN_DOSSIER_BADGE,
      CHEMIN_DOSSIER_COOKIE,
      CHEMIN_DOSSIER_DEVOIR,
      CHEMIN_DOSSIER_DUMP,
      CHEMIN_DOSSIER_EXPORT,
      CHEMIN_DOSSIER_IMPORT,
      CHEMIN_DOSSIER_LOGINPASS,
      CHEMIN_DOSSIER_LOGO,
      CHEMIN_DOSSIER_OFFICIEL,
      CHEMIN_DOSSIER_RSS
    );
    foreach($tab_dossier as $dossier)
    {
      $poursuivre = $poursuivre && FileSystem::creer_dossier($dossier,$affichage);
    }
  }
  // Affichage du résultat des opérations
  echo $affichage;
  echo ($poursuivre) ? '<p><span class="tab"><a href="#" class="step2">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step1">Reprendre l\'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 2 - Remplissage des dossiers avec le contenu approprié
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==2 )
{
  // Création des fichiers index.htm
  $poursuivre1 = TRUE;
  $tab_dossier = array('badge','cookie','devoir','dump-base','export','import','login-mdp','logo','officiel','rss');
  foreach($tab_dossier as $dossier)
  {
    $poursuivre1 = $poursuivre1 && FileSystem::ecrire_fichier_index( CHEMIN_DOSSIER_TMP.$dossier , FALSE /*obligatoire*/ ) ;
  }
  if($poursuivre1)
  {
    $affichage .= '<label class="valide">Fichiers &laquo;&nbsp;<b>index.htm</b>&nbsp;&raquo; créés dans chaque sous-dossier de &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_DOSSIER_TMP).'</b>&nbsp;&raquo;.</label><br />'."\r\n";
  }
  else
  {
    $affichage .= '<label class="erreur">Échec lors de la création d\'un ou plusieurs fichiers &laquo;&nbsp;<b>index.htm</b>&nbsp;&raquo; dans chaque dossier précédent.</label><br />'."\r\n";
  }
  // Création du fichier .htaccess
  $poursuivre2 = FileSystem::ecrire_fichier_si_possible( CHEMIN_DOSSIER_PRIVATE.'.htaccess' , 'deny from all'."\r\n" );
  if($poursuivre2)
  {
    $affichage .= '<label class="valide">Fichier &laquo;&nbsp;<b>.htaccess</b>&nbsp;&raquo; créé dans le dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_DOSSIER_PRIVATE).'</b>&nbsp;&raquo;.</label>'."\r\n";
  }
  else
  {
    $affichage .= '<label class="erreur">Échec lors de la création du fichier &laquo;&nbsp;<b>.htaccess</b>&nbsp;&raquo; dans le dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_DOSSIER_PRIVATE).'</b>&nbsp;&raquo;.</label>.'."\r\n";
  }
  // Affichage du résultat des opérations
  echo $affichage;
  echo ($poursuivre1 && $poursuivre2) ? '<p><span class="tab"><a href="#" class="step3">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step2">Reprendre l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 3 - Choix du type d'installation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==3 )
{
  if( defined('HEBERGEUR_INSTALLATION') && defined('HEBERGEUR_DENOMINATION') && defined('HEBERGEUR_UAI') && defined('HEBERGEUR_ADRESSE_SITE') && defined('HEBERGEUR_LOGO') && defined('CNIL_NUMERO') && defined('CNIL_NUMERO') && defined('CNIL_DATE_ENGAGEMENT') && defined('CNIL_DATE_RECEPISSE') && defined('WEBMESTRE_PRENOM') && defined('WEBMESTRE_COURRIEL') && defined('WEBMESTRE_PASSWORD_MD5') )
  {
    $affichage .= '<p><label class="valide">Les informations concernant le type d\'installation, l\'hébergement et le webmestre sont déjà renseignées.</label></p>'."\r\n";
    $affichage .= '<p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  }
  else
  {
    $affichage .= '<p><label class="astuce">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).'</b>&nbsp;&raquo; n\'existant pas (cas d\'une première installation), ou étant corrompu, vous devez renseigner les étapes 4 et 5.</label></p>'."\r\n";
    $affichage .= '<h2>Type d\'installation</h2>'."\r\n";
    $affichage .= '<p class="astuce">Le type d\'installation, déterminant, n\'est pas modifiable ultérieurement : sélectionnez ce qui vous correspond vraiment !</p>'."\r\n";
    $affichage .= '<ul class="puce"><li><a href="#" class="step4" id="mono-structure">Installation d\'un unique établissement sur ce serveur, nécessitant une seule base de données.</a></li></ul>'."\r\n";
    $affichage .= '<div class="danger">La base MySQL à utiliser doit déjà exister (la créer maintenant si nécessaire, typiquement via "phpMyAdmin").</div>'."\r\n";
    $affichage .= '<p>&nbsp;</p>'."\r\n";
    $affichage .= '<ul class="puce"><li><a href="#" class="step4" id="multi-structures">Gestion d\'établissements multiples (par un rectorat...) avec autant de bases de données associées.</a></li></ul>'."\r\n";
    $affichage .= '<div class="danger">Il faut disposer d\'un compte MySQL avec des droits d\'administration de bases et d\'utilisateurs (création, suppression).</div>'."\r\n";
    $affichage .= '<p><span class="tab"><label id="ajax_msg">&nbsp;</label></span></p>'."\r\n";
  }
  echo $affichage;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 4 - Informations concernant l'hébergement et le webmestre
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==4 )
{
  // récupérer et tester le paramètre
  $installation = (isset($_POST['f_installation'])) ? Clean::texte($_POST['f_installation']) : '';
  if( in_array($installation,array('mono-structure','multi-structures')) )
  {
    $exemple_denomination = ($installation=='mono-structure') ? 'Collège de Trucville' : 'Rectorat du paradis' ;
    $exemple_adresse_web  = ($installation=='mono-structure') ? 'http://www.college-trucville.com' : 'http://www.ac-paradis.fr' ;
    $uai_div_hide_avant   = ($installation=='mono-structure') ? '' : '<div class="hide">' ;
    $uai_div_hide_apres   = ($installation=='mono-structure') ? '' : '</div>' ;
    $affichage .= '<fieldset>'."\r\n";
    $affichage .= '<h2>Caractéristiques de l\'hébergement</h2>'."\r\n";
    $affichage .= '<label class="tab" for="f_installation">Installation <img alt="" src="./_img/bulle_aide.png" title="Valeur déjà renseignée." /> :</label><input id="f_installation" name="f_installation" size="18" type="text" value="'.$installation.'" readonly /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_denomination">Dénomination <img alt="" src="./_img/bulle_aide.png" title="Exemple : '.$exemple_denomination.'" /> :</label><input id="f_denomination" name="f_denomination" size="55" type="text" value="" /><br />'."\r\n";
    $affichage .= $uai_div_hide_avant.'<label class="tab" for="f_uai">n° UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" title="Ce champ est facultatif." /> :</label><input id="f_uai" name="f_uai" size="8" type="text" value="" /><br />'.$uai_div_hide_apres."\r\n";
    $affichage .= '<label class="tab" for="f_adresse_site">Adresse web <img alt="" src="./_img/bulle_aide.png" title="Exemple : '.$exemple_adresse_web.'<br />Ce champ est facultatif." /> :</label><input id="f_adresse_site" name="f_adresse_site" size="60" type="text" value="" /><br />'."\r\n";
    $affichage .= '<h2>Coordonnées du webmestre</h2>'."\r\n";
    $affichage .= '<label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" size="20" type="text" value="" /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" size="20" type="text" value="" /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" size="60" type="text" value="" /><br />'."\r\n";
    $affichage .= '<h2>Mot de passe du webmestre</h2>'."\r\n";
    $affichage .= '<div class="astuce">Ce mot de passe doit être complexe pour offrir un niveau de sécurité suffisant !</div>'."\r\n";
    $affichage .= '<label class="tab" for="f_password1"><img alt="" src="./_img/bulle_aide.png" title="La robustesse du mot de passe indiqué dans ce champ est estimée ci-dessous." /> Saisie 1/2 :</label><input id="f_password1" name="f_password1" size="20" type="password" value="" /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_password2">Saisie 2/2 :</label><input id="f_password2" name="f_password2" size="20" type="password" value="" />'."\r\n";
    $affichage .= '<p><span class="tab"></span><input id="f_step" name="f_step" type="hidden" value="41" /><button id="f_submit" type="submit" class="valider">Valider.</button><label id="ajax_msg">&nbsp;</label></p>'."\r\n";
    $affichage .= '<hr />'."\r\n";
    $affichage .= '<p><span class="astuce">Un mot de passe est considéré comme robuste s\'il comporte de nombreux caractères, mélangeant des lettres minuscules et majuscules, des chiffres et d\'autres symboles.</span></p>'."\r\n";
    $affichage .= '<div id="robustesse" style="border:1px solid blue;margin:auto 10%;text-align:center;font-style:italic;background-color:#F99">indicateur de robustesse : <span>0</span> / 12</div>'."\r\n";
    $affichage .= '</fieldset>'."\r\n";
    echo $affichage;
    exit();
  }
  else
  {
    exit('Erreur avec les données transmises !');
  }
}

if( $step==41 )
{
  // récupérer et tester les paramètres
  $installation = (isset($_POST['f_installation'])) ? Clean::texte($_POST['f_installation']) : '';
  $denomination = (isset($_POST['f_denomination'])) ? Clean::texte($_POST['f_denomination']) : '';
  $uai          = (isset($_POST['f_uai']))          ? Clean::uai($_POST['f_uai'])            : '';
  $adresse_site = (isset($_POST['f_adresse_site'])) ? Clean::url($_POST['f_adresse_site'])   : '';
  $nom          = (isset($_POST['f_nom']))          ? Clean::nom($_POST['f_nom'])            : '';
  $prenom       = (isset($_POST['f_prenom']))       ? Clean::prenom($_POST['f_prenom'])      : '';
  $courriel     = (isset($_POST['f_courriel']))     ? Clean::courriel($_POST['f_courriel'])  : '';
  $password     = (isset($_POST['f_password1']))    ? Clean::password($_POST['f_password1']) : '';
  if( in_array($installation,array('mono-structure','multi-structures')) && $denomination && $nom && $prenom && $courriel && $password )
  {
    // On ne vérifie pas le domaine du serveur mail car ce peut être une installation sur un serveur local non ouvert sur l'extérieur, ou dont le proxy n'a pas encore été configuré.
    /*
    $mail_domaine = ester_domaine_courriel_valide($courriel);
    if($mail_domaine!==TRUE)
    {
      exit('Erreur avec le domaine '.$mail_domaine.' !');
    }
    */
    // Il faut tout transmettre car à ce stade le fichier n'existe pas.
    FileSystem::fabriquer_fichier_hebergeur_info( array('HEBERGEUR_INSTALLATION'=>$installation,'HEBERGEUR_DENOMINATION'=>$denomination,'HEBERGEUR_UAI'=>$uai,'HEBERGEUR_ADRESSE_SITE'=>$adresse_site,'HEBERGEUR_LOGO'=>'','CNIL_NUMERO'=>'non renseignée','CNIL_DATE_ENGAGEMENT'=>'','CNIL_DATE_RECEPISSE'=>'','WEBMESTRE_NOM'=>$nom,'WEBMESTRE_PRENOM'=>$prenom,'WEBMESTRE_COURRIEL'=>$courriel,'WEBMESTRE_PASSWORD_MD5'=>crypter_mdp($password),'WEBMESTRE_ERREUR_DATE'=>0,'SERVEUR_PROXY_USED'=>'','SERVEUR_PROXY_NAME'=>'','SERVEUR_PROXY_PORT'=>'','SERVEUR_PROXY_TYPE'=>'','SERVEUR_PROXY_AUTH_USED'=>'','SERVEUR_PROXY_AUTH_METHOD'=>'','SERVEUR_PROXY_AUTH_USER'=>'','SERVEUR_PROXY_AUTH_PASS'=>'','FICHIER_TAILLE_MAX'=>500,'FICHIER_DUREE_CONSERVATION'=>12,'CHEMIN_LOGS_PHPCAS'=>CHEMIN_DOSSIER_TMP) );
    $affichage .= '<p><label class="valide">Les informations concernant le webmestre et l\'hébergement sont maintenant renseignées.</label></p>'."\r\n";
    $affichage .= '<div class="astuce">Vous pourrez les modifier depuis l\'espace du webmestre, en particulier ajouter un logo et un numéro de déclaration à la CNIL.</div>'."\r\n";
    $affichage .= '<p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
    echo $affichage;
    exit();
  }
  else
  {
    exit('Erreur avec les données transmises !');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 5 - Indication des paramètres de connexion MySQL
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==5 )
{
  // A ce niveau, le fichier d'informations sur l'hébergement doit exister.
  if(!defined('HEBERGEUR_INSTALLATION'))
  {
    $affichage .= '<label class="valide">Les données du fichier <b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).'</b> n\'ont pas été correctement chargées.</label>'."\r\n";
    $affichage .= '<p><span class="tab"><a href="#" class="step3">Retour à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  }
  elseif(is_file(CHEMIN_FICHIER_CONFIG_MYSQL))
  {
    $affichage .= '<p><label class="valide">Le fichier <b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_MYSQL).'</b> existe déjà ; modifiez-en manuellement le contenu si les paramètres sont incorrects.</label></p>'."\r\n";
    $affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  }
  else
  {
    // afficher le formulaire pour entrer les paramètres
    $texte_alerte = (HEBERGEUR_INSTALLATION=='multi-structures') ? 'ce compte mysql doit avoir des droits d\'administration de bases et d\'utilisateurs (typiquement un utilisateur "root")' : 'la base à utiliser doit déjà exister (elle ne sera pas créée par SACoche) ; veuillez la créer manuellement maintenant si besoin' ;
    $affichage .= '<p><label class="astuce">Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_MYSQL).'</b>&nbsp;&raquo; n\'existant pas, indiquez ci-dessous vos paramètres de connexion à la base de données.</label></p>'."\r\n";
    $affichage .= '<p class="danger">Comme indiqué précédemment, '.$texte_alerte.'.</p>'."\r\n";
    $affichage .= '<fieldset>'."\r\n";
    $affichage .= '<h2>Paramètres MySQL</h2>'."\r\n";
    $affichage .= '<label class="tab" for="f_host"><img alt="" src="./_img/bulle_aide.png" title="Parfois \'localhost\' sur un serveur que l\'on administre." /> Hôte ou IP :</label><input id="f_host" name="f_host" size="20" type="text" value="" /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_port"><img alt="" src="./_img/bulle_aide.png" title="Valeur 3306 par défaut (dans la quasi totalité des situations)." /> Port :</label><input id="f_port" name="f_port" size="20" type="text" value="3306" /><label class="alerte">Ne changez pas cette valeur, sauf rares exceptions !</label><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_user">Nom d\'utilisateur :</label><input id="f_user" name="f_user" size="20" type="text" value="" /><br />'."\r\n";
    $affichage .= '<label class="tab" for="f_pass">Mot de passe :</label><input id="f_pass" name="f_pass" size="20" type="password" value="" /><br />'."\r\n";
    $affichage .= '<span class="tab"></span><input id="f_name" name="f_name" size="20" type="hidden" value="remplissage bidon" /><input id="f_step" name="f_step" type="hidden" value="51" /><button id="f_submit" type="submit" class="valider">Valider.</button><label id="ajax_msg">&nbsp;</label>'."\r\n";
    $affichage .= '</fieldset>'."\r\n";
  }
  echo $affichage;
  exit();
}

elseif( $step==51 )
{
  // A ce niveau, le fichier d'informations sur l'hébergement doit exister.
  if(!defined('HEBERGEUR_INSTALLATION'))
  {
    exit('Erreur : problème avec le fichier : '.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).' !');
  }
  // Récupérer les paramètres de connexion
  $BD_host = (isset($_POST['f_host'])) ? Clean::texte($_POST['f_host']) : '';
  $BD_port = (isset($_POST['f_port'])) ? Clean::entier($_POST['f_port']) : 0;
  $BD_name = (isset($_POST['f_name'])) ? Clean::texte($_POST['f_name']) : '';
  $BD_user = (isset($_POST['f_user'])) ? Clean::texte($_POST['f_user']) : '';
  $BD_pass = (isset($_POST['f_pass'])) ? Clean::texte($_POST['f_pass']) : '';
  // Tester les paramètres de connexion (sans spécifier de base de données)
  try
  {
    $BD_handler_root = @new PDO( 'mysql:host='.$BD_host.';port='.$BD_port , $BD_user , $BD_pass );
  }
  catch (PDOException $e)
  {
    exit('Erreur : impossible de se connecter à MySQL [ '.html($e->getMessage()).' ] !');
  }
  // Récupérer la version de MySQL
  $BD_result = $BD_handler_root->query('SELECT VERSION()');
  if($BD_result===FALSE)
  {
    exit('Erreur : version de MySQL non détectable [ SELECT VERSION()  &rarr; échec ] !');
  }
  $mysql_version = (float)substr( current( $BD_result->fetch(PDO::FETCH_NUM) ) ,0,3);
  // Vérifier la version de MySQL
  if(version_compare($mysql_version,MYSQL_VERSION_MINI_REQUISE,'<'))
  {
    exit('Erreur : MySQL trop ancien (version utilisée '.$mysql_version.' ; version minimum requise '.MYSQL_VERSION_MINI_REQUISE.') !');
  }
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    // Récupérer les droits du compte
    $BD_result = $BD_handler_root->query('SHOW GRANTS FOR CURRENT_USER()');
    if($BD_result===FALSE)
    {
      exit('Erreur : droits non détectable [ SHOW GRANTS FOR CURRENT_USER() &rarr; échec ] !');
    }
    $mysql_droits = current( $BD_result->fetch(PDO::FETCH_NUM) );
    // Vérifier que ce compte a les droits suffisants
    // Réponses typiques :
    // GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION
    // GRANT USAGE ON *.* TO 'sac_user_...'@'%' IDENTIFIED BY PASSWORD '...'
    if( (strpos($mysql_droits,'ALL PRIVILEGES')==FALSE) && (strpos($mysql_droits,'WITH GRANT OPTION')==FALSE) )
    {
      exit('Erreur : ce compte MySQL n\'a pas les droits suffisants [ SHOW GRANTS FOR CURRENT_USER() &rarr; '.html($mysql_droits).' ] !');
    }
    // Créer la base de données du webmestre, si elle n'existe pas déjà
    $BD_name = 'sacoche_webmestre';
    try
    {
      $BD_handler_dbname = @new PDO( 'mysql:host='.$BD_host.';port='.$BD_port.';dbname='.$BD_name , $BD_user , $BD_pass );
    }
    catch (PDOException $e)
    {
      $BD_result = $BD_handler_root->query('CREATE DATABASE '.$BD_name.' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci');
      if($BD_result===FALSE)
      {
        exit('Erreur : impossible de créer la base "sacoche_webmestre" [ CREATE DATABASE '.$BD_name.' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  &rarr; échec ] !');
      }
    }
    // Créer le fichier de connexion de la base de données du webmestre, installation multi-structures
    FileSystem::fabriquer_fichier_connexion_base(0,$BD_host,$BD_port,$BD_name,$BD_user,$BD_pass);
    $affichage .= '<p><label class="valide">Les paramètres de connexion MySQL, testés avec succès, sont maintenant enregistrés.</label></p>'."\r\n";
    $affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  }
  elseif(HEBERGEUR_INSTALLATION=='mono-structure')
  {
    $tab_tables = array();
    // Récupérer, si l'hébergeur l'accepte, la liste des bases sur lequelles l'utilisateur a les droits
    // On retire tout de même de la liste les bases d'administration de MySQL.
    $BD_result = $BD_handler_root->query('SHOW DATABASES');
    if( ($BD_result!==FALSE) && ($BD_result->rowCount()) )
    {
      $tab_tables = array_diff( $BD_result->fetchAll(PDO::FETCH_COLUMN,0) , array('mysql','information_schema','performance_schema') );
    }
    // afficher le formulaire pour choisir le nom de la base
    $affichage .= '<fieldset>'."\r\n";
    $affichage .= '<p><label class="valide">Les paramètres de connexion MySQL ont été testés avec succès.</label></p>'."\r\n";
    $affichage .= '<h2>Base à utiliser</h2>'."\r\n";
    if(count($tab_tables))
    {
      // Si on a pu lister les bases accessible, on affiche un select
      $options = '<option value=""></option>';
      foreach($tab_tables as $table)
      {
        $options .= '<option value="'.html($table).'">'.html($table).'</option>';
      }
      $affichage .= '<label class="tab" for="f_name">Nom de la base :</label><select id="f_name" name="f_name">'.$options.'</select><br />'."\r\n";
    }
    else
    {
      // Sinon, c'est un input
      $affichage .= '<label class="tab" for="f_name">Nom de la base :</label><input id="f_name" name="f_name" size="20" type="text" value="" /><br />'."\r\n";
    }
    $affichage .= '<span class="tab"></span><input id="f_host" name="f_host" size="20" type="hidden" value="'.html($BD_host).'" /><input id="f_port" name="f_port" size="20" type="hidden" value="'.$BD_port.'" /><input id="f_user" name="f_user" size="20" type="hidden" value="'.html($BD_user).'" /><input id="f_pass" name="f_pass" size="20" type="hidden" value="'.html($BD_pass).'" /><input id="f_step" name="f_step" type="hidden" value="52" /><button id="f_submit" type="submit" class="valider">Valider.</button><label id="ajax_msg">&nbsp;</label>'."\r\n";
    $affichage .= '</fieldset>'."\r\n";
  }
  echo $affichage;
  exit();
}

elseif( $step==52 )
{
  // A ce niveau, le fichier d'informations sur l'hébergement doit exister.
  if(!defined('HEBERGEUR_INSTALLATION'))
  {
    exit('Erreur : problème avec le fichier : '.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).' !');
  }
  if(HEBERGEUR_INSTALLATION!='mono-structure')
  {
    exit('Erreur : cette étape est réservée au choix d\'un unique établissement !');
  }
  // Récupérer les paramètres de connexion
  $BD_host = (isset($_POST['f_host'])) ? Clean::texte($_POST['f_host']) : '';
  $BD_port = (isset($_POST['f_port'])) ? Clean::entier($_POST['f_port']) : 0;
  $BD_name = (isset($_POST['f_name'])) ? Clean::texte($_POST['f_name']) : '';
  $BD_user = (isset($_POST['f_user'])) ? Clean::texte($_POST['f_user']) : '';
  $BD_pass = (isset($_POST['f_pass'])) ? Clean::texte($_POST['f_pass']) : '';
  // Tester les paramètres de connexion (sans spécifier de base de données)
  try
  {
    $BD_handler_root = @new PDO( 'mysql:host='.$BD_host.';port='.$BD_port , $BD_user , $BD_pass );
  }
  catch (PDOException $e)
  {
    exit('Erreur : impossible de se connecter à MySQL [ '.html($e->getMessage()).' ] !');
  }
  // Sélectionner la base de données de la structure
  try
  {
    $BD_handler_dbname = @new PDO( 'mysql:host='.$BD_host.';port='.$BD_port.';dbname='.$BD_name , $BD_user , $BD_pass );
  }
  catch (PDOException $e)
  {
    exit('Erreur : impossible d\'accéder à la base "'.html($BD_name).'" [ '.html($e->getMessage()).' ] !');
  }
  // Créer le fichier de connexion de la base de données du webmestre, installation multi-structures
  FileSystem::fabriquer_fichier_connexion_base(0,$BD_host,$BD_port,$BD_name,$BD_user,$BD_pass);
  $affichage .= '<p><label class="valide">Les paramètres de connexion MySQL sont maintenant enregistrés.</label></p>'."\r\n";
  $affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
  echo $affichage;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape 6 - Installation des tables de la base de données
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $step==6 )
{
  // A ce niveau, le fichier d'informations sur l'hébergement doit exister.
  if(!is_file(CHEMIN_FICHIER_CONFIG_INSTALL))
  {
    exit('Erreur : problème avec le fichier : '.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_INSTALL).' !');
  }
  // A ce niveau, le fichier de connexion à la base de données doit exister.
  if(!is_file(CHEMIN_FICHIER_CONFIG_MYSQL))
  {
    exit('Erreur : problème avec le fichier : '.FileSystem::fin_chemin(CHEMIN_FICHIER_CONFIG_MYSQL).' !');
  }
  // Créer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires, sujets et corrigés de devoirs
  if(HEBERGEUR_INSTALLATION=='mono-structure')
  {
    $tab_sous_dossier = array('badge','cookie','devoir','officiel','rss');
    foreach($tab_sous_dossier as $sous_dossier)
    {
      FileSystem::creer_dossier( CHEMIN_DOSSIER_TMP.$sous_dossier.DS.'0' , $affichage );
      FileSystem::ecrire_fichier_index(CHEMIN_DOSSIER_TMP.$sous_dossier.DS.'0');
    }
  }
  // On cherche d'éventuelles tables existantes de SACoche.
  $DB_TAB = (HEBERGEUR_INSTALLATION=='mono-structure') ? DB_STRUCTURE_COMMUN::DB_recuperer_tables_informations() : DB_WEBMESTRE_PUBLIC::DB_recuperer_tables_informations() ;
  $nb_tables_presentes = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
  if($nb_tables_presentes)
  {
    $s = ($nb_tables_presentes>1) ? 's' : '' ;
    $base_nom = (HEBERGEUR_INSTALLATION=='mono-structure') ? SACOCHE_STRUCTURE_BD_NAME : SACOCHE_WEBMESTRE_BD_NAME ;
    $affichage .= '<p><label class="alerte">'.$nb_tables_presentes.' table'.$s.' de SACoche étant déjà présente'.$s.' dans la base &laquo;&nbsp;<b>'.$base_nom.'</b>&nbsp;&raquo;, les tables n\'ont pas été installées.</label></p>'."\r\n";
    $affichage .= '<p class="astuce">Si besoin, supprimez les tables manuellement, puis <a href="#" class="step6">relancer l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></p>'."\r\n";
    $affichage .= '<hr />'."\r\n";
    $affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
    $affichage .= '<p>Pour se connecter avec le compte webmestre : <a href="'.URL_DIR_SACOCHE.'?webmestre">'.URL_DIR_SACOCHE.'?webmestre</a></p>'."\r\n";
  }
  else
  {
    if(HEBERGEUR_INSTALLATION=='mono-structure')
    {
      DB_STRUCTURE_COMMUN::DB_creer_remplir_tables_structure();
      // Il est arrivé que la fonction DB_modifier_parametres() retourne une erreur disant que la table n'existe pas.
      // Comme si les requêtes de DB_creer_remplir_tables_structure() étaient en cache, et pas encore toutes passées (parcequ'au final, quand on va voir la base, toutes les tables sont bien là).
      // Est-ce que c'est possible au vu du fonctionnement de la classe de connexion ? Et, bien sûr, y a-t-il quelque chose à faire pour éviter ce problème ?
      // En attendant une réponse de SebR, j'ai mis ce sleep(1)... sans trop savoir si cela pouvait aider...
      @sleep(1);
      // Personnaliser certains paramètres de la structure
      $tab_parametres = array();
      $tab_parametres['version_base']               = VERSION_BASE_STRUCTURE;
      $tab_parametres['webmestre_uai']              = HEBERGEUR_UAI;
      $tab_parametres['webmestre_denomination']     = HEBERGEUR_DENOMINATION;
      $tab_parametres['etablissement_denomination'] = HEBERGEUR_DENOMINATION;
      DB_STRUCTURE_COMMUN::DB_modifier_parametres($tab_parametres);
      // Insérer un compte administrateur dans la base de la structure
      $password = fabriquer_mdp();
      $user_id = DB_STRUCTURE_COMMUN::DB_ajouter_utilisateur($user_sconet_id=0,$user_sconet_elenoet=0,$reference='','ADM',WEBMESTRE_NOM,WEBMESTRE_PRENOM,$login='admin',crypter_mdp($password),$classe_id=0,$id_ent='',$id_gepi='');
      // Affichage du retour
      $affichage .= '<p><label class="valide">Les tables de la base de données ont été installées.</label></p>'."\r\n";
      $affichage .= '<span class="astuce">Le premier compte administrateur a été créé avec votre identité :</span>'."\r\n";
      $affichage .= '<ul class="puce">';
      $affichage .= '<li>nom d\'utilisateur " admin "</li>';
      $affichage .= '<li>mot de passe " '.$password.' "</li>';
      $affichage .= '</ul>'."\r\n";
      $affichage .= '<label class="alerte">Notez ces identifiants avant de poursuivre !</label>'."\r\n";
      $affichage .= '<hr />'."\r\n";
      $affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
      $affichage .= '<p>Se connecter avec le compte webmestre : <a href="'.URL_DIR_SACOCHE.'?webmestre">'.URL_DIR_SACOCHE.'?webmestre</a></p>'."\r\n";
      $affichage .= '<p>Se connecter avec le compte administrateur : <a href="'.URL_DIR_SACOCHE.'">'.URL_INSTALL_SACOCHE.'</a></p>'."\r\n";
    }
    elseif(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      DB_WEBMESTRE_PUBLIC::DB_creer_remplir_tables_webmestre();
      $affichage .= '<p><label class="valide">Les tables de la base de données du webmestre ont été installées.</label></p>'."\r\n";
      $affichage .= '<hr />'."\r\n";
      $affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
      $affichage .= '<p>Se connecter avec le compte webmestre pour gérer les structures hébergées : <a href="'.URL_DIR_SACOCHE.'?webmestre">'.URL_DIR_SACOCHE.'?webmestre</a></p>'."\r\n";
    }
  }
  echo $affichage;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'Erreur avec les données transmises !';

?>
