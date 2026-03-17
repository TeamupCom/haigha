<?php

namespace Haigha;

use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface;
use Nelmio\Alice\Fixtures\Fixture;
use Ramsey\Uuid\Uuid;

class TableRecordInstantiator implements MethodInterface
{
    private $ids = [];
    private $auto_uuid_column = null;

    /**
     * Use this method to define a specific column to automatically receive a generated UUID.
     */
    public function setAutoUuidColumn($colum_name): void
    {
        $this->auto_uuid_column = $colum_name;
    }

    /**
    * {@inheritDoc}
    */
    public function canInstantiate(Fixture $fixture): bool
    {
        return 0 === strpos($fixture->getClass(), 'table.');
    }

    /**
    * {@inheritDoc}
    */
    public function instantiate(Fixture $fixture)
    {
        $tablename = substr($fixture->getClass(), 6);
        $r = new TableRecord($tablename);

        if ($this->auto_uuid_column) {
            $uuid = (string) Uuid::uuid4();
            $r->setR_uuid($uuid);
        }

        return $r;
    }
}
