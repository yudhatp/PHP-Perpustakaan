<?php
/**
 * @author Achmad Solichin
 * @website http://achmatim.net
 * @email achmatim@gmail.com
 */
$root = $_SERVER['DOCUMENT_ROOT'];
require($root.'/orangehrm-3.3.2/symfony/lib/export/pdf/fpdf.php');

class FPDF_AutoWrapTable extends FPDF {
  	private $data = array();
  	private $options = array(
  		'filename' => '',
  		'destinationfile' => '',
  		'paper_size'=>'A4',
  		'orientation'=>'P'
  	);
 
  	function __construct($data = array(), $options = array()) {
    	parent::__construct();
    	$this->data = $data;
    	$this->options = $options;
	}
 
	public function rptDetailData () {
		//
		$border = 0;
		$this->AddPage();
		$this->SetAutoPageBreak(true,60);
		$this->AliasNbPages();
		$left = 25;
 
		//header
		$this->SetFont("", "B", 15);
		$this->MultiCell(0, 12, 'PT. Aplikanusa Lintasarta');
		$this->Cell(0, 1, " ", "B");
		$this->Ln(10);
		$this->SetFont("", "B", 12);
		$this->SetX($left); $this->Cell(0, 10, 'LAPORAN DATA KARYAWAN', 0, 1,'C');
		$this->Ln(10);
 
		$h = 13;
		$left = 40;
		$top = 80;	
		#tableheader
		$this->SetFillColor(200,200,200);	
		$left = $this->GetX();
		$this->Cell(10,$h,'NO',1,0,'L',true);
		$this->SetX($left += 10); $this->Cell(20, $h, 'TANGGAL', 1, 0, 'C',true);
		$this->SetX($left += 40); $this->Cell(30, $h, 'NAMA PEGAWAI', 1, 0, 'C',true);
		$this->SetX($left += 50); $this->Cell(20, $h, 'MASUK', 1, 0, 'C',true);
		$this->SetX($left += 10); $this->Cell(20, $h, 'PULANG', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(25, $h, 'MASUK REAL', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(35, $h, 'KETERANGAN', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(25, $h, 'PULANG REAL', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(35, $h, 'KETERANGAN', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(20, $h, 'JUMLAH JAM KERJA', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(30, $h, 'ATTACHMENT', 1, 1, 'C',true);
		$this->SetX($left += 10); $this->Cell(20, $h, 'KETERANGAN', 1, 1, 'C',true);
		//$this->Ln(20);
 
		$this->SetFont('Arial','',9);
		//$this->SetWidths(array(20,75,100,150,100,100));
		$this->SetWidths(array(20, 30, 20, 20, 25, 35, 25, 35, 20, 30, 20 ));
		$this->SetAligns(array('C','L','L','L','L','L'));
		$no = 1; $this->SetFillColor(255);
		foreach ($this->data as $baris) {
			$this->Row(
				array($no++, 
				$baris['Tgl'], 
				$baris['empName'], 
				$baris['punchInS'], 
				$baris['punchOutS'], 
				$baris['punchIn'], 
				$baris['NoteMasuk'], 
				$baris['punchOut'], 
				$baris['NoteKeluar'], 
				$baris['punchOutS'], 
				$baris['jumlah'],
				$baris['Attachment'], 
				$baris['Note']
			));
		}
 
	}
 
	public function printPDF () {
 
		if ($this->options['paper_size'] == "F4") {
			$a = 8.3 * 72; //1 inch = 72 pt
			$b = 13.0 * 72;
			//$this->FPDF($this->options['orientation'], "pt", array($a,$b));
		} else {
			//$this->FPDF($this->options['orientation'], "pt", $this->options['paper_size']);
		}
 
		$this->SetTitle('Laporan Harian All User', true);
	    $this->SetAutoPageBreak(false);
	    $this->AliasNbPages();
	    $this->SetFont("helvetica", "B", 10);
	    //$this->AddPage();
 
	    $this->rptDetailData();
 
	    $this->Output($this->options['filename'],$this->options['destinationfile']);
  	}
 
  	private $widths;
	private $aligns;
 
	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}
 
	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}
 
	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=10*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,10,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}
 
	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}
 
	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}
} //end of class
?>