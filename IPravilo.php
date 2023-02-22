<?php

abstract class IPravilo{

	protected $imePravila;
	protected $opisPravila;	
	protected $status = true;
	protected $log = array();
	protected $greske = array();
	protected $tabelaZaglavlje = array();
	protected $tabelaRedci = array();
	protected $template = 'application/mail/template.html';

	protected $naslovMaila = null;
	protected $tekstMaila = "";

	protected $formula;
	protected $sqlUpit;


	/**
	 * Getter za polje template
	 * @return mixed
	 */
	public function get_template()
	{
	    return $this->template;
	}
	
	/**
	 * Setter za polje template 
	 * @param mixed $template Value to set
	 * @return self
	 */
	public function set_template($template)
	{
	    $this->template = $template;
	}
	
	public function get_imePravila(){
		return $this->imePravila;
	}

	public function set_imePravila($naziv){
		$this->imePravila = $naziv;
	}

	public function get_opisPravila(){
		return $this->opisPravila;
	}

	public function set_opisPravila($opis){
		$this->opisPravila = $opis;
	}

	public function get_status(){
		return $this->status;
	}

	public function set_status($statusProvjere){
		$this->status = $statusProvjere;
	}

	public function get_log(){
		return $this->log;
	}

	public function set_log(array $log){
		$this->log = $log;
	}

	public function get_greske(){
		return $this->greske;
	}

	public function set_greske(array $greske){
		$this->greske = $greske;
	}

	public function get_tekstMaila(){
		return $this->tekstMaila;
	}
	public function set_tekstMaila($tekstMaila){
		$this->tekstMaila = $tekstMaila;
	}

	public function get_naslovMaila(){
		return $this->naslovMaila;
	}
	
	public function set_naslovMaila($naslovMaila){
		$this->naslovMaila = $naslovMaila;
	}

	public function get_formula(){
		return $this->formula;
	}

	public function set_formula($formula){
		$this->formula = $formula;
	}

	public function get_sqlUpit(){
		return $this->sqlUpit;
	}
	public function set_sqlUpit($sqlUpit){
		$this->sqlUpit = $sqlUpit;
	}

	protected function sastaviHeader(){
		$elementiHeadera = $this->tabelaZaglavlje;
		$header = '<tr bgcolor="#06507F" style="color: #FFFFFF;">';
		foreach ($elementiHeadera as $element) {
			$header .= '<th>' . $element . '</th>';
		}
		$header .= '</tr>';
		return $header;
	}

	protected function sastaviTijelo(){
		$redci = $this->tabelaRedci;
		$tijelo = "";
		foreach($redci as $red){
			$tekstReda =  '<tr>';

			foreach ($red as $element) {
				$tekstReda .= '<td>' . $element . '</td>';
			}
			$tekstReda .=  '</tr>';

			$tijelo .= $tekstReda;
		}
		return $tijelo;
	}

	public function sastaviTekst(){
		$template = file_get_contents($this->template);
		$template = str_replace('@IMEPRAVILA', $this->imePravila, $template);
		$template = str_replace('@OPISPRAVILA', $this->opisPravila, $template);
		$template = str_replace('@PODNASLOV', "", $template);
		$zaglavlje = $this->sastaviHeader();
		$tijelo = $this->sastaviTijelo();

		$template = str_replace('@ZAGLAVLJE', $zaglavlje, $template);
		$template = str_replace('@REDCI', $tijelo, $template);
		return $template;
	}

	public function sastaviOdgovor($uspjeh){
		if($this->tekstMaila==""){
			$this->tekstMaila = $this->sastaviTekst();
		}

		return array(
			'uspjelo' => $uspjeh,
			'naziv' => $this->get_imePravila(),
			'status' => $this->get_status(),
			'log' => $this->get_log(),
			'greske' => $this->get_greske(),
			'tekst' => $this->get_tekstMaila()
			);
	}


	protected function evaluirajFormulu(){

	}

	protected function izvrsiUpit(){

	}

	public static function handlajGresku($errno, $errstr, $errfile, $errline){
		throw new Exception(sprintf("(%s:%d): %s", $errfile, $errline, $errstr), 1);						
		return false;
	}

	public function pokreniProvjeru(){

		set_error_handler(array('IPravilo', 'handlajGresku'));
		try{
			$rezultat = $this->provjeri();			
			restore_error_handler();
			return $rezultat;
		}
		catch(Exception $e){
			$this->set_status(false);
			$this->log[] = "Greška kod provođenja provjere";
			$this->greske[] = $e->__toString();	//var_export($e);
			restore_error_handler();
			return $this->sastaviOdgovor(false);
		}
	}

	abstract public function provjeri();

}

?>