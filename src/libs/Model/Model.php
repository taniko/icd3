<?php
namespace Hrgruri\Icd3\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class Model
{
    protected $capsule;

    public function __construct(Capsule $capsule)
    {
        $this->capsule = $capsule;
    }
}
