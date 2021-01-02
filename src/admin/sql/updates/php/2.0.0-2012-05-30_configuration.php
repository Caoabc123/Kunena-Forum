<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

// Kunena 2.0.0: Convert deprecated configuration options
/**
 * @param   string  $parent parent
 *
 * @return  array
 *
 * @since   Kunena 6.0
 *
 * @throws  Exception
 */
function kunena_200_2012_05_30_configuration($parent)
{
	$config = KunenaFactory::getConfig();

	// Unset deprecated configuration options which have been migrated earlier
	unset($config->board_ofset);
	unset($config->allowavatar);
	unset($config->avatar_src);
	unset($config->fb_profile);
	unset($config->pm_component);
	unset($config->js_actstr_integration);
	unset($config->sefcats);
	unset($config->rules_cid);
	unset($config->help_cid);

	if (isset($config->allowimageupload))
	{
		$config->set('image_upload', 'nobody');

		if ($config->get('allowimageregupload') == 1)
		{
			$config->set('image_upload', 'registered');
		}

		if ($config->get('allowimageupload') == 1)
		{
			$config->set('image_upload', 'everybody');
		}

		unset($config->allowimageupload, $config->allowimageregupload);
	}

	if (isset($config->allowfileupload))
	{
		$config->set('file_upload', 'nobody');

		if ($config->get('allowfileregupload') == 1)
		{
			$config->set('file_upload', 'registered');
		}

		if ($config->get('allowfileupload') == 1)
		{
			$config->set('file_upload', 'everybody');
		}

		unset($config->allowfileupload, $config->allowfileregupload);
	}

	if (isset($config->fbsessiontimeout))
	{
		$config->set('sessiontimeout', $config->get('fbsessiontimeout', 1800));
		unset($config->fbsessiontimeout);
	}

	if (isset($config->fbdefaultpage))
	{
		$config->set('defaultpage', $config->get('fbdefaultpage', 'recent'));
		unset($config->fbdefaultpage);
	}

	// Move integration settings into plugins
	if (isset($config->integration_access))
	{
		// Load configuration options
		$types = ['access' => '', 'login' => '', 'avatar' => '', 'profile' => '', 'private' => '', 'activity' => ''];

		foreach ($types as $type => &$value)
		{
			$field = "integration_{$type}";
			$value = $config->get($field, 'auto');
			unset($config->$field);
		}

		$integration = ['alphauserpoints' => 'alphauserpoints', 'jomsocial' => 'community', 'communitybuilder' => 'comprofiler', 'uddeim' => 'uddeim'];

		$plugins = [];

		foreach ($integration as $cfgname => $pluginname)
		{
			$plugin = $parent->loadPlugin('kunena', $pluginname);

			if ($plugin)
			{
				$params            = new Joomla\Registry\Registry($plugin->params);
				$plugin->params    = $params;
				$plugins[$cfgname] = $plugin;
			}
		}

		foreach ($types as $type => $value)
		{
			foreach ($plugins as $name => $plugin)
			{
				if ($plugin->params->get($type, null) === null)
				{
					continue;
				}

				if ($value == 'auto' || $value == $name)
				{
					$plugin->enabled = 1;
					$plugin->params->set($type, 1);
				}
				else
				{
					$plugin->params->set($type, 0);
				}
			}
		}

		if (isset($plugins['alphauserpoints']))
		{
			$plugins['alphauserpoints']->params->set('activity_points_limit', $config->get('alphauserpointsnumchars', 0));
		}

		if (isset($plugins['jomsocial']))
		{
			$plugins['jomsocial']->params->set('activity_stream_limit', $config->get('activity_limit', 0));
		}

		unset($config->activity_limit, $config->alphauserpointsnumchars);

		foreach ($plugins as $plugin)
		{
			$plugin->params = $plugin->params->toString();

			if (!empty($plugin->enabled))
			{
				$plugin->store();
			}
		}
	}

	// Save configuration
	$config->save();

	return ['action' => '', 'name' => Text::_('COM_KUNENA_INSTALL_200_CONFIGURATION'), 'success' => true];
}
