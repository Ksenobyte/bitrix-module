<?php

namespace RetailCrm\Exception;

	class DispatchInterruptException extends \RuntimeException
	{
		function __construct() {
			$message = 'Ошибка отправки в RetailCrm';
		}
	} 
