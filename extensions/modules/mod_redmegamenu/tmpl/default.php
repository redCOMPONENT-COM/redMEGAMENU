<?php
/**
 * @package     Site
 * @subpackage  mod_redmegamenu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.loadCss', true);

JFactory::getDocument()->addScriptDeclaration('
(function($){
	$(document).ready(function () {
		jQuery(\'#redshopbMegaMenu_' . $module->id . '\').shopbMegaMenu({
			effect: \'' . $params->get('effect', 'fade') . '\', animation: \'' . $params->get('animation', 'none') . '\',
			indicatorFirstLevel: \'' . $params->get('indicatorFirstLevel', '+') . '\', indicatorSecondLevel: \'' . $params->get('indicatorSecondLevel', '+') . '\',
			showSpeed: ' . (int) $params->get('showSpeed', 300) . ', hideSpeed: ' . (int) $params->get('hideSpeed', 300) . ',
			minWidth: ' . (int) $params->get('minWidth', 770) . '
		});
	});
})(jQuery);
');

// Note. It is important to remove spaces between elements.
?><div id="redshopbMegaMenu_<?php
echo $module->id; ?>" class="navbar shopbMegaMenu maxMegaMenuWidth"><ul class="nav shopbMegaMenu-menu menu<?php
	echo $class_sfx; ?>"<?php
$tag = '';

if ($params->get('tag_id') != null)
{
	$tag = $params->get('tag_id') . '';
	echo ' id="' . $tag . '"';
}
?>><?php

foreach ($list as $i => &$item)
{
    if (isset($item->mega))
    {
	    if (($item->mega && !$item->replaceItem) || !$item->mega)
	    {
		    $class = array('item-' . $item->id, 'level-item-' . $item->level);

		    if (($item->id == $active_id) OR ($item->type == 'alias' AND $item->params->get('aliasoptions') == $active_id))
		    {
			    $class[] = 'current';
		    }

		    if (in_array($item->id, $path))
		    {
			    $class[] = 'active';
		    }
		    elseif ($item->type == 'alias')
		    {
			    $aliasToId = $item->params->get('aliasoptions');

			    if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
			    {
				    $class[] = 'active';
			    }
			    elseif (in_array($aliasToId, $path))
			    {
				    $class[] = 'alias-parent-active';
			    }
		    }

		    if ($item->type == 'separator')
		    {
			    $class[] = 'divider';
		    }

		    if ($item->deeper || isset($item->redShopBCategories))
		    {
			    $class[] = 'deeper';
		    }

		    if ($item->parent)
		    {
			    $class[] = 'parent';
		    }

		    echo '<li class="' . implode(' ', $class) . '">';

		    // Render the menu item.
		    switch ($item->type)
		    {
			    case 'separator':
			    case 'url':
			    case 'component':
			    case 'heading':
			    case 'module':
				    include JModuleHelper::getLayoutPath('mod_redmegamenu', 'default_' . $item->type);
				    break;

			    default:
				    include JModuleHelper::getLayoutPath('mod_redmegamenu', 'default_url');
				    break;
		    }
	    }

	    if ($item->mega)
	    {
		    $item->pluginParams = $params;
		    $item->lastItem = 0;
		    ModRedMegaMenuHelper::displayLevel($item->childs, $item, $item->displayLevel, $item->id);
	    }

	    if (($item->mega && !$item->replaceItem) || !$item->mega)
	    {
		    // The next item is deeper.
		    if ($item->deeper)
		    {
			    echo '<ul class="nav-child unstyled small dropdown">';

			    continue;
		    }

		    if ($item->shallower)
		    {
			    // The next item is shallower.
			    echo '</li>';
			    echo str_repeat('</ul></li>', $item->level_diff);

			    continue;
		    }

		    // The next item is on the same level.
		    echo '</li>';
	    }
    }
}
?></ul><div class="clr"></div></div><?php
