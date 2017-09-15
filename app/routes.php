<?php
/**
 * Application Routes
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2017 Bowling Green State University Libraries
 * @license MIT
 */

namespace App\Action;

use Slim\Container;
use Slim\Flash\Messages;
use App\Session;
use Slim\Views\Twig;
use Swift_Mailer;

// Index action.
$container[IndexAction::class] = function (Container $container) {
    return new IndexAction($container['settings']['app']['redirect']);
};

$app->get('/', IndexAction::class);

// Javascript action.
$container[JsAction::class] = function (Container $container) {
    return new JsAction(
        $container[Messages::class],
        $container[Session::class],
        $container[Twig::class]
    );
};

$app->get('/button.js', JsAction::class);

// Schedule For Use action.
$container[ScheduleForUseAction::class] = function (Container $container) {
    return new ScheduleForUseAction(
        $container[Messages::class],
        $container[Session::class],
        $container[Twig::class],
        $container[Swift_Mailer::class],
        $container['settings']['actions']
    );
};

$app->map(['GET', 'POST'], '/schedule-for-use', ScheduleForUseAction::class);

// Firelands Request action.
$container[FirelandsRequestAction::class] = function (Container $container) {
    return new FirelandsRequestAction(
        $container[Messages::class],
        $container[Session::class],
        $container[Twig::class],
        $container[Swift_Mailer::class],
        $container['settings']['actions']
    );
};

$app->map(['GET', 'POST'], '/firelands-request', FirelandsRequestAction::class);
