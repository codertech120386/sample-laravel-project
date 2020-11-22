<?php

namespace App\Configurators;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class SearchWorkspaceIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    /**
     * @var array
     */
    protected $settings = [
        //
    ];
}
