<?php

declare(strict_types=1);

namespace App\Support;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic;

use function config;

class Csp extends Basic
{
    public function configure()
    {
        parent::configure();

        $this->addDirective(Directive::FONT, '*');
        $this->addDirective(Directive::IMG, '*');
        $this->addDirective(Directive::IMG, 'data: w3.org/svg/2000');
        $this->addDirective(Directive::STYLE, '*');

        $this->addDirective(Directive::CONNECT, config('app.url'));
        $this->addDirective(Directive::DEFAULT, config('app.url'));

        $this->addDirective(Directive::CONNECT, 'wss://nexus.test:5173/');
    }
}
