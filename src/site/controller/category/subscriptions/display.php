<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Category
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Category\Subscriptions;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Exception\KunenaAuthorise;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessageHelper;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Site\Model\CategoryModel;
use function defined;

/**
 * Class ComponentCategoryControllerSubscriptionsDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentCategoryControllerSubscriptionsDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Category/List';

	/**
	 * @var     integer
	 * @since   Kunena 6.0
	 */
	public $total;

	/**
	 * @var     Pagination
	 * @since   Kunena 6.0
	 */
	public $pagination;

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	public $categories = [];

	/**
	 * Prepare category subscriptions display.
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

		$model = new CategoryModel([], $this->input);
		$model->initialize($this->getOptions(), $this->getOptions()->get('embedded', false));
		$state = $model->getState();

		$me = KunenaUserHelper::getMyself();

		if (!$me->exists())
		{
			throw new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), 401);
		}

		$user = KunenaUserHelper::get($state->get('user'));

		$limit = $this->input->getInt('limit', 0);

		if ($limit < 1 || $limit > 100)
		{
			$limit = 20;
		}

		$limitstart = $this->input->getInt('limitstart', 0);

		if ($limitstart < 0)
		{
			$limitstart = 0;
		}

		list($total, $this->categories) = KunenaCategoryHelper::getLatestSubscriptions($state->get('user'));

		$topicIds = [];
		$userIds  = [];
		$postIds  = [];

		foreach ($this->categories as $category)
		{
			// Get list of topics.
			if ($category->last_topic_id)
			{
				$topicIds[$category->last_topic_id] = $category->last_topic_id;
			}
		}

		// Pre-fetch topics (also display unauthorized topics as they are in allowed categories).
		$topics = KunenaTopicHelper::getTopics($topicIds, 'none');

		// Pre-fetch users (and get last post ids for moderators).
		foreach ($topics as $topic)
		{
			$userIds[$topic->last_post_userid] = $topic->last_post_userid;
			$postIds[$topic->id]               = $topic->last_post_id;
		}

		KunenaUserHelper::loadUsers($userIds);
		KunenaMessageHelper::getMessages($postIds);

		// Pre-fetch user related stuff.
		if ($me->exists() && !$me->isBanned())
		{
			// Load new topic counts.
			KunenaCategoryHelper::getNewTopics(array_keys($this->categories));
		}

		$actions = $this->getActions();

		$this->pagination = new Pagination($total, $limitstart, $limit);

		$headerText = Text::_('COM_KUNENA_CATEGORY_SUBSCRIPTIONS');
	}

	/**
	 * Get topic action option list.
	 *
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 */
	public function getActions()
	{
		$options   = [];
		$options[] = HTMLHelper::_('select.option', 'none', Text::_('COM_KUNENA_BULK_CHOOSE_ACTION'));
		$options[] = HTMLHelper::_('select.option', 'unsubscribe', Text::_('COM_KUNENA_UNSUBSCRIBE_SELECTED'));

		return $options;
	}

	/**
	 * Prepare document.
	 *
	 * @return  void|boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	protected function prepareDocument(): bool
	{
		$menu_item = $this->app->getMenu()->getActive();

		$config = Factory::getApplication();
		$robots = $config->get('robots');

		if ($robots == 'noindex, follow')
		{
			$this->setMetaData('robots', 'noindex, follow');
		}
		elseif ($robots == 'index, nofollow')
		{
			$this->setMetaData('robots', 'index, nofollow');
		}
		elseif ($robots == 'noindex, nofollow')
		{
			$this->setMetaData('robots', 'noindex, nofollow');
		}
		else
		{
			$this->setMetaData('robots', 'index, follow');
		}

		if ($menu_item)
		{
			$params             = $menu_item->getParams();
			$params_title       = $params->get('page_title');
			$params_description = $params->get('menu-meta_description');
			$params_robots      = $params->get('robots');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$title = Text::_('COM_KUNENA_VIEW_CATEGORIES_USER');
				$this->setTitle($title);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$description = Text::_('COM_KUNENA_CATEGORY_SUBSCRIPTIONS') . ' - ' . $this->config->board_title;
				$this->setDescription($description);
			}

			if (!empty($params_robots))
			{
				$robots = $params->get('robots');
				$this->setMetaData('robots', $robots);
			}
		}
	}
}