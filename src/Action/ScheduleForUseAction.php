<?php
/**
 * Schedule For Use Action Class
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
 * A class for the schedule for use action.
 */
class ScheduleForUseAction extends AbstractAction
{
    /**
     * Email sender.
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * Actions handled by the application.
     * @var array
     */
    private $actions;

    /**
     * The field names used by this action.
     * @var array
     */
    protected $fields = ['name', 'email', 'tel', 'from', 'date', 'comments'];

    /**
     * Construct the action with objects and configuration.
     * @param Messages $flash Flash messenger.
     * @param Session $session Session manager.
     * @param Twig $view View renderer.
     * @param Swift_Mailer Email sender.
     * @param array $actions Actions handled by the application.
     */
    public function __construct(
        Messages $flash,
        Session $session,
        Twig $view,
        Swift_Mailer $mailer,
        array $actions
    ) {
        parent::__construct($flash, $session, $view);
        $this->mailer = $mailer;
        $this->actions = $actions;
    }

    /**
     * Method called when class is invoked as an action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     */
    public function __invoke(Request $req, Response $res, array $args)
    {
        // Set the default template for the action.
        $template = 'action/ScheduleForUse.html.twig';

        // Add flash messages to arguments.
        $args['messages'] = $this->messages();

        // Add any query parameters to the arguments.
        $args['query'] = $req->getQueryParams();

        // Add current action to arguments based on query.
        $args['action'] = $this->getAction($args['query']);

        // Check if an action was provided.
        if (!empty($args['action'])) {
            // Get the data about the current action.
            $action = $this->actions[$args['action']];

            // Set the request window for the action.
            $args['window'] = $action['window'];

            // Get the template for the current action.
            $template = 'action/' .
                preg_replace('/\s+/', '', $action['action']) . '.html.twig';

            // Check if a POST form was submitted.
            if ($req->getMethod() === 'POST') {
                // Add values from all form fields to arguments.
                foreach ($this->fields as $field) {
                    $args['session'][$field] = $req->getParsedBodyParam($field);
                }

                // Store the values from all form fields to the session.
                $this->session->actions_schedule = $args['session'];

                try {
                    // Validate and send the request.
                    $this->validateCsrf($req);
                    $this->validateRequest($args);
                    $this->sendEmail($args);

                    // Redirect without a query to notify user request was
                    // successful, but not let them continue without selecting
                    // another item from the catalog.
                    $this->flash->addMessage(
                        'success',
                        'Your request has been sent.'
                    );

                    return $res->withStatus(302)->withHeader(
                        'Location',
                        $req->getUri()->withQuery('')
                    );
                } catch (RequestException $exception) {
                    // Part of the request failed, so notify user.
                    $args['messages'][] = [
                        'level' => 'failure',
                        'message' => $exception->getMessage()
                    ];
                }
            }
        }

        // Add any session data to the arguments if no form was submitted.
        if (empty($args['session'])) {
            if (!empty($this->session->actions_schedule)) {
                $args['session'] = $this->session->actions_schedule;
            }
        }

        // Render the template.
        return $this->view->render($res, $template, $args);
    }

    /**
     * Get the current action from the query.
     * @param array $query The query data.
     * @return string|false The current action, or false if none is selected.
     */
    protected function getAction(array $query)
    {
        // Make sure Permalink, Location, and Status are specified.
        foreach (['Permalink', 'Location', 'Status'] as $key) {
            if (empty($query[$key])) {
                return false;
            }
        }

        // Check to see if any of the actions match what is
        // specified. If so, return the key of that action.
        foreach ($this->actions as $key => $value) {
            if (preg_match('/' . $value['location'] . '/', $query['Location'])
             && preg_match('/' . $value['status'] . '/', $query['Status'])) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Sends an email based on the given arguments.
     * @param array $args Arguments for the email.
     */
    protected function sendEmail(array $args)
    {
        // Create a query item of the Record Number from the Permalink.
        $args['query']['Record_Number'] = preg_replace(
            '/^.*(b\d{7,}).*$/',
            '$1a',
            $args['query']['Permalink']
        );

        // Add current action to arguments based on query.
        $args['action'] = $this->getAction($args['query']);

        // Get the data about the current action.
        $action = $this->actions[$args['action']];

        // Add the email from that action to the arguments.
        $args['email'] = $action['email'];

        // Set the subject to the current action.
        $mailSubject = $action['action'];

        // Add the date of the action to the subject if available.
        if (!empty($args['session']['date'])) {
            $mailSubject .= ' ' .
                date('Y-m-d', strtotime($args['session']['date']));
        }

        // Add the title of the item to the subject if available.
        if (!empty($args['query']['Title'])) {
            $mailSubject .= ': ' .
                $args['query']['Title'];
        }

        // Set the recipient email to the address from the action.
        $mailTo = $action['email'];

        // Set the sender email from the session data.
        $mailFrom = [$args['session']['email'] => $args['session']['name']];

        // Get the template for the current action.
        $template = 'email/' .
            preg_replace('/\s+/', '', $action['action']) . '.html.twig';

        try {
            // Create a message with all of the provided data.
            $message = $this->mailer->createMessage()
                ->setSubject($mailSubject)
                ->setFrom($mailFrom)
                ->setTo($mailTo)
                ->setCc($mailFrom)
                ->setBody($this->view->fetch($template, $args), 'text/html');

            // Send the message.
            if (!$this->mailer->send($message)) {
                throw new RequestException(
                    'Could not send email.'
                );
            }
        } catch (\Swift_SwiftException $e) {
            throw new RequestException(
                'An unexpected error occurred. Please try again.'
            );
        }
    }

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

        // Check that the phone number consists of at least ten digits.
        if (preg_match_all('/\d/', $args['session']['tel']) < 10) {
            $args['errors'][] = 'tel';

            throw new RequestException(
                'Please specify a valid phone number.'
            );
        }

        // Check that the date is at least three week days in the future.
        if (strtotime($args['session']['date']) < strtotime($args['window'])) {
            $args['errors'][] = 'date';

            throw new RequestException(
                'Please choose a date at least three weekdays from now.'
            );
        }
    }
}
