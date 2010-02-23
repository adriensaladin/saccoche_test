<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Importer identifiant Gepi";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=import_identifiant_Gepi_SACoche">DOC : Import des identifiants de Gepi dans SACoche.</a></span></p>

<form action="">
<ul class="puce">
<li>Importer le fichier <b>base_eleves_gepi.csv</b> issu de Gepi (aide ci-dessus) : <input id="import_gepi_eleves" type="button" value="Parcourir..." /></li>
<li>Importer le fichier <b>base_professeurs_gepi.csv</b> issu de Gepi (aide ci-dessus) : <input id="import_gepi_profs" type="button" value="Parcourir..." /></li>
<li>Prendre et <input id="copy_id_ENT" type="button" value="recopier l'identifiant de l'ENT déjà importé" /> comme identifiant de Gepi pour tous les utilisateurs.</li>
<li>Prendre et <input id="copy_login_SACoche" type="button" value="recopier le login de SACoche" /> comme identifiant de Gepi pour tous les utilisateurs.</li>
<li>Pour un traitement individuel, on peut aussi utiliser la page "<a href="./index.php?dossier=administrateur&amp;fichier=eleve&amp;section=gestion">Gérer les élèves</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=professeur&amp;section=gestion">Gérer les professeurs</a>" ou "<a href="./index.php?dossier=administrateur&amp;fichier=directeur&amp;section=gestion">Gérer les directeurs</a>".</li>
</ul>
</form>

<hr />

<p class="hc"><label id="ajax_msg">&nbsp;</label></p>
<div id="ajax_retour" class="hc"></div>
