<?php
// Copyright (c) 2018, Altiria TIC SL
// All rights reserved.
// El uso de este código de ejemplo es solamente para mostrar el uso de la pasarela de envío de SMS de Altiria
// Para un uso personalizado del código, es necesario consultar la API de especificaciones técnicas, donde también podrás encontrar
// más ejemplos de programación en otros lenguajes y otros protocolos (http, REST, web services)
// https://www.altiria.com/api-envio-sms/

	class AltiriaSMS {
		
		public $url;
		public $domainId;
		public $login;
		public $password;
		public $debug;

		public function getUrl() {
			return $this->url;
		}

		public function setUrl($val) {
			$this->url = $val;
			return $this;
		}
		public function getDomainId() {
			return $this->domain;
		}

		public function setDomainId($val) {
			$this->domain = $val;
			return $this;
		}
		public function getLogin() {
			return $this->login;
		}

		public function setLogin($val) {
			$this->login = $val;
			return $this;
		}
		public function getPassword() {
			return $this->password;
		}

		public function setPassword($val) {
			$this->password = $val;
			return $this;
		}
		public function getDebug() {
			return $this->debug;
		}

		public function setDebug($val) {
			$this->debug = $val;
			return $this;
		}

		public function sendSMS($destination, $message, $senderId=null) {

			$return=false;

			// Set the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->getUrl());
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded; charset=UTF-8'));
			curl_setopt($ch, CURLOPT_HEADER, false);
			// Max timeout in seconds to complete http request	
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);


			 $COMANDO='cmd=sendsms&domainId='.$this->getDomainId().'&login='.$this->getLogin().'&passwd='.$this->getPassword();
			 $COMANDO.='&msg='.urlencode($message);

			//Como destinatarios se admite un array de teléfonos, una cadena de teléfonos separados por comas o un único teléfono
			if (is_array($destination)){
			    foreach ($destination as $telefono) {
					$this->logMsg("Add destination ".$telefono);
					$COMANDO.='&dest='.$telefono;
			    }
			}
			else{
				if( strpos($destination, ',') !== false ){
					$destinationTmp= '&dest='.str_replace(',','&dest=',$destination).'&';
				    $COMANDO .=$destinationTmp;
					$this->logMsg("Add destination ".$destinationTmp);
				 }
				 else{
					$COMANDO.='&dest='.$destination;

				 }
			}

			//No es posible utilizar el remitente en América pero sí en España y Europa
			if (!isset($senderId) || empty($senderId)) {
				$this->logMsg("NO senderId ");
			}
			else{				
				$COMANDO.='&senderId='.$senderId;
				$this->logMsg("Add senderId ".$senderId);
			}


			// Set the request as a POST FIELD for curl.
			curl_setopt($ch, CURLOPT_POSTFIELDS, $COMANDO);

			// Get response from the server.
			$httpResponse = curl_exec($ch);


			if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){
				$this->logMsg("Server Altiria response: ".$httpResponse);

				if (strstr($httpResponse,"ERROR errNum")){
					$this->logMsg("Error sending SMS: ".$httpResponse);
					return false;
				}
				else
					$return = $httpResponse;
			}
			else{
				$this->logMsg("Error sending SMS: ".curl_error($ch).'('.curl_errno($ch).')'.$httpResponse);
				$return = false;
			}

			curl_close($ch);
			
			return $return;
		}


		public static function getCredit() {
			$return=false;
			// Set the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->getUrl());
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded; charset=UTF-8'));
			curl_setopt($ch, CURLOPT_HEADER, false);
			// Max timeout in seconds to complete http request
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);



			$COMANDO='cmd=getcredit&domainId='.$this->getDomainId().'&login='.$this->getLogin().'&passwd='.$this->getPassword();
			

			// Set the request as a POST FIELD for curl.
			curl_setopt($ch, CURLOPT_POSTFIELDS, $COMANDO);

			// Get response from the server.
			$httpResponse = curl_exec($ch);


			if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){
				$this->logMsg("Server Altiria response: ".$httpResponse);

				if (strstr($httpResponse,"ERROR errNum")){
					$this->logMsg("Error asking SMS credit: ".$httpResponse);
					$return = false;
				}
				else
					$return = $httpResponse;
			}
			else{
				$this->logMsg("Error asking SMS credit: ".curl_error($ch).'('.curl_errno($ch).')'.$httpResponse);

				$return = false;
			}

			curl_close($ch);
			return $return;
		}

        public function logMsg($msg) {
        	if ($this->getDebug()===true)
	            error_log("\n".date(DATE_RFC2822)." : ".$msg."\r\n", 3, "app.log");
        }

	}
?>