<?php

class Validators{

    /**
	 * Summary of validDateRegex
	 * Regex that checks if a date is valid according to the ISO 8601 standard
	 */
    public static $validDateRegex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
    /**
	 * Summary of validColorCodeRegex
	 * Regex that checks if a color code is valid according to hex color codes
	 */
    public static $validColorCodeRegex = '/#([a-fA-F0-9]{3}){1,2}\b/';

    public static function validateName($name){
        if (strlen($name) > 255) {
            throw new Exception('Name exceeds limit of 255 characters!');
		}
    }
    public static function validateStartDate($startDate){
        if (!(preg_match(self::$validDateRegex, $startDate) > 0)) {
            throw new Exception ("Invalid start date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z");
        }
    }
    public static function validateEndDate($startDate, $endDate){
        if ($endDate != null) {
			if ((!preg_match(self::$validDateRegex, $endDate) > 0)) {
                throw new Exception("Invalid end date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z");
			}
			if ($startDate > $endDate) {
                throw new Exception("End date cannot be before start date!");
			}
		}
    }

    public static function validateExternalId($externalId){
        if ($externalId != null) {
            if (strlen($externalId) > 255) {
                throw new Exception("External ID exceeds limit of 255 characters!");
            }
        }
    }

    public static function validateColor($color){
        if ($color != null) {
            if ((!preg_match(self::$validColorCodeRegex, $color))) {
                throw new Exception("Invalid color code");
            }
        }
    }
}