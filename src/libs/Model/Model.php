<?php
namespace Hrgruri\Icd3\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class Model
{
    protected $capsule;
    protected $logger;

    public function __construct(Capsule $capsule, $logger = null)
    {
        $this->capsule  = $capsule;
        $this->logger   = $logger;
    }
}
