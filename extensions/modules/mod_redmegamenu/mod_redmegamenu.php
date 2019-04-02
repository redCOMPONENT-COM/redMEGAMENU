<?php
/**
 * @package     Redshopb.Site
 * @subpackage  mod_redmegamenu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$active = ModRedMegaMenuHelper::getActive();
$document = JFactory::getDocument();

// Page title management
if ($active)
{
	$pagetitle = $document->getTitle();
	$title = $pagetitle;

	if (preg_match("/||/", $active->title))
	{
		$title = explode("||", $active->title);
		$title = str_replace($active->title, $title[0], $pagetitle);
	}

	$title = str_replace('[mega]', '', $title);

	// Returns the page title without special symbols
	$document->setTitle($title);
}

$params->set('activePath', $active->tree);
$params->set('activeId', $active->id);
$list = (new ModRedMegaMenuHelper)->getList($params);

if (count($list))
{
	$active_id = $active->id;
	$path = $active->tree;

	$class_sfx = htmlspecialchars($params->get('class_sfx'));
	$imageWidth = (int) $params->get('imageWidth', 65);
	$imageHeight = (int) $params->get('imageHeight', 65);
	$document->addStyleDeclaration(
		'.megaMenuEmptyBox{width:' . $imageWidth . 'px;height:' . $imageHeight . 'px;background: #dfdfdf}
		.maxMegaMenuWidth{max-width: ' . (int) $params->get('maxWidth', 1170) . 'px}'
	);
	JHtml::stylesheet('mod_redmegamenu/mega.css', false, true);
	JHtml::script('mod_redmegamenu/mega.js', false, true);
	require JModuleHelper::getLayoutPath('mod_redmegamenu', $params->get('layout', 'default'));
}
