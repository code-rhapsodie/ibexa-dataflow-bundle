<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__.'/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
