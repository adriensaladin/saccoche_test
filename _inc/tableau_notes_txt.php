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

// Tableau avec le nom et les équivalents textes par défaut des symboles colorés utilisés pour la notation
// L'indice principal est le nom du dossier qui contient les images ainsi que la valeur enregistrée en base de données

$tab_notes_info = array
(
  'Albert'      => array( 'nom'=>'Albert'      , 'RR' =>'R'  , 'R' =>'O'  , 'V' =>'V'  , 'VV' =>'VV' ),
  'Alienor'     => array( 'nom'=>'Aliénor'     , 'RR' =>'NA' , 'R' =>'VA' , 'V' =>'A'  , 'VV' =>'E'  ),
  'Aubrac'      => array( 'nom'=>'Aubrac'      , 'RR' =>'J'  , 'R' =>'V'  , 'V' =>'B'  , 'VV' =>'N'  ),
  'Blaye'       => array( 'nom'=>'Blaye'       , 'RR' =>'NA' , 'R' =>'VA' , 'V' =>'AR' , 'VV' =>'A'  ),
  'CitesUnies'  => array( 'nom'=>'Cités Unies' , 'RR' =>'Rou', 'R' =>'Ora', 'V' =>'Ver', 'VV' =>'Dor'),
  'Courbet'     => array( 'nom'=>'Courbet'     , 'RR' =>'NA' , 'R' =>'ECA', 'V' =>'A'  , 'VV' =>'M'  ),
  'Deutsch'     => array( 'nom'=>'Deutsch'     , 'RR' =>'R'  , 'R' =>'O'  , 'V' =>'V'  , 'VV' =>'B'  ),
  'Europe'      => array( 'nom'=>'Europe'      , 'RR' =>'D'  , 'R' =>'C'  , 'V' =>'B'  , 'VV' =>'A'  ),
  'Indonesie'   => array( 'nom'=>'Indonésie'   , 'RR' =>'D'  , 'R' =>'C'  , 'V' =>'B'  , 'VV' =>'A'  ),
  'JeanZay'     => array( 'nom'=>'Jean Zay'    , 'RR' =>'CNA', 'R' =>'CIA', 'V' =>'CVA', 'VV' =>'CA' ),
  'Koeberle'    => array( 'nom'=>'Koeberlé'    , 'RR' =>'NA' , 'R' =>'CA' , 'V' =>'RF' , 'VV' =>'Ex' ),
  'Leon'        => array( 'nom'=>'Léon'        , 'RR' =>'N'  , 'R' =>'R'  , 'V' =>'O'  , 'VV' =>'V'  ),
  'Lomer'       => array( 'nom'=>'Lomer'       , 'RR' =>'RR' , 'R' =>'R'  , 'V' =>'V'  , 'VV' =>'VV' ),
  'LomerBiseau' => array( 'nom'=>'Lomer Biseau', 'RR' =>'RR' , 'R' =>'R'  , 'V' =>'V'  , 'VV' =>'VV' ),
  'Luys'        => array( 'nom'=>'Luys'        , 'RR' =>'R'  , 'R' =>'O'  , 'V' =>'V'  , 'VV' =>'B'  ),
  'Mistral'     => array( 'nom'=>'Mistral'     , 'RR' =>'SA' , 'R' =>'NA' , 'V' =>'EC' , 'VV' =>'A'  ),
  'Reval'       => array( 'nom'=>'Reval'       , 'RR' =>'NA' , 'R' =>'EVA', 'V' =>'AR' , 'VV' =>'A'  ),
  'Salengro'    => array( 'nom'=>'Salengro'    , 'RR' =>'R'  , 'R' =>'O'  , 'V' =>'B'  , 'VV' =>'V'  ),
  'Verne'       => array( 'nom'=>'Verne'       , 'RR' =>'NA' , 'R' =>'ECA', 'V' =>'AR' , 'VV' =>'A'  ),
  'Voltaire'    => array( 'nom'=>'Voltaire'    , 'RR' =>'NA' , 'R' =>'ECA', 'V' =>'A'  , 'VV' =>'M'  ),
);
?>