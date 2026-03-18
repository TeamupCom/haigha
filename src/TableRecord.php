<?php

namespace Haigha;

#[\AllowDynamicProperties]
class TableRecord
{
    private array $__meta = [];

    public function __construct(string $tablename)
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
            return (string) $this->id;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function __call($key, $params)
    {
        if (0 === strpos($key, 'set')) {
            $var = lcfirst(substr($key, 3));
            $value = $params[0];
            if ($value instanceof \DateTime) {
                $value = $value->getTimeStamp();
            }
            $this->$var = $value;
        } else {
            throw new \RuntimeException("Unexpected key passed to magic call to TableRecord: $key");
        }
    }

    public function __meta($key)
    {
        return $this->__meta[$key];
    }
}
