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

// Tableau avec la liste des ids de référentiels associables aux rubriques de type "matière" du livret pour les classes de collège

/*
(130, "matiere",  10, "Français", NULL),
(140, "matiere",  20, "Mathématiques", NULL),
(150, "matiere",  30, "Histoire-Géographie / Enseignement moral et civique", NULL),
(160, "matiere",  40, "Langue vivante 1", NULL),
(161, "matiere",  41, "Langue vivante 2", NULL),
(170, "matiere",  50, "Éducation physique et sportive", NULL),
(180, "matiere",  60, "Arts plastiques", NULL),
(190, "matiere",  70, "Éducation musicale", NULL),
(200, "matiere",  80, "Sciences de la Vie et de la Terre", NULL),
(210, "matiere",  90, "Technologie", NULL),
(220, "matiere", 100, "Physique-Chimie", NULL),
(231, "matiere", 111, "Enseignement(s) de complément", "Latin / Langue et culture régionales");
*/

// Clefs issues de la table sacoche_livret_rubrique
(, "matiere",  10, "", NULL),
(, "matiere",  20, "", NULL),
(, "matiere",  30, "", NULL),
(, "matiere",  40, "", NULL),
(, "matiere",  41, "", NULL),
(, "matiere",  50, "", NULL),
(, "matiere",  60, "", NULL),
(, "matiere",  70, "", NULL),
(, "matiere",  80, "", NULL),
(, "matiere",  90, "", NULL),
(, "matiere", 100, "", NULL),
(, "matiere", 111, "");

$tableau_livret_matieres = array(
  // Français
  130 => array(207,6920) ,
  // Mathématiques
  140 => array(613,6930) ,
  // Histoire-Géographie / Enseignement moral et civique
  150 => array(406,414,416,421,437,438,6925,6926) ,
  // Langue vivante 1
  160 => array(315,316,317,318,319,320,321,322,323,324,325,326) ,
  // Langue vivante 2
  161 => array(327,328,329,330,331,332,333,334,335,336,337,338) ,
  // Éducation physique et sportive
  170 => array(1001,6914) ,
  // Arts plastiques
  180 => array(901) ,
  // Éducation musicale
  190 => array(813) ,
  // Sciences de la Vie et de la Terre
  200 => array(629,6946) ,
  // Technologie
  210 => array(708,738) ,
  // Physique-Chimie
  220 => array(623,6936) ,
  // Enseignement(s) de complément", "Latin / Langue et culture régionales
  231 => array(50,51,74,201,202,203,204,230,399,6923,6929) ,
);
?>