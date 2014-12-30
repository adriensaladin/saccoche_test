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
 
// Extension de classe qui étend FPDI

// Ces méthodes ne concernent que la mise en page des fiches brevet

class PDF_fiche_brevet extends FPDI
{

  public function information( $tab_pdf_coords , $type , $contenu )
  {
    $size = ($type!='Session') ? 9  : 13 ;
    $bold = (!in_array($type,array('Session','Avertissement'))) ? '' : 'B' ;
    list( $posx , $posy , $largeur , $hauteur ) = $tab_pdf_coords;
    $this->SetXY( $posx , $posy );
    $this->SetFont( 'Arial' , $bold , $size );
    if(!in_array($type,array('Établissement','Avertissement')))
    {
      $this->CellFit( $largeur , $hauteur , To::pdf($contenu) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE  /*fond*/ );
    }
    else
    {
      $this->Rect(23,54,37,6,'F');
      if($type=='Établissement')
      {
        foreach($contenu as $ligne)
        {
          $this->CellFit( $largeur , $hauteur , To::pdf($ligne) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        }
      }
      elseif($type=='Avertissement')
      {
        $this->choisir_couleur_texte('rougevif');
        $this->CellFit( $largeur , 6 , To::pdf('Exemplaire archivé.')        , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $this->CellFit( $largeur , 6 , To::pdf('Copie pour information.')    , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $this->CellFit( $largeur , 6 , To::pdf('Seul l\'original fait foi.') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*fond*/ );
        $this->choisir_couleur_texte('noir');
      }
    }
  }

  public function precision( $tab_pdf_coords , $contenu )
  {
    list( $posx , $posy , $largeur , $hauteur ) = $tab_pdf_coords;
    $this->SetXY( $posx , $posy );
    $this->SetFont( 'Arial' , '' , 8 );
    $this->SetFillColor(255,255,255);
    foreach($contenu as $ligne)
    {
      $this->CellFit( $largeur , $hauteur , To::pdf($ligne) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , TRUE  /*fond*/ );
    }
  }

  public function note( $tab_pdf_coords , $type , $contenu )
  {
    $size = ($type=='total')  ? 13 : 11 ;
    $bold = ($type=='classe') ? '' : 'B' ;
    list( $posx , $posy , $largeur , $hauteur ) = $tab_pdf_coords;
    $this->SetXY( $posx , $posy );
    $this->SetFont( 'Arial' , $bold , $size );
    $this->CellFit( $largeur , $hauteur , To::pdf($contenu) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*fond*/ );
  }

  public function rectangle($tab_pdf_coords)
  {
    list( $posx , $posy , $largeur , $hauteur ) = $tab_pdf_coords;
    $this->SetFillColor(0,0,0);
    $this->Rect( $posx , $posy , $largeur , $hauteur , 'F' );
  }

  public function appreciation( $tab_pdf_coords , $contenu )
  {
    list( $posx , $posy , $largeur , $hauteur ) = $tab_pdf_coords;
    $this->SetXY( $posx , $posy );
    $this->afficher_appreciation( $largeur , $hauteur , 10 /*taille_police*/ , 5 /*taille_interligne*/ , $contenu );
  }

  public function ligne_tag($tag_date_heure_initiales)
  {
    $taille_police = 5 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont( 'Arial' , '' , $taille_police );
    $this->SetXY( $this->marge_gauche-1 , $this->marge_haut+8.5 );
    $this->Cell( $this->page_largeur_moins_marges , $ligne_hauteur , To::pdf($tag_date_heure_initiales) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*fond*/ );
  }

}
?>