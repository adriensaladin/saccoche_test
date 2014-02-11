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
$TITRE = ''; // Pas de titre pour que le logo s'affiche à la place

// Alerte non déconnexion de l'ENT si deconnexion de SACoche depuis un compte connecté via un ENT
if( (isset($_COOKIE[COOKIE_STRUCTURE])) && (isset($_COOKIE[COOKIE_AUTHMODE])) && ($_COOKIE[COOKIE_AUTHMODE]!='normal') )
{
  echo'<div class="danger">Attention : vous n\'êtes pas déconnecté du service d\'authentification externe, on peut revenir dans <em>SACoche</em> sans s\'identifier !<br />Fermez votre navigateur ou <a href="index.php?page=public_logout_SSO&amp;base='.$_COOKIE[COOKIE_STRUCTURE].'">déconnectez-vous de ce service</a>.</div>'.NL.'<hr />'.NL;
}

// Supprimer le cookie avec le mode d'identification, servant à une reconnexion SSO, devenu inutile puisque déconnecté à présent.
if(isset($_COOKIE[COOKIE_AUTHMODE]))
{
  setcookie( COOKIE_AUTHMODE /*name*/ , '' /*value*/, $_SERVER['REQUEST_TIME']-42000 /*expire*/ , '/' /*path*/ , getServerUrl() /*domain*/ );
}

// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations.
// Un UAI ou un id de base peut être transmis, mais on peut aussi retrouver l'information dans un cookie.
$BASE = 0;
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  // Lecture d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
  $BASE = (isset($_COOKIE[COOKIE_STRUCTURE])) ? Clean::entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ;
  // Test si id d'établissement transmis dans l'URL ; historiquement "id" si connexion normale et "base" si connexion SSO
  $BASE = (isset($_GET['id']))   ? Clean::entier($_GET['id'])   : $BASE ;
  $BASE = (isset($_GET['base'])) ? Clean::entier($_GET['base']) : $BASE ;
  // Test si UAI d'établissement transmis dans l'URL
  $BASE = (isset($_GET['uai'])) ? DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_id_base_for_UAI(Clean::uai($_GET['uai'])) : $BASE ;
}

// Test si affichage d'un formulaire spécial, autres liens de bascule
$partenaire_possible = ( IS_HEBERGEMENT_SESAMATH && (HEBERGEUR_INSTALLATION=='multi-structures') ) ? TRUE : FALSE ;
if(isset($_GET['webmestre']))
{
  $profil = 'webmestre';
  $h1_identification = '<span style="color:#C00">Accès webmestre</span>';
  $liens_autres_profils = '<a class="anti_h2" href="index.php">profils établissement</a>';
  $liens_autres_profils.= ($partenaire_possible) ? '<a class="anti_h2" href="index.php?partenaire">accès partenaire</a>' : '' ;
}
elseif( isset($_GET['partenaire']) && $partenaire_possible )
{
  $profil = 'partenaire';
  $h1_identification = '<span style="color:#C00">Accès partenaire</span>';
  $liens_autres_profils = '<a class="anti_h2" href="index.php">profils établissement</a><a class="anti_h2" href="index.php?webmestre">accès webmestre</a>';
}
else
{
  $profil = 'structure';
  $h1_identification = 'Identification';
  $liens_autres_profils = '<a class="anti_h2" href="index.php?webmestre">accès webmestre</a>';
  $liens_autres_profils.= ($partenaire_possible) ? '<a class="anti_h2" href="index.php?partenaire">accès partenaire</a>' : '' ;
}
?>

<h1 class="identification"><?php echo $h1_identification ?><?php echo $liens_autres_profils ?></h1>
<form id="form_auth" action="#" method="post">
  <fieldset>
  <input id="f_base" name="f_base" type="hidden" value="<?php echo $BASE ?>" />
  <input id="f_profil" name="f_profil" type="hidden" value="<?php echo $profil ?>" />
  <label id="ajax_msg" class="loader">Chargement en cours...</label>
  </fieldset>
</form>
<form id="form_lost" action="#" method="post" class="hide ml">
  <fieldset id="lost_structure" class="hide">
    <div class="astuce">Le mot de passe, crypté, ne peut pas être renvoyé en cas d'oubli.</div>
    <ul class="puce">
      <li class="p">Si vous aviez renseigné une adresse de courriel, alors indiquez-la pour obtenir de nouveaux identifiants :<br /><input id="f_courriel_lost" name="f_courriel" type="text" value="" size="30" maxlength="63" /> <button id="submit_lost" type="button" class="mail_envoyer">Envoyer.</button><label id="ajax_msg_lost">&nbsp;</label></li>
      <li class="p">Sinon, suivre selon votre profil <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__identifiants#toggle_oubli_mdp">la procédure décrite dans la documentation</a></span>.</li>
    </ul>
  </fieldset>
  <div id="lost_webmestre" class="hide">
    <div class="astuce">Le webmestre est la personne qui a installé le logiciel sur ce serveur.</div>
    <p>En cas de perte de ce mot de passe, suivre <span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__identifiants#toggle_oubli_mdp">la procédure décrite dans la documentation</a></span>.</p>
  </div>
  <div id="lost_partenaire" class="hide">
    <p>Oh, sérieusement ?! Alors contactez Sésamath...</p> <!-- Les personnes concernées se comptent sur le doigt de la main et sauront nous trouver si besoin ! -->
  </div>
  <div id="lost_confirmation" class="hide">
    <p><label class="valide">Courriel envoyé à l'adresse indiquée : consultez votre boite aux lettres électronique.</label></p>
  </div>
  <div class="ti"><button id="quit_lost" type="button" class="retourner">Retour au formulaire d'identification.</button></div>
</form>

<hr />

<h1 class="hebergement">Hébergement</h1>
<ul class="puce">
  <li><em>SACoche</em> peut être téléchargé et installé sur différents serveurs.</li>
  <li>Cette installation (<?php echo (HEBERGEUR_INSTALLATION=='mono-structure') ? HEBERGEUR_INSTALLATION : DB_WEBMESTRE_PUBLIC::DB_compter_structure() ; ?>) a été effectuée par : <?php echo (HEBERGEUR_ADRESSE_SITE) ? '<a class="lien_ext" href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.html(HEBERGEUR_DENOMINATION).'</a>' : html(HEBERGEUR_DENOMINATION); ?> (<?php echo Html::mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Attention ! Si vous êtes élève, parent, professeur ou directeur, alors il ne faut pas contacter le webmestre du serveur, mais l\'administrateur de votre établissement qui a créé les comptes utilisateurs.'); ?>).</li>
  <li><a class="lien_ext" href="<?php echo SERVEUR_CNIL ?>">Informations CNIL</a>. Déclaration <?php echo intval(CNIL_NUMERO) ? 'n°'.CNIL_NUMERO : 'non renseignée' ; ?>.</li>
</ul>

<hr />

<h1 class="informations">Informations</h1>
<ul class="puce">
  <li><em>SACoche</em> est un logiciel <span class="b">gratuit</span>, <span class="b">libre</span>, développé avec le soutien de <a class="lien_ext" href="<?php echo SERVEUR_ASSO ?>"><em>Sésamath</em></a>.</li>
  <li class="b">Consulter <a href="<?php echo SERVEUR_PROJET ?>" class="lien_ext">le site officiel de <em>SACoche</em></a> pour tout renseignement.</li>
  <li>Version installée <em><?php echo VERSION_PROG ?></em>.<label id="ajax_version"></label></li>
</ul>
