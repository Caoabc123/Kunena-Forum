<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Comprofiler
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Plugin\Kunena\Comprofiler;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Integration\Profile;
use RuntimeException;
use function defined;

require_once dirname(__FILE__) . '/integration.php';

/**
 * Class KunenaProfileComprofiler
 *
 * @since   Kunena 6.0
 */
class KunenaProfileComprofiler extends Profile
{
	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $params = null;

	/**
	 * KunenaProfileComprofiler constructor.
	 *
	 * @param   object  $params  params
	 *
	 * @since   Kunena 6.0
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * @param   string  $event   event
	 * @param   object  $params  params
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public static function trigger($event, &$params)
	{
		KunenaIntegrationComprofiler::trigger($event, $params);
	}

	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function open()
	{
		KunenaIntegrationComprofiler::open();
	}

	/**
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function close()
	{
		KunenaIntegrationComprofiler::close();
	}

	/**
	 * @param   string  $action  action
	 * @param   bool    $xhtml   xhtml
	 *
	 * @return  boolean|string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getUserListURL($action = '', $xhtml = true)
	{
		global $_CB_framework;

		$config = KunenaFactory::getConfig();
		$my     = Factory::getApplication()->getIdentity();

		if ($config->userlist_allowed == 0 && $my->id == 0)
		{
			return false;
		}

		return $_CB_framework->userProfilesListUrl(null, $xhtml);
	}

	/**
	 * @param   int     $user   user
	 * @param   string  $task   task
	 * @param   bool    $xhtml  xhtml
	 *
	 * @return  boolean|string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getProfileURL($user, $task = '', $xhtml = true)
	{
		global $_CB_framework;

		$user = KunenaFactory::getUser($user);

		if ($user->userid == 0)
		{
			return false;
		}

		// Get CUser object
		$cbUser = CBuser::getInstance($user->userid);

		if ($cbUser === null)
		{
			return false;
		}

		return $_CB_framework->userProfileUrl($user->userid, $xhtml);
	}

	/**
	 * @param   string  $view    view
	 * @param   object  $params  params
	 *
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 */
	public function showProfile($view, &$params)
	{
		global $_PLUGINS;

		$_PLUGINS->loadPluginGroup('user');

		return implode(
			' ', $_PLUGINS->trigger(
				'forumSideProfile', ['kunena', $view, $view->profile->userid,
				['config' => &$view->config, 'userprofile' => &$view->profile, 'params' => &$params], ]
			)
		);
	}

	/**
	 * @param   int  $limit  limit
	 *
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function _getTopHits($limit = 0)
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('cu.user_id AS id, cu.hits AS count');
		$query->from($db->quoteName('#__comprofiler', 'cu'));
		$query->innerJoin($db->quoteName('#__users', 'u') . ' ON u.id = cu.user_id');
		$query->where('cu.hits > 0');
		$query->order('cu.hits DESC');
		$query->setLimit($limit);
		$db->setQuery($query);

		try
		{
			$top = (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $top;
	}

	/**
	 * @param   int   $userid  userid
	 * @param   bool  $xhtml   xhtml
	 *
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 */
	public function getEditProfileURL($userid, $xhtml = true)
	{
		global $_CB_framework;

		return $_CB_framework->userProfileEditUrl(null, $xhtml);
	}
}
