<?php

namespace App\Twig;

use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'pluralize']),
        ];
    }

    public function pluralize(int $count, string $singular, ?string $plural = null): string
    {

        /* si le résultat est = à 1 on prend le singulier sinon on prend le pluriel */
        $result = $count === 1 ? $singular : $plural;
        /*  on retourne la quantité et le resultat */
        return "$count $result";
    }
}
