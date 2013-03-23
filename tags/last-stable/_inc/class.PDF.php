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

// Stream handler to read from global variables
// Requis pour PDF_MemImage() MemImage() (http://www.fpdf.org/fr/script/script45.php)

class VariableStream
{
  var $varname;
  var $position;

  function stream_open($path, $mode, $options, &$opened_path)
  {
    $url = parse_url($path);
    $this->varname = $url['host'];
    if(!isset($GLOBALS[$this->varname]))
    {
      trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
      return FALSE;
    }
    $this->position = 0;
    return TRUE;
  }

  function stream_read($count)
  {
    $ret = substr($GLOBALS[$this->varname], $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  function stream_eof()
  {
    return $this->position >= strlen($GLOBALS[$this->varname]);
  }

  function stream_tell()
  {
    return $this->position;
  }

  function stream_seek($offset, $whence)
  {
    if($whence==SEEK_SET)
    {
      $this->position = $offset;
      return TRUE;
    }
    return FALSE;
  }

  function stream_stat()
  {
    return array();
  }
}

// Extension de classe qui étend FPDF

class PDF extends FPDF
{

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour optimiser la gestion de la mémoire et éviter un « Fatal error : Allowed memory size ... »
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Consulter  http://www.fpdf.org/fr/script/script18.php (a l'avantage de ne rien changer au code)
   * Voir aussi http://www.fpdf.org/fr/script/script76.php (a l'inconvénient qu'il faut changer certaines syntaxes, et malgré un a priori favorable des tests ont montré que c'était totalement équivalent à l'autre script)
   * 
   * Attention : la mémoire utilisée par fpdf n'est pas comptabilisée dans memory_get_usage().
   * Un script peut annoncer utiliser 8Mo, et dépasser en réalité 32Mo même pour générer un pdf de 200Ko au final.
  **/

  public function _putpages()
  {
    $nb=$this->page;
    if(!empty($this->AliasNbPages))
    {
      //Replace number of pages
      for($n=1;$n<=$nb;$n++)
      {
        if($this->compress)
          $this->pages[$n]=gzcompress(str_replace($this->AliasNbPages,$nb,gzuncompress($this->pages[$n])));
        else
          $this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
      }
    }
    if($this->DefOrientation=='P')
    {
      $wPt=$this->DefPageSize[0]*$this->k;
      $hPt=$this->DefPageSize[1]*$this->k;
    }
    else
    {
      $wPt=$this->DefPageSize[1]*$this->k;
      $hPt=$this->DefPageSize[0]*$this->k;
    }
    $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
    for($n=1;$n<=$nb;$n++)
    {
      //Page
      $this->_newobj();
      $this->_out('<</Type /Page');
      $this->_out('/Parent 1 0 R');
      if(isset($this->PageSizes[$n]))
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
      $this->_out('/Resources 2 0 R');
      if(isset($this->PageLinks[$n]))
      {
        //Links
        $annots='/Annots [';
        foreach($this->PageLinks[$n] as $pl)
        {
          $rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
          $annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
          if(is_string($pl[4]))
            $annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
          else
          {
            $l=$this->links[$pl[4]];
            $h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
            $annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
          }
        }
        $this->_out($annots.']');
      }
      $this->_out('/Contents '.($this->n+1).' 0 R>>');
      $this->_out('endobj');
      //Page content
      $p=$this->pages[$n];
      $this->_newobj();
      $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
      $this->_putstream($p);
      $this->_out('endobj');
    }
    //Pages root
    $this->offsets[1]=strlen($this->buffer);
    $this->_out('1 0 obj');
    $this->_out('<</Type /Pages');
    $kids='/Kids [';
    for($i=0;$i<$nb;$i++)
      $kids.=(3+2*$i).' 0 R ';
    $this->_out($kids.']');
    $this->_out('/Count '.$nb);
    $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
    $this->_out('>>');
    $this->_out('endobj');
  }

  public function _endpage()
  {
    parent::_endpage();
    if($this->compress)
      $this->pages[$this->page] = gzcompress($this->pages[$this->page]);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour ajuster l'étirement d'un texte dans une cellule en fonction de sa longueur
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script62.php (repris partiellement)
 * Voir aussi http://www.fpdf.org/fr/script/script32.php
**/

  public function FitOn( $largeur_cellule , $texte )
  {
    $this->cMargin_memo = $this->cMargin ;
    if($largeur_cellule<=10)
    {
      $this->cMargin = $largeur_cellule/10 ;
    }
    $largeur_texte = $this->GetStringWidth($texte);
    $largeur_cellule = ($largeur_cellule!=0) ? $largeur_cellule : $this->w - $this->rMargin - $this->x ;
    $ratio = ($largeur_texte) ? ( $largeur_cellule - $this->cMargin*2 ) / $largeur_texte : 1 ;
    $fit = ($ratio < 1);
    if ($fit)
    {
      $horiz_scale = $ratio*100.0;
      $this->_out(sprintf('BT %.2F Tz ET',$horiz_scale));
    }
    return $fit;
  }

  public function FitOff($fit)
  {
    $this->cMargin = $this->cMargin_memo ;
    if ($fit)
    {
      $this->_out('BT 100 Tz ET');
    }
  }

  public function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=FALSE, $link='')
  {
    $fit = $this->FitOn( $w , $txt );
    $align = ($fit) ? 'L' : $align ;
    $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
    $this->FitOff($fit);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour écrire un texte tourné
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script31.php
 * Voir aussi http://www.fpdf.org/fr/script/script2.php
**/

  public function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
  {
    $font_angle += 90+$txt_angle;
    $txt_angle  *= M_PI/180;
    $font_angle *= M_PI/180;
    $txt_dx  = cos($txt_angle);
    $txt_dy  = sin($txt_angle);
    $font_dx = cos($font_angle);
    $font_dy = sin($font_angle);
    $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
  }

/*
  public function TextWithDirection($x, $y, $txt, $direction='R')
  {
    if ($direction=='R')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='L')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='U')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='D')
      $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    else
      $s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
  }
*/

  public function VertCell($width, $height, $txt, $border=0, $ln=0, $fill=FALSE)
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    // Cellule si bordure et/ou couleur
    if( $border || $fill )
    {
      $this->Cell($width , $height , '' , $border , 0 , 'C' , $fill , '');
    }
    // Texte tourné de 90°
    $x = $memo_x + ($width)/2 + 1 ;
    $y = $memo_y + $height - 1 ;
    $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0, $x*$this->k ,($this->h-$y)*$this->k , $this->_escape($txt) );
    if ($this->ColorFlag)
      $s = 'q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
    // Positionnement xy
    switch($ln)
    {
      case 0 : $this->SetXY( $memo_x+$width , $memo_y ); break;
      case 1 : $this->SetXY( $this->lMargin , $memo_y+$height ); break;
      case 2 : $this->SetXY( $memo_x , $memo_y+$height ); break;
    }
  }

  public function VertCellFit($width, $height, $txt, $border=0, $ln=0, $fill=FALSE)
  {
    $fit = $this->FitOn( $height , $txt );
    $this->VertCell($width, $height, $txt, $border, $ln, $fill);
    $this->FitOff($fit);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour savoir le nombre de lignes requises afin d'écrire un texte, et le découper en conséquence.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script49.php (modifié pour renvoyer une nouvelle chaine et pas passer l'ancienne en référence à la fonction)
 * Voir aussi http://www.fpdf.org/fr/script/script16.php (MultiCell permettant de spécifier un paramètre maxline et de renvoyer la partie de la chaîne qui n'a pas été imprimée, mais pas d'adaptation de l'interligne...)
 * Pourquoi pas regarder un jour GetStringWidth() <https://github.com/tbelliard/gepi/blob/master/fpdf/tfpdf.php> et cell_ajustee() <https://github.com/tbelliard/gepi/blob/master/lib/share-pdf.inc.php>
**/

  public function WordWrap($text, $maxwidth)
  {
    $text = trim($text);
    if ($text==='')
      return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;
    foreach ($lines as $line)
    {
      $words = preg_split('/ +/', $line);
      $width = 0;
      foreach ($words as $word)
      {
        $wordwidth = $this->GetStringWidth($word);
        if ($wordwidth > $maxwidth)
        {
          // Word is too long, we cut it
          for($i=0; $i<strlen($word); $i++)
          {
            $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
            if($width + $wordwidth <= $maxwidth)
            {
              $width += $wordwidth;
              $text .= substr($word, $i, 1);
            }
            else
            {
              $width = $wordwidth;
              $text = rtrim($text)."\n".substr($word, $i, 1);
              $count++;
            }
          }
        }
        elseif($width + $wordwidth <= $maxwidth)
        {
          $width += $wordwidth + $space;
          $text .= $word.' ';
        }
        else
        {
          $width = $wordwidth + $space;
          $text = rtrim($text)."\n".$word.' ';
          $count++;
        }
      }
      $text = rtrim($text)."\n";
      $count++;
    }
    $text = rtrim($text);
    return array($count,$text);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour afficher des images chargées en mémoire sans avoir besoin de passer par un fichier temporaire.
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter http://www.fpdf.org/fr/script/script45.php
 * Utilise aussi "stream_wrapper_register('var', 'VariableStream');" dans le constructeur et la classe "VariableStream" située au début de ce fichier
**/

  public function MemImage($data, $x=NULL, $y=NULL, $w=0, $h=0, $type='', $link='')
  {
    // Display the image contained in $data
    $v = 'img'.md5($data);
    $GLOBALS[$v] = $data;
    $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
    unset($GLOBALS[$v]);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Pour tracer un cercle (ou une ellipse)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script6.php
 * Voir aussi http://www.fpdf.org/fr/script/script28.php
 * Voir aussi http://www.fpdf.org/fr/script/script69.php
**/

  public function Circle($x, $y, $r, $style='D')
  {
    $this->Ellipse($x,$y,$r,$r,$style);
  }

  public function Ellipse($x, $y, $rx, $ry, $style='D')
  {
    if($style=='F')
      $op = 'f';
    elseif($style=='FD' || $style=='DF')
      $op = 'B';
    else
      $op = 'S';
    $lx = 4/3*(M_SQRT2-1)*$rx;
    $ly = 4/3*(M_SQRT2-1)*$ry;
    $k = $this->k;
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c', ($x+$rx)*$k, ($h-$y)*$k,       ($x+$rx)*$k, ($h-($y-$ly))*$k, ($x+$lx)*$k, ($h-($y-$ry))*$k,$x*$k, ($h-($y-$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$lx)*$k, ($h-($y-$ry))*$k, ($x-$rx)*$k, ($h-($y-$ly))*$k, ($x-$rx)*$k, ($h-$y)*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',             ($x-$rx)*$k, ($h-($y+$ly))*$k, ($x-$lx)*$k, ($h-($y+$ry))*$k, $x*$k,       ($h-($y+$ry))*$k));
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',          ($x+$lx)*$k, ($h-($y+$ry))*$k, ($x+$rx)*$k, ($h-($y+$ly))*$k, ($x+$rx)*$k, ($h-$y)*$k,             $op));
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Attributs de la classe (équivalents des "variables")
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  // Couleurs de fond
  private $tab_couleur       = array();
  private $tab_choix_couleur = array();
  // Lettres utilisées en remplacement des images Lomer pour du noir et blanc
  private $tab_lettre = array();
  // Valeurs des marges principales pour la mise en page PDF
  private $officiel      = FALSE;
  private $orientation   = '';
  private $couleur       = 'oui';
  private $legende       = 1;
  private $filigrane     = NULL;
  private $page_largeur  = 0;
  private $page_hauteur  = 0;
  private $marge_haut    = 5;
  private $marge_gauche  = 5;
  private $marge_droite  = 5;
  private $marge_bas     = 5;
  private $distance_pied = 0;
  private $page_largeur_moins_marges = 0;
  private $page_hauteur_moins_marges = 0;
  // Conserver les informations de l'élève pour une recopie sur plusieurs pages
  private $eleve_id     = 0;
  private $eleve_nom    = '';
  private $eleve_prenom = '';
  private $doc_titre    = '';
  // Définition de qqs variables supplémentaires
  private $cases_nb              = 0;
  private $cases_largeur         = 0;
  private $cases_hauteur         = 0;
  private $lignes_hauteur        = 0;
  private $reference_largeur     = 0;
  private $intitule_largeur      = 0;
  private $synthese_largeur      = 0;
  private $etiquette_hauteur     = 0;
  private $colonne_bilan_largeur = 0;
  private $colonne_vide_largeur  = 0;
  // Définition de qqs variables supplémentaires
  private $pilier_largeur      = 0;
  private $section_largeur     = 0;
  private $item_largeur        = 0;
  private $pourcentage_largeur = 0;
  // Définition de qqs variables supplémentaires
  private $eleve_largeur     = 0;
  private $taille_police     = 8;
  // Définition de qqs variables supplémentaires
  private $lomer_espace_largeur = 0;
  private $lomer_espace_hauteur = 0;
  private $lomer_image_largeur  = 0;
  private $lomer_image_hauteur  = 0;
  // Définition de qqs variables supplémentaires
  private $coef_conv_pixel_to_mm = 0;
  private $photo_hauteur_maxi    = 0;
  private $cadre_photo_hauteur   = 0;
  // Définition de qqs variables supplémentaires
  private $page_nombre_alias      = '{|}'; // Pas celui de FPDF ($this->AliasNbPages) car géré différemment (plusieurs élèves par fichier) ; court car occupation en largeur prise en compte.
  private $page_numero_first      = 1;
  private $page_nombre_alignement = '';

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Constructeur
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __construct($officiel,$orientation,$marge_gauche=5,$marge_droite=5,$marge_haut=5,$marge_bas=12,$couleur='oui',$legende='oui',$filigrane=NULL)
  {
    // Register var stream protocol => Voir MemImage()
    if (in_array('var', stream_get_wrappers()))
    {
      stream_wrapper_unregister('var');
    }
    stream_wrapper_register('var', 'VariableStream');
    // Appeler le constructeur de la classe mère
    parent::FPDF( $orientation , 'mm' /*unit*/ , 'A4' /*format*/ );
    // On passe à la classe fille
    $this->officiel    = $officiel;
    $this->orientation = $orientation;
    $this->couleur     = $couleur;
    $this->legende     = ($legende=='oui') ? 1 : 0 ;
    $this->filigrane   = $filigrane;
    // Déclaration de la police pour la rendre disponible même si non présente sur le serveur
    $this->AddFont('Arial','' ,'arial.php');
    $this->AddFont('Arial','B','arialbd.php');
    // initialiser les marges principales
    if($orientation=='portrait')
    {
      $this->page_largeur  = 210;
      $this->page_hauteur  = 297;
      $this->marge_haut    = $marge_haut;
      $this->marge_gauche  = $marge_gauche;
      $this->marge_droite  = $marge_droite;
      $this->marge_bas     = ($this->officiel) ? $marge_bas : max(12,$marge_bas) ;
      $this->distance_pied = 9;
    }
    elseif($orientation=='landscape')
    {
      $this->page_largeur  = 297;
      $this->page_hauteur  = 210;
      $this->marge_haut    = $marge_droite;
      $this->marge_gauche  = $marge_haut;
      $this->marge_droite  = ($this->officiel) ? $marge_bas    : max(12,$marge_bas) ;
      $this->marge_bas     = ($this->officiel) ? $marge_gauche : max(10,$marge_gauche) ;
      $this->distance_pied = 7;
    }
    $this->page_largeur_moins_marges = $this->page_largeur - $this->marge_gauche - $this->marge_droite ;
    $this->page_hauteur_moins_marges = $this->page_hauteur - $this->marge_haut   - $this->marge_bas ;
    // Couleurs prédéfinies
    $this->tab_choix_couleur = ($this->couleur=='oui') ? array( 'NA'=>'rouge'      , 'VA'=>'jaune'      , 'A'=>'vert'        , 'v0'=>'invalidé'   , 'v1'=>'validé'     , 'v2'=>'non renseigné' )
                                                       : array( 'NA'=>'gris_fonce' , 'VA'=>'gris_moyen' , 'A'=>'gris_clair'  , 'v0'=>'gris_fonce' , 'v1'=>'gris_clair' , 'v2'=>'blanc'         ) ;
    $this->tab_couleur['blanc']      = array('r'=>255,'v'=>255,'b'=>255);
    $this->tab_couleur['gris_clair'] = array('r'=>230,'v'=>230,'b'=>230);
    $this->tab_couleur['gris_moyen'] = array('r'=>190,'v'=>190,'b'=>190);
    $this->tab_couleur['gris_fonce'] = array('r'=>150,'v'=>150,'b'=>150);
    $this->tab_couleur['noir']       = array('r'=>  0,'v'=>  0,'b'=>  0);
    $this->tab_couleur['rougevif']   = array('r'=>255,'v'=>  0,'b'=>  0);
    // Couleurs des états d'acquisition ; il faut convertir l'hexadécimal en RVB décimal
    $rr = hexdec(substr($_SESSION['BACKGROUND_NA'],1,2));
    $rv = hexdec(substr($_SESSION['BACKGROUND_NA'],3,2));
    $rb = hexdec(substr($_SESSION['BACKGROUND_NA'],5,2));
    $jr = hexdec(substr($_SESSION['BACKGROUND_VA'],1,2));
    $jv = hexdec(substr($_SESSION['BACKGROUND_VA'],3,2));
    $jb = hexdec(substr($_SESSION['BACKGROUND_VA'],5,2));
    $vr = hexdec(substr($_SESSION['BACKGROUND_A'] ,1,2));
    $vv = hexdec(substr($_SESSION['BACKGROUND_A'] ,3,2));
    $vb = hexdec(substr($_SESSION['BACKGROUND_A'] ,5,2));
    $this->tab_couleur['rouge'] = array('r'=>$rr,'v'=>$rv,'b'=>$rb);
    $this->tab_couleur['jaune'] = array('r'=>$jr,'v'=>$jv,'b'=>$jb);
    $this->tab_couleur['vert']  = array('r'=>$vr,'v'=>$vv,'b'=>$vb);
    // Couleurs des états de validation ; il faut convertir l'hexadécimal en RVB décimal
    $rr = hexdec(substr($_SESSION['BACKGROUND_V0'],1,2));
    $rv = hexdec(substr($_SESSION['BACKGROUND_V0'],3,2));
    $rb = hexdec(substr($_SESSION['BACKGROUND_V0'],5,2));
    $vr = hexdec(substr($_SESSION['BACKGROUND_V1'],1,2));
    $vv = hexdec(substr($_SESSION['BACKGROUND_V1'],3,2));
    $vb = hexdec(substr($_SESSION['BACKGROUND_V1'],5,2));
    $br = hexdec(substr($_SESSION['BACKGROUND_V2'],1,2));
    $bv = hexdec(substr($_SESSION['BACKGROUND_V2'],3,2));
    $bb = hexdec(substr($_SESSION['BACKGROUND_V2'],5,2));
    $this->tab_couleur['invalidé']      = array('r'=>$rr,'v'=>$rv,'b'=>$rb);
    $this->tab_couleur['validé']        = array('r'=>$vr,'v'=>$vv,'b'=>$vb);
    $this->tab_couleur['non renseigné'] = array('r'=>$br,'v'=>$bv,'b'=>$bb);
    // Lettres utilisées en remplacement des images Lomer pour du noir et blanc
    $this->tab_lettre['RR']  = $_SESSION['NOTE_TEXTE']['RR'];
    $this->tab_lettre['R']   = $_SESSION['NOTE_TEXTE']['R'];
    $this->tab_lettre['V']   = $_SESSION['NOTE_TEXTE']['V'];
    $this->tab_lettre['VV']  = $_SESSION['NOTE_TEXTE']['VV'];
    $this->tab_lettre['REQ'] = '.....';
    // Les dimensions d'une image (photo, signature) sont données en pixels, et il faut les convertir en mm.
    // Problème : dpi inconnue ! On prend 96 par défaut... mais ça peut être 72 ou 300 ou ... ça dépend de chaque image...
    // mm = (pixels * 25.4) / dpi
    // pixels = (mm * dpi) / 25.4
    $this->coef_conv_pixel_to_mm = 25.4 / 96 ;
    // Alignement du nombre de pages et du rappel des infos sur les pages
    $this->page_nombre_alignement = ($this->officiel) ? 'R' : 'C' ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Pour récupérer un attribut private (c'est comme s'il était en lecture seule)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __get($nom)
  {
    return (isset($this->$nom)) ? $this->$nom : null ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode Magique - Pour affecter une valeur à un attribut
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __set($nom,$valeur)
  {
      $this->$nom = $valeur;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour calculer les dimensions d'une image Lomer
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function calculer_dimensions_images($espace_largeur,$espace_hauteur)
  {
    $this->lomer_espace_largeur = $espace_largeur;
    $this->lomer_espace_hauteur = $espace_hauteur;
    // Une image a des dimensions initiales de 20px sur 10px
    $rapport_largeur = $espace_largeur / 20 ;
    $rapport_hauteur = $espace_hauteur / 10 ;
    $centrage     = ($rapport_largeur<$rapport_hauteur) ? 'hauteur' : 'largeur';
    $rapport_coef = ($centrage=='hauteur') ? $rapport_largeur : $rapport_hauteur ;
    $rapport_coef = min( floor($rapport_coef*10)/10 , 0.4 ) ;  // A partir de PHP 5.3 on peut utiliser l'option PHP_ROUND_HALF_DOWN de round()
    $this->lomer_image_largeur = floor(20*$rapport_coef) ;
    $this->lomer_image_hauteur = floor(10*$rapport_coef) ;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour choisir une couleur de fond ou une couleur de tracé ou une couleur de texte
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function choisir_couleur_fond($couleur)
  {
    $this->SetFillColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  public function choisir_couleur_trait($couleur)
  {
    $this->SetDrawColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  public function choisir_couleur_texte($couleur)
  {
    $this->SetTextColor($this->tab_couleur[$couleur]['r'] , $this->tab_couleur[$couleur]['v'] , $this->tab_couleur[$couleur]['b']);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une note Lomer
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_note_lomer($note,$border,$br,$fill='')
  {
    $tab_fill = array( ''=>'blanc' , 'prev_date'=>'gris_moyen' , 'prev_year'=>'gris_fonce' );
    $this->choisir_couleur_fond($tab_fill[$fill]);
    switch ($note)
    {
      case 'RR' :
      case 'R' :
      case 'V' :
      case 'VV' :
      case 'REQ' :
        if($this->couleur == 'oui')
        {
          $memo_x = $this->GetX();
          $memo_y = $this->GetY();
          $img_pos_x = $memo_x + ( ($this->lomer_espace_largeur - $this->lomer_image_largeur) / 2 ) ;
          $img_pos_y = $memo_y + ( ($this->lomer_espace_hauteur - $this->lomer_image_hauteur) / 2 ) ;
          $dossier = ($note!='REQ') ? $_SESSION['NOTE_DOSSIER'] : 'commun' ;
          $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
          $this->Image('./_img/note/'.$dossier.'/h/'.$note.'.gif',$img_pos_x,$img_pos_y,$this->lomer_image_largeur,$this->lomer_image_hauteur,'GIF');
          // $this->SetXY($memo_x , $memo_y);
          // $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
        }
        else
        {
          $txt = $this->tab_lettre[$note];
          $this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur ,  $txt , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        }
        break;
      case 'ABS' :
      case 'DISP' :
      case 'NN' :
        $tab_texte = array('ABS'=>'Abs.','DISP'=>'Disp.','NN'=>'N.N.');
        $this->cMargin /= 2;
        $this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , $tab_texte[$note] , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->cMargin *= 2;
        break;
      default :
        $this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un état de validation (date sur fond coloré)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_etat_validation($gras,$tab_infos)
{
  // $tab_infos contient 'etat' / 'date' / 'info'
  $this->SetFont('Arial' , $gras , $this->taille_police);
  $texte = ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ;
  $this->choisir_couleur_fond($this->tab_choix_couleur['v'.$tab_infos['etat']]);
  $this->Cell( $this->validation_largeur , $this->cases_hauteur , To::pdf($texte) , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
}

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un pourcentage d'items acquis (texte A VA NA et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_pourcentage_acquis($gras,$tab_infos,$affich)
{
  // $tab_infos contient 'A' / 'VA' / 'NA' / 'nb' / '%'
  if($tab_infos['%']===FALSE)
  {
    $this->choisir_couleur_fond('blanc');
    $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
  }
  else
  {
        if($tab_infos['%']<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
    elseif($tab_infos['%']>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
    else                                                   {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
    if($affich=='detail')
    {
      $this->SetFont('Arial' , $gras , $this->taille_police);
      $this->CellFit( $this->pourcentage_largeur , $this->cases_hauteur , To::pdf($tab_infos['%'].'% acquis ('.$tab_infos['A'].$_SESSION['ACQUIS_TEXTE']['A'].' '.$tab_infos['VA'].$_SESSION['ACQUIS_TEXTE']['VA'].' '.$tab_infos['NA'].$_SESSION['ACQUIS_TEXTE']['NA'].')') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    elseif($affich=='pourcentage')
    {
      $this->SetFont('Arial' , $gras , $this->taille_police/2);
      $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , To::pdf($tab_infos['%']) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    elseif($affich=='rien')
    {
      $this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , '' /*alignement*/ , TRUE /*remplissage*/ );
    }
  }
}

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher un score bilan (bilan sur 100 et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_score_bilan($score,$br)
  {
    // Pour un bulletin on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
    $afficher_score = test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , (bool)$this->officiel /*forcer_parent*/ );
    if($score===FALSE)
    {
      $affichage = ($afficher_score) ? '-' : '' ;
      $this->choisir_couleur_fond('blanc');
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    else
    {
          if($score<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
      elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
      else                                          {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
      $affichage = ($afficher_score) ? $score : '' ;
      $this->SetFont('Arial' , '' , $this->taille_police-2);
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $affichage , 1 /*bordure*/ , $br /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      $this->SetFont('Arial' , '' , $this->taille_police);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une barre avec les états des items acquis (rectangles A VA NA et couleur de fond suivant le seuil)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_proportion_acquis($largeur,$hauteur,$tab_infos,$total,$avec_texte_nombre,$avec_texte_code)
  {
    // $tab_infos contient 'A' / 'VA' / 'NA'
    $abscisse = $this->GetX();
    $ordonnee = $this->GetY();
    // Couleurs de fond + textes
    foreach($tab_infos as $etat => $nb)
    {
      $this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
      $largeur_case = $largeur*$nb/$total ;
          if(  $avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $nb.' '.$_SESSION['ACQUIS_TEXTE'][$etat]; }
      elseif( !$avec_texte_nombre &&  $avec_texte_code ) { $texte_complet = $_SESSION['ACQUIS_TEXTE'][$etat]; }
      elseif( !$avec_texte_nombre && !$avec_texte_code ) { $texte_complet = ''; }
      elseif(  $avec_texte_nombre && !$avec_texte_code ) { $texte_complet = $nb; }
      $texte = ( (strlen($texte_complet)<$largeur_case) || !$avec_texte_nombre || !$avec_texte_code ) ? $texte_complet : $nb ;
      $this->CellFit($largeur_case , $hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    // Bordure unique autour
    $this->SetXY($abscisse , $ordonnee);
    $this->Cell($largeur , $hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une appréciation sur plusieurs lignes dans une zone de dimensions données
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  private function correction_espaces($texte)
  {
    // Ajout d'espaces insécables judicieux et retrait d'espaces de mise en forme inappropriés
    $e = chr(0xC2).chr(0xA0); // espace insécable en UTF-8 (http://fr.wikipedia.org/wiki/Espace_ins%C3%A9cable ; http://fr.wikipedia.org/wiki/UTF-8)
    $tab_bad = array(   ' !' ,   ' ?' ,   ' :' ,   ' ;' ,   ' %' , ' .' , ' ,' );
    $tab_bon = array( $e.'!' , $e.'?' , $e.':' , $e.';' , $e.'%' ,  '.' ,  ',' );
    return str_replace( $tab_bad , $tab_bon , $texte );
  }

  public function afficher_appreciation($largeur_autorisee,$hauteur_autorisee,$taille_police,$taille_interligne,$texte)
  {
    $this->SetFont('Arial' , '' , $taille_police);
    $texte = $this->correction_espaces($texte);
    // Ajustement de la taille de la police et de l'interligne si appréciation trop longue
    do
    {
      list($nb_lignes,$split_texte) = $this->WordWrap($texte,$largeur_autorisee);
      $hauteur_requise = $nb_lignes*$taille_interligne ;
      $is_trop_haut = ( $hauteur_requise > $hauteur_autorisee ) ? TRUE : FALSE ;
      if($is_trop_haut)
      {
        $taille_police *= 0.95;
        $taille_interligne *= 0.95;
        $this->SetFontSize($taille_police);
      }
    }
    while($is_trop_haut);
    // Affichage du texte ligne par ligne
    $this->SetFont('Arial' , '' , $taille_police);
    $memo_abscisse = $this->GetX();
    $memo_ordonnee = $this->GetY();
    $ordonnee = $this->GetY() + ($hauteur_autorisee - $hauteur_requise ) / 3 ; // Verticalement, on laisse 1/3 marge dessus et 2/3 marge dessous
    $this->SetXY( $memo_abscisse , $ordonnee );
    $tab_lignes = explode("\n",$split_texte);
    for( $num_ligne=0 ; $num_ligne<$nb_lignes ; $num_ligne++ )
    {
      $this->CellFit( $largeur_autorisee , $taille_interligne , To::pdf($tab_lignes[$num_ligne]) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    $this->SetXY( $memo_abscisse , $memo_ordonnee+$hauteur_autorisee );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher une signature ou un logo d'établissement d'un bilan officiel
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  private function afficher_image( $largeur_bloc , $hauteur_autorisee , $tab_image , $objet /* signature | logo */ )
  {
    list($img_contenu,$img_format,$img_largeur,$img_hauteur) = $tab_image;
    $img_largeur *= $this->coef_conv_pixel_to_mm;
    $img_hauteur *= $this->coef_conv_pixel_to_mm;
    $coef_ratio_largeur = ($objet=='signature') ? 2 : min( $img_largeur/$img_hauteur , 2 ) ;
    $largeur_autorisee  = $hauteur_autorisee * $coef_ratio_largeur;
    $coef_largeur = $largeur_autorisee / $img_largeur ;
    $coef_hauteur = $hauteur_autorisee / $img_hauteur ;
    $ratio = min( $coef_largeur , $coef_hauteur , 1 ) ;
    $img_largeur *= $ratio;
    $img_hauteur *= $ratio;
    $retrait_x = ($objet=='signature') ? max($hauteur_autorisee,$img_largeur) : $img_largeur ;
    $img_pos_x = ($objet=='signature') ? $this->GetX() + $largeur_bloc - $retrait_x : $this->GetX() ;
    $img_pos_y = $this->GetY() + ( $hauteur_autorisee - $img_hauteur ) / 2 ;
    $this->MemImage($img_contenu,$img_pos_x,$img_pos_y,$img_largeur,$img_hauteur,strtoupper($img_format));
    return $retrait_x;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher la ligne d'absences/retards d'un bilan officiel
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_assiduite($texte_assiduite)
  {
    $this->SetFont('Arial' , '' , $this->taille_police*1.2);
    $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.35);
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte_assiduite) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher la ligne avec un message personnalisé d'un bilan officiel
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_ligne_additionnelle($texte_personnalise)
  {
    $this->SetFont('Arial' , '' , $this->taille_police*1.2);
    $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($texte_personnalise) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour afficher la légende ( $type_legende = 'codes_notation' | 'anciennete_notation' | 'etat_acquisition' | 'pourcentage_acquis' | 'etat_validation' )
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function afficher_legende($type_legende,$ordonnee)
  {
    $espace  = '     ';
    $hauteur = min(4,$this->lignes_hauteur*0.9);
    $size    = ceil($hauteur * 1.6);
    $this->SetXY($this->marge_gauche , $ordonnee);
    $case_hauteur = $hauteur*0.9;
    $case_largeur = $hauteur*0.9*1.5;
    // Afficher la légende des codes de notation
    if($type_legende=='codes_notation')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Notes aux évaluations :') , '');
      $this->SetFont('Arial' , '' , $size);
      $memo_lomer_espace_largeur = $this->lomer_espace_largeur;
      $memo_lomer_espace_hauteur = $this->lomer_espace_hauteur;
      $memo_taille_police = $this->taille_police;
      $this->taille_police = $size; // On est obligé de le changer provisoirement car, si impression N&B, afficher_note_lomer() l'utilise
      $this->calculer_dimensions_images($case_largeur,$case_hauteur);
      $this->Write($hauteur , $espace , '');
      $this->afficher_note_lomer('RR', 1 /*border*/ , 0 /*br*/ ); $this->Write($hauteur , To::pdf($_SESSION['NOTE_LEGENDE']['RR']) , '');
      $this->Write($hauteur , $espace , '');
      $this->afficher_note_lomer('R' , 1 /*border*/ , 0 /*br*/ ); $this->Write($hauteur , To::pdf($_SESSION['NOTE_LEGENDE']['R'])  , '');
      $this->Write($hauteur , $espace , '');
      $this->afficher_note_lomer('V' , 1 /*border*/ , 0 /*br*/ ); $this->Write($hauteur , To::pdf($_SESSION['NOTE_LEGENDE']['V'])  , '');
      $this->Write($hauteur , $espace , '');
      $this->afficher_note_lomer('VV', 1 /*border*/ , 0 /*br*/ ); $this->Write($hauteur , To::pdf($_SESSION['NOTE_LEGENDE']['VV']) , '');
      $this->calculer_dimensions_images($memo_lomer_espace_largeur,$memo_lomer_espace_hauteur);
      $this->taille_police = $memo_taille_police;
    }
    // Afficher la légende de l'ancienneté de la notation
    if($type_legende=='anciennete_notation')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Ancienneté des notes :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_etats = array('blanc'=>'Sur la période.','gris_moyen'=>'Début d\'année scolaire.','gris_fonce'=>'Année scolaire précédente.');
      foreach($tab_etats as $couleur => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($couleur);
        $this->Cell($case_largeur , $case_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($texte) , '');
      }
    }
    // Afficher la légende des scores bilan
    if($type_legende=='score_bilan')
    {
      // Pour un bulletin on prend les droits du profil parent, surtout qu'il peut être imprimé par un administrateur (pas de droit paramétré pour lui).
      $afficher_score = test_user_droit_specifique( $_SESSION['DROIT_VOIR_SCORE_BILAN'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ , (bool)$this->officiel /*forcer_parent*/ );
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Etats d\'acquisitions :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_seuils = array
      (
        TRUE  => array( 'NA'=>'0 à '.$_SESSION['CALCUL_SEUIL']['R'] , 'VA'=>$_SESSION['CALCUL_SEUIL']['R'].' à '.$_SESSION['CALCUL_SEUIL']['V'] , 'A'=>$_SESSION['CALCUL_SEUIL']['V'].' à 100' ) ,
        FALSE => array( 'NA'=>''                                    , 'VA'=>''                                                                  , 'A'=>'' )
      );
      foreach($tab_seuils[$afficher_score] as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
        $this->Cell(2*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($_SESSION['ACQUIS_LEGENDE'][$etat]) , '');
      }
    }
    // Afficher la légende des états d'acquisition
    if($type_legende=='etat_acquisition')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $this->Write($hauteur , To::pdf('Etats d\'acquisitions :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_etats = array('NA','VA','A');
      foreach($tab_etats as $etat)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
        $this->Cell($case_largeur , $case_hauteur , To::pdf($_SESSION['ACQUIS_TEXTE'][$etat]) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
        $this->Write($hauteur , To::pdf($_SESSION['ACQUIS_LEGENDE'][$etat]) , '');
      }
    }
    // Afficher la légende des pourcentages d'items acquis
    if($type_legende=='pourcentage_acquis')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $indication_position = ($this->orientation=='portrait') ? ' (à gauche)' : '' ;
      $this->Write($hauteur , To::pdf('Pourcentages d\'items acquis'.$indication_position.' :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_seuils = array('NA'=>'0 à '.$_SESSION['CALCUL_SEUIL']['R'],'VA'=>$_SESSION['CALCUL_SEUIL']['R'].' à '.$_SESSION['CALCUL_SEUIL']['V'],'A'=>$_SESSION['CALCUL_SEUIL']['V'].' à 100');
      foreach($tab_seuils as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
        $this->Cell(3*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      }
    }
    // Afficher la légende des états de validation
    if($type_legende=='etat_validation')
    {
      $this->SetFont('Arial' , 'B' , $size);
      $indication_position = ($this->orientation=='portrait') ? ' (à droite)' : '' ;
      $this->Write($hauteur , To::pdf('États de validation'.$indication_position.' :') , '');
      $this->SetFont('Arial' , '' , $size);
      $tab_etats = array('v1'=>'Validé','v0'=>'Invalidé','v2'=>'Non renseigné');
      foreach($tab_etats as $etat => $texte)
      {
        $this->Write($hauteur , $espace , '');
        $this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
        $this->Cell(3.5*$case_largeur , $case_hauteur , To::pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      }
    }
    $this->SetXY($this->marge_gauche , $ordonnee+$hauteur);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour changer le pied de page
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function Footer()
  {
    if(!$this->officiel)
    {
      $this->SetXY( 0 , -$this->distance_pied );
      $this->SetFont( 'Arial' , '' , 7 );
      $this->choisir_couleur_fond('gris_clair');
      $this->choisir_couleur_trait('gris_moyen');
      $this->Cell( $this->page_largeur , 3 , To::pdf('Généré le '.date("d/m/Y \à H\hi\m\i\\n").' par '.$_SESSION['USER_PRENOM']{0}.'. '.$_SESSION['USER_NOM'].' ('.$_SESSION['USER_PROFIL_NOM_COURT'].') avec SACoche [ '.SERVEUR_PROJET.' ] version '.VERSION_PROG.'.') , 'TB' /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ , SERVEUR_PROJET);
    }
    else
    {
      if($this->filigrane)
      {
        $this->SetFont( 'Arial' , 'B' , 72 );
        $this->choisir_couleur_texte('gris_fonce');
        $this->TextWithRotation( $this->page_largeur/6 /*x*/ , $this->page_hauteur*5/6 /*y*/ , "TEST D'IMPRESSION" /*txt*/ , tanh($this->page_hauteur/$this->page_largeur)*180/M_PI /*txt_angle*/ , 0 /*font_angle*/ );
      }
      $this->SetFont( 'Arial' , '' , 4 );
      $this->choisir_couleur_texte('noir');
      $this->SetXY( 0 , -$this->distance_pied - 3 );
      $this->Cell( $this->page_largeur - $this->marge_droite , 3 , To::pdf('Suivi d\'Acquisition de Compétences') , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ , SERVEUR_PROJET);
      $this->SetXY( 0 , -$this->distance_pied - 1.5 );
      $this->Cell( $this->page_largeur - $this->marge_droite , 3 , To::pdf(SERVEUR_PROJET) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ , SERVEUR_PROJET);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour remplacer le nombre de pages par la bonne valeur
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function reporter_page_nb()
  {
    $page_nb = $this->page - $this->page_numero_first + 1 ;
    for( $page_numero=$this->page_numero_first ; $page_numero<=$this->page ; $page_numero++ )
    {
      $this->pages[$page_numero] = ($page_numero<$this->page) ? gzcompress(str_replace( $this->page_nombre_alias , $page_nb , gzuncompress($this->pages[$page_numero]) )) : str_replace( $this->page_nombre_alias , $page_nb , $this->pages[$page_numero] ) ;
    }
    return $page_nb;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthode pour ajouter une page blanche
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function ajouter_page_blanche()
  {
    $this->AddPage($this->orientation , 'A4');
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'une synthèse matiere ou pluridisciplinaire ; a priori pas de pb avec la hauteur de ligne minimale
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // bilan_synthese_initialiser()   c'est là que les calculs se font pour une sortie "matiere"
  // bilan_synthese_entete()        c'est là que les calculs se font pour une sortie "multimatiere"
  // bilan_synthese_premiere_page()
  // bilan_synthese_rappel_eleve_page()
  // bilan_synthese_ligne_matiere()
  // bilan_synthese_ligne_synthese()
  // bilan_synthese_appreciation_rubrique()
  // bilan_synthese_appreciation_generale()
  // bilan_synthese_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function bilan_synthese_initialiser($format,$nb_lignes_total,$eleves_nb)
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    if($format=='matiere')
    {
      // Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
      $hauteur_dispo_par_page     = $this->page_hauteur_moins_marges ;
      $lignes_nb_tous_eleves      = $eleves_nb * ( 1 + 1 + ($this->legende*1.5) ) + $nb_lignes_total ; // eleves * [ intitulé-structure + classe-élève-date + légende ] + toutes_synthèses
      $hauteur_ligne_moyenne      = 6;
      $lignes_nb_moyen_par_page   = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen              = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $eleves_nb_par_page         = ceil( $eleves_nb / $nb_page_moyen ) ;
      // $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
      $lignes_nb_moyen_eleve      = $lignes_nb_tous_eleves / $eleves_nb ;
      $lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas car un élève peut alors être considéré à cheval sur 2 pages
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
      $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
      $this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
      // On s'occupe aussi maintenant de la taille de la police
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Pour forcer à prendre une nouvelle page au 1er élève
      $this->SetXY(0,$this->page_hauteur);
    }
  }

  public function bilan_synthese_entete($format,$tab_infos_entete,$eleve_nom,$eleve_prenom,$eleve_nb_lignes)
  {
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    if($format=='matiere')
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $lignes_nb = 1 + 1 + ($this->legende*1.5) + $eleve_nb_lignes ; // intitulé-structure + classe-élève-date + légende + synthèses
      if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        // Interligne
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
      }
    }
    elseif($format=='multimatiere')
    {
      // On prend une nouvelle page PDF
      $this->bilan_synthese_premiere_page();
      if($this->officiel)
      {
        // Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet entête.
        list( $tab_etabl_coords , $tab_etabl_logo , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
        $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
        // Bloc adresse en positionnement contraint
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
          $this->SetXY($this->marge_gauche,$this->marge_haut);
        }
        // Bloc établissement
        $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
        $bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$tab_etabl_logo,$bloc_etabl_largeur);
        // Bloc titres
        $alerte_archive = ($tab_adresse==='archive') ? TRUE : FALSE ;
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          // En dessous du bloc établissement
          $bloc_titre_largeur = $bloc_etabl_largeur ;
          $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
          $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
        }
        else
        {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
          $bloc_titre_largeur = 100;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur)+4;
          $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
          $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
        }
        // Tag date heure initiales (sous le bloc titres dans toutes les situations)
        $this->officiel_ligne_tag($tag_date_heure_initiales,$bloc_titre_largeur);
        // Bloc adresse en positionnement libre
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre') )
        {
          $bloc_adresse_largeur = $bloc_titre_largeur;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_adresse_largeur , $this->marge_haut+$bloc_titre_hauteur+2 );
          $bloc_adresse_hauteur = $this->officiel_bloc_adresse_position_libre($tab_adresse,$bloc_adresse_largeur);
          $bloc_droite_hauteur = $bloc_titre_hauteur + $bloc_adresse_hauteur ;
        }
        $hauteur_entete = max($bloc_gauche_hauteur,$bloc_droite_hauteur);
      }
      else
      {
        $hauteur_entete = 2*4 ; // HG L1 intitulé L2 période ; HD L1 structure L2 élève classe
      }
      // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible (personnalisée par élève), un minimum de pages sinon
      $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
      $lignes_nb = ( $hauteur_entete / 4 ) + $eleve_nb_lignes + ($this->legende*1.5) ; // entête + synthèses + légendes
      $hauteur_ligne_minimale = ($this->officiel) ? 4 : 3.5 ;
      $hauteur_ligne_maximale = $hauteur_ligne_minimale + 2;
      $nb_pages = 0;
      do
      {
        $nb_pages++;
        $lignes_rabe = ($nb_pages-1)*10; // Prendre un peu de marge pour tenir compte des sauts de page laissant du blanc en bas de page si une rubrique ne rentre pas.
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / ($lignes_nb+$lignes_rabe) ;
      }
      while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
      $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
      $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
    }
    if(!$this->officiel)
    {
      list( $texte_format , $texte_periode , $groupe_nom ) = $tab_infos_entete;
      $this->doc_titre = 'Synthèse '.$texte_format.' - '.$texte_periode;
      // Intitulé (dont éventuellement matière) / structure
      $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf('Synthèse '.$texte_format)                  , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      // Période / Classe - élève
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Cell($largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      if($format=='matiere')
      {
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
      }
    }
    else
    {
      $this->SetXY($this->marge_gauche,$this->marge_haut+$hauteur_entete);
    }
  }

  public function bilan_synthese_premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , 'B' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
  }

  public function bilan_synthese_rappel_eleve_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
  }

  public function bilan_synthese_ligne_matiere($format,$matiere_nom,$lignes_nb,$tab_infos_matiere,$total,$moyenne_eleve,$moyenne_classe,$avec_texte_nombre,$avec_texte_code)
  {
    if($format=='multimatiere')
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
      if( $test_nouvelle_page )
      {
        $this->bilan_synthese_rappel_eleve_page();
      }
      else
      {
        // Interligne
        $nb_lignes_vides = ($this->officiel) ? 1 : 1.5 ;
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*$nb_lignes_vides);
      }
    }
    if(!$this->officiel)
    {
      // Intitulé matière
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ;
      $this->choisir_couleur_fond($couleur_fond);
      $this->CellFit( $this->page_largeur_moins_marges - 80 , $this->lignes_hauteur*1.5 , To::pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
      // Proportions acquis matière
      $this->SetFont('Arial' , 'B' , $this->taille_police);
      $this->afficher_proportion_acquis(80,$this->lignes_hauteur*1.5,$tab_infos_matiere,$total,$avec_texte_nombre,$avec_texte_code);
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
    }
    else
    {
      $memo_y = $this->GetY();
      $demi_largeur = ( $this->page_largeur_moins_marges ) / 2 ;
      // Intitulé matière
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ;
      $this->choisir_couleur_fond($couleur_fond);
      $this->CellFit( $demi_largeur , $this->lignes_hauteur*2 , To::pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
      // Moyenne élève (éventuelle) et moyenne classe (éventuelle)
      if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
      {
        $nb_lignes_hauteur = 2 - $_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS'] ;
        $largeur_note = 10;
        $this->Rect( $this->GetX() , $this->GetY() , $demi_largeur , $this->lignes_hauteur*$nb_lignes_hauteur , 'D' /* DrawFill */ );
        $texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne élève (classe) :' : 'Moyenne élève :' ;
        $this->SetFont('Arial' , '' , $this->taille_police);
        $largueur_texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $demi_largeur-2*$largeur_note : $demi_largeur-$largeur_note ;
        $this->Cell( $largueur_texte , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
        $moyenne_eleve = ($moyenne_eleve!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_eleve,1,',','') : ($moyenne_eleve*5).'%' ) : '-' ;
        $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
        $this->Cell( $largeur_note , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
        if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
        {
          $moyenne_classe = ($moyenne_classe!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_classe,1,',','') : round($moyenne_classe*5).'%' ) : '-' ;
          $this->SetFont('Arial' , '' , $this->taille_police*0.8);
          $this->Cell( $largeur_note , $this->lignes_hauteur*$nb_lignes_hauteur , To::pdf('('.$moyenne_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
        }
        $this->SetXY($this->marge_gauche + $demi_largeur , $this->GetY() + $this->lignes_hauteur*$nb_lignes_hauteur );
      }
      // Proportions acquis matière
      if($_SESSION['OFFICIEL']['BULLETIN_BARRE_ACQUISITIONS'])
      {
        $nb_lignes_hauteur = 2 - $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ;
        $this->SetFont('Arial' , '' , $this->taille_police);
        $this->afficher_proportion_acquis($demi_largeur,$this->lignes_hauteur*$nb_lignes_hauteur,$tab_infos_matiere,$total,$avec_texte_nombre,$avec_texte_code);
      }
      // Positionnement
      $this->SetXY($this->marge_gauche , $memo_y + $this->lignes_hauteur*2);
    }
  }

  public function bilan_synthese_ligne_synthese($synthese_nom,$tab_infos_synthese,$total,$hauteur_ligne_synthese,$avec_texte_nombre,$avec_texte_code)
  {
    $hauteur_ligne = $this->lignes_hauteur * $hauteur_ligne_synthese ;
    $largeur_diagramme = ($this->officiel) ? 20 : 40 ;
    $this->SetFont('Arial' , '' , $this->taille_police*0.8);
    $this->afficher_proportion_acquis($largeur_diagramme,$hauteur_ligne,$tab_infos_synthese,$total,$avec_texte_nombre,$avec_texte_code);
    $intitule_synthese_largeur = ( ($this->officiel) && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) ) ? ( $this->page_largeur_moins_marges ) / 2 - $largeur_diagramme : $this->page_largeur_moins_marges - $largeur_diagramme ;
    // Intitulé synthèse
    $this->SetFont('Arial' , '' , $this->taille_police);
    $couleur_fond = ($this->couleur=='oui') ? 'gris_clair' : 'blanc' ;
    $this->choisir_couleur_fond($couleur_fond);
    $this->CellFit( $intitule_synthese_largeur , $hauteur_ligne , To::pdf($synthese_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
  }

  public function bilan_synthese_appreciation_rubrique($tab_saisie,$nb_lignes_hauteur)
  {
    $cadre_hauteur = $nb_lignes_hauteur * $this->lignes_hauteur ;
    $demi_largeur = ( $this->page_largeur_moins_marges ) / 2 ;
    $this->SetXY( $this->marge_gauche + $demi_largeur , $this->GetY() - $cadre_hauteur );
    $this->Rect( $this->GetX() , $this->GetY() , $demi_largeur , $cadre_hauteur , 'D' /* DrawFill */ );
    if($tab_saisie!==NULL)
    {
      unset($tab_saisie[0]); // la note
      $memo_y = $this->GetY();
      $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $demi_largeur , $this->lignes_hauteur , 'bulletin' , $_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE'] , $cadre_hauteur );
      $this->SetXY( $this->marge_gauche , $memo_y + $cadre_hauteur );
    }
    else
    {
      $this->SetXY( $this->marge_gauche , $this->GetY() + $cadre_hauteur );
    }
  }

  public function bilan_synthese_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_message_et_legende , $moyenne_generale_eleve , $moyenne_generale_classe )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_message_et_legende ) ;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->bilan_synthese_rappel_eleve_page();
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->page_largeur_moins_marges , $this->lignes_hauteur , $moyenne_generale_eleve , $moyenne_generale_classe );
  }

  public function bilan_synthese_legende($format)
  {
    // Légende : en bas de page si 'multimatiere', à la suite si 'matiere'
    $ordonnee = ($format=='multimatiere') ?  $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 : $this->GetY() + $this->lignes_hauteur*0.5 ;
    $this->afficher_legende( 'etat_acquisition' /*type_legende*/ , $ordonnee );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un bilan d'items d'une matiere ou pluridisciplinaire
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // bilan_item_individuel_initialiser()   c'est là que les calculs se font pour une sortie "matiere" ou "selection"
  // bilan_item_individuel_entete()        c'est là que les calculs se font pour une sortie "multimatiere"
  // bilan_item_individuel_premiere_page()
  // bilan_item_individuel_rappel_eleve_page()
  // bilan_item_individuel_transdisciplinaire_ligne_matiere()
  // bilan_item_individuel_appreciation_rubrique()
  // bilan_item_individuel_appreciation_generale()
  // bilan_item_individuel_debut_ligne_item()
  // bilan_item_individuel_ligne_synthese()
  // bilan_item_individuel_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function bilan_item_individuel_initialiser($format,$aff_etat_acquisition,$aff_anciennete_notation,$cases_nb,$cases_largeur,$lignes_nb,$eleves_nb,$pages_nb_methode)
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    $this->format                  = $format;
    $this->cases_nb                = $cases_nb;
    $this->cases_largeur           = $cases_largeur;
    $this->colonne_bilan_largeur   = ($aff_etat_acquisition) ? $this->cases_largeur : 0 ;
    $this->reference_largeur       = 10; // valeur fixe
    $this->synthese_largeur        = $this->page_largeur_moins_marges - $this->reference_largeur;
    $this->intitule_largeur        = $this->synthese_largeur - ( $this->cases_nb * $this->cases_largeur ) - $this->colonne_bilan_largeur;
    $this->legende_deja_affichee   = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
    $this->legende_nb_lignes       = 1 + (int)$aff_anciennete_notation + (int)$aff_etat_acquisition ;
    $this->aff_codes_notation      = TRUE;
    $this->aff_anciennete_notation = $aff_anciennete_notation;
    $this->aff_etat_acquisition    = $aff_etat_acquisition;
    if( ($this->format=='matiere') || ($this->format=='selection') )
    {
      // Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
      $hauteur_dispo_par_page   = $this->page_hauteur_moins_marges ;
      $lignes_nb_tous_eleves    = $eleves_nb * ( 1 + 1 + $lignes_nb + ($this->legende*$this->legende_nb_lignes) + 2 ) ; // eleves * [ intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge ]
      $hauteur_ligne_moyenne    = 5;
      $lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
      $nb_page_moyen            = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
      $eleves_nb_par_page       = ceil( $eleves_nb / $nb_page_moyen ) ;
      if($pages_nb_methode=='augmente')
      {
        $eleves_nb_par_page = max( 1 , $eleves_nb_par_page-1 ) ; // Sans doute à revoir... un élève demeure forcé sur 1 page...
      }
      // $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
      $lignes_nb_moyen_eleve      = $lignes_nb_tous_eleves / $eleves_nb ;
      $lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas car un élève peut alors être considéré à cheval sur 2 pages
      $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
      $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
      $this->lignes_hauteur = min ( $this->lignes_hauteur , 7.5 ) ;
      // On s'occupe aussi maintenant de la taille de la police
      $this->taille_police  = min($this->lignes_hauteur,$this->cases_largeur) * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Pour forcer à prendre une nouvelle page au 1er élève
      $this->SetXY(0,$this->page_hauteur);
      // Hauteur d'une case
      $this->cases_hauteur = $this->lignes_hauteur;
      $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
    }
  }

  public function bilan_item_individuel_premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , '' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
  }

  public function bilan_item_individuel_rappel_eleve_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
  }

  public function bilan_item_individuel_entete($pages_nb_methode,$tab_infos_entete,$eleve_nom,$eleve_prenom,$eleve_nb_lignes)
  {
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    if( ($this->format=='matiere') || ($this->format=='selection') )
    {
      // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
      $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
      $lignes_nb = 1 + 1 + $eleve_nb_lignes + ($this->legende*$this->legende_nb_lignes) + 2 ; // intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge
      if($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
      else
      {
        // Interligne
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*2);
      }
      list( $texte_format , $texte_periode , $groupe_nom ) = $tab_infos_entete;
    }
    elseif($this->format=='multimatiere')
    {
      // On prend une nouvelle page PDF
      $this->bilan_item_individuel_premiere_page();
      $this->legende_deja_affichee = FALSE; // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
      if($this->officiel)
      {
        // Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet entête.
        list( $tab_etabl_coords , $tab_etabl_logo , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
        $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
        // Bloc adresse en positionnement contraint
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
          $this->SetXY($this->marge_gauche,$this->marge_haut);
        }
        // Bloc établissement
        $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
        $bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$tab_etabl_logo,$bloc_etabl_largeur);
        // Bloc titres
        $alerte_archive = ($tab_adresse==='archive') ? TRUE : FALSE ;
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
        {
          // En dessous du bloc établissement
          $bloc_titre_largeur = $bloc_etabl_largeur ;
          $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
          $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
        }
        else
        {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
          $bloc_titre_largeur = 100;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
          $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur)+4;
          $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
          $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
        }
        // Tag date heure initiales (sous le bloc titres dans toutes les situations)
        $this->officiel_ligne_tag($tag_date_heure_initiales,$bloc_titre_largeur);
        // Bloc adresse en positionnement libre
        if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre') )
        {
          $bloc_adresse_largeur = $bloc_titre_largeur;
          $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_adresse_largeur , $this->marge_haut+$bloc_titre_hauteur+2 );
          $bloc_adresse_hauteur = $this->officiel_bloc_adresse_position_libre($tab_adresse,$bloc_adresse_largeur);
          $bloc_droite_hauteur = $bloc_titre_hauteur + $bloc_adresse_hauteur ;
        }
        $hauteur_entete = max($bloc_gauche_hauteur,$bloc_droite_hauteur);
      }
      else
      {
        list( $texte_format , $texte_periode , $groupe_nom ) = $tab_infos_entete;
        $this->doc_titre = 'Bilan '.$texte_format.' - '.$texte_periode;
        $hauteur_entete = 2*4 ; // HG L1 intitulé L2 période ; HD L1 structure L2 élève classe
      }
      // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible (personnalisée par élève), un minimum de pages sinon
      $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
      $lignes_nb = ( $hauteur_entete / 3 ) + $eleve_nb_lignes + ($this->legende*$this->legende_nb_lignes) ; // entête + matières(marge+intitulé) & lignes dont résumés + légendes
      $hauteur_ligne_minimale = ($this->officiel) ? 3.5 : 3 ;
      $hauteur_ligne_maximale = $hauteur_ligne_minimale + 2;
      $nb_pages = 0;
      do
      {
        $nb_pages++;
        $lignes_nb += 1; // report infos élève
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
      }
      while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
      if($pages_nb_methode=='augmente')
      {
        $nb_pages++;
        $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
      }
      $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
      $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
      $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
      $this->taille_police  = min ( $this->taille_police , 10 ) ;
      // Hauteur d'une case
      $this->cases_hauteur = $this->lignes_hauteur;
      $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
    }
    if(!$this->officiel)
    {
      // Intitulé (dont éventuellement matière) / structure
      $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf('Bilan '.$texte_format)                     , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      // Période / Classe - élève
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Cell($largeur_demi_page , $this->taille_police*0.8 , To::pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      if( ($this->format=='matiere') || ($this->format=='selection') )
      {
        $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
      }
    }
    else
    {
      $this->SetXY($this->marge_gauche,$this->marge_haut+$hauteur_entete);
    }
  }

  public function bilan_item_individuel_transdisciplinaire_ligne_matiere($matiere_nom,$lignes_nb)
  {
    // La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
    $hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
    $lignes_nb = 1.5 + $lignes_nb ; // matière(marge+intitulé) + lignes dont résumés (on ne compte pas la légende)
    $test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
    if( $test_nouvelle_page )
    {
      if( ($this->legende) && (!$this->legende_deja_affichee) )
      {
         // Si multimatières, on n'est pas certain qu'il y ait la place pour la légende en dernière page, alors on la met dès que possible
        $test_place_legende = ($this->lignes_hauteur*$this->legende_nb_lignes*0.9 < $hauteur_dispo_restante) ;
        if( $test_place_legende )
        {
          $this->bilan_item_individuel_legende();
          $this->legende_deja_affichee = TRUE;
        }
      }
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    // Intitulé matière + éventuellement rappel élève
    if( $test_nouvelle_page )
    {
      $this->bilan_item_individuel_rappel_eleve_page();
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->Cell($this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($matiere_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }

  public function bilan_item_individuel_appreciation_rubrique($tab_saisie)
  {
    $this->SetXY( $this->marge_gauche + $this->reference_largeur , $this->GetY() );
    $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->synthese_largeur , $this->lignes_hauteur , 'releve' , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE'] );
  }

  public function bilan_item_individuel_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_message_et_legende )
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_message_et_legende ) ;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->bilan_item_individuel_rappel_eleve_page();
      $this->SetXY( $this->marge_gauche+$this->reference_largeur , $this->GetY() + 2 );
    }
    else
    {
      // Interligne
      $this->SetXY($this->marge_gauche+$this->reference_largeur , $this->GetY() + $this->lignes_hauteur*0.5);
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->synthese_largeur , $this->cases_hauteur , NULL /*moyenne_generale_eleve*/ , NULL /*moyenne_generale_classe*/ );
  }

  public function bilan_item_individuel_debut_ligne_item($item_ref,$item_texte)
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = $this->lignes_hauteur;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève (y a des bilans avec tellement d'items qu'il faut aussi mettre le test ici...
      $this->AddPage($this->orientation , 'A4');
      $this->choisir_couleur_texte('gris_fonce');
      $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom.' (suite)') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      $this->choisir_couleur_texte('noir');
      $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
    }
    list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
    $this->choisir_couleur_fond('gris_clair');
    $this->SetFont('Arial' , '' , $this->taille_police*0.8);
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($ref_suite) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE  /*remplissage*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->intitule_largeur , $this->cases_hauteur , To::pdf($item_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->choisir_couleur_fond('blanc');
  }

  public function bilan_item_individuel_ligne_synthese($bilan_texte)
  {
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    $this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                    , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->Cell( $this->synthese_largeur  , $this->cases_hauteur , To::pdf($bilan_texte) , 1 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , TRUE  /*remplissage*/ );
  }

  public function bilan_item_individuel_legende()
  {
    if(!$this->legende_deja_affichee)
    {
      // Légende : à la suite si 'matiere' ou 'selection' , en bas de page si 'multimatiere',
      $ordonnee = ( ($this->format=='matiere') || ($this->format=='selection') ) ? $this->GetY() + $this->lignes_hauteur*0.2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*$this->legende_nb_lignes*0.9 ;
      if($this->aff_codes_notation)      { $this->afficher_legende( 'codes_notation'      /*type_legende*/ , $ordonnee     /*ordonnée*/ ); } /*toujours TRUE*/
      if($this->aff_anciennete_notation) { $this->afficher_legende( 'anciennete_notation' /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
      if($this->aff_etat_acquisition)    { $this->afficher_legende( 'score_bilan'         /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'une grille d'items d'un référentiel
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // grille_referentiel_initialiser()
  // grille_referentiel_entete()
  // grille_referentiel_domaine()
  // grille_referentiel_theme()
  // grille_referentiel_item()
  // grille_referentiel_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function grille_referentiel_initialiser($cases_nb,$cases_largeur,$lignes_nb,$legende_nb_lignes,$colonne_bilan,$colonne_vide)
  {
    // On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible, un minimum de pages sinon
    $hauteur_dispo_par_page = $this->page_hauteur_moins_marges ;
    $lignes_nb = 1 + 1 + 1 + $lignes_nb + ($this->legende*$legende_nb_lignes+0.25) ; // intitulé-structure + matière-niveau-élève + marge (1 & un peu plus car aussi avant domaines) + lignes (domaines+thèmes+items) + légende
    $hauteur_ligne_minimale = 3.5;
    $hauteur_ligne_maximale = 5;
    $nb_pages = 0;
    do
    {
      $nb_pages++;
      $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $lignes_nb ;
    }
    while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
    if($nb_pages>1)
    {
      $hauteur_ligne_calcule -= $nb_pages*0.1; // Tenter de contrebalancer un peu le pb des thèmes non coupés
    }
    $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
    $this->taille_police  = $this->lignes_hauteur * 1.6 ; // 5mm de hauteur par ligne donne une taille de 8
    $this->taille_police  = min ( $this->taille_police , 10 ) ;
    // La suite est classique
    $this->cases_nb          = $cases_nb;
    $this->cases_largeur     = $cases_largeur;
    $this->cases_hauteur     = $this->lignes_hauteur;
    $this->colonne_bilan_largeur = ($colonne_bilan=='non') ? 0 : $cases_largeur ;
    $this->colonne_vide_largeur  = $colonne_vide;
    $this->reference_largeur = 10; // valeur fixe
    $this->intitule_largeur  = $this->page_largeur_moins_marges - $this->reference_largeur - ($this->cases_nb * $this->cases_largeur) - $this->colonne_bilan_largeur - $this->colonne_vide_largeur ;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function grille_referentiel_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom)
  {
    // On prend une nouvelle page PDF pour chaque élève
    $this->AddPage($this->orientation , 'A4');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
    $largeur_demi_page = ( $this->page_largeur_moins_marges ) / 2;
    // intitulé-structure
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
    $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf('Grille d\'items d\'un référentiel')        , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
    // matière-niveau-élève
    $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($matiere_nom.' - Niveau '.$niveau_nom) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    if($eleve_id)
    {
      $this->Cell($largeur_demi_page , $this->lignes_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
    }
    else
    {
      $this->choisir_couleur_trait('gris_moyen');
      $this->SetLineWidth(0.1);
      $this->Line($this->page_largeur-$this->marge_droite-75 , $this->marge_haut+2*$this->lignes_hauteur , $this->page_largeur-$this->marge_droite , $this->marge_haut+2*$this->lignes_hauteur);
      $this->choisir_couleur_trait('noir');
    }
    $this->SetXY($this->marge_gauche,$this->marge_haut+2.5*$this->lignes_hauteur);
  }

  public function grille_referentiel_domaine($domaine_nom,$domaine_nb_lignes)
  {
    $hauteur_requise = $this->cases_hauteur * $domaine_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas
      $this->AddPage($this->orientation , 'A4');
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
    $this->SetXY(15 , $this->GetY()+1);
    $this->Cell( $this->intitule_largeur , $this->cases_hauteur , To::pdf($domaine_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }

  public function grille_referentiel_theme($theme_ref,$theme_nom,$theme_nb_lignes)
  {
    $hauteur_requise = $this->cases_hauteur * $theme_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas
      $this->AddPage($this->orientation , 'A4');
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    $this->Cell( $this->reference_largeur , $this->cases_hauteur , To::pdf($theme_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    $this->Cell( $this->intitule_largeur , $this->cases_hauteur , To::pdf($theme_nom)  , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    if($this->colonne_vide_largeur)
    {
      // Ajouter une case vide sur la hauteur du nombre d'items du thème
      $abscisse = $this->GetX();
      $ordonnee = $this->GetY();
      $this->SetXY( $this->page_largeur - $this->marge_droite - $this->colonne_vide_largeur , $ordonnee );
      $this->Cell( $this->colonne_vide_largeur , $this->cases_hauteur * ($theme_nb_lignes-1) , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->SetXY( $abscisse , $ordonnee );
    }
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function grille_referentiel_item($item_ref,$item_texte)
  {
    $this->choisir_couleur_fond('gris_clair');
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($item_ref)   , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE  /*remplissage*/ );
    $this->CellFit( $this->intitule_largeur  , $this->cases_hauteur , To::pdf($item_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->choisir_couleur_fond('blanc');
  }

  public function grille_referentiel_legende( $codes_notation , $anciennete_notation , $score_bilan )
  {
    $nb_lignes = (int)$codes_notation + (int)$anciennete_notation + (int)$score_bilan ;
    $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*$nb_lignes*0.9 ;
    if($codes_notation)      { $this->afficher_legende( 'codes_notation'      /*type_legende*/ , $ordonnee     /*ordonnée*/ ); }
    if($anciennete_notation) { $this->afficher_legende( 'anciennete_notation' /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
    if($score_bilan)         { $this->afficher_legende( 'score_bilan'         /*type_legende*/ , $this->GetY() /*ordonnée*/ ); }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour l'entête des bilans officiels
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // officiel_bloc_etablissement()
  // officiel_bloc_titres()
  // officiel_ligne_tag()
  // officiel_bloc_adresse_position_libre()
  // officiel_bloc_adresse_position_contrainte_et_pliures()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  private function officiel_bloc_etablissement($tab_etabl_coords,$tab_etabl_logo,$bloc_largeur)
  {
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    // logo
    if($tab_etabl_logo)
    {
      $hauteur_logo_autorisee = max( count($tab_etabl_coords) , 5 ) * 8*0.4 ;
      $largeur_logo = $this->afficher_image( $bloc_largeur , $hauteur_logo_autorisee , $tab_etabl_logo , 'logo' );
      $this->SetXY($memoX+$largeur_logo,$memoY);
    }
    else
    {
      $hauteur_logo_autorisee = 0;
      $largeur_logo = 0;
    }
    // texte
    $bloc_hauteur_texte = 0 ;
    foreach($tab_etabl_coords as $key => $ligne_etabl)
    {
      if($key==0)
      {
        // Nom de l'établissement
        $taille_police = 11 ;
        $ligne_hauteur = $taille_police*0.4 ;
        $this->SetFont('Arial' , '' , $taille_police);
      }
      elseif($key==1)
      {
        // A partir de la ligne suivante
        $taille_police = 8 ;
        $ligne_hauteur = $taille_police*0.4 ;
        $this->SetFont('Arial' , '' , $taille_police);
      }
      $this->CellFit( $bloc_largeur-$largeur_logo , $ligne_hauteur , To::pdf($ligne_etabl) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $bloc_hauteur_texte += $ligne_hauteur ;
    }
    $hauteur_bloc = max($bloc_hauteur_texte,$hauteur_logo_autorisee);
    $this->SetY($memoY+$hauteur_bloc);
    return $hauteur_bloc;
  }

  private function officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_largeur)
  {
    $taille_police = 10 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $bloc_hauteur = ($alerte_archive) ? 4*$ligne_hauteur : 3*$ligne_hauteur ;
    $this->SetFont('Arial' , 'B' , $taille_police);
    $tab_bloc_titres[2] = $this->eleve_nom.' '.$this->eleve_prenom.' ('.$tab_bloc_titres[2].')';
    $this->choisir_couleur_fond('gris_clair');
    $this->Rect( $this->GetX() , $this->GetY() , $bloc_largeur , $bloc_hauteur , 'DF' /* DrawFill */ );
    foreach($tab_bloc_titres as $ligne_titre)
    {
      $this->CellFit( $bloc_largeur , $ligne_hauteur , To::pdf($ligne_titre) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    }
    if($alerte_archive)
    {
      // Ligne d'avertissement
      $this->choisir_couleur_texte('rougevif');
      $this->CellFit( $bloc_largeur , $ligne_hauteur , To::pdf('Copie partielle pour information. Seul l\'original fait foi.') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
      $this->choisir_couleur_texte('noir');
    }
    return $bloc_hauteur;
  }

  private function officiel_ligne_tag($tag_date_heure_initiales,$ligne_largeur)
  {
    $taille_police = 5 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    $this->Cell( $ligne_largeur , $ligne_hauteur , To::pdf($tag_date_heure_initiales) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
  }

  private function officiel_bloc_adresse_position_libre($tab_adresse,$bloc_largeur)
  {
    $taille_police = 9 ;
    $ligne_hauteur = $taille_police*0.4 ;
    $this->SetFont('Arial' , '' , $taille_police);
    foreach($tab_adresse as $ligne_adresse)
    {
      $this->CellFit( $bloc_largeur , $ligne_hauteur , To::pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    return count($tab_adresse)*$ligne_hauteur;
  }

  private function officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse)
  {
    // Placer les marques des pliures
    $longueur_tiret = 1; // <= 5
    $this->SetLineWidth(0.1);
    $enveloppe_hauteur = $_SESSION['ENVELOPPE']['VERTICAL_HAUT'] + $_SESSION['ENVELOPPE']['VERTICAL_MILIEU'] + $_SESSION['ENVELOPPE']['VERTICAL_BAS'] ;
    $enveloppe_largeur = $_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] + $_SESSION['ENVELOPPE']['HORIZONTAL_MILIEU'] + $_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $jeu_minimum    = 2 ;
    $jeu_horizontal = $enveloppe_largeur - $this->page_largeur - $jeu_minimum ;
    $jeu_vertical   = $jeu_minimum ;
    $ligne2_y = $this->page_hauteur - $enveloppe_hauteur - $jeu_vertical ;
    $this->Line( $this->marge_gauche-$longueur_tiret , $ligne2_y , $this->marge_gauche , $ligne2_y );
    $this->Line( $this->page_largeur-$this->marge_droite , $ligne2_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne2_y );
    $ligne1_y = $ligne2_y - $enveloppe_hauteur - $jeu_vertical ;
    $this->Line( $this->marge_gauche-$longueur_tiret , $ligne1_y , $this->marge_gauche , $ligne1_y );
    $this->Line( $this->page_largeur-$this->marge_droite , $ligne1_y , $this->page_largeur-$this->marge_droite+$longueur_tiret , $ligne1_y );
    $jeu_vertical -= 1 ; // Le pliage est manuel donc imparfait et il y a l'épaisseur du papier ;)
    // Déterminer et dessiner l'emplacement du bloc adresse
    $interieur_coin_hg_x = $_SESSION['ENVELOPPE']['HORIZONTAL_GAUCHE'] ;
    $exterieur_coin_hg_x = $interieur_coin_hg_x - $jeu_horizontal ;
    $interieur_coin_bd_x = $this->page_largeur - $_SESSION['ENVELOPPE']['HORIZONTAL_DROITE'] ;
    $exterieur_coin_bd_x = $interieur_coin_bd_x + $jeu_horizontal ;
    $interieur_coin_bd_y = $ligne1_y - $_SESSION['ENVELOPPE']['VERTICAL_BAS'] ;
    $exterieur_coin_bd_y = $interieur_coin_bd_y + $jeu_vertical ;
    $exterieur_coin_hg_y = max( $interieur_coin_bd_y - $_SESSION['ENVELOPPE']['VERTICAL_MILIEU'] , 5 ) ;
    $interieur_coin_hg_y = $exterieur_coin_hg_y + $jeu_vertical ;
    $exterieur_largeur = $exterieur_coin_bd_x - $exterieur_coin_hg_x ;
    $exterieur_hauteur = $exterieur_coin_bd_y - $exterieur_coin_hg_y ;
    $interieur_largeur = $interieur_coin_bd_x - $interieur_coin_hg_x ;
    $interieur_hauteur = $interieur_coin_bd_y - $interieur_coin_hg_y ;
    $this->choisir_couleur_trait('gris_clair');
    $this->Rect( $exterieur_coin_hg_x , $exterieur_coin_hg_y , $exterieur_largeur , $exterieur_hauteur , 'D' /* DrawFill */ );
    $this->choisir_couleur_trait('gris_moyen');
    $this->Rect( $interieur_coin_hg_x , $interieur_coin_hg_y , $interieur_largeur , $interieur_hauteur , 'D' /* DrawFill */ );
    $this->choisir_couleur_trait('noir');
    // Affiner la position du contenu de l'adresse
    $marge_suppl_x = $interieur_largeur*0.05;
    $marge_suppl_y = $interieur_hauteur*0.05;
    $interieur_largeur_reste = $interieur_largeur*0.8;
    $interieur_hauteur_reste = $interieur_hauteur*0.8;
    $lignes_adresse_nb = count($tab_adresse);
    $ligne_hauteur_reste = min( 4 , $interieur_hauteur_reste/$lignes_adresse_nb );
    $taille_police = $ligne_hauteur_reste*2.5 ;
    $marge_centrage_y = ( $interieur_hauteur_reste - $ligne_hauteur_reste*$lignes_adresse_nb ) / 2 ;
    $this->SetXY( $interieur_coin_hg_x+$marge_suppl_x , $interieur_coin_hg_y+$marge_suppl_y+$marge_centrage_y );
    $this->SetFont('Arial' , '' , $taille_police);
    foreach($tab_adresse as $ligne_adresse)
    {
      $this->CellFit( $interieur_largeur_reste , $ligne_hauteur_reste , To::pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    $bloc_hauteur = $exterieur_coin_bd_y - $this->marge_haut ;
    $bloc_gauche_largeur_restante = $exterieur_coin_hg_x - $this->marge_gauche - 2 ;
    return array($bloc_hauteur,$bloc_gauche_largeur_restante) ;
  }

  private function officiel_bloc_appreciation_intermediaire( $tab_saisie , $bloc_largeur , $ligne_hauteur , $bilan_type , $nb_caracteres_maxi , $cadre_hauteur=0 )
  {
    // Récupération des données des appréciations
    if($bilan_type!='bulletin')
    {
      $nb_lignes_appreciation_potentielle_par_prof_hors_intitule = ($nb_caracteres_maxi<250) ? 1 : 2 ;
    }
    else
    {
      $nb_lignes_appreciation_potentielle_par_prof_hors_intitule = $nb_caracteres_maxi / 100 / 2 ;
    }
    $nb_lignes_prevues = 0;
    $texte = '';
    $nb_saisies = count($tab_saisie);
    foreach($tab_saisie as $prof_id => $tab)
    {
      extract($tab);  // $prof_info $appreciation $note
      $nom_auteur = ($nb_saisies==1) ? '' : '[ '.$prof_info.' ] ' ; // associer le nom de l'auteur avec l'appréciation si plusieurs appréciations pour une même rubrique
      $texte .= str_replace( array("\r\n","\r","\n") , ' ' , $nom_auteur.$appreciation )."\r\n";
      $nb_lignes_prevues += $nb_lignes_appreciation_potentielle_par_prof_hors_intitule;
    }
    // Intitulé "Appréciations / Conseils :" + auteurs
    $hauteur_ligne_auteurs = $ligne_hauteur*0.8;
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->Write( $hauteur_ligne_auteurs , To::pdf('Appréciations / Conseils') );
    if($nb_saisies==1) // mettre le nom de l'auteur en tête si plusieurs appréciations pour une même rubrique
    {
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Write( $hauteur_ligne_auteurs , To::pdf('   [ '.$prof_info.' ]') );
    }
    $this->SetXY( $memoX , $memoY+$hauteur_ligne_auteurs );
    // cadre appréciations : affichage
    $largeur_autorisee = $bloc_largeur;
    $hauteur_autorisee = ($bilan_type!='bulletin') ? $ligne_hauteur*$nb_lignes_prevues : $cadre_hauteur-$hauteur_ligne_auteurs ;
    $taille_police = $this->taille_police*1.2;
    $taille_interligne = $ligne_hauteur*0.8;
    $this->afficher_appreciation( $largeur_autorisee , $hauteur_autorisee , $taille_police , $taille_interligne , $texte );
  }

  private function officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $bloc_largeur , $ligne_hauteur , $moyenne_generale_eleve , $moyenne_generale_classe )
  {
    $nb_lignes_appreciation_generale_hors_intitule = $nb_lignes_appreciation_generale_avec_intitule - 1 ;
    // Récupération des données de l'appréciation
    extract($tab_infos);  // $prof_info $appreciation $note
    $appreciation_sans_br = str_replace( array("\r\n","\r","\n") , ' ' , $appreciation , $nombre_br );
    $appreciation = ($nombre_br<$nb_lignes_appreciation_generale_hors_intitule) ? $appreciation : $appreciation_sans_br ;
    // Intitulé "Appréciation générale"
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
    $this->Write( $ligne_hauteur , To::pdf('Appréciation générale') );
    if($prof_info)
    {
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->Write( $ligne_hauteur , To::pdf('   [ '.$prof_info.' ]') );
    }
    // Moyenne générale éventuelle (élève & classe)
    $this->SetXY( $memoX , $memoY );
    $largeur = $this->page_largeur_moins_marges ;
    if($moyenne_generale_eleve!==NULL)
    {
      $largeur_note = 10;
      $texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne générale élève (classe) :' : 'Moyenne générale élève :' ;
      $this->SetFont('Arial' , '' , $this->taille_police);
      $largueur_texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $largeur-2*$largeur_note : $largeur-$largeur_note ;
      $this->Cell( $largueur_texte , $ligne_hauteur , To::pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      $moyenne_generale_eleve = ($moyenne_generale_eleve!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_generale_eleve,1,',','') : round($moyenne_generale_eleve*5).'%' ) : '-' ;
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
      $this->Cell( $largeur_note , $ligne_hauteur , To::pdf($moyenne_generale_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
      if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
      {
        $moyenne_generale_classe = ($moyenne_generale_classe!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($moyenne_generale_classe,1,',','') : round($moyenne_generale_classe*5).'%' ) : '-' ;
        $this->SetFont('Arial' , '' , $this->taille_police*0.8);
        $this->Cell( $largeur_note , $ligne_hauteur , To::pdf('('.$moyenne_generale_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
      }
    }
    $this->SetXY( $memoX , $memoY+$ligne_hauteur );
    // préparation cadre appréciation
    $largeur_autorisee = $bloc_largeur;
    $hauteur_autorisee = $ligne_hauteur * $nb_lignes_appreciation_generale_hors_intitule;
    $memoX = $this->GetX();
    $memoY = $this->GetY();
    // signature
    $largeur_signature = ($tab_image_tampon_signature) ? $this->afficher_image( $largeur_autorisee , $hauteur_autorisee , $tab_image_tampon_signature , 'signature' ) : $hauteur_autorisee ;
    // contour cadre
    $this->SetXY($memoX,$memoY);
    $this->Cell( $largeur_autorisee , $hauteur_autorisee , '' , 1 /*bordure*/ , 2 /*br*/ , '' /*alignement*/ , FALSE /*remplissage*/ );
    // contenu appréciation
    $this->SetXY($memoX,$memoY);
    if($prof_id)
    {
      $taille_police = $this->taille_police*1.2;
      $taille_interligne = $ligne_hauteur*0.8;
      $this->afficher_appreciation( $largeur_autorisee-$largeur_signature , $hauteur_autorisee , $taille_police , $taille_interligne , $appreciation );
    }
    $this->SetXY($memoX,$memoY+$hauteur_autorisee);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un releve d'attestation de socle commun
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // releve_socle_initialiser()
  // releve_socle_entete()
  // releve_socle_premiere_page()
  // releve_socle_rappel_eleve_page()
  // releve_socle_pilier()
  // releve_socle_section()
  // releve_socle_item()
  // releve_socle_appreciation_rubrique()
  // releve_socle_appreciation_generale()
  // releve_socle_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function releve_socle_initialiser($test_affichage_Pourcentage,$test_affichage_Validation)
  {
    $this->pourcentage_largeur = 25; // valeur fixe
    $this->validation_largeur  = 15; // valeur fixe
    $this->retrait_pourcentage = ( $test_affichage_Pourcentage ) ? $this->pourcentage_largeur : 0;
    $retrait_validation        = ( $test_affichage_Validation )  ? $this->validation_largeur  : 0;
    $this->item_largeur        = $this->page_largeur_moins_marges - $this->retrait_pourcentage - $retrait_validation;
    $this->section_largeur     = $this->item_largeur;
    $this->pilier_largeur      = $this->section_largeur - $retrait_validation;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->SetAutoPageBreak(FALSE);
  }

  public function releve_socle_entete($tab_infos_entete,$break,$eleve_id,$eleve_nom,$eleve_prenom,$eleve_nb_lignes)
  {
    $this->eleve_id     = $eleve_id;
    $this->eleve_nom    = $eleve_nom;
    $this->eleve_prenom = $eleve_prenom;
    // On prend une nouvelle page PDF pour chaque élève en cas d'affichage d'un palier avec tous les piliers ; pour un seul pilier, on étudie la place restante... tout en forçant une nouvelle page pour le 1er élève
    if( ($break==FALSE) || ($this->GetY()==0) )
    {
      $this->releve_socle_premiere_page();
      if($break==FALSE)
      {
        if($this->officiel)
        {
          // Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cet entête.
          list( $tab_etabl_coords , $tab_etabl_logo , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
          $this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
          // Bloc adresse en positionnement contraint
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
          {
            list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
            $this->SetXY($this->marge_gauche,$this->marge_haut);
          }
          // Bloc établissement
          $bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
          $bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$tab_etabl_logo,$bloc_etabl_largeur);
          // Bloc titres
          $alerte_archive = ($tab_adresse==='archive') ? TRUE : FALSE ;
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
          {
            // En dessous du bloc établissement
            $bloc_titre_largeur = $bloc_etabl_largeur ;
            $this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
            $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
            $bloc_gauche_hauteur = $bloc_etabl_hauteur + 2 + $bloc_titre_hauteur + 2 ;
          }
          else
          {
          // En haut à droite, modulo la place pour le texte indiquant le nombre de pages
            $bloc_titre_largeur = 100;
            $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut+4 );
            $bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur+4);
            $bloc_gauche_hauteur = $bloc_etabl_hauteur ;
            $bloc_droite_hauteur = $bloc_titre_hauteur ; // temporaire, au cas où il n'y aurait pas d'adresse à ajouter
          }
          // Tag date heure initiales (sous le bloc titres dans toutes les situations)
          $this->officiel_ligne_tag($tag_date_heure_initiales,$bloc_titre_largeur);
          // Bloc adresse en positionnement libre
          if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_libre') )
          {
            $bloc_adresse_largeur = $bloc_titre_largeur;
            $this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_adresse_largeur , $this->marge_haut+$bloc_titre_hauteur+2 );
            $bloc_adresse_hauteur = $this->officiel_bloc_adresse_position_libre($tab_adresse,$bloc_adresse_largeur);
            $bloc_droite_hauteur = $bloc_titre_hauteur + $bloc_adresse_hauteur ;
          }
          $hauteur_entete = max($bloc_gauche_hauteur,$bloc_droite_hauteur);
        }
        else
        {
          $hauteur_entete = 3*4.5 ; // HG L1 intitulé L2 palier-pilier ; HD L1 structure L2 élève
        }
        // On optimise la hauteur de ligne pour limiter le nombre de pages si possible dans le cas d'un palier avec tous les piliers.
        $hauteur_ligne_maximale   = 5;
        $hauteur_dispo_par_page   = $this->page_hauteur_moins_marges ;
        $hauteur_ligne_moyenne    = 4.5;
        $lignes_nb                = ( $hauteur_entete / 4.5 ) + $eleve_nb_lignes + ($this->legende*2) + 2 ; // entete + lignes dont résumés + légendes + marge
        $lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
        $nb_page_moyen            = $lignes_nb / $lignes_nb_moyen_par_page ;
        /*
        $nb_page_pleines          = floor($nb_page_moyen) ;
        $prop_last_page           = $nb_page_moyen - $nb_page_pleines ;
        if( ($nb_page_pleines==1) && ($prop_last_page<0.25) )
        {
          $nb_page_calcule = $nb_page_pleines - 0.1 ;
        }
        elseif( ($nb_page_pleines>1) && ($prop_last_page<0.5) )
        {
          $nb_page_calcule = $nb_page_pleines - 0.2 ;
        }
        elseif( ($nb_page_pleines>1) && ($prop_last_page>0.8) )
        {
          $nb_page_calcule = $nb_page_pleines + 0.8 ;
        }
        else
        {
          $nb_page_calcule = max(0.9,$nb_page_moyen) ;
        }
        */
        $nb_page_calcule = $nb_page_moyen*0.9; // Pour tenter de compenser la place perdue à cause des blocs par pilier
        $lignes_nb_calcule_par_page = $lignes_nb / $nb_page_calcule ;
        $hauteur_ligne_calcule      = $hauteur_dispo_par_page / $lignes_nb_calcule_par_page ;
        $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
        $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
        $this->cases_hauteur  = $this->lignes_hauteur;
        // On s'occupe aussi maintenant de la taille de la police
        $this->taille_police  = $this->lignes_hauteur * 1.5 ; // 4mm de hauteur par ligne donne une taille de 6
      }
      else
      {
        $this->cases_hauteur  = 4.5;
        $this->lignes_hauteur = $this->cases_hauteur;
        $this->taille_police  = 6;
      }
      if($this->officiel)
      {
        $this->SetXY($this->marge_gauche,$this->marge_haut+$hauteur_entete);
      }
      else
      {
        $this->SetXY($this->marge_gauche,$this->marge_haut);
      }
    }
    else
    {
      $hauteur_requise  = $this->cases_hauteur * ($eleve_nb_lignes + 2 + 0.5 + 1); // titres + marge + interligne
      $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
      if($hauteur_requise > $hauteur_restante)
      {
        $this->releve_socle_premiere_page();
      }
      else
      {
        $this->SetXY($this->marge_gauche,$this->GetY()+$this->cases_hauteur);
      }
    }
    if(!$this->officiel)
    {
      list( $titre , $palier_nom ) = $tab_infos_entete;
      $this->doc_titre = $titre.' - '.$palier_nom;
      // Intitulé
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , To::pdf($titre)      , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , To::pdf($palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      // Structure + Nom / prénom ; on met le document au nom de l'élève ou on établit un document générique
      if(!$this->eleve_id)
      {
        $this->choisir_couleur_trait('gris_moyen');
        $this->SetLineWidth(0.1);
        $this->Line($this->page_largeur-$this->marge_droite-75 , $this->marge_haut+2*$this->cases_hauteur , $this->page_largeur-$this->marge_droite , $this->marge_haut+2*$this->cases_hauteur);
        $this->choisir_couleur_trait('noir');
      }
      $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
      $this->SetXY($this->page_largeur-$this->marge_droite-50 , max($this->marge_haut,$this->GetY()-2*$this->cases_hauteur) ); // Soit c'est une nouvelle page, soit il ne faut pas se mettre en haut de la page
      $this->Cell(50 , $this->cases_hauteur , To::pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
      $this->Cell(50 , $this->cases_hauteur , To::pdf($this->eleve_nom.' '.$this->eleve_prenom)   , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
    }
  }

  public function releve_socle_premiere_page()
  {
    $this->AddPage($this->orientation , 'A4');
    $this->page_numero_first = $this->page;
    $this->choisir_couleur_texte('gris_fonce');
    $this->SetFont('Arial' , 'B' , 7);
    $this->Cell( $this->page_largeur_moins_marges , 4 /*ligne_hauteur*/ , To::pdf('Page 1/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY($this->marge_gauche,$this->marge_haut);
  }

  public function releve_socle_rappel_eleve_page()
  {
    $info_identite = ($this->eleve_id) ? ' - '.$this->eleve_nom.' '.$this->eleve_prenom : '' ;
    $this->AddPage($this->orientation , 'A4');
    $page_numero = $this->page - $this->page_numero_first + 1 ;
    $this->SetFont('Arial' , 'B' , 7);
    $this->choisir_couleur_texte('gris_fonce');
    $this->Cell( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($this->doc_titre.$info_identite.' - Page '.$page_numero.'/'.$this->page_nombre_alias) , 0 /*bordure*/ , 1 /*br*/ , $this->page_nombre_alignement , FALSE /*remplissage*/ );
    $this->choisir_couleur_texte('noir');
    $this->SetXY( $this->marge_gauche+$this->retrait_pourcentage , $this->GetY()+1 );
  }

  public function releve_socle_pilier($pilier_nom,$pilier_nb_lignes,$test_affichage_Validation,$tab_pilier_validation,$drapeau_langue)
  {
    $this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur);
    $hauteur_requise = $this->cases_hauteur * $pilier_nb_lignes;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->releve_socle_rappel_eleve_page();
    }
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
    $this->choisir_couleur_fond('gris_moyen');
    $br = $test_affichage_Validation ? 0 : 1 ;
    $this->CellFit( $this->pilier_largeur , $this->cases_hauteur , To::pdf($pilier_nom) , 1 , $br , 'L' , TRUE , '');
    if($test_affichage_Validation)
    {
      $this->afficher_etat_validation('B',$tab_pilier_validation);
    }
    if($drapeau_langue)
    {
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()+$this->pilier_largeur-$this->cases_hauteur-0.5,$this->GetY()-$this->cases_hauteur,$this->cases_hauteur,$this->cases_hauteur,'GIF');
    }
  }

  public function releve_socle_section($section_nom)
  {
    $this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY());
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->choisir_couleur_fond('gris_moyen');
    $this->CellFit( $this->section_largeur , $this->cases_hauteur , To::pdf($section_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
  }

  public function releve_socle_item($item_nom,$test_affichage_Pourcentage,$tab_item_pourcentage,$test_affichage_Validation,$tab_item_validation)
  {
    // Case pourcentage
    if($test_affichage_Pourcentage)
    {
      $this->afficher_pourcentage_acquis( '' , $tab_item_pourcentage , 'detail' /*affich*/ );
    }
    // Case intitulé
    $this->choisir_couleur_fond('gris_clair');
    $this->SetFont('Arial' , '' , $this->taille_police);
    $br = $test_affichage_Validation ? 0 : 1 ;
    $this->CellFit( $this->item_largeur , $this->cases_hauteur , To::pdf($item_nom) , 1 /*bordure*/ , $br , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    // Case validation
    if($test_affichage_Validation)
    {
      $this->afficher_etat_validation('',$tab_item_validation);
    }
  }

  public function releve_socle_appreciation_rubrique($tab_saisie)
  {
    $this->SetXY( $this->marge_gauche + $this->retrait_pourcentage , $this->GetY() );
    $this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->item_largeur , $this->cases_hauteur , 'socle' , $_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE'] );
  }

  public function releve_socle_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite_et_message_et_legende )
  {
    $this->SetXY( $this->marge_gauche + $this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur );
    $hauteur_requise = $this->lignes_hauteur * ( $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite_et_message_et_legende ) ;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      // Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
      $this->releve_socle_rappel_eleve_page();
    }
    $this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->item_largeur , $this->cases_hauteur , NULL /*moyenne_generale_eleve*/ , NULL /*moyenne_generale_classe*/ );
  }

  public function releve_socle_legende($test_affichage_Pourcentage,$test_affichage_Validation)
  {
    if($test_affichage_Pourcentage)
    {
      $ordonnee = ($test_affichage_Validation) ? $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1 ;
      $this->afficher_legende( 'pourcentage_acquis' /*type_legende*/ , $ordonnee /*ordonnée*/ );
    }
    if($test_affichage_Validation)
    {
      $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1 ;
      $this->afficher_legende( 'etat_validation' /*type_legende*/ , $ordonnee /*ordonnée*/ );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'une synthèse des validations du socle commun
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // releve_synthese_socle_initialiser()
  // releve_synthese_socle_entete()
  // releve_synthese_socle_validation_eleve()
  // releve_synthese_socle_pourcentage_eleve()
  // releve_synthese_socle_legende()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function releve_synthese_socle_initialiser($titre_info,$groupe_nom,$palier_nom,$eleves_nb,$items_nb,$piliers_nb)
  {
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->eleve_largeur  = 40; // valeur fixe
    $this->cases_largeur  = ($this->page_largeur_moins_marges - $this->eleve_largeur - $piliers_nb) / ($items_nb); // - intercolonne de 1 * nb piliers
    $this->cases_hauteur  = ($this->page_hauteur_moins_marges - $this->taille_police - $eleves_nb - $this->legende*5) / ($eleves_nb+1); // - titre de 5 - ( interligne de 1 * nb élèves ) - legende
    $this->cases_hauteur  = min($this->cases_hauteur,10);
    $this->lignes_hauteur = $this->cases_hauteur;
    $this->taille_police = 8;
    // Intitulés
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
    $this->Cell(0 , $this->taille_police , To::pdf('Synthèse de maîtrise du socle : '.$titre_info.' - '.$groupe_nom.' - '.$palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }

  public function releve_synthese_socle_entete($tab_pilier)
  {
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->SetXY($this->marge_gauche+$this->eleve_largeur,$this->marge_haut+$this->taille_police);
    $this->choisir_couleur_fond('gris_fonce');
    foreach($tab_pilier as $tab)
    {
      extract($tab);  // $pilier_ref $pilier_nom $pilier_nb_entrees
      $texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
      $this->SetX( $this->GetX()+1 );
      $this->Cell($pilier_nb_entrees*$this->cases_largeur , $this->cases_hauteur , To::pdf($texte.$pilier_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    // positionnement pour la suite
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
  }

  public function releve_synthese_socle_validation_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_user_pilier,$tab_user_entree,$tab_pilier,$tab_socle,$drapeau_langue)
  {
    $this->choisir_couleur_fond('gris_moyen');
    $this->CellFit( $this->eleve_largeur , $this->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    if($drapeau_langue)
    {
      $taille_image = min($this->cases_hauteur,5);
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()-$taille_image-0.5,$this->GetY(),$taille_image,$taille_image,'GIF');
    }
    $demi_hauteur = $this->cases_hauteur / 2 ;
    // - - - - -
    // Indication des compétences validées
    // - - - - -
    // Pour chaque pilier...
    foreach($tab_pilier as $pilier_id => $tab)
    {
      extract($tab);  // $pilier_ref $pilier_nom $pilier_nb_entrees
      $texte = ( ($this->couleur=='non') && ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==2) ) ? '-' : '' ;
      $this->SetX( $this->GetX()+1 );
      $this->choisir_couleur_fond($this->tab_choix_couleur['v'.$tab_user_pilier[$eleve_id][$pilier_id]['etat']]);
      $this->Cell($pilier_nb_entrees*$this->cases_largeur , $demi_hauteur , $texte , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche+$this->eleve_largeur , $this->GetY()+$demi_hauteur );
    // - - - - -
    // Indication des items validés
    // - - - - -
    // Pour chaque entrée du socle...
    foreach($tab_socle as $pilier_id => $tab)
    {
      $this->SetX( $this->GetX()+1 );
      foreach($tab as $socle_id => $socle_nom)
      {
        $texte = ( ($this->couleur=='non') && ($tab_user_pilier[$eleve_id][$pilier_id]['etat']!=1) && ($tab_user_entree[$eleve_id][$socle_id]['etat']==2) ) ? '-' : '' ;
        $couleur = ( ($tab_user_pilier[$eleve_id][$pilier_id]['etat']==1) && ($tab_user_entree[$eleve_id][$socle_id]['etat']==2) && (!$_SESSION['USER_DALTONISME']) ) ? 'gris_clair' : $this->tab_choix_couleur['v'.$tab_user_entree[$eleve_id][$socle_id]['etat']] ;
        $this->choisir_couleur_fond($couleur);
        $this->Cell( $this->cases_largeur , $demi_hauteur , $texte , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      }
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche , $this->GetY()+$demi_hauteur+1 );
  }

  public function releve_synthese_socle_pourcentage_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_score_socle_eleve,$tab_socle,$drapeau_langue)
  {
    $this->pourcentage_largeur = $this->cases_largeur;
    $this->choisir_couleur_fond('gris_moyen');
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->eleve_largeur , $this->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    if($drapeau_langue)
    {
      $taille_image = min($this->cases_hauteur,5);
      $this->Image('./_img/drapeau/'.$drapeau_langue.'.gif',$this->GetX()-$taille_image-0.5,$this->GetY(),$taille_image,$taille_image,'GIF');
    }
    // - - - - -
    // Indication des pourcentages
    // - - - - -
    // Pour chaque entrée du socle...
    foreach($tab_socle as $pilier_id => $tab)
    {
      $this->SetX( $this->GetX()+1 );
      foreach($tab as $socle_id => $socle_nom)
      {
        $this->afficher_pourcentage_acquis( '' , $tab_score_socle_eleve[$socle_id][$eleve_id] , 'pourcentage' /*affich*/ );
      }
    }
    // positionnement pour la suite
    $this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
  }

  public function releve_synthese_socle_legende($type)
  {
    if($this->legende)
    {
      $ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 ;
      $type_legende = ($type=='pourcentage') ? 'pourcentage_acquis' : 'etat_validation' ;
      $this->afficher_legende( $type_legende , $ordonnee );
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un bilan de synthèse d'un groupe sur une période
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // bilan_periode_synthese_initialiser()
  // bilan_periode_synthese_entete()
  // bilan_periode_synthese_pourcentages()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function bilan_periode_synthese_initialiser($eleve_nb,$item_nb,$tableau_tri_objet)
  {
    $hauteur_entete = 10;
    $intitule_facteur  = ($tableau_tri_objet=='eleve') ? 4 : 3 ;
    $etiquette_facteur = ($tableau_tri_objet=='item')  ? 4 : 3 ;
    $colonnes_nb = ($tableau_tri_objet=='eleve') ? $item_nb : $eleve_nb ;
    $lignes_nb   = ($tableau_tri_objet=='item')  ? $item_nb : $eleve_nb ;
    $this->cases_largeur     = ($this->page_largeur_moins_marges - 2) / ($colonnes_nb+2+$intitule_facteur); // -2 pour une petite marge ; 2 colonnes ajoutées + identité/item
    $this->intitule_largeur  = $intitule_facteur  * $this->cases_largeur;
    $this->taille_police     = $this->cases_largeur*0.8;
    $this->taille_police     = min($this->taille_police,10); // pas plus de 10
    $this->taille_police     = max($this->taille_police,5);  // pas moins de 5
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - 2 - $hauteur_entete) / ($lignes_nb+2+$etiquette_facteur); // -2 pour une petite marge - entête ; 2 lignes ajoutées + identité/item
    $this->etiquette_hauteur = $etiquette_facteur * $this->cases_hauteur;
    $this->cases_hauteur     = min($this->cases_hauteur,10); // pas plus de 10
    $this->cases_hauteur     = max($this->cases_hauteur,3);  // pas moins de 3
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function bilan_periode_synthese_entete($titre_nom,$matiere_et_groupe,$texte_periode)
  {
    $hauteur_entete = 10;
    // Intitulé
    $this->SetFont('Arial' , 'B' , 10);
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , To::pdf('Bilan '.$titre_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , To::pdf($matiere_et_groupe)  , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // Synthèse
    $this->SetXY($this->page_largeur-$this->marge_droite-50 , $this->marge_haut);
    $this->Cell(20 , 4 , To::pdf('SYNTHESE') , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    // Période
    $this->SetFont('Arial' , '' , 8);
    $this->Cell( $this->page_largeur-$this->marge_gauche-$this->marge_droite , 4 , To::pdf($texte_periode) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
    // On se positionne sous l'entête
    $this->SetXY($this->marge_gauche , $this->marge_haut+$hauteur_entete);
    $this->SetFont('Arial' , '' , $this->taille_police);
  }

  public function bilan_periode_synthese_pourcentages($moyenne_pourcent,$moyenne_nombre,$last_ligne,$last_colonne)
  {
    // $last_ligne = TRUE si on veut afficher les deux dernières lignes
    // $last_colonne = TRUE si on veut afficher les deux dernières colonnes
    // si $last_ligne = $last_colonne = TRUE alors ce sont les deux dernières cases en diagonale

    // sauter 2mm pour la dernière colonne ; pour la ligne cela a déjà été fait avec l'étiquette de ligne
    if($last_colonne)
    {
      $this->SetX( $this->GetX()+2 );
    }
    // pour la dernière ligne, mais pas pour les 2 dernières cases, mémoriser l'ordonnée pour s'y repositionner à la fin
    elseif($last_ligne)
    {
      $memo_y = $this->GetY();
    }

    // aller vers le bas ou vers la droite après la 1ère case 
    $direction_after_case1 = ($last_ligne) ? 2 : 0;
    // aller à la ligne ou vers la droite après la 2ème case 
    $direction_after_case2 = ($last_colonne) ? 1 : 0;

    // première case
    if($moyenne_pourcent===FALSE)
    {
      $this->choisir_couleur_fond('blanc');
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , $direction_after_case1 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    else
    {
          if($moyenne_pourcent<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
      elseif($moyenne_pourcent>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
      else                                                     {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
      $score_affiche = test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_BILAN']) ? $moyenne_pourcent.'%' : '' ;
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 /*bordure*/ , $direction_after_case1 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }

    // pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
    if($last_colonne && $last_ligne)
    {
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    }

    // deuxième case
    if($moyenne_pourcent===FALSE)
    {
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 /*bordure*/ , $direction_after_case2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    else
    {
          if($moyenne_nombre<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
      elseif($moyenne_nombre>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
      else                                                   {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
      $score_affiche = test_user_droit_specifique($_SESSION['DROIT_VOIR_SCORE_BILAN']) ? $moyenne_nombre.'%' : '' ;
      $this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 /*bordure*/ , $direction_after_case2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }

    // pour la dernière ligne, mais pas pour les 2 dernières cases, se repositionner à la bonne ordonnée
    if($last_ligne && !$last_colonne)
    {
      $memo_x = $this->GetX();
      $this->SetXY($memo_x , $memo_y);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un tableau vierge de saisie d'évaluation
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // tableau_saisie_initialiser()
  // tableau_saisie_reference_devoir()
  // tableau_saisie_reference_eleve()
  // tableau_saisie_reference_item()
  // tableau_devoir_repartition_quantitative_initialiser()
  // tableau_devoir_repartition_nominative_initialiser()
  // tableau_devoir_repartition_nominative_entete()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function tableau_saisie_initialiser($eleve_nb,$item_nb)
  {
    $reference_largeur_minimum = 50;
    $cases_hauteur_maximum     = 25;
    $this->cases_largeur     = 10; // valeur par défaut ; diminué si pas assez de place pour la référence de l'item
    $this->etiquette_hauteur = 40; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - ($eleve_nb * $this->cases_largeur);
    if($this->reference_largeur < $reference_largeur_minimum)
    {
      $this->reference_largeur = $reference_largeur_minimum;
      $this->cases_largeur     = ($this->page_largeur_moins_marges - $this->reference_largeur) / $eleve_nb;
    }
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $item_nb;
    $this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description)
  {
    $hauteur_tiers = $this->etiquette_hauteur / 3 ;
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($groupe_nom)  , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($date_fr)     , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_tiers , To::pdf($description) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->Cell( $this->reference_largeur , $this->etiquette_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  }

  public function tableau_saisie_reference_eleve($texte)
  {
    $this->choisir_couleur_fond('gris_clair');
    $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($texte), 1 /*border*/ , 0 /*ln*/ , TRUE /*fill*/ );
  }

  public function tableau_saisie_reference_item($item_intro,$item_nom)
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->choisir_couleur_fond('gris_clair');
    $this->Cell( $this->reference_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    $this->SetXY($memo_x , $memo_y+1);
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , 3 , To::pdf($item_intro) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->MultiCell( $this->reference_largeur , 3 , To::pdf($item_nom) , 0 /*bordure*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un bilan d'un devoir : répartition quantitative ou nominative
  // tableau_devoir_repartition_quantitative_initialiser() tableau_devoir_repartition_nominative_initialiser() tableau_devoir_repartition_nominative_entete()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function tableau_devoir_repartition_quantitative_initialiser($item_nb)
  {
    $cases_hauteur_maximum   = 20;
    $this->cases_largeur     = 20; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - (4 * $this->cases_largeur);
    $this->etiquette_hauteur = 10; // valeur fixe
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $item_nb;
    $this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->etiquette_hauteur);
  }

  public function tableau_devoir_repartition_nominative_initialiser($lignes_nb)
  {
    $this->cases_largeur     = 35; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - (4 * $this->cases_largeur);
    $this->etiquette_hauteur = 10; // valeur fixe
    $lignes_hauteur_maximum  = 5;
    $this->lignes_hauteur    = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $lignes_nb;
    $this->lignes_hauteur    = min($this->lignes_hauteur,$lignes_hauteur_maximum);
    $this->lignes_hauteur    = max($this->cases_hauteur,3.5); // pas moins de 3,5
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->etiquette_hauteur);
  }

  public function tableau_devoir_repartition_nominative_entete($groupe_nom,$date_fr,$description,$tab_init_quantitatif,$tab_repartition_quantitatif)
  {
    // on calcule la hauteur de la case
    $this->cases_hauteur = $this->lignes_hauteur * max(4,max($tab_repartition_quantitatif));
    // On prend une nouvelle page PDF si besoin et y remettre la ligne d'entête si y a pas assez de place
    if($this->GetY()>$this->marge_haut)
    {
      // on regarde s'il y a la place
      $hauteur_requise = $this->cases_hauteur;
      $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
      if($hauteur_requise > $hauteur_restante)
      {
        $this->AddPage($this->orientation , 'A4');
      }
    }
    // 1ère ligne : référence des codes
    if($this->GetY()==$this->marge_haut)
    {
      $this->tableau_saisie_reference_devoir($groupe_nom,$date_fr,$description);
      $this->SetXY($this->marge_gauche+$this->reference_largeur , $this->marge_haut);
      foreach($tab_init_quantitatif as $note=>$vide)
      {
        $this->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ );
      }
      $this->SetXY($this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur);
    }
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un cartouche
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // cartouche_initialiser()
  // cartouche_entete()
  // cartouche_minimal_competence()
  // cartouche_complet_competence()
  // cartouche_interligne()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function cartouche_initialiser($detail,$item_nb)
  {
    $this->cases_largeur     = ($detail=='minimal') ? ($this->page_largeur_moins_marges) / $item_nb : 10 ;
    $this->cases_hauteur     = 5 ;
    $this->reference_largeur = 15 ;
    $this->intitule_largeur  = ($detail=='minimal') ? 0 : $this->page_largeur_moins_marges - $this->reference_largeur - $this->cases_largeur ;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
  }

  public function cartouche_entete($texte_entete,$lignes_nb)
  {
    // On prend une nouvelle page PDF si besoin
    $hauteur_requise = $this->cases_hauteur * $lignes_nb;
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    if($hauteur_requise > $hauteur_restante)
    {
      $this->AddPage($this->orientation , 'A4');
    }
    // Intitulé
    $this->SetFont('Arial' , '' , 10);
    $this->Cell(0 , $this->cases_hauteur , To::pdf($texte_entete) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetFont('Arial' , '' , 8);
  }

  public function cartouche_minimal_competence($item_ref,$note)
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
    $this->SetFont('Arial' , '' , 7);
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , To::pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , To::pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetFont('Arial' , '' , 8);
    $this->SetXY($memo_x , $memo_y);
    $this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ );
    $this->SetXY($memo_x+$this->cases_largeur , $memo_y);
  }

  public function cartouche_complet_competence($item_ref,$item_intitule,$note)
  {
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
    $this->SetFont('Arial' , '' , 7);
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , To::pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , To::pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetFont('Arial' , '' , 8);
    $this->SetXY($memo_x , $memo_y);
    $this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                         , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->intitule_largeur  , $this->cases_hauteur , To::pdf($item_intitule) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->afficher_note_lomer( $note , 1 /*border*/ , 1 /*br*/ );
  }

  public function cartouche_interligne($nb_lignes)
  {
    $this->SetXY($this->marge_gauche , $this->GetY() + $nb_lignes*$this->cases_hauteur);
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page de la récupération des appréciations d'un bilan officiel
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // tableau_appreciation_intitule()
  // tableau_appreciation_interligne()
  // tableau_appreciation_page_break()
  // tableau_appreciation_initialiser_*()
  // tableau_appreciation_rubrique_*()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function tableau_appreciation_intitule($intitule)
  {
    $this->taille_police = $this->lignes_hauteur * 2;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(FALSE);
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
    $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($intitule)  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
  }

  private function tableau_appreciation_page_break()
  {
    $hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
    $hauteur_requise = 3*$this->lignes_hauteur;
    if($hauteur_requise > $hauteur_restante)
    {
      $this->AddPage($this->orientation , 'A4');
    }
  }

  public function tableau_appreciation_initialiser_eleves_prof($nb_eleves,$nb_lignes_supplémentaires,$with_moyenne)
  {
    $this->reference_largeur = 40; // valeur fixe
    $note_largeur          = ($with_moyenne) ? 10 : 0 ; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2*$nb_eleves)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / app)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur - $note_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
    $this->choisir_couleur_fond('gris_clair');
  }

  public function tableau_appreciation_initialiser_eleves_collegues($nb_eleves,$nb_lignes_rubriques)
  {
    $this->reference_largeur = 40; // valeur fixe
    $nb_lignes_necessaires  = 2 + 1.5*$nb_eleves + $nb_lignes_rubriques ; // titre + élèves et marges (0.5 ligne / eleve) + rubriques avec appréciations
    $this->cases_largeur    = $this->page_largeur_moins_marges - $this->reference_largeur ;
    $hauteur_dispo_par_page = $this->page_hauteur_moins_marges;
    $hauteur_ligne_minimale = 3.5;
    $hauteur_ligne_maximale = 5;
    $nb_pages = 0;
    do
    {
      $nb_pages++;
      $hauteur_ligne_calcule = $nb_pages*$hauteur_dispo_par_page / $nb_lignes_necessaires ;
    }
    while($hauteur_ligne_calcule < $hauteur_ligne_minimale);
    $this->lignes_hauteur = floor($hauteur_ligne_calcule*10)/10 ; // round($hauteur_ligne_calcule,1,PHP_ROUND_HALF_DOWN) à partir de PHP 5.3
    $this->lignes_hauteur = min ( $this->lignes_hauteur , $hauteur_ligne_maximale ) ;
  }

  public function tableau_appreciation_initialiser_classe_collegues($nb_eleves,$nb_rubriques,$nb_lignes_supplémentaires)
  {
    $this->reference_largeur = 40; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2.5*$nb_rubriques)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / rubrique + 0.5 de marge)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
  }

  public function tableau_appreciation_initialiser_eleves_syntheses($nb_eleves,$nb_lignes_supplémentaires,$with_moyenne)
  {
    $this->reference_largeur = 40; // valeur fixe
    $note_largeur          = ($with_moyenne) ? 10 : 0 ; // valeur fixe
    $nb_lignes_necessaires = 1.5 + (2*$nb_eleves)+$nb_lignes_supplémentaires ; // titre + appreciations (2 lignes mini / app)
    $this->cases_largeur   = $this->page_largeur_moins_marges - $this->reference_largeur - $note_largeur ;
    $this->lignes_hauteur  = ($this->page_hauteur_moins_marges) / $nb_lignes_necessaires;
    $this->lignes_hauteur  = min($this->lignes_hauteur,5);
    $this->choisir_couleur_fond('gris_clair');
  }

  public function tableau_appreciation_rubrique_eleves_prof($eleve_id,$eleve_nom,$eleve_prenom,$note,$appreciation,$with_moyenne)
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciation)/125) );
    $note_largeur = 10; // valeur fixe
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $remplissage = ($eleve_id) ? FALSE : TRUE ;
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , $remplissage );
    // nom-prénom
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2+(int)$remplissage)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($eleve_nom)    , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // moyenne
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    if($with_moyenne)
    {
      $moyenne_eleve = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      $this->CellFit( $note_largeur , $nb_lignes*$this->lignes_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    }
    else
    {
      $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+2*$this->lignes_hauteur );
    }
    // appréciation
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciation );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function tableau_appreciation_rubrique_eleves_collegues($eleve_id,$eleve_nom,$eleve_prenom,$rubrique_nom,$note,$appreciations,$with_moyenne)
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciations)/125) );
    // On prend une nouvelle page PDF si besoin
    $this->tableau_appreciation_page_break();
    $this->choisir_couleur_fond('gris_moyen');
    // nom-prénom
    if($eleve_nom && $eleve_prenom)
    {
      $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
      $this->SetFont('Arial' , '' , $this->taille_police);
      $this->CellFit( $this->page_largeur_moins_marges , $this->lignes_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
    }
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // rubrique + moyenne
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    if($with_moyenne)
    {
      $moyenne_eleve = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+$nb_lignes*$this->lignes_hauteur );
    // appréciations
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciations );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function tableau_appreciation_rubrique_classe_collegues($rubrique_nom,$note,$appreciations,$with_moyenne)
  {
    $nb_lignes = max( 2 , ceil(mb_strlen($appreciations)/125) );
    // marge
    $this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
    // cadre
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    $this->Cell( $this->page_largeur_moins_marges , $nb_lignes*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // rubrique + moyenne
    $this->SetXY($memo_x , $memo_y+($nb_lignes-2)*$this->lignes_hauteur/2);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($rubrique_nom)    , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    if($with_moyenne)
    {
      $moyenne_eleve = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
      $this->CellFit( $this->reference_largeur , $this->lignes_hauteur , To::pdf($moyenne_eleve) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    $this->Line( $memo_x+$this->reference_largeur , $memo_y , $memo_x+$this->reference_largeur , $memo_y+$nb_lignes*$this->lignes_hauteur );
    // appréciation
    $this->SetXY($memo_x+$this->reference_largeur , $memo_y);
    $this->afficher_appreciation( $this->cases_largeur , $nb_lignes*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciations );
    $this->SetXY($memo_x , $memo_y+$nb_lignes*$this->lignes_hauteur);
  }

  public function tableau_moyennes_initialiser($eleve_nb,$rubrique_nb)
  {
    $reference_largeur_minimum = 50;
    $cases_hauteur_maximum     = 25;
    $this->cases_largeur     = 10; // valeur par défaut ; diminué si pas assez de place pour la référence de l'item
    $this->etiquette_hauteur = 50; // valeur fixe
    $this->reference_largeur = $this->page_largeur_moins_marges - ($rubrique_nb * $this->cases_largeur);
    if($this->reference_largeur < $reference_largeur_minimum)
    {
      $this->reference_largeur = $reference_largeur_minimum;
      $this->cases_largeur     = ($this->page_largeur_moins_marges - $this->reference_largeur) / $rubrique_nb;
    }
    $this->cases_hauteur     = ($this->page_hauteur_moins_marges - $this->etiquette_hauteur) / $eleve_nb;
    $this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
  }

  public function tableau_moyennes_intitule( $classe_nom , $periode_nom )
  {
    $hauteur_quart = $this->etiquette_hauteur / 4 ;
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , 'B' , $this->taille_police);
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf('Bulletin scolaire')    , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf('Tableau des moyennes') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($classe_nom)            , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $this->reference_largeur , $hauteur_quart , To::pdf($periode_nom)           , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->SetXY($this->marge_gauche , $this->marge_haut);
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->Cell( $this->reference_largeur , $this->etiquette_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  }

  public function tableau_moyennes_reference_rubrique($rubrique_id,$rubrique_nom)
  {
    $couleur = ($rubrique_id) ? 'gris_clair' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    $this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, To::pdf($rubrique_nom), 1 /*border*/ , 0 /*ln*/ , TRUE /*fill*/ );
  }

  public function tableau_moyennes_reference_eleve($eleve_id,$eleve_nom_prenom)
  {
    $couleur = ($eleve_id) ? 'gris_clair' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    $this->CellFit( $this->reference_largeur , $this->cases_hauteur , To::pdf($eleve_nom_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
  }

  public function tableau_moyennes_note($eleve_id,$rubrique_id,$note)
  {
    $couleur = ($eleve_id && $rubrique_id) ? 'blanc' : 'gris_fonce' ;
    $this->choisir_couleur_fond($couleur);
    $moyenne_eleve = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
    $this->CellFit( $this->cases_largeur , $this->cases_hauteur , To::pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes pour la mise en page d'un trombinoscope
  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // trombinoscope_initialiser()
  // trombinoscope_vignette()
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  public function trombinoscope_initialiser($regroupement)
  {
    $this->taille_police = 10;
    $this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
    $this->AddPage($this->orientation , 'A4');
    $this->SetAutoPageBreak(TRUE);
    // Titre
    $this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
    $this->CellFit( $this->page_largeur_moins_marges , 7 , To::pdf($regroupement)  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    // Avertissement
    $this->SetFont('Arial' , '' , $this->taille_police*0.9);
    $this->SetX( $this->GetX() + 5 );
    $message = 'Le trombinoscope est privé (accessible aux seuls personnels habilités) et réservé à un usage pédagogique interne (il ne doit pas être transmis à un tiers), ceci quel que soit son support (numérique ou imprimé). Pour davantage d\'informations relatives au respect de la vie privée et au droit à l\'image, consulter la documentation correspondante (intégrée à l\'application et disponible sur internet).';
    $this->MultiCell( $this->page_largeur_moins_marges - 2*5 , 4 , To::pdf($message)  , 1 /*bordure*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // Next...
    $this->SetY( $this->GetY() + 2 );
    $this->SetFont('Arial' , '' , $this->taille_police);
    $this->photo_hauteur_maxi   = PHOTO_DIMENSION_MAXI*$this->coef_conv_pixel_to_mm;
    $this->cadre_photo_hauteur  = $this->photo_hauteur_maxi + 0.5 + 8 ; // 0.5 marge + 2x4mm pour lignes nom et prénom
  }

  public function trombinoscope_vignette($tab_vignette)
  {
    $espacement_x = 2;
    $espacement_y = 2;
    // On récupère les infos
    extract($tab_vignette); // $user_nom $user_prenom $img_width $img_height $img_src $img_title
    $img_width  *= $this->coef_conv_pixel_to_mm;
    $img_height *= $this->coef_conv_pixel_to_mm;
    // retour à la ligne si manque de place
    if( $this->GetX() + $img_width + $this->marge_droite > $this->page_largeur )
    {
      $this->SetXY( $this->marge_gauche , $this->GetY() + $this->cadre_photo_hauteur + $espacement_y );
      // saut de page si manque de place
      if( $this->GetY() + $this->cadre_photo_hauteur + $this->marge_bas > $this->page_hauteur )
      {
        $this->AddPage($this->orientation , 'A4');
      }
    }
    // image
    $memo_x = $this->GetX();
    $memo_y = $this->GetY();
    if($img_src)
    {
      $this->MemImage(base64_decode($img_src),$memo_x,$memo_y,$img_width,$img_height,'JPEG');
    }
    else
    {
      $this->Image('./_img/trombinoscope_vide.png',$memo_x,$memo_y,$img_width,$img_height,'PNG');
    }
    // nom & prénom
    $this->SetXY( $memo_x , $memo_y + $this->photo_hauteur_maxi + 0.5 );
    $this->CellFit( $img_width , 4 , To::pdf($user_nom)    , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    $this->CellFit( $img_width , 4 , To::pdf($user_prenom) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
    // positionnement pour la photo suivante
    $this->SetXY( $memo_x + $img_width + $espacement_x , $memo_y );
  }

}
?>
