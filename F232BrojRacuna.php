<?php

require_once('IPravilo.php');

class F232BrojRacuna extends IPravilo{

	protected $formula = /*MARKERFORMULA*/"@FORMULA"/*MARKERFORMULA*/;
	protected $sqlUpit = /*MARKERSQL*/"@SQLUPIT"/*MARKERSQL*/;

/*MARKERPHP*/
function izvrsiOracleUpit($veza, $upit){
	$s = oci_parse($veza, $upit);
	oci_execute($s);
	$rezultat = array();

	while (($red = oci_fetch_array($s, OCI_BOTH))) {
		$rezultat[] = $red;
	}

	oci_free_statement($s);
	return $rezultat;
}

protected function sastaviTijelo(){
	$redci = $this->tabelaRedci;
	$tijelo = '';
	
	foreach($redci as $red){
		$tekstReda =  '<tr>';	
		$i = 0;
		foreach ($red as $element) {
			if($i == 2){
				$broj =  $element;
				$tekstReda .= '<td align="right" style="border: #666666 1px solid;">' . $broj . '&nbsp;</td>';		
			} 
			else {
				$tekstReda .= '<td align="left" style="border: #666666 1px solid;">' . $element . '</td>';					
			}
			$i++;
		}		
		$tekstReda .=  '</tr>';

		$tijelo .= $tekstReda;
	}
	return $tijelo;
}

public function provjeri(){
include ('login.php.inc');
//dodaj logiku pravila, upiši true ak sve štima, false ak ima neispravnosti 
//false = crvena svjetla, alarmi, slanje mailova 
$sveJeURedu = false;

$datefrom=date('Y-m-d', strtotime("-1 day"));
$dateto=date('Y-m-d', strtotime("now"));
	
//****************************************************************************************************************************
      $this->log[] = 'DOBAVLJANJE PROMETA IZ INFISK2';
//****************************************************************************************************************************
      $c = oci_connect($username, $password, $host);

      $upit="SELECT COUNT(*) from FISK.RACUN where oznakaposlovnogprostora='POSL0232' and datumizdavanja > to_date(to_char(sysdate, 'YYYY-MM-DD') || ' 00:00', 'YYYY-MM-DD HH24:MI')";

      $s = oci_parse($c, $upit);
      oci_execute($s);
	
	  $this->tabelaZaglavlje = array( 'Trgovina' ,' ' ,'Br. računa');
	
	  $rezultat = oci_fetch_array($s, OCI_NUM+OCI_RETURN_NULLS);
		  
	  $Ukupno=$rezultat[0];	
		
	  $this->tabelaRedci[] = array('F232','Ukupni broj računa do ' . date('H:i',strtotime('Now')), $Ukupno);

      oci_free_statement($s);
      oci_close($c);

$this->set_naslovMaila("Crikvenica: broj računa kod novootvorenja");
$rezultat = $this->sastaviOdgovor($sveJeURedu); 
return $rezultat; 
} /*MARKERPHP*/

}