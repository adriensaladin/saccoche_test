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
			return false;
		}
		$this->position = 0;
		return true;
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
			return true;
		}
		return false;
	}

	function stream_stat()
	{
		return array();
	}
}

// Extension de classe qui étend FPDF

class PDF extends FPDF
{

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour optimiser la gestion de la mémoire et éviter un « Fatal error : Allowed memory size ... »
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour ajuster l'étirement d'un texte dans une cellule en fonction de sa longueur
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour écrire un texte tourné
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Consulter  http://www.fpdf.org/fr/script/script31.php
 * Voir aussi http://www.fpdf.org/fr/script/script2.php
**/

/*
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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour savoir le nombre de lignes requises afin d'écrire un texte, et le découper en conséquence.
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour afficher des images chargées en mémoire sans avoir besoin de passer par un fichier temporaire.
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Pour tracer un cercle (ou une ellipse)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Attributs de la classe (équivalents des "variables")
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
	private $page_largeur  = 0;
	private $page_hauteur  = 0;
	private $marge_haut    = 5;
	private $marge_gauche  = 5;
	private $marge_droite  = 5;
	private $marge_bas     = 5;
	private $distance_pied = 0;
	// Conserver les informations de l'élève pour une recopie sur plusieurs pages
	private $eleve_id     = 0;
	private $eleve_nom    = '';
	private $eleve_prenom = '';
	private $doc_titre    = '';
	// Définition de qqs variables supplémentaires
	private $cases_nb             = 0;
	private $cases_largeur        = 0;
	private $cases_hauteur        = 0;
	private $lignes_hauteur       = 0;
	private $lignes_nb            = 0;
	private $reference_largeur    = 0;
	private $intitule_largeur     = 0;
	private $synthese_largeur     = 0;
	private $etiquette_hauteur    = 0;
	private $colonne_vide_largeur = 0;
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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Constructeur
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __construct($officiel,$orientation,$marge_gauche=5,$marge_droite=5,$marge_haut=5,$marge_bas=12,$couleur='oui',$legende='oui')
	{
		// Register var stream protocol => Voir MemImage()
		if (in_array('var', stream_get_wrappers()))
		{
			stream_wrapper_unregister('var');
		}
		stream_wrapper_register('var', 'VariableStream');
		// Appeler le constructeur de la classe mère
		parent::FPDF($orientation , $unit='mm' , $format='A4');
		// On passe à la classe fille
		$this->officiel    = $officiel;
		$this->orientation = $orientation;
		$this->couleur     = $couleur;
		$this->legende     = ($legende=='oui') ? 1 : 0 ;
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
		$this->tab_lettre['RR'] = $_SESSION['NOTE_TEXTE']['RR'];
		$this->tab_lettre['R']  = $_SESSION['NOTE_TEXTE']['R'];
		$this->tab_lettre['V']  = $_SESSION['NOTE_TEXTE']['V'];
		$this->tab_lettre['VV'] = $_SESSION['NOTE_TEXTE']['VV'];
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Pour récupérer un attribut private (c'est comme s'il était en lecture seule)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __get($nom)
	{
		return (isset($this->$nom)) ? $this->$nom : null ;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode Magique - Pour affecter une valeur à un attribut
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __set($nom,$valeur)
	{
			$this->$nom = $valeur;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour calculer les dimensions d'une image Lomer
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function calculer_dimensions_images($espace_largeur,$espace_hauteur)
	{
		$this->lomer_espace_largeur = $espace_largeur;
		$this->lomer_espace_hauteur = $espace_hauteur;
		// Une image a des dimensions initiales de 20px sur 10px
		$rapport_largeur = $espace_largeur / 20 ;
		$rapport_hauteur = $espace_hauteur / 10 ;
		$centrage     = ($rapport_largeur<$rapport_hauteur) ? 'hauteur' : 'largeur';
		$rapport_coef = ($centrage=='hauteur') ? $rapport_largeur : $rapport_hauteur ;
		$rapport_coef = min( floor($rapport_coef*10)/10 , 0.4 ) ;	// A partir de PHP 5.3 on peut utiliser l'option PHP_ROUND_HALF_DOWN de round()
		$this->lomer_image_largeur = floor(20*$rapport_coef) ;
		$this->lomer_image_hauteur = floor(10*$rapport_coef) ;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour choisir une couleur de fond ou une couleur de tracé ou une couleur de texte
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une note Lomer
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_note_lomer($note,$border,$br)
	{
		$this->choisir_couleur_fond('blanc');
		switch ($note)
		{
			case 'RR' :
			case 'R' :
			case 'V' :
			case 'VV' :
				if($this->couleur == 'oui')
				{
					$memo_x = $this->GetX();
					$memo_y = $this->GetY();
					$img_pos_x = $memo_x + ( ($this->lomer_espace_largeur - $this->lomer_image_largeur) / 2 ) ;
					$img_pos_y = $memo_y + ( ($this->lomer_espace_hauteur - $this->lomer_image_hauteur) / 2 ) ;
					$this->Image('./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/'.$note.'.gif',$img_pos_x,$img_pos_y,$this->lomer_image_largeur,$this->lomer_image_hauteur,'GIF');
					$this->SetXY($memo_x , $memo_y);
					$this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border , $br , 'C' , FALSE , '');
				}
				else
				{
					$this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur ,  $this->tab_lettre[$note] , $border , $br , 'C' , TRUE , '');
				}
				break;
			case 'ABS' :
			case 'NN' :
			case 'DISP' :
				$tab_texte = array('ABS'=>'Abs.','NN'=>'N.N.','DISP'=>'Disp.');
					$this->cMargin /= 2;
				$this->CellFit( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , $tab_texte[$note] , $border , $br , 'C' , TRUE , '');
					$this->cMargin *= 2;
				break;
			default :
				$this->Cell( $this->lomer_espace_largeur , $this->lomer_espace_hauteur , '' , $border , $br , 'C' , TRUE , '');
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un état de validation (date sur fond coloré)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_etat_validation($gras,$tab_infos)
{
	// $tab_infos contient 'etat' / 'date' / 'info'
	$this->SetFont('Arial' , $gras , $this->taille_police);
	$texte = ($tab_infos['etat']==2) ? '---' : $tab_infos['date'] ;
	$this->choisir_couleur_fond($this->tab_choix_couleur['v'.$tab_infos['etat']]);
	$this->Cell( $this->validation_largeur , $this->cases_hauteur , pdf($texte) , 1 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un pourcentage d'items acquis (texte A VA NA et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
			$this->CellFit( $this->pourcentage_largeur , $this->cases_hauteur , pdf($tab_infos['%'].'% acquis ('.$tab_infos['A'].$_SESSION['ACQUIS_TEXTE']['A'].' '.$tab_infos['VA'].$_SESSION['ACQUIS_TEXTE']['VA'].' '.$tab_infos['NA'].$_SESSION['ACQUIS_TEXTE']['NA'].')') , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		}
		elseif($affich=='pourcentage')
		{
			$this->SetFont('Arial' , $gras , $this->taille_police/2);
			$this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , pdf($tab_infos['%']) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		}
		elseif($affich=='rien')
		{
			$this->Cell( $this->pourcentage_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , '' /*alignement*/ , TRUE /*remplissage*/ );
		}
	}
}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher un score bilan (bilan sur 100 et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_score_bilan($score,$br)
	{
		if($score===FALSE)
		{
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? '-' : '' ;
			$this->choisir_couleur_fond('blanc');
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $br , 'C' , TRUE , '');
		}
		else
		{
					if($score<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($score>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                          {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $score : '' ;
			$this->SetFont('Arial' , '' , $this->taille_police-2);
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $br , 'C' , TRUE , '');
			$this->SetFont('Arial' , '' , $this->taille_police);
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une barre avec les états des items acquis (rectangles A VA NA et couleur de fond suivant le seuil)
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_proportion_acquis($largeur,$hauteur,$tab_infos,$total)
	{
		// $tab_infos contient 'A' / 'VA' / 'NA'
		$abscisse = $this->GetX();
		$ordonnee = $this->GetY();
		// Couleurs de fond + textes
		foreach($tab_infos as $etat => $nb)
		{
			$this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
			$largeur_case = $largeur*$nb/$total ;
			$texte_complet = $nb.' '.$_SESSION['ACQUIS_TEXTE'][$etat];
			$texte = (strlen($texte_complet)<$largeur_case) ? $texte_complet : $nb ;
			$this->CellFit($largeur_case , $hauteur , pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		}
		// Bordure unique autour
		$this->SetXY($abscisse , $ordonnee);
		$this->Cell($largeur , $hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une appréciation sur plusieurs lignes dans une zone de dimensions données
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_appreciation($largeur_autorisee,$hauteur_autorisee,$taille_police,$taille_interligne,$texte)
	{
		$this->SetFont('Arial' , '' , $taille_police);
		// Ajout d'espaces insécables judicieux et retrait d'espaces de mise en forme inappropriés
		$e = chr(0xC2).chr(0xA0); // espace insécable en UTF-8 (http://fr.wikipedia.org/wiki/Espace_ins%C3%A9cable ; http://fr.wikipedia.org/wiki/UTF-8)
		$tab_bad = array(   ' !' ,   ' ?' ,   ' :' ,   ' ;' ,   ' %' , ' .' , ' ,' );
		$tab_bon = array( $e.'!' , $e.'?' , $e.':' , $e.';' , $e.'%' ,  '.' ,  ',' );
		$texte = str_replace( $tab_bad , $tab_bon , $texte );
		// Ajustement de la taille de la police et de l'interligne si appréciation trop longue
		do
		{
			list($nb_lignes,$split_texte) = $this->WordWrap($texte,$largeur_autorisee);
			$hauteur_requise = $nb_lignes*$taille_interligne ;
			$is_trop_haut = ( $hauteur_requise > $hauteur_autorisee ) ? TRUE : FALSE ;
			if($is_trop_haut)
			{
				$taille_police *= 0.9;
				$taille_interligne *= 0.9;
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
			$this->CellFit( $largeur_autorisee , $taille_interligne , pdf($tab_lignes[$num_ligne]) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			// $this->Write($taille_interligne,pdf($tab_lignes[$num_ligne]));
			// $ordonnee += $taille_interligne;
		}
		$this->SetXY( $memo_abscisse , $memo_ordonnee+$hauteur_autorisee );
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher une signature d'un bilan officiel
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_signature($largeur_cadre_appreciation,$hauteur_autorisee,$tab_image_tampon_signature)
	{
		list($img_contenu,$img_format,$img_largeur,$img_hauteur) = $tab_image_tampon_signature;
		// Les dimensions sont données en pixels, il faut les convertir en mm/
		// Problème : dpi inconnue ! On prend 96 par défaut... mais ça peut être 72 ou 300 ou ... ça dépend de chaque image...
		// mm = (pixels * 25.4) / dpi
		// pixels = (mm * dpi) / 25.4
		$coef_conversion = 25.4 / 96 ;
		$img_largeur *= $coef_conversion;
		$img_hauteur *= $coef_conversion;
		$largeur_autorisee = $hauteur_autorisee * 2 ;
		$coef_largeur = $largeur_autorisee / $img_largeur ;
		$coef_hauteur = $hauteur_autorisee / $img_hauteur ;
		$ratio = min( $coef_largeur , $coef_hauteur , 1 ) ;
		$img_largeur *= $ratio;
		$img_hauteur *= $ratio;
		$retrait_x = max($hauteur_autorisee,$img_largeur);
		$img_pos_x = $this->GetX() + $largeur_cadre_appreciation - $retrait_x ;
		$img_pos_y = $this->GetY() + ( $hauteur_autorisee - $img_hauteur ) / 2 ;
		// echo'*'.$ratio.'*'.$img_largeur.'*'.$img_hauteur;
		$this->MemImage($img_contenu,$img_pos_x,$img_pos_y,$img_largeur,$img_hauteur,strtoupper($img_format));
		return $retrait_x;
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour afficher la légende ( $type_legende = 'codes_notation' | 'etat_acquisition' | 'pourcentage_acquis' | 'etat_validation' )
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function afficher_legende($type_legende,$ordonnee)
	{
		$espace = '     ';
		$hauteur = min(4,$this->lignes_hauteur);
		$this->SetFont('Arial' , '' , ceil($hauteur * 1.6));
		$this->SetXY($this->marge_gauche , $ordonnee);
		// Afficher la légende des codes de notation
		if($type_legende=='codes_notation')
		{
			$memo_lomer_espace_largeur = $this->lomer_espace_largeur;
			$memo_lomer_espace_hauteur = $this->lomer_espace_hauteur;
			$border = ($this->couleur == 'oui') ? 0 : 1 ;
			$memo_taille_police = $this->taille_police;
			$this->taille_police = ceil($hauteur * 1.6); // On est obligé de le changer provisoirement car si impression N&B afficher_note_lomer() l'utilise
			$this->calculer_dimensions_images($hauteur*1.5,$hauteur);
			$this->afficher_note_lomer('RR',$border,$br=0); $this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['RR']).$espace , '');
			$this->afficher_note_lomer('R' ,$border,$br=0); $this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['R']) .$espace , '');
			$this->afficher_note_lomer('V' ,$border,$br=0); $this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['V']) .$espace , '');
			$this->afficher_note_lomer('VV',$border,$br=0); $this->Write($hauteur , pdf($_SESSION['NOTE_LEGENDE']['VV']).$espace , '');
			$this->calculer_dimensions_images($memo_lomer_espace_largeur,$memo_lomer_espace_hauteur);
			$this->taille_police = $memo_taille_police;
		}
		// Afficher la légende des états d'acquisition
		if($type_legende=='etat_acquisition')
		{
			$tab_etats = array('NA','VA','A');
			foreach($tab_etats as $etat)
			{
				$this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
				$this->Cell($hauteur*1.5 , $hauteur , pdf($_SESSION['ACQUIS_TEXTE'][$etat]) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
				$this->Write($hauteur , pdf($_SESSION['ACQUIS_LEGENDE'][$etat]).$espace , '');
			}
		}
		// Afficher la légende des pourcentages d'items acquis
		if($type_legende=='pourcentage_acquis')
		{
			$tab_seuils = array('NA'=>'< '.$_SESSION['CALCUL_SEUIL']['R'].'%','VA'=>'médian','A'=>'> '.$_SESSION['CALCUL_SEUIL']['V'].'%');
			$this->Write($hauteur , pdf('Pourcentages d\'items acquis :').$espace , '');
			foreach($tab_seuils as $etat => $texte)
			{
				$this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
				$this->Cell(20 , $hauteur , pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
				$this->Write($hauteur , $espace , '');
			}
		}
		// Afficher la légende des états de validation
		if($type_legende=='etat_validation')
		{
			$tab_etats = array('v1'=>'Validé','v0'=>'Invalidé','v2'=>'Non renseigné');
			$this->Write($hauteur , pdf('États de validation :').$espace , '');
			foreach($tab_etats as $etat => $texte)
			{
				$this->choisir_couleur_fond($this->tab_choix_couleur[$etat]);
				$this->Cell(20 , $hauteur , pdf($texte) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
				$this->Write($hauteur , $espace , '');
			}
		}
		$this->SetXY($this->marge_gauche , $ordonnee+$hauteur);
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthode pour changer le pied de page
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function Footer()
	{
		if($this->officiel==FALSE)
		{
			$this->SetXY(0 , -$this->distance_pied);
			$this->SetFont('Arial' , '' , 7);
			$this->choisir_couleur_fond('gris_clair');
			$this->choisir_couleur_trait('gris_moyen');
			$this->Cell( $this->page_largeur , 3 , pdf('Généré le '.date("d/m/Y \à H\hi\m\i\\n").' par '.$_SESSION['USER_PRENOM']{0}.'. '.$_SESSION['USER_NOM'].' ('.$_SESSION['USER_PROFIL'].') avec SACoche [ '.SERVEUR_PROJET.' ].') , 'TB' , 0 , 'C' , TRUE , SERVEUR_PROJET);
		}
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une synthèse matiere ou pluridisciplinaire ; a priori pas de pb avec la hauteur de ligne minimale
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	bilan_synthese_initialiser()   c'est là que les calculs se font pour une sortie "matiere"
	//	bilan_synthese_entete()        c'est là que les calculs se font pour une sortie "multimatiere"
	//	bilan_synthese_ligne_matiere()
	//	bilan_synthese_ligne_synthese()
	//	bilan_synthese_appreciation_rubrique()
	//	bilan_synthese_appreciation_generale()
	//	bilan_synthese_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function bilan_synthese_initialiser($format,$nb_lignes_total,$eleves_nb)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->SetAutoPageBreak(FALSE);
		if($format=='matiere')
		{
			// Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
			$hauteur_dispo_par_page     = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb_tous_eleves      = $eleves_nb * ( 1 + 1 + ($this->legende*1.5) ) + $nb_lignes_total ; // eleves * [ intitulé-structure + classe-élève-date + légende ] + toutes_synthèses
			$hauteur_ligne_moyenne      = 6;
			$lignes_nb_moyen_par_page   = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
			$nb_page_moyen              = max( 1 , round( $lignes_nb_tous_eleves / $lignes_nb_moyen_par_page ) ); // max 1 pour éviter une division par zéro
			$eleves_nb_par_page         = ceil( $eleves_nb / $nb_page_moyen ) ;
			// $nb_page_calcule = ceil( $eleves_nb / $eleves_nb_par_page ) ; // devenu inutile
			$lignes_nb_moyen_eleve      = $lignes_nb_tous_eleves / $eleves_nb ;
			$lignes_nb_calcule_par_page = $eleves_nb_par_page * $lignes_nb_moyen_eleve ; // $lignes_nb/$nb_page_calcule ne va pas à cause un élève peut alors être considéré à cheval sur 2 pages
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
			$this->AddPage($this->orientation , 'A4');
			if($this->officiel)
			{
				// Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cette entête.
				list( $tab_etabl_coords , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
				$this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
				// Bloc adresse en positionnement contraint
				if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
				{
					list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
					$this->SetXY($this->marge_gauche,$this->marge_haut);
				}
				// Bloc établissement
				$bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
				$bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$bloc_etabl_largeur);
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
					// En haut à droite
					$bloc_titre_largeur = 100;
					$this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut );
					$bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
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
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb = ( $hauteur_entete / 4 ) + $eleve_nb_lignes + ($this->legende*1.5) ; // entête + synthèses + légendes
			$hauteur_ligne_minimale = ($this->officiel) ? 4.5 : 3.5 ;
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
			$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2;
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Synthèse '.$texte_format)                  , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			// Période / Classe - élève
			$this->SetFont('Arial' , '' , $this->taille_police);
			$this->Cell($largeur_demi_page , $this->taille_police*0.8 , pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
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

	public function bilan_synthese_ligne_matiere($format,$matiere_nom,$lignes_nb,$tab_infos_matiere,$total,$moyenne_eleve,$moyenne_classe)
	{
		if($format=='multimatiere')
		{
			// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
			$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
			$test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
			if( $test_nouvelle_page )
			{
				$this->AddPage($this->orientation , 'A4');
				$this->SetFont('Arial' , 'B' , $this->taille_police);
				$this->choisir_couleur_texte('gris_fonce');
				$this->Cell( $this->page_largeur - $this->marge_gauche - $this->marge_droite , $this->lignes_hauteur , pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' (suite)') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
				$this->choisir_couleur_texte('noir');
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
			$this->CellFit( $this->page_largeur - $this->marge_gauche - $this->marge_droite - 80 , $this->lignes_hauteur*1.5 , pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
			// Proportions acquis matière
			$this->SetFont('Arial' , 'B' , $this->taille_police);
			$this->afficher_proportion_acquis(80,$this->lignes_hauteur*1.5,$tab_infos_matiere,$total);
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*1.5);
		}
		else
		{
			$demi_largeur = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2 ;
			// Intitulé matière
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
			$couleur_fond = ($this->couleur=='oui') ? 'gris_moyen' : 'blanc' ;
			$this->choisir_couleur_fond($couleur_fond);
			$this->CellFit( $demi_largeur , $this->lignes_hauteur*2 , pdf($matiere_nom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
			// Moyenne élève (éventuelle) et moyenne classe (éventuelle)
			if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
			{
				$largeur_note = 10;
				$this->Rect( $this->GetX() , $this->GetY() , $demi_largeur , $this->lignes_hauteur , 'D' /* DrawFill */ );
				$texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne élève (classe) :' : 'Moyenne élève :' ;
				$this->SetFont('Arial' , '' , $this->taille_police);
				$largueur_texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $demi_largeur-2*$largeur_note : $demi_largeur-$largeur_note ;
				$this->Cell( $largueur_texte , $this->lignes_hauteur , pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
				$moyenne_eleve = ($moyenne_eleve!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($moyenne_eleve,1,',','') : ($moyenne_eleve*5).'%' ) : '-' ;
				$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
				$this->Cell( $largeur_note , $this->lignes_hauteur , pdf($moyenne_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
				if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
				{
					$moyenne_classe = ($moyenne_classe!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($moyenne_classe,1,',','') : round($moyenne_classe*5).'%' ) : '-' ;
					$this->SetFont('Arial' , '' , $this->taille_police*0.8);
					$this->Cell( $largeur_note , $this->lignes_hauteur , pdf('('.$moyenne_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
				}
				$this->SetXY($this->marge_gauche + $demi_largeur , $this->GetY() + $this->lignes_hauteur );
				$nb_lignes_acquis = 1;
				$acquis_bold = '';
			}
			else
			{
				$nb_lignes_acquis = 2;
				$acquis_bold = 'B';
			}
			// Proportions acquis matière
			$this->SetFont('Arial' , $acquis_bold , $this->taille_police);
			$this->afficher_proportion_acquis($demi_largeur,$this->lignes_hauteur*$nb_lignes_acquis,$tab_infos_matiere,$total);
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*$nb_lignes_acquis);
		}
	}

	public function bilan_synthese_ligne_synthese($synthese_nom,$tab_infos_synthese,$total,$hauteur_ligne_synthese)
	{
		$hauteur_ligne = $this->lignes_hauteur * $hauteur_ligne_synthese ;
		$largeur_diagramme = ($this->officiel) ? 20 : 40 ;
		$this->SetFont('Arial' , '' , $this->taille_police*0.8);
		$this->afficher_proportion_acquis($largeur_diagramme,$hauteur_ligne,$tab_infos_synthese,$total);
		$intitule_synthese_largeur = ( ($this->officiel) && ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) ) ? ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2 - $largeur_diagramme : $this->page_largeur - $this->marge_gauche - $this->marge_droite - $largeur_diagramme ;
		// else
		// {
			// Pourcentage acquis synthèse
			// $this->pourcentage_largeur = 10;
			// $this->cases_hauteur = $hauteur_ligne;
			// $this->afficher_pourcentage_acquis( '' /*gras*/ , $tab_infos_synthese , 'rien' /*affich*/ );
			// $intitule_synthese_largeur = ($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_RUBRIQUE']) ? ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2 - $this->pourcentage_largeur : $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->pourcentage_largeur ;
		// }
		// Intitulé synthèse
		$this->SetFont('Arial' , '' , $this->taille_police);
		$couleur_fond = ($this->couleur=='oui') ? 'gris_clair' : 'blanc' ;
		$this->choisir_couleur_fond($couleur_fond);
		$this->CellFit( $intitule_synthese_largeur , $hauteur_ligne , pdf($synthese_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
	}

	public function bilan_synthese_appreciation_rubrique($tab_saisie,$nb_lignes_hauteur)
	{
		$cadre_hauteur = $nb_lignes_hauteur * $this->lignes_hauteur ;
		$demi_largeur = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2 ;
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

	public function bilan_synthese_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $moyenne_generale_eleve , $moyenne_generale_classe )
	{
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		$hauteur_requise = $this->lignes_hauteur * $nb_lignes_appreciation_generale_avec_intitule ;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->choisir_couleur_texte('gris_fonce');
			$this->Cell( $this->page_largeur - $this->marge_gauche - $this->marge_droite , $this->lignes_hauteur , pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' (suite)') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			$this->choisir_couleur_texte('noir');
			$this->SetXY( $this->marge_gauche , $this->GetY() + 2 );
		}
		else
		{
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
		}
		$this->officiel_bloc_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $this->page_largeur - $this->marge_gauche - $this->marge_droite , $this->lignes_hauteur , $moyenne_generale_eleve , $moyenne_generale_classe );
	}

	public function bilan_synthese_legende($format)
	{
		// Légende : en bas de page si 'multimatiere', à la suite si 'matiere'
		$ordonnee = ($format=='multimatiere') ?  $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 : $this->GetY() + $this->lignes_hauteur*0.5 ;
		$this->afficher_legende( 'etat_acquisition' /*type_legende*/ , $ordonnee );
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'un bilan d'items d'une matiere ou pluridisciplinaire
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	bilan_item_individuel_initialiser()   c'est là que les calculs se font pour une sortie "matiere"
	//	bilan_item_individuel_entete()        c'est là que les calculs se font pour une sortie "multimatiere" ou "selection"
	//	bilan_item_individuel_transdisciplinaire_ligne_matiere()
	//	bilan_item_individuel_appreciation_rubrique()
	//	bilan_item_individuel_appreciation_generale()
	//	bilan_item_individuel_debut_ligne_item()
	//	bilan_item_individuel_ligne_synthese()
	//	bilan_item_individuel_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function bilan_item_individuel_initialiser($format,$cases_nb,$cases_largeur,$lignes_nb,$eleves_nb,$pages_nb_methode)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->SetAutoPageBreak(FALSE);
		$this->cases_nb          = $cases_nb;
		$this->cases_largeur     = $cases_largeur;
		$this->reference_largeur = 10; // valeur fixe
		$this->synthese_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->reference_largeur;
		$this->intitule_largeur  = $this->synthese_largeur - (($this->cases_nb+1) * $this->cases_largeur);
		if($format=='matiere')
		{
			// Dans ce cas on met plusieurs élèves par page : on calcule maintenant combien et la hauteur de ligne à prendre
			$hauteur_dispo_par_page   = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb_tous_eleves    = $eleves_nb * ( 1 + 1 + $lignes_nb + ($this->legende*2*1) + 2 ) ; // eleves * [ intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge ]
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

	public function bilan_item_individuel_entete($format,$pages_nb_methode,$tab_infos_entete,$eleve_nom,$eleve_prenom,$eleve_nb_lignes)
	{
		$this->eleve_nom    = $eleve_nom;
		$this->eleve_prenom = $eleve_prenom;
		if($format=='matiere')
		{
			// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
			$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
			$lignes_nb = 1 + 1 + $eleve_nb_lignes + ($this->legende*2*1) + 2 ; // intitulé-matiere-structure + classe-élève-date + lignes dont résumés + légendes + marge
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
		elseif( ($format=='multimatiere') || ($format=='selection') )
		{
			// On prend une nouvelle page PDF
			$this->AddPage($this->orientation , 'A4');
			if($this->officiel)
			{
				// Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cette entête.
				list( $tab_etabl_coords , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
				$this->doc_titre = $tab_bloc_titres[0].' - '.$tab_bloc_titres[1];
				// Bloc adresse en positionnement contraint
				if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
				{
					list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
					$this->SetXY($this->marge_gauche,$this->marge_haut);
				}
				// Bloc établissement
				$bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
				$bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$bloc_etabl_largeur);
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
					// En haut à droite
					$bloc_titre_largeur = 100;
					$this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut );
					$bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
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
			$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
			$lignes_nb = ( $hauteur_entete / 4 ) + $eleve_nb_lignes + ($this->legende*2*1) ; // entête + matières(marge+intitulé) & lignes dont résumés + légendes
			$hauteur_ligne_minimale = 3;
			$hauteur_ligne_maximale = 3+2;
			$nb_pages = 0;
			do
			{
				$nb_pages++;
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
			$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2;
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Bilan '.$texte_format)                     , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			// Période / Classe - élève
			$this->SetFont('Arial' , '' , $this->taille_police);
			$this->Cell($largeur_demi_page , $this->taille_police*0.8 , pdf($texte_periode) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($this->eleve_nom.' '.$this->eleve_prenom.' ('.$groupe_nom.')') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
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

	public function bilan_item_individuel_transdisciplinaire_ligne_matiere($matiere_nom,$lignes_nb)
	{
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2;
		// La hauteur de ligne a déjà été calculée ; mais il reste à déterminer si on saute une page ou non en fonction de la place restante (et sinon => interligne)
		$hauteur_dispo_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas ;
		$lignes_nb = 1.5 + $lignes_nb ; // matière(marge+intitulé) + lignes dont résumés (on ne compte pas la légende)
		$test_nouvelle_page = ($this->lignes_hauteur*$lignes_nb > $hauteur_dispo_restante) && ($this->GetY() > $this->lignes_hauteur*5) ; // 2e condition pour éviter un saut de page si déjà en haut (à cause d'une liste à rallonge dans une matière)
		if( $test_nouvelle_page )
		{
			$this->AddPage($this->orientation , 'A4');
		}
		else
		{
			// Interligne
			$this->SetXY($this->marge_gauche , $this->GetY() + $this->lignes_hauteur*0.5);
		}
		// Intitulé matière + éventuellement rappel élève
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		if( $test_nouvelle_page )
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom)                            , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->SetFont('Arial' , 'B' , $this->taille_police);
			$this->choisir_couleur_texte('gris_fonce');
			$this->Cell( $largeur_demi_page , $this->lignes_hauteur , pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' (suite)') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			$this->choisir_couleur_texte('noir');
		}
		else
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		}
	}

	public function bilan_item_individuel_appreciation_rubrique($tab_saisie)
	{
		$this->SetXY( $this->marge_gauche + $this->reference_largeur , $this->GetY() );
		$this->officiel_bloc_appreciation_intermediaire( $tab_saisie , $this->synthese_largeur , $this->lignes_hauteur , 'releve' , $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE'] );
	}

	public function bilan_item_individuel_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule )
	{
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		$hauteur_requise = $this->lignes_hauteur * $nb_lignes_appreciation_generale_avec_intitule ;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->choisir_couleur_texte('gris_fonce');
			$this->Cell( $this->page_largeur - $this->marge_gauche - $this->marge_droite , $this->lignes_hauteur , pdf($this->doc_titre.' - '.$this->eleve_nom.' '.$this->eleve_prenom.' (suite)') , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			$this->choisir_couleur_texte('noir');
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
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->choisir_couleur_fond('gris_clair');
		$this->SetFont('Arial' , '' , $this->taille_police*0.8);
		$this->CellFit( $this->reference_largeur , $this->cases_hauteur , pdf($ref_suite) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->CellFit( $this->intitule_largeur , $this->cases_hauteur , pdf($item_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->choisir_couleur_fond('blanc');
	}

	public function bilan_item_individuel_ligne_synthese($bilan_texte)
	{
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->choisir_couleur_fond('gris_moyen');
		$this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->Cell( $this->synthese_largeur  , $this->cases_hauteur , pdf($bilan_texte) , 1 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , TRUE /*remplissage*/  );
	}

	public function bilan_item_individuel_legende($format)
	{
		// Légende : à la suite si 'matiere' , en bas de page si 'multimatiere' ou 'selection',
		$ordonnee = ($format=='matiere') ? $this->GetY() + $this->lignes_hauteur*0.2 : $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*1.5 ;
		$this->afficher_legende( 'codes_notation'   /*type_legende*/ , $ordonnee /*ordonnée*/ );
		$this->afficher_legende( 'etat_acquisition' /*type_legende*/ , $this->GetY() + $this->lignes_hauteur*0.2 );
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une grille d'items d'un référentiel
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	grille_referentiel_initialiser()
	//	grille_referentiel_entete()
	//	grille_referentiel_domaine()
	//	grille_referentiel_theme()
	//	grille_referentiel_item()
	//	grille_referentiel_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function grille_referentiel_initialiser($cases_nb,$cases_largeur,$lignes_nb,$colonne_bilan,$colonne_vide)
	{
		// On calcule la hauteur de la ligne et la taille de la police pour tout faire rentrer sur une page si possible, un minimum de pages sinon
		$hauteur_dispo_par_page = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
		$lignes_nb = 1 + 1 + 1 + $lignes_nb + ($this->legende+0.25) ; // intitulé-structure + matière-niveau-élève + marge (1 & un peu plus car aussi avant domaines) + lignes (domaines+thèmes+items) + légende
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
		$this->intitule_largeur  = $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->reference_largeur - ($this->cases_nb * $this->cases_largeur) - $this->colonne_bilan_largeur - $this->colonne_vide_largeur ;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->SetAutoPageBreak(FALSE);
		$this->calculer_dimensions_images($this->cases_largeur,$this->cases_hauteur);
	}

	public function grille_referentiel_entete($matiere_nom,$niveau_nom,$eleve_id,$eleve_nom,$eleve_prenom)
	{
		// On prend une nouvelle page PDF pour chaque élève
		$this->AddPage($this->orientation , 'A4');
		$this->SetXY($this->marge_gauche,$this->marge_haut);
		$largeur_demi_page = ( $this->page_largeur - $this->marge_gauche - $this->marge_droite ) / 2;
		// intitulé-structure
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf('Grille d\'items d\'un référentiel')        , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
		// matière-niveau-élève
		$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($matiere_nom.' - Niveau '.$niveau_nom) , 0 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		if($eleve_id)
		{
			$this->Cell($largeur_demi_page , $this->lignes_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
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
		$this->Cell( $this->intitule_largeur , $this->cases_hauteur , pdf($domaine_nom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
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
		$this->Cell( $this->reference_largeur , $this->cases_hauteur , pdf($theme_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		$this->Cell( $this->intitule_largeur , $this->cases_hauteur , pdf($theme_nom)  , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
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
		$this->CellFit( $this->reference_largeur , $this->cases_hauteur , pdf($item_ref)   , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		$this->CellFit( $this->intitule_largeur  , $this->cases_hauteur , pdf($item_texte) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->choisir_couleur_fond('blanc');
	}

	public function grille_referentiel_legende()
	{
		$this->afficher_legende( 'codes_notation' /*type_legende*/ , $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.8 /*ordonnée*/ );
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour l'entête des bilans officiels
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	officiel_bloc_etablissement()
	//	officiel_bloc_titres()
	//	officiel_ligne_tag()
	//	officiel_bloc_adresse_position_libre()
	//	officiel_bloc_adresse_position_contrainte_et_pliures()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function officiel_bloc_etablissement($tab_etabl_coords,$bloc_largeur)
	{
		$bloc_hauteur = 0 ;
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
			$this->CellFit( $bloc_largeur , $ligne_hauteur , pdf($ligne_etabl) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$bloc_hauteur += $ligne_hauteur ;
		}
		return $bloc_hauteur;
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
			$this->CellFit( $bloc_largeur , $ligne_hauteur , pdf($ligne_titre) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		}
		if($alerte_archive)
		{
			// Ligne d'avertissement
			$this->choisir_couleur_texte('rougevif');
			$this->CellFit( $bloc_largeur , $ligne_hauteur , pdf('Copie partielle pour information. Seul l\'original fait foi.') , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
			$this->choisir_couleur_texte('noir');
		}
		return $bloc_hauteur;
	}

	private function officiel_ligne_tag($tag_date_heure_initiales,$ligne_largeur)
	{
		$taille_police = 5 ;
		$ligne_hauteur = $taille_police*0.4 ;
		$this->SetFont('Arial' , '' , $taille_police);
		$this->Cell( $ligne_largeur , $ligne_hauteur , pdf($tag_date_heure_initiales) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
	}

	private function officiel_bloc_adresse_position_libre($tab_adresse,$bloc_largeur)
	{
		$taille_police = 9 ;
		$ligne_hauteur = $taille_police*0.4 ;
		$this->SetFont('Arial' , '' , $taille_police);
		foreach($tab_adresse as $ligne_adresse)
		{
			$this->CellFit( $bloc_largeur , $ligne_hauteur , pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
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
		$marge_suppl_x = $interieur_largeur*0.1;
		$marge_suppl_y = $interieur_hauteur*0.1;
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
			$this->CellFit( $interieur_largeur_reste , $ligne_hauteur_reste , pdf($ligne_adresse) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
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
		$tab_auteurs = array();
		foreach($tab_saisie as $prof_id => $tab)
		{
			extract($tab);	// $prof_info $appreciation $note
			$texte .= str_replace( array("\r\n","\r","\n") , ' ' , $appreciation )."\r\n";
			$tab_auteurs[] = $prof_info;
			$nb_lignes_prevues += $nb_lignes_appreciation_potentielle_par_prof_hors_intitule;
		}
		// Intitulé "Appréciations / Conseils :" + auteurs
		$hauteur_ligne_auteurs = $ligne_hauteur*0.8;
		$memoX = $this->GetX();
		$memoY = $this->GetY();
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->Write( $hauteur_ligne_auteurs , pdf('Appréciations / Conseils') );
		if(count($tab_auteurs))
		{
			$this->SetFont('Arial' , '' , $this->taille_police);
			$this->Write( $hauteur_ligne_auteurs , pdf('   [ '.implode(' ; ',$tab_auteurs).' ]') );
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
		extract($tab_infos);	// $prof_info $appreciation $note
		$appreciation_sans_br = str_replace( array("\r\n","\r","\n") , ' ' , $appreciation , $nombre_br );
		$appreciation = ($nombre_br<$nb_lignes_appreciation_generale_hors_intitule) ? $appreciation : $appreciation_sans_br ;
		// Intitulé "Appréciation générale"
		$memoX = $this->GetX();
		$memoY = $this->GetY();
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.4);
		$this->Write( $ligne_hauteur , pdf('Appréciation générale') );
		if($prof_info)
		{
			$this->SetFont('Arial' , '' , $this->taille_police);
			$this->Write( $ligne_hauteur , pdf('   [ '.$prof_info.' ]') );
		}
		// Moyenne générale éventuelle (élève & classe)
		$this->SetXY( $memoX , $memoY );
		$largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droite ;
		if($moyenne_generale_eleve!==NULL)
		{
			$largeur_note = 10;
			$texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? 'Moyenne générale élève (classe) :' : 'Moyenne générale élève :' ;
			$this->SetFont('Arial' , '' , $this->taille_police);
			$largueur_texte = ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ? $largeur-2*$largeur_note : $largeur-$largeur_note ;
			$this->Cell( $largueur_texte , $ligne_hauteur , pdf($texte) , 0 /*bordure*/ , 0 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
			$moyenne_generale_eleve = ($moyenne_generale_eleve!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($moyenne_generale_eleve,1,',','') : round($moyenne_generale_eleve*5).'%' ) : '-' ;
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
			$this->Cell( $largeur_note , $ligne_hauteur , pdf($moyenne_generale_eleve) , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
			if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
			{
				$moyenne_generale_classe = ($moyenne_generale_classe!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($moyenne_generale_classe,1,',','') : round($moyenne_generale_classe*5).'%' ) : '-' ;
				$this->SetFont('Arial' , '' , $this->taille_police*0.8);
				$this->Cell( $largeur_note , $ligne_hauteur , pdf('('.$moyenne_generale_classe.')') , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
			}
		}
		$this->SetXY( $memoX , $memoY+$ligne_hauteur );
		// préparation cadre appréciation
		$largeur_autorisee = $bloc_largeur;
		$hauteur_autorisee = $ligne_hauteur * $nb_lignes_appreciation_generale_hors_intitule;
		$memoX = $this->GetX();
		$memoY = $this->GetY();
		// signature
		$largeur_signature = ($tab_image_tampon_signature) ? $this->afficher_signature( $largeur_autorisee , $hauteur_autorisee , $tab_image_tampon_signature ) : $hauteur_autorisee ;
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
	}

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'un releve d'attestation de socle commun
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	releve_socle_initialiser()
	//	releve_socle_identite()
	//	releve_socle_entete()
	//	releve_socle_pilier()
	//	releve_socle_section()
	//	releve_socle_item()
	//	releve_socle_appreciation_rubrique()
	//	releve_socle_appreciation_generale()
	//	releve_socle_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function releve_socle_initialiser($test_affichage_Pourcentage,$test_affichage_Validation)
	{
		$this->pourcentage_largeur = 25; // valeur fixe
		$this->validation_largeur  = 15; // valeur fixe
		$this->retrait_pourcentage = ( $test_affichage_Pourcentage ) ? $this->pourcentage_largeur : 0;
		$retrait_validation        = ( $test_affichage_Validation )  ? $this->validation_largeur  : 0;
		$this->item_largeur        = $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->retrait_pourcentage - $retrait_validation;
		$this->section_largeur     = $this->item_largeur;
		$this->pilier_largeur      = $this->section_largeur - $retrait_validation;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->SetAutoPageBreak(FALSE);
	}

	public function releve_socle_identite()
	{
		// On met le document au nom de l'élève, ou on établit un document générique
		if(!$this->eleve_id)
		{
			$this->choisir_couleur_trait('gris_moyen');
			$this->SetLineWidth(0.1);
			$this->Line($this->page_largeur-$this->marge_droite-75 , $this->marge_haut+2*$this->cases_hauteur , $this->page_largeur-$this->marge_droite , $this->marge_haut+2*$this->cases_hauteur);
			$this->choisir_couleur_trait('noir');
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->SetXY($this->page_largeur-$this->marge_droite-50 , max($this->marge_haut,$this->GetY()-2*$this->cases_hauteur) ); // Soit c'est une nouvelle page, soit il ne faut pas se mettre en haut de la page
		$this->Cell(50 , $this->cases_hauteur , pdf($_SESSION['ETABLISSEMENT']['DENOMINATION']) , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
		$this->Cell(50 , $this->cases_hauteur , pdf($this->eleve_nom.' '.$this->eleve_prenom)   , 0 /*bordure*/ , 2 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
	}

	public function releve_socle_entete($tab_infos_entete,$break,$eleve_id,$eleve_nom,$eleve_prenom,$eleve_nb_lignes)
	{
		$this->eleve_id     = $eleve_id;
		$this->eleve_nom    = $eleve_nom;
		$this->eleve_prenom = $eleve_prenom;
		// On prend une nouvelle page PDF pour chaque élève en cas d'affichage d'un palier avec tous les piliers ; pour un seul pilier, on étudie la place restante... tout en forçant une nouvelle page pour le 1er élève
		if( ($break==FALSE) || ($this->GetY()==0) )
		{
			$this->AddPage($this->orientation , 'A4');
			if($break==FALSE)
			{
				if($this->officiel)
				{
					// Ecrire l'entête (qui ne dépend pas de la taille de la police calculée ensuite) et récupérer la place requise par cette entête.
					list( $tab_etabl_coords , $etabl_coords__bloc_hauteur , $tab_bloc_titres , $tab_adresse , $tag_date_heure_initiales ) = $tab_infos_entete;
					// Bloc adresse en positionnement contraint
					if( (is_array($tab_adresse)) && ($_SESSION['OFFICIEL']['INFOS_RESPONSABLES']=='oui_force') )
					{
						list( $bloc_droite_hauteur , $bloc_gauche_largeur_restante ) = $this->officiel_bloc_adresse_position_contrainte_et_pliures($tab_adresse);
						$this->SetXY($this->marge_gauche,$this->marge_haut);
					}
					// Bloc établissement
					$bloc_etabl_largeur = (isset($bloc_gauche_largeur_restante)) ? $bloc_gauche_largeur_restante : 80 ;
					$bloc_etabl_hauteur = $this->officiel_bloc_etablissement($tab_etabl_coords,$bloc_etabl_largeur);
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
						// En haut à droite
						$bloc_titre_largeur = 100;
						$this->SetXY( $this->page_largeur-$this->marge_droite-$bloc_titre_largeur , $this->marge_haut );
						$bloc_titre_hauteur = $this->officiel_bloc_titres($tab_bloc_titres,$alerte_archive,$bloc_titre_largeur);
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
				$hauteur_dispo_par_page   = $this->page_hauteur - $this->marge_haut - $this->marge_bas ;
				$hauteur_ligne_moyenne    = 4.5;
				$lignes_nb                = ( $hauteur_entete / 4.5 ) + $eleve_nb_lignes + ($this->legende*2) + 2 ; // entete + lignes dont résumés + légendes + marge
				$lignes_nb_moyen_par_page = $hauteur_dispo_par_page / $hauteur_ligne_moyenne ;
				$nb_page_moyen            = $lignes_nb / $lignes_nb_moyen_par_page ;
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
				$this->AddPage($this->orientation , 'A4');
				$this->SetXY($this->marge_gauche,$this->marge_haut);
			}
			else
			{
				$this->SetXY($this->marge_gauche,$this->GetY()+$this->cases_hauteur);
			}
		}
		if(!$this->officiel)
		{
			list( $titre , $palier_nom ) = $tab_infos_entete;
			// Intitulé
			$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
			$this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , pdf($titre)      , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			$this->Cell( $this->page_largeur-$this->marge_droite-75 , $this->cases_hauteur , pdf($palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
			// Nom / prénom
			$this->releve_socle_identite();
		}
	}

	public function releve_socle_pilier($pilier_nom,$pilier_nb_lignes,$test_affichage_Validation,$tab_pilier_validation,$drapeau_langue)
	{
		$this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur);
		$hauteur_requise = $this->cases_hauteur * $pilier_nb_lignes;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->releve_socle_identite();
			$this->SetXY($this->marge_gauche+$this->retrait_pourcentage , $this->GetY()+2);
		}
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.25);
		$this->choisir_couleur_fond('gris_moyen');
		$br = $test_affichage_Validation ? 0 : 1 ;
		$this->CellFit( $this->pilier_largeur , $this->cases_hauteur , pdf($pilier_nom) , 1 , $br , 'L' , TRUE , '');
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
		$this->CellFit( $this->section_largeur , $this->cases_hauteur , pdf($section_nom) , 1 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
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
		$this->CellFit( $this->item_largeur , $this->cases_hauteur , pdf($item_nom) , 1 /*bordure*/ , $br , 'L' /*alignement*/ , TRUE /*remplissage*/ );
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

	public function releve_socle_appreciation_generale( $prof_id , $tab_infos , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule )
	{
		$this->SetXY( $this->marge_gauche + $this->retrait_pourcentage , $this->GetY() + $this->cases_hauteur );
		$hauteur_requise = $this->cases_hauteur * $nb_lignes_appreciation_generale_avec_intitule ;
		$hauteur_restante = $this->page_hauteur - $this->GetY() - $this->marge_bas;
		if($hauteur_requise > $hauteur_restante)
		{
			// Prendre une nouvelle page si ça ne rentre pas, avec recopie de l'identité de l'élève
			$this->AddPage($this->orientation , 'A4');
			$this->releve_socle_identite();
			$this->SetXY( $this->marge_gauche+$this->retrait_pourcentage , $this->GetY()+2 );
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

	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Méthodes pour la mise en page d'une synthèse des validations du socle commun
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	releve_synthese_socle_initialiser()
	//	releve_synthese_socle_entete()
	//	releve_synthese_socle_validation_eleve()
	//	releve_synthese_socle_pourcentage_eleve()
	//	releve_synthese_socle_legende()
	//	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function releve_synthese_socle_initialiser($titre_info,$groupe_nom,$palier_nom,$eleves_nb,$items_nb,$piliers_nb)
	{
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(TRUE);
		$this->eleve_largeur  = 40; // valeur fixe
		$this->cases_largeur  = ($this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->eleve_largeur - $piliers_nb) / ($items_nb); // - intercolonne de 1 * nb piliers
		$this->cases_hauteur  = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->taille_police - $eleves_nb - $this->legende*5) / ($eleves_nb+1); // - titre de 5 - ( interligne de 1 * nb élèves ) - legende
		$this->cases_hauteur  = min($this->cases_hauteur,10);
		$this->lignes_hauteur = $this->cases_hauteur;
		$this->taille_police = 8;
		// Intitulés
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.5);
		$this->Cell(0 , $this->taille_police , pdf('Synthèse de maîtrise du socle : '.$titre_info.' - '.$groupe_nom.' - '.$palier_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
	}

	public function releve_synthese_socle_entete($tab_pilier)
	{
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->SetXY($this->marge_gauche+$this->eleve_largeur,$this->marge_haut+$this->taille_police);
		$this->choisir_couleur_fond('gris_fonce');
		foreach($tab_pilier as $tab)
		{
			extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
			$texte = ($pilier_nb_entrees>10) ? 'Compétence ' : 'Comp. ' ;
			$this->SetX( $this->GetX()+1 );
			$this->Cell($pilier_nb_entrees*$this->cases_largeur , $this->cases_hauteur , pdf($texte.$pilier_ref) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
		}
		// positionnement pour la suite
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->SetXY( $this->marge_gauche , $this->GetY()+$this->cases_hauteur+1 );
	}

	public function releve_synthese_socle_validation_eleve($eleve_id,$eleve_nom,$eleve_prenom,$tab_user_pilier,$tab_user_entree,$tab_pilier,$tab_socle,$drapeau_langue)
	{
		$this->choisir_couleur_fond('gris_moyen');
		$this->CellFit( $this->eleve_largeur , $this->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
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
			extract($tab);	// $pilier_ref $pilier_nom $pilier_nb_entrees
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
		$this->CellFit( $this->eleve_largeur , $this->cases_hauteur , pdf($eleve_nom.' '.$eleve_prenom) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
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

	public function releve_synthese_socle_legende($legende,$type)
	{
		if($this->legende)
		{
			$ordonnee = $this->page_hauteur - $this->marge_bas - $this->lignes_hauteur*0.5 ;
			$type_legende = ($type=='pourcentage') ? 'pourcentage_acquis' : 'etat_validation' ;
			$this->afficher_legende( $type_legende , $ordonnee );
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan de synthèse d'un groupe sur une période
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	bilan_periode_synthese_initialiser()
	//	bilan_periode_synthese_entete()
	//	bilan_periode_synthese_pourcentages()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function bilan_periode_synthese_initialiser($eleve_nb,$item_nb,$tableau_tri_objet)
	{
		$hauteur_entete = 10;
		$intitule_facteur  = ($tableau_tri_objet=='eleve') ? 4 : 3 ;
		$etiquette_facteur = ($tableau_tri_objet=='item')  ? 4 : 3 ;
		$colonnes_nb = ($tableau_tri_objet=='eleve') ? $item_nb : $eleve_nb ;
		$lignes_nb   = ($tableau_tri_objet=='item')  ? $item_nb : $eleve_nb ;
		$this->cases_largeur     = ($this->page_largeur - $this->marge_gauche - $this->marge_droite - 2) / ($colonnes_nb+2+$intitule_facteur); // -2 pour une petite marge ; 2 colonnes ajoutées + identité/item
		$this->intitule_largeur  = $intitule_facteur  * $this->cases_largeur;
		$this->taille_police     = $this->cases_largeur*0.8;
		$this->taille_police     = min($this->taille_police,10); // pas plus de 10
		$this->taille_police     = max($this->taille_police,5);  // pas moins de 5
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - 2 - $hauteur_entete) / ($lignes_nb+2+$etiquette_facteur); // -2 pour une petite marge - entête ; 2 lignes ajoutées + identité/item
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
		$this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , pdf('Bilan '.$titre_nom) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->Cell( $this->page_largeur-$this->marge_droite-55 , 4 , pdf($matiere_et_groupe)  , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		// Synthèse
		$this->SetXY($this->page_largeur-$this->marge_droite-50 , $this->marge_haut);
		$this->Cell(20 , 4 , pdf('SYNTHESE') , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		// Période
		$this->SetFont('Arial' , '' , 8);
		$this->Cell( $this->page_largeur-$this->marge_gauche-$this->marge_droite , 4 , pdf($texte_periode) , 0 /*bordure*/ , 1 /*br*/ , 'R' /*alignement*/ , FALSE /*remplissage*/ );
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
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case1 , 'C' , TRUE , '');
		}
		else
		{
					if($moyenne_pourcent<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($moyenne_pourcent>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                                     {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $moyenne_pourcent.'%' : '' ;
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $direction_after_case1 , 'C' , TRUE , '');
		}

		// pour les 2 cases en diagonales, une case invisible permet de se positionner correctement
		if($last_colonne && $last_ligne)
		{
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		}

		// deuxième case
		if($moyenne_pourcent===FALSE)
		{
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , '-' , 1 , $direction_after_case2 , 'C' , TRUE , '');
		}
		else
		{
					if($moyenne_nombre<$_SESSION['CALCUL_SEUIL']['R']) {$this->choisir_couleur_fond($this->tab_choix_couleur['NA']);}
			elseif($moyenne_nombre>$_SESSION['CALCUL_SEUIL']['V']) {$this->choisir_couleur_fond($this->tab_choix_couleur['A']);}
			else                                                   {$this->choisir_couleur_fond($this->tab_choix_couleur['VA']);}
			$score_affiche = (mb_substr_count($_SESSION['DROIT_VOIR_SCORE_BILAN'],$_SESSION['USER_PROFIL'])) ? $moyenne_nombre.'%' : '' ;
			$this->Cell( $this->cases_largeur , $this->cases_hauteur , $score_affiche , 1 , $direction_after_case2 , 'C' , TRUE , '');
		}

		// pour la dernière ligne, mais pas pour les 2 dernières cases, se repositionner à la bonne ordonnée
		if($last_ligne && !$last_colonne)
		{
			$memo_x = $this->GetX();
			$this->SetXY($memo_x , $memo_y);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un tableau vierge de saisie d'évaluation
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	tableau_saisie_initialiser()
	//	tableau_saisie_reference_devoir()
	//	tableau_saisie_reference_eleve()
	//	tableau_saisie_reference_item()
	//	tableau_devoir_repartition_quantitative_initialiser()
	//	tableau_devoir_repartition_nominative_initialiser()
	//	tableau_devoir_repartition_nominative_entete()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function tableau_saisie_initialiser($eleve_nb,$item_nb)
	{
		$reference_largeur_minimum = 50;
		$cases_hauteur_maximum     = 25;
		$this->cases_largeur     = 10; // valeur par défaut ; diminué si pas assez de place pour la référence de l'item
		$this->etiquette_hauteur = 40; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droite - ($eleve_nb * $this->cases_largeur);
		if($this->reference_largeur < $reference_largeur_minimum)
		{
			$this->reference_largeur = $reference_largeur_minimum;
			$this->cases_largeur     = ($this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->reference_largeur) / $eleve_nb;
		}
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $item_nb;
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
		$this->CellFit( $this->reference_largeur , $hauteur_tiers , pdf($groupe_nom)  , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $this->reference_largeur , $hauteur_tiers , pdf($date_fr)     , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $this->reference_largeur , $hauteur_tiers , pdf($description) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->SetXY($this->marge_gauche , $this->marge_haut);
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->Cell( $this->reference_largeur , $this->etiquette_hauteur , '' , 0 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
	}

	public function tableau_saisie_reference_eleve($texte)
	{
		$this->choisir_couleur_fond('gris_clair');
		$this->VertCellFit( $this->cases_largeur, $this->etiquette_hauteur, pdf($texte), 1 /*border*/ , 0 /*ln*/ , TRUE /*fill*/ );
	}

	public function tableau_saisie_reference_item($item_intro,$item_nom)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		$this->choisir_couleur_fond('gris_clair');
		$this->Cell( $this->reference_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , TRUE /*remplissage*/ );
		$this->SetXY($memo_x , $memo_y+1);
		$this->SetFont('Arial' , 'B' , $this->taille_police);
		$this->CellFit( $this->reference_largeur , 3 , pdf($item_intro) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->MultiCell( $this->reference_largeur , 3 , pdf($item_nom) , 0 , 'L' , FALSE , '');
		$this->SetXY($memo_x+$this->reference_largeur , $memo_y);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un bilan d'un devoir : répartition quantitative ou nominative
	//	tableau_devoir_repartition_quantitative_initialiser() tableau_devoir_repartition_nominative_initialiser() tableau_devoir_repartition_nominative_entete()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function tableau_devoir_repartition_quantitative_initialiser($item_nb)
	{
		$cases_hauteur_maximum   = 20;
		$this->cases_largeur     = 20; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droite - (4 * $this->cases_largeur);
		$this->etiquette_hauteur = 10; // valeur fixe
		$this->cases_hauteur     = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $item_nb;
		$this->cases_hauteur     = min($this->cases_hauteur,$cases_hauteur_maximum);
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(TRUE);
		$this->calculer_dimensions_images($this->cases_largeur,$this->etiquette_hauteur);
	}

	public function tableau_devoir_repartition_nominative_initialiser($lignes_nb)
	{
		$this->cases_largeur     = 35; // valeur fixe
		$this->reference_largeur = $this->page_largeur - $this->marge_gauche - $this->marge_droite - (4 * $this->cases_largeur);
		$this->etiquette_hauteur = 10; // valeur fixe
		$lignes_hauteur_maximum  = 5;
		$this->lignes_hauteur    = ($this->page_hauteur - $this->marge_haut - $this->marge_bas - $this->etiquette_hauteur) / $lignes_nb;
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
				$this->afficher_note_lomer($note,$border=1,$br=0);
			}
			$this->SetXY($this->marge_gauche , $this->marge_haut+$this->etiquette_hauteur);
		}
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un cartouche
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	cartouche_initialiser()
	//	cartouche_entete()
	//	cartouche_minimal_competence()
	//	cartouche_complet_competence()
	//	cartouche_interligne()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function cartouche_initialiser($detail,$item_nb)
	{
		$this->cases_largeur     = ($detail=='minimal') ? ($this->page_largeur - $this->marge_gauche - $this->marge_droite) / $item_nb : 10 ;
		$this->cases_hauteur     = 5 ;
		$this->reference_largeur = 15 ;
		$this->intitule_largeur  = ($detail=='minimal') ? 0 : $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->reference_largeur - $this->cases_largeur ;
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
		$this->Cell(0 , $this->cases_hauteur , pdf($texte_entete) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->SetFont('Arial' , '' , 8);
	}

	public function cartouche_minimal_competence($item_ref,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $this->cases_largeur , $this->cases_hauteur/2 , pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell( $this->cases_largeur , $this->cases_hauteur , '' , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->afficher_note_lomer($note,$border=1,$br=0);
		$this->SetXY($memo_x+$this->cases_largeur , $memo_y);
	}

	public function cartouche_complet_competence($item_ref,$item_intitule,$note)
	{
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		list($ref_matiere,$ref_suite) = explode('.',$item_ref,2);
		$this->SetFont('Arial' , '' , 7);
		$this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_matiere) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $this->reference_largeur , $this->cases_hauteur/2 , pdf($ref_suite)   , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->SetFont('Arial' , '' , 8);
		$this->SetXY($memo_x , $memo_y);
		$this->Cell( $this->reference_largeur , $this->cases_hauteur , ''                     , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $this->intitule_largeur  , $this->cases_hauteur , pdf($item_intitule) , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->afficher_note_lomer($note,$border=1,$br=1);
	}

	public function cartouche_interligne($nb_lignes)
	{
		$this->SetXY($this->marge_gauche , $this->GetY() + $nb_lignes*$this->cases_hauteur);
	}

	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Méthodes pour la mise en page d'un tableau d'appréciation d'un prof sur un bulletin
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	tableau_appreciation_initialiser()
	//	tableau_appreciation_intitule()
	//	tableau_appreciation_interligne()
	//	tableau_appreciation_rubrique()
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

	public function tableau_appreciation_initialiser($nb_appreciations,$nb_eleves,$with_moyenne)
	{
		$eleve_matiere_largeur = 50; // valeur fixe
		$note_largeur          = ($with_moyenne) ? 10 : 0 ; // valeur fixe
		$nb_lignes_necessaires   = 1 + 2*$nb_appreciations + 0.5*$nb_eleves ; // titre + appreciations (2 lignes / app) + marges entre élèves (0.5 ligne / eleve)
		$this->cases_largeur     = $this->page_largeur - $this->marge_gauche - $this->marge_droite - $eleve_matiere_largeur - $note_largeur ;
		$this->lignes_hauteur    = ($this->page_hauteur - $this->marge_haut - $this->marge_bas) / $nb_lignes_necessaires;
		$this->lignes_hauteur    = min($this->lignes_hauteur,8);
		$this->taille_police     = $this->lignes_hauteur * 2;
		$this->SetMargins($this->marge_gauche , $this->marge_haut , $this->marge_droite);
		$this->AddPage($this->orientation , 'A4');
		$this->SetAutoPageBreak(FALSE);
	}

	public function tableau_appreciation_intitule($intitule)
	{
		$this->SetFont('Arial' , 'B' , $this->taille_police*1.2);
		$this->CellFit( $this->page_largeur - $this->marge_gauche - $this->marge_droite , $this->lignes_hauteur , pdf($intitule)  , 0 /*bordure*/ , 1 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
	}

	public function tableau_appreciation_interligne()
	{
		$this->SetXY($this->marge_gauche , $this->GetY() + 0.5*$this->lignes_hauteur);
	}

	public function tableau_appreciation_rubrique($eleve_nom_prenom,$matiere_nom,$appreciation,$note,$with_moyenne)
	{
		$eleve_matiere_largeur = 50; // valeur fixe
		$note_largeur          = 10; // valeur fixe
		// cadre
		$memo_x = $this->GetX();
		$memo_y = $this->GetY();
		$this->Cell( $this->page_largeur - $this->marge_gauche - $this->marge_droite , 2*$this->lignes_hauteur , '' , 1 /*bordure*/ , 0 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		// nom-prénom + rubrique
		$this->SetXY($memo_x , $memo_y);
		$this->SetFont('Arial' , '' , $this->taille_police);
		$this->CellFit( $eleve_matiere_largeur , $this->lignes_hauteur , pdf($eleve_nom_prenom) , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		$this->CellFit( $eleve_matiere_largeur , $this->lignes_hauteur , pdf($matiere_nom)      , 0 /*bordure*/ , 1 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
		// moyenne
		$this->SetXY($memo_x+$eleve_matiere_largeur , $memo_y);
		if($with_moyenne)
		{
			$moyenne_eleve = ($note!==NULL) ? ( ($_SESSION['OFFICIEL']['BULLETIN_NOTE_SUR_20']) ? number_format($note,1,',','') : ($note*5).'%' ) : '-' ;
			$this->CellFit( $note_largeur , 2*$this->lignes_hauteur , pdf($moyenne_eleve) , 1 /*bordure*/ , 0 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
		}
		else
		{
			$this->Line( $memo_x+$eleve_matiere_largeur , $memo_y , $memo_x+$eleve_matiere_largeur , $memo_y+2*$this->lignes_hauteur );
		}
		// appréciation
		$this->afficher_appreciation( $this->cases_largeur , 2*$this->lignes_hauteur , $this->taille_police , $this->lignes_hauteur , $appreciation );
		$this->SetXY($memo_x , $memo_y+2*$this->lignes_hauteur);
	}

}
?>
