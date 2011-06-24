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
$VERSION_JS_FILE += 8;

// Lecture d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
$BASE = (isset($_COOKIE[COOKIE_STRUCTURE])) ? clean_entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ;
// Test si id d'établissement transmis dans l'URL
$BASE = (isset($_GET['id'])) ? clean_entier($_GET['id']) : $BASE ;
// Test si affichage du formulaire spécial pour le webmestre
$profil = (isset($_GET['webmestre'])) ? 'webmestre' : 'normal' ;
// Bascule profil webmestre / profils autres
$liens_autres_profils = ($profil=='normal') ? '<a class="anti_h2" href="index.php?webmestre">profil webmestre</a>' : '<a class="anti_h2" href="index.php">profils classiques</a>' ;

// Fichiers temporaires à effacer
// Il y a ausi le dossier './__tmp/cookie/' auquel on ne touche pas, et les sous-dossiers de './__tmp/badge/' traités ailleurs
// On fait en sorte que plusieurs utilisateurs ne lancent pas le nettoyage simultanément (sinon on trouve qqs warning php dans les logs)
$fichier_lock = './__tmp/lock.txt';
if(!file_exists($fichier_lock))
{
	Ecrire_Fichier($fichier_lock,'');
	effacer_fichiers_temporaires('./__tmp/login-mdp' ,     10); // Nettoyer ce dossier des fichiers antérieurs à 10 minutes
	effacer_fichiers_temporaires('./__tmp/export'    ,     60); // Nettoyer ce dossier des fichiers antérieurs à 1 heure
	effacer_fichiers_temporaires('./__tmp/dump-base' ,     60); // Nettoyer ce dossier des fichiers antérieurs à 1 heure
	effacer_fichiers_temporaires('./__tmp/import'    ,  10080); // Nettoyer ce dossier des fichiers antérieurs à 1 semaine
	effacer_fichiers_temporaires('./__tmp/rss'       ,  43800); // Nettoyer ce dossier des fichiers antérieurs à 1 mois
	unlink($fichier_lock);
}

// Alerte si navigateur trop ancien
require_once('./_inc/fonction_css_browser_selector.php');
echo afficher_navigateurs_alertes($hr_avant='<hr />',$chemin_image='./_img',$hr_apres='');

// Alerte non déconnexion de l'ENT si deconnexion de SACoche depuis un compte connecté via un ENT
if($ALERTE_SSO)
{
	echo'<hr />';
	echo'<div class="danger">Attention : vous n\'êtes pas déconnecté de l\'ENT et on peut revenir dans <em>SACoche</em> sans s\'identifier ! Fermez votre navigateur ou <a href="index.php?page=public_logout_SSO&amp;'.$ALERTE_SSO.'">déconnectez-vous de l\'ENT</a>.</div>';
}

?>

<hr />

<h2><img src="./_img/login.gif" alt="Identification" /> <?php echo($profil=='normal')?'Identification':'<span style="color:#C00">Accès webmestre</span>'; ?><?php echo $liens_autres_profils ?></h2>
<form action=""><fieldset>
	<input id="f_base" name="f_base" type="hidden" value="<?php echo $BASE ?>" />
	<input id="f_profil" name="f_profil" type="hidden" value="<?php echo $profil ?>" />
	<label id="ajax_msg" class="loader">Chargement en cours...</label>
</fieldset></form>

<hr />

<h2><img src="./_img/serveur.png" alt="Hébergement" /> Hébergement</h2>
<ul class="puce">
	<li><em>SACoche</em> peut être téléchargé et installé sur différents serveurs.</li>
	<li>Cette installation (<?php echo (HEBERGEUR_INSTALLATION=='mono-structure') ? HEBERGEUR_INSTALLATION : DB_WEBMESTRE_compter_structure() ; ?>) a été effectuée par : <?php echo (HEBERGEUR_ADRESSE_SITE) ? '<a class="lien_ext" href="'.html(HEBERGEUR_ADRESSE_SITE).'">'.html(HEBERGEUR_DENOMINATION).'</a>' : html(HEBERGEUR_DENOMINATION); ?> (<?php echo mailto(WEBMESTRE_COURRIEL,'SACoche','contact','Attention ! Si vous êtes élève, professeur ou directeur, alors il ne faut pas contacter le webmestre du serveur, mais l\'administrateur de votre établissement qui a créé les comptes utilisateurs.'); ?>).</li>
	<li><a class="lien_ext" href="http://sacoche.sesamath.net/index.php?dossier=presentation&amp;fichier=accueil__cnil">Information CNIL</a>. Déclaration <?php echo intval(CNIL_NUMERO) ? 'n°'.CNIL_NUMERO : 'non renseignée' ; ?>.</li>
</ul>

<hr />

<h2><img src="./_img/puce_astuce.png" alt="Informations" /> Informations</h2>
<ul class="puce">
	<li><em>SACoche</em> est un logiciel gratuit, libre, développé avec le soutien de <a class="lien_ext" href="http://www.sesamath.net"><em>Sésamath</em></a>.</li>
	<li class="b">Consulter <a href="<?php echo SERVEUR_PROJET ?>" class="lien_ext">le site officiel de <em>SACoche</em></a> pour tout renseignement.</li>
	<li>Version installée <em><?php echo VERSION_PROG ?></em>.<label id="ajax_version" for="version"></label></li>
</ul>

<script type="text/javascript">
	var VERSION_PROG = "<?php echo VERSION_PROG ?>";
</script>
