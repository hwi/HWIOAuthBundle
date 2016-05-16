<?php

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in([__DIR__])
    )
;
