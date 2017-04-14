<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'no_unreachable_default_argument_value' => false,
        'heredoc_to_nowdoc' => false,
    ))
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;
