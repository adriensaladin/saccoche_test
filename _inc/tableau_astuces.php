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

// Tableau avec la liste des astuces "Le saviez-vous ?" affichées après authentification.
// A priori pas utile pour les webmestres / parents / élèves.

$astuce_bulletin_scolaire                = '<p><em>SACoche</em> permet éditer des <b>bulletins scolaires</b> (avec appréciations, moyennes, coordonnées établissement et parents, signature numérique). <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=releves_bilans__officiel_bulletin_scolaire">Documentation</a></span></p>';
$astuce_panneau_affichage                = '<p>Vous pouvez <b>programmer l\'affichage d\'un message à destination d\'utilisateurs ciblés</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=environnement_generalites__messages_accueil">Documentation</a></span></p>';
$astuce_memorisation_selection_items     = '<p>Vous pouvez <b>mémoriser des regroupements d\'items</b> pour les années suivantes ou des bilans ciblés. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__gestion_regroupements_items">Documentation</a></span></p>';
$astuce_devoir_autoevaluation            = '<p>Vous pouvez permettre aux élèves de <b>s\'autoévaluer sur un devoir</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_autoeval">Documentation</a></span></p>';
$astuce_devoir_joindre_fichiers          = '<p>Vous pouvez joindre ou référencer <b>un sujet et un corrigé</b> à une évaluation. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_fichiers">Documentation</a></span></p>';
$astuce_devoir_partage                   = '<p>Vous pouvez <b>partager une évaluation avec des collègues</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_profs">Documentation</a></span></p>';
$astuce_devoir_ordonner_items            = '<p>Vous pouvez <b>choisir l\'ordre des items d\'une évaluation</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_gestion#toggle_evaluations_ordonner">Documentation</a></span></p>';
$astuce_devoir_saisies_multiples         = '<p>Vous pouvez <b>saisir une note dans plusieurs cellules</b> simultanément. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_professeur__evaluations_saisie_resultats#toggle_saisies_multiples">Documentation</a></span></p>';
$astuce_referentiel_lier_ressources      = '<p>Vous pouvez <b>associer aux items des ressources</b> pour un travail en autonomie des élèves. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__referentiel_lier_ressources">Documentation</a></span></p>';
$astuce_referentiel_uploader_ressources  = '<p>Vous pouvez <b>mettre en ligne des ressources</b> pour ensuite les associer aux items. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__referentiel_uploader_ressources">Documentation</a></span></p>';
$astuce_socle_choisir_langue             = '<p>Vous pouvez <b>indiquer la langue étrangère</b> des élèves pour le socle commun. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=referentiels_socle__socle_choisir_langue">Documentation</a></span></p>';
$astuce_dates_periodes                   = '<p>Vous pouvez <a href="./index.php?page=consultation_groupe_periode"><b>consulter les dates des périodes</b></a> associées aux classes et aux groupes.</p>';
$astuce_date_connexion                   = '<p>Vous pouvez <a href="./index.php?page=consultation_date_connexion"><b>consulter la date de dernière connexion</b></a> des élèves.</p>';
$astuce_faq_b2i                          = '<p><em>SACoche</em> peut être utilisé pour <b>l\'évaluation du B2i</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=faq_documentation__evaluer_b2i">Documentation</a></span></p>';
$astuce_authentification_ent             = '<p>On peut se connecter à <em>SACoche</em> en utilisant <b>l\'authentification de plusieurs ENT</b>. <span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=support_administrateur__gestion_mode_identification">Documentation</a></span></p>';

$tab_astuces = array(
	'administrateur' => array(
		$astuce_bulletin_scolaire,
		$astuce_panneau_affichage,
		$astuce_socle_choisir_langue,
		$astuce_faq_b2i,
		$astuce_authentification_ent
	),
	'directeur' => array(
		$astuce_bulletin_scolaire,
		$astuce_panneau_affichage,
		$astuce_socle_choisir_langue,
		$astuce_memorisation_selection_items,
		$astuce_dates_periodes,
		$astuce_date_connexion,
		$astuce_faq_b2i
	),
	'professeur' => array(
		$astuce_bulletin_scolaire,
		$astuce_panneau_affichage,
		$astuce_devoir_autoevaluation,
		$astuce_devoir_joindre_fichiers,
		$astuce_devoir_saisies_multiples,
		$astuce_memorisation_selection_items,
		$astuce_referentiel_lier_ressources,
		$astuce_referentiel_uploader_ressources,
		$astuce_devoir_partage,
		$astuce_dates_periodes,
		$astuce_date_connexion,
		$astuce_devoir_ordonner_items,
		$astuce_faq_b2i
	)
);

?>