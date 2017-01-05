<?php
//rotinas para habilitar a exibicao de erros na pagina. Tire se nao quiser.
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', '1');
 
include "PhpSerial.php"; //import da biblioteca de serial com php
$read = "";
$salt = "tfgECO2016";		

$serial = new phpSerial(); //Cria um novo objeto para comunicacao serial
//$serial->deviceSet("/dev/ttyS0"); //associa esse objeto com a serial do Arduino
//$serial->confBaudRate(57600); //configura baudrate em 9600
$serial->deviceSet("/dev/ttyACM0"); 
$serial->confBaudRate(9600); 
$serial->confParity("none"); //sem paridade
$serial->confCharacterLength(8); //8 bits de mensagem
$serial->confStopBits(1); //1 bit de parada
$serial->confFlowControl("none"); //sem controle de fluxo		
$serial->deviceOpen(); //abre o dispositivo serial para comunicacao

//Se receber 'a' via GET na Pagina
if(isset($_GET['a'])){
 $cmd = "digitalON";
 $str = "$" . "TFG" . "," . $cmd . "#"; 
 $strmd5 = $str . md5($str . $salt) . "\n";
 $serial->sendMessage($strmd5); //envia o caractere 'a' via Serial pro Arduino
 //echo printf "\n";
 sleep(1); //delay para o Arduino enviar a resposta.
 $read = $serial->readPort(); //faz a leitura da resposta na variavel $read
}

if(isset($_GET['c'])){
 $cmd = "get";
 $str = "$" . "TFG" . "," . $cmd . "#"; 
 $strmd5 = $str . md5($str . $salt) . "\n";
 $serial->sendMessage($strmd5); //envia o caractere 'a' via Serial pro Arduino
 //echo printf "\n";
 sleep(1); //delay para o Arduino enviar a resposta.
 $read = $serial->readPort(); //faz a leitura da resposta na variavel $read
}

//Se receber 'd' via GET na pagina
if(isset($_GET['d'])){
 $cmd = "digitalOFF";
 $str = "$" . "TFG" . "," . $cmd . "#"; 
 $strmd5 = $str . md5($str . $salt) . "\n";
 $serial->sendMessage($strmd5); //envia o caractere 'a' via Serial pro Arduino
 sleep(1); //delay para o Arduino enviar a resposta
 $read = $serial->readPort(); //faz a leitura da resposta na variavel $read
}

if(isset($_GET['b'])){
 $cmd = "md5";
 $str = "$" . "TFG" . "," . $cmd . "#"; 
 $strmd5 = $str . md5($str . $salt - 1) . "\n";
 $serial->sendMessage($strmd5); //envia o caractere 'a' via Serial pro Arduino
 sleep(1); //delay para o Arduino enviar a resposta
 $read = $serial->readPort(); //faz a leitura da resposta na variavel $read
}


if(isset($_GET['ref'])){
 $ref = $_GET['ref1']; 
 $kp = $_GET['Kp']; 
 $ki = $_GET['Ki']; 
 $kd = $_GET['Kd']; 
 $cmd = $ref . "&" . $kp . "+" . $ki . "*" . $kd;
 $str = "$" . "TFG" . "," . $cmd . "#"; 
 $strmd5 = $str . md5($str . $salt) . "\n";
 $serial->sendMessage($strmd5); //envia o caractere 'a' via Serial pro Arduino
 sleep(1); //delay para o Arduino enviar a resposta
 $read = $serial->readPort(); //faz a leitura da resposta na variavel $read
}

$serial->deviceClose(); //encerra a conexao serial
 
?>
 
 
<html>	
<head>
<link rel="stylesheet" type="text/css" href="tfg.css">
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta charset="UTF-8">
</head>


<body style="margin: 0 0 0 0;">
<div class="art1">
	<div class="dropdown" style="float:left;">
	  <button class="dropbtn">About</button>
	  <div class="dropdown-content" style="left:0;">
		<a>Trabalho Final de Graduação<br>André Luiz de Souza Antonieto<br>Engenharia da Computação<br>Orientador: Rodrigo Almeida<br>2016</a>
	  </div>
	</div>
	<div class="cent">
		<h1 style="font-family: Verdana;font-size: 50px;"><center> Automação Industrial <img hspace="20" src="logo_unifei.png" width="150" height="150" align="center"></center></h1>
	</div>
</div>
<div class="art2">
	<center>
			<br><br><br>
			
			<input type="button"
			 onclick="location.href='/tfg.php?a=1'"
			 value="Digital ON" 
			 />
			 
			<input type="button"
			 onclick="location.href='/tfg.php?d=1'"
			 value="Digital OFF" />
			 
			 <input type="button"
			 onclick="location.href='/tfg.php?b=1'"
			 value="MD5 False" />
			 
			 <input type="button"
			 onclick="location.href='/tfg.php?c=1'"
			 value="GET"/>
			 
			<br><br><br>
	</center>	
		
		<form name="form2" action="" method="">
			<center>
		 <font size="5" style="font-family: Verdana; font-weight:bold;">Kp : </font><input type="text" value="" name="Kp" style="width: 100px;height: 25px" required>
		 <font size="5" style="font-family: Verdana; font-weight:bold;">Ki : </font><input type="text" value="" name="Ki" style="width: 100px;height: 25px" required>
		 <font size="5" style="font-family: Verdana; font-weight:bold;">Kd : </font><input type="text" value="" name="Kd" style="width: 100px;height: 25px" required>	
		 <br><br>
		 <font size="5" style="font-family: Verdana; font-weight:bold;">Reference : </font><input type="text" value="" name="ref1" style="width: 100px;height: 25px" required> 
		 <br><br>
		 <input type="Submit" name="ref" value="Send">
		 </center>
		</form>
		
		
	<br><br>
	<center>
			<font size="5" style="font-family: Courier; color: #F73333; font-weight:bold;"><?php echo $read?></font>
	</center>
</div>
</body>

</html>

