<?php

require_once('IPravilo.php');

class F232Novootvorenje extends IPravilo{

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

function uspostaviFiskArhivaVezu(){
	$host="HR-DB11.hr.lidl.net/HRHQ001F.hr.lidl.net";
	$user="pomakro";
	$pass="Orkam0#";
	$veza = oci_connect($user, $pass, $host);
	return $veza;

}

protected function sastaviTijelo(){
	$redci = $this->tabelaRedci;
	$tijelo = '';
	
	foreach($redci as $red){
		$tekstReda =  '<tr>';	
		$i = 0;
		foreach ($red as $element) {
			if($i == 2){
				$broj =  is_numeric($element) ? number_format($element, 2, ',', '.') : 0.00;
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
	  $vezaArhiva = $this->uspostaviFiskArhivaVezu();

      $upit="select oznakaposlovnogprostora, oznakanaplatnoguredjaja, sum(ukupaniznos) from fisk.racun where  trunc(Datumizdavanja) = DATE '" . $dateto . "' and oznakaposlovnogprostora='POSL0232' group by trunc(Datumizdavanja), oznakaposlovnogprostora, oznakanaplatnoguredjaja order by oznakanaplatnoguredjaja";

	//valjda sam dobro izračunal od 22.8(zabok) do 3.10
	//na dan otvorenja okini pokretanje provjere ručno, da vidiš dal dobar dan basa za zaboky
      $danaOdZadnjeProdaje = 42;
      $danPrijeZadnjeProdaje = $danaOdZadnjeProdaje + 1;

      $upitPrijasnjaProdaja = "SELECT NVL(sum(ukupaniznos),0) AS suma FROM fisk.racun WHERE Datumizdavanja BETWEEN to_date(to_char(sysdate, 'YYYY-MM-DD') || ' 00:00', 'YYYY-MM-DD HH24:MI') - $danaOdZadnjeProdaje AND sysdate - $danaOdZadnjeProdaje AND oznakaposlovnogprostora='POSL0234'";
	      
      $datumUsporedbe = date('d.m.Y',strtotime("-$danaOdZadnjeProdaje days"));
	
      $s = oci_parse($c, $upit);
	  $sPrijasnjaProdaja = oci_parse($c, $upitPrijasnjaProdaja);
	
      oci_execute($s);
	  oci_execute($sPrijasnjaProdaja);

	$this->tabelaZaglavlje = array( 'Trgovina' ,'Blagajna' ,'Promet');
	
	$Ukupno=0;	
	$redPrijasnjaProdaja = $this->izvrsiOracleUpit($vezaArhiva, $upitPrijasnjaProdaja);
	$SumaPrijasnjaProdaja = $redPrijasnjaProdaja[0][0];

	while ($row = oci_fetch_array($s, OCI_NUM+OCI_RETURN_NULLS)) {
		$INFISKTrgovina=substr($row[0], -4);
		$INFISKTrgovina=$INFISKTrgovina*1;
		$INFISKNaplatnogUredjaja=$row[1];
		$INFISKPrometVal=$row[2];
		$Ukupno=$Ukupno+$INFISKPrometVal;
		$this->tabelaRedci[] = array($INFISKTrgovina,$INFISKNaplatnogUredjaja,$INFISKPrometVal);	
		
	}	
	$this->tabelaRedci[] = array('','Ukupni promet do ' . date('H:i',strtotime('Now')), $Ukupno);
	
	$this->tabelaRedci[] = array('',"Promet u Zaboku (trg. 0234, $datumUsporedbe, isti period):", $SumaPrijasnjaProdaja);
	
	oci_free_statement($s);
	oci_close($c);
	oci_close($vezaArhiva);

	$this->set_naslovMaila("Bjelovar: promet kod novootvorenja");
	$rezultat = $this->sastaviOdgovor($sveJeURedu); 
	return $rezultat; 
} /*MARKERPHP*/

}