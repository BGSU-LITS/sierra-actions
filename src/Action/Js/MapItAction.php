<?php
/**
 * JS Map It Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2017 Bowling Green State University Libraries
 * @license MIT
 */

namespace App\Action\Js;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * An class for the JS Map It action.
 */
class MapItAction extends \App\Action\AbstractAction
{
    /**
     * Method called when class is invoked as an action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     */
    public function __invoke(Request $req, Response $res, array $args)
    {
        // Unused.
        $req;

        // Render form template.
        return $this->view->render(
            $res->withHeader('Content-Type', 'application/javascript'),
            'js/mapit.js.twig',
            $args
        );
    }
}
