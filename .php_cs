<?php

$header = <<<EOF
This file is part of the HWIOAuthBundle package.

(c) Hardware.Info <opensource@hardware.info>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'no_unreachable_default_argument_value' => false,
        'heredoc_to_nowdoc' => false,
        'header_comment' => array('header' => $header),
    ))
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    )
;
