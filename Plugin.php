<?php

namespace Kanboard\Plugin\Matrix;

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Matrix Plugin
 *
 * @package  matrix
 * @author   Frederic Guillot (original Mattermost plugin)
 * @author   Chris Metz
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:config:integrations', 'matrix:config/integration');
        $this->template->hook->attach('template:project:integrations', 'matrix:project/integration');
        $this->projectNotificationTypeModel->setType('matrix', t('Matrix'), '\Kanboard\Plugin\Matrix\Notification\Matrix');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginDescription()
    {
        return 'Receive notifications on Matrix';
    }

    public function getPluginAuthor()
    {
        return 'Chris Metz';
    }

    public function getPluginVersion()
    {
        return '1.1.1';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/chriswep/kanboard-plugin-matrix';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
