<?php

/*
 * This file is part of the ModularBundle project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Test\Controller;

use Symfony\Component\HttpFoundation\Response;

class TestController
{
    public function __invoke()
    {
        return new Response('pong', 200);
    }
}
