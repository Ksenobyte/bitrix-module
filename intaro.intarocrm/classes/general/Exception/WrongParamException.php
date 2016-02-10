<?php

namespace RetailCrm\Exception;

class WrongParamException extends \RuntimeException
{
	function __construct($el, $key = "") {
		$code = $el;
		if ($key == "array") {
			$message = 'Отсутствуют необходимые элементы массива';
		} elseif($key == "search") {
			$message = 'Не удалось найти заказ с данным товаром';
		} else {
			$message = 'Аргумент не передан или имеет неверное значение';
		}
	}
}
