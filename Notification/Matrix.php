<?php

namespace Kanboard\Plugin\Matrix\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;

/**
 * Matrix Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class Matrix extends Base implements NotificationInterface
{
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data)
    {
        $webhook = $this->projectMetadataModel->get($project['id'], 'matrix_webhook_url', $this->configModel->get('matrix_webhook_url'));

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $project, $event_name, $event_data);
        }
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     * @return array
     */
    public function getMessage(array $project, $event_name, array $event_data)
    {
        if($event_name === 'comment.create' && !empty($event_data['comment']['user_id'])) {
            $eventUser = $this->userModel->getById($event_data['comment']['user_id']);
            $author = $this->helper->user->getFullname($eventUser);
            $commentBody = preg_replace("/([\r\n]{6,}|[\n]{3,}|[\r]{3,})/", "\n\n", $event_data['comment']['comment']);
            $body = '<em>'.$author. ' commented: </em>'.nl2br($commentBody);
        } else {
            if ($this->userSession->isLogged()) {
                $author = $this->helper->user->getFullname();
            } else if(!empty($event_data['user_id'])) {
                $eventUser = $this->userModel->getById($event_data['user_id']);
                $author = $this->helper->user->getFullname($eventUser);
            }
            
            if(!empty(@$author)) $body = $this->notificationModel->getTitleWithAuthor($author, $event_name, $event_data);
            else $body = $this->notificationModel->getTitleWithoutAuthor($event_name, $event_data);
            $body = '<em>'.$body.'</em>';
        }
            
        $message = '<strong>['.$project['name']."]</strong> &ndash; ";
        $message .= '<a href="'.$this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true).'">';
        $message .= '<strong>'.$event_data['task']['title']."</strong>";
        $message .= '</a><br />';
        $message .= $body;

        return array(
            'text' => $message,
            'format' => 'html',
            'displayName' => 'Kanboard',
            // 'avatarUrl' => 'LINK_TO_PNG',
        );
    }

    /**
     * Send message to Matrix
     *
     * @access private
     * @param  string    $webhook
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    private function sendMessage($webhook, array $project, $event_name, array $event_data)
    {
        $payload = $this->getMessage($project, $event_name, $event_data);

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}
