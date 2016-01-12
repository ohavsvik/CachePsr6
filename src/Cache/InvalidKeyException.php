<?php

namespace Anax\Cache;

/**
 *
 */
class InvalidKeyException extends \Exception implements \Psr\Cache\InvalidArgumentException
{

    private $invalidKeys;

    public function __construct($invalidKeys, $message = "", $code = 0, Exception $previous = null)
    {
        $this->invalidKeys = $invalidKeys;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        $stringValues = "";
        foreach ($this->invalidKeys as $val) {
            $stringValues .= implode(" ", $val);
        }
        return __CLASS__ . ": Invalid values in key ( {$stringValues} )\n";
    }
}
