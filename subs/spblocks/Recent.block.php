<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Recent Post or Topic block, shows the most recent posts or topics on the forum
 *
 * @param mixed[] $parameters
 *		'boards' => list of boards to get posts from,
 *		'limit' => number of topics/posts to show
 *		'type' => recent 0 posts or 1 topics
 * 		'display' => compact or full view of the post/topic
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Recent_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'boards' => 'boards',
			'limit' => 'int',
			'type' => 'select',
			'display' => 'select',
		);

		parent::__construct($db);
	}

	function setup($parameters)
	{
		global $color_profile;

		$boards = !empty($parameters['boards']) ? explode('|', $parameters['boards']) : null;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$type = 'ssi_recent' . (empty($parameters['type']) ? 'Posts' : 'Topics');
		$this->data['display'] = empty($parameters['display']) ? 'compact' : 'full';

		// Pass the values to the ssi_ function
		$this->data['items'] = $type($limit, null, $boards, 'array');
		$this->data['class_type'] = empty($parameters['type']) ? 'post' : 'topic';

		if (!empty($this->data['items']))
			$this->data['items'][count($this->data['items']) - 1]['is_last'] = true;

		$colorids = array();
		foreach ($this->data['items'] as $item)
			$colorids[] = $item['poster']['id'];

		if (!empty($colorids) && sp_loadColors($colorids) !== false)
		{
			foreach ($this->data['items'] as $k => $p)
			{
				if (!empty($color_profile[$p['poster']['id']]['link']))
					$this->data['items'][$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
			}
		}

		$this->setTemplate('template_sp_recent');
	}
}

function template_sp_recent($data)
{
	global $txt, $scripturl;

	if (empty($data['items']))
	{
		echo '
								', $txt['error_sp_no_posts_found'];

		return;
	}

	// Show the data in either a compact or full format
	if ($data['display'] == 'compact')
	{
		foreach ($data['items'] as $item)
			echo '
								', $item['new'] ? '' : ' <a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new" rel="nofollow"><span class="new_posts">' . $txt['new'] . '</span></a>&nbsp;', '
								<a href="', $item['href'], '">', $item['subject'], '</a>
								<span class="smalltext">', $txt['by'], ' ', $item['poster']['link'],
								'<br />[', $item['time'], ']</span>
								<br />', empty($item['is_last']) ? '<hr />' : '';
	}
	elseif ($data['display'] == 'full')
	{
		echo '
								<table class="sp_fullwidth">';

		$embed_class = sp_embed_class($data['class_type'], '', 'sp_recent_icon centertext' );
		foreach ($data['items'] as $item)
			echo '
									<tr>
										<td ', $embed_class, '></td>
										<td class="sp_recent_subject">',
											$item['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new"><span class="new_posts">' . $txt['new'] . '</span></a>&nbsp;', '
											<a href="', $item['href'], '">', $item['subject'], '</a>
											<br />[', $item['board']['link'], ']
										</td>
										<td class="sp_recent_info righttext">
											', $item['poster']['link'], '<br />', $item['time'], '
										</td>
									</tr>';

		echo '
								</table>';
	}
}