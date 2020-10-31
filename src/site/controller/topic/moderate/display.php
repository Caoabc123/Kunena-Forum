<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Topic
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Topic\Moderate;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Exception\Authorise;
use Kunena\Forum\Libraries\Forum\Message\Message;
use Kunena\Forum\Libraries\Forum\Message\MessageHelper;
use Kunena\Forum\Libraries\Forum\Topic\Topic;
use Kunena\Forum\Libraries\Forum\Topic\TopicHelper;
use Kunena\Forum\Libraries\Template\Template;
use Kunena\Forum\Libraries\User\Ban;
use function defined;

/**
 * Class ComponentTopicControllerModerateDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentTopicControllerModerateDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     Topic
	 * @since   Kunena 6.0
	 */
	public $topic;

	/**
	 * @var     Message|null
	 * @since   Kunena 6.0
	 */
	public $message;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $uri;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $title;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $topicIcons;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $userLink;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Topic/Moderate';

	/**
	 * Prepare topic moderate display.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  null
	 * @throws  Exception
	 */
	protected function before()
	{
		parent::before();

		$catid = $this->input->getInt('catid');
		$id    = $this->input->getInt('id');
		$mesid = $this->input->getInt('mesid');

		if (!$mesid)
		{
			$this->topic = TopicHelper::get($id);
			$this->topic->tryAuthorise('move');
		}
		else
		{
			$this->message = MessageHelper::get($mesid);
			$this->message->tryAuthorise('move');
			$this->topic = $this->message->getTopic();
		}

		if ($this->config->read_only)
		{
			throw new Authorise(Text::_('COM_KUNENA_NO_ACCESS'), '401');
		}

		$this->category = $this->topic->getCategory();

		$this->uri   = "index.php?option=com_kunena&view=topic&layout=moderate"
			. "&catid={$this->category->id}&id={$this->topic->id}"
			. ($this->message ? "&mesid={$this->message->id}" : '');
		$this->title = !$this->message ?
			Text::_('COM_KUNENA_TITLE_MODERATE_TOPIC') :
			Text::_('COM_KUNENA_TITLE_MODERATE_MESSAGE');

		$this->template = Template::getInstance();
		$this->template->setCategoryIconset($this->topic->getCategory()->iconset);
		$this->topicIcons = $this->template->getTopicIcons(false);

		// Have a link to moderate user as well.
		if (isset($this->message))
		{
			$user = $this->message->getAuthor();

			if ($user->exists())
			{
				$username       = $user->getName();
				$this->userLink = $this->message->userid ? HTMLHelper::_('link',
					'index.php?option=com_kunena&view=user&layout=moderate&userid=' . $this->message->userid,
					$username . ' (' . $this->message->userid . ')', $username . ' (' . $this->message->userid . ')'
				)
					: null;
			}
		}

		if ($this->message)
		{
			$this->banHistory = Ban::getUserHistory($this->message->userid);
			$this->me         = Factory::getApplication()->getIdentity();

			// Get thread and reply count from current message:
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(mm.id) AS replies')
				->from($db->quoteName('#__kunena_messages', 'm'))
				->innerJoin($db->quoteName('#__kunena_messages', 't') . ' ON m.thread=t.id')
				->leftJoin($db->quoteName('#__kunena_messages', 'mm') . ' ON mm.thread=m.thread AND mm.time > m.time')
				->where('m.id=' . $db->quote($this->message->id));
			$query->setLimit(1);
			$db->setQuery($query);

			try
			{
				$this->replies = $db->loadResult();
			}
			catch (ExecutionFailureException $e)
			{
				KunenaError::displayDatabaseError($e);

				return;
			}
		}

		$this->banInfo = Ban::getInstanceByUserid($this->app->getIdentity()->id, true);
	}

	/**
	 * Prepare document.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	protected function prepareDocument()
	{
		$menu_item = $this->app->getMenu()->getActive();

		if ($menu_item)
		{
			$params             = $menu_item->getParams();
			$params_title       = $params->get('page_title');
			$params_description = $params->get('menu-meta_description');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$this->setTitle($this->title);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$this->setDescription($this->title);
			}
		}
	}
}
