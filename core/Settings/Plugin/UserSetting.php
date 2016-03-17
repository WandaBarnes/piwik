<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Exception;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage;

/**
 * Describes a per user setting. Each user will be able to change this setting for themselves,
 * but not for other users.
 */
class UserSetting extends Setting
{
    private $userLogin = null;

    /**
     * Null while not initialized, bool otherwise.
     * @var null|bool
     */
    private $hasWritePermission = null;

    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $pluginName The name of the plugin the setting belongs to
     * @param string $userLogin The name of the user the value should be set or get for
     * @throws Exception
     */
    public function __construct($name, $defaultValue, $pluginName, $userLogin)
    {
        parent::__construct($name, $defaultValue, $pluginName);

        if (empty($userLogin)) {
            throw new Exception('No userLogin given');
        }

        $this->userLogin = $userLogin;

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $factory->getPluginStorage($this->pluginName, $this->userLogin);
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if (isset($this->hasWritePermission)) {
            return $this->hasWritePermission;
        }

        // performance improvement, do not detect this in __construct otherwise likely rather "big" query to DB.
        $this->hasWritePermission = Piwik::isUserHasSomeViewAccess();

        return $this->hasWritePermission;
    }

    /**
     * Set whether setting is writable or not. For example to hide setting from the UI set it to false.
     *
     * @param bool $isWritable
     */
    public function setIsWritableByCurrentUser($isWritable)
    {
        $this->hasWritePermission = (bool) $isWritable;
    }

}
