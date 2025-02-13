<?php

namespace Haigha;

use RuntimeException;
use Exception;

#[\AllowDynamicProperties]
class TableRecord
{
    // @codingStandardsIgnoreStart
    private $__meta = array();
    // @codingStandardsIgnoreEnd

    public function __construct($tablename)
    {
        $this->__meta['tablename'] = $tablename;
    }

    /**
     * @todo Determine primary field name
     *
     * @return string ID
     */
    public function __toString()
    {
        try {
            return (string)$this->id;
        } catch (Exception $exception) {
            return '';
        }
    }

    public function __call($key, $params)
    {
        if (substr($key, 0, 3) == 'set') {
            $var = lcfirst(substr($key, 3));
            $value = $params[0];
            if ($value instanceof \DateTime) {
                $value = $value->getTimeStamp();
            }
            $this->$var = $value;
        } else {
            throw new RuntimeException(sprintf(
                "Unexpected key passed to magic call to TableRecord: '%s'",
                $key
            ));
        }
    }

    public function __meta($key)
    {
        return $this->__meta[$key];
    }
}
