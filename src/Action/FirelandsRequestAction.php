<?php
/**
 * Firelands Request Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2017 Bowling Green State University Libraries
 * @license MIT
 */

namespace App\Action;

use \App\Exception\RequestException;

use Slim\Flash\Messages;
use App\Session;
use Slim\Views\Twig;
use Swift_Mailer;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * An class for the Firelands Request action.
 */
class FirelandsRequestAction extends ScheduleForUseAction
{
    /**
     * The display name of this action.
     * @var string
     */
    protected $action = 'Firelands Request';

    /**
     * The field names used by this action.
     * @var array
     */
    protected $fields = ['name', 'email', 'id', 'comments'];

    /**
     * Validates a request to the action.
     * @param array $args Arguments provided to the action.
     */
    protected function validateRequest(array &$args)
    {
        // Check that all fields except for comments have been completed.
        foreach ($this->fields as $field) {
            if ($field !== 'comments' && empty($args['session'][$field])) {
                $args['errors'][] = $field;
            }
        }

        if (!empty($args['errors'])) {
            throw new RequestException(
                'Please complete all missing fields.'
            );
        }

        // Check that the specified email address is potentially valid.
        if (!filter_var($args['session']['email'], FILTER_VALIDATE_EMAIL)) {
            $args['errors'][] = 'email';

            throw new RequestException(
                'Please specify a valid email address.'
            );
        }

        // Check that the specified email address is a BGSU email account.
        if (!preg_match('/@(\w+\.)?bgsu\.edu$/', $args['session']['email'])) {
            $args['errors'][] = 'email';

            throw new RequestException(
                'Please specify a BGSU email address.'
            );
        }

        // Check that the ID number is exactly ten digits.
        if (!preg_match('/^\d{10}$/', $args['session']['id'])) {
            $args['errors'][] = 'id';

            throw new RequestException(
                'Please specify a 10-digit BGSU ID number.'
            );
        }
    }
}
