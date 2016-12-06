<?php
/**
 * @package     Site
 * @subpackage  mod_redmegamenu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class RedMegaMenuModuleHelper
 *
 * @since  1.0.0
 */
class RedMegaMenuModuleHelper extends JModuleHelper
{
	/**
	 * Getting all modules
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function &getAllModules()
	{
		static $modules;

		if (isset($modules))
		{
			return $modules;
		}

		$modules    = array();
		$allModules = parent::load();

		if ($allModules)
		{
			foreach ($allModules as $module)
			{
				$modules[$module->id] = $module;
			}
		}

		return $modules;
	}
}

/**
 * Helper for mod_redmegamenu
 *
 * @package     Site
 * @subpackage  mod_redmegamenu
 * @since       1.0.0
 */
class ModRedMegaMenuHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function getList(&$params)
	{
		$app      = JFactory::getApplication();
		$menu     = $app->getMenu();
		$items    = $menu->getItems('menutype', $params->get('menutype'));
		$lastItem = 0;
		$ignore   = $params->get('ignoreItems', array());

		if ($items)
		{
			do
			{
				$i                = key($items);
				$item             = current($items);
				$item->deeper     = false;
				$item->shallower  = false;
				$item->level_diff = 0;
				$levels[]         = $item->level;
				$levels           = array_unique($levels);

				if (isset($items[$lastItem]))
				{
					$items[$lastItem]->deeper     = ($item->level > $items[$lastItem]->level);
					$items[$lastItem]->shallower  = ($item->level < $items[$lastItem]->level);
					$items[$lastItem]->level_diff = ($items[$lastItem]->level - $item->level);
				}

				$lastItem = $i;
				self::setValues($item);

				if (!in_array($item->id, $ignore))
				{
					$item->mega = true;

					self::getListForMegamenu($items, $params);
				}
				else
				{
					$item->mega = false;
				}
			}
			while (next($items) !== false);

			if (isset($items[$lastItem]))
			{
				$items[$lastItem]->deeper     = (1 > $items[$lastItem]->level);
				$items[$lastItem]->shallower  = (1 < $items[$lastItem]->level);
				$items[$lastItem]->level_diff = ($items[$lastItem]->level - 1);
			}
		}

		return $items;
	}

	/**
	 * Get List For Mega menu
	 *
	 * @param   array                      &$items  Menu Items
	 * @param   \Joomla\Registry\Registry  $params  Module params
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function getListForMegamenu(&$items, $params)
	{
		$parent               = current($items);
		$parent->displayLevel = $parent->level + 1;
		$parent->replaceItem  = false;
		$minimalLevel         = $parent->level;
		$countChilds          = array();
		$ids                  = array();
		$images               = array();
		$parent->hasSprite    = false;
		$parent->activePath   = $params->get('activePath', array());
		$parent->activeId     = $params->get('activeId', 0);
		$useImageSprite       = (int) $params->get('useImageSprite', 1);
		$imageWidth           = (int) $params->get('imageWidth', 65);
		$imageHeight          = (int) $params->get('imageHeight', 65);
		$lastItem             = 0;
		$childs               = array();
		$app                  = JFactory::getApplication();
		$menu                 = $app->getMenu();
		$dLevel               = $params->get('dropdownLevel', 1);
		$lvlCounter           = array();
		$maxLevel             = -1;

		while (next($items) !== false)
		{
			$i            = key($items);
			$child        = current($items);
			$lvlCounter[] = $child->level;
			$lvlCounter   = array_unique($lvlCounter);

			if ($parent->level >= $child->level)
			{
				prev($items);
				break;
			}

			if (count($lvlCounter) > $dLevel)
			{
				if ($maxLevel == -1)
				{
					$maxLevel = $child->level;
				}

				if ($child->level >= $maxLevel && $maxLevel != -1)
				{
					prev($items);
					unset($items[$i]);
					continue;
				}
			}

			$relationLevel = $parent->level + ($child->level - $minimalLevel);

			if (!isset($countChilds[$child->parent_id]))
			{
				$countChilds[$child->parent_id] = 0;
			}

			$countChilds[$child->parent_id]++;

			$child->deeper        = false;
			$child->shallower     = false;
			$child->level_diff    = 0;
			$child->relationLevel = $relationLevel;
			$ids[$child->id]      = $i;

			if (count($lvlCounter) < $dLevel)
			{
				$child->parent = (boolean) $menu->getItems('parent_id', (int) $child->id, true);
			}
			else
			{
				$child->parent = false;
			}

			if (isset($childs[$lastItem]))
			{
				$child[$lastItem]->deeper     = ($relationLevel > $child[$lastItem]->relationLevel);
				$child[$lastItem]->shallower  = ($relationLevel < $child[$lastItem]->relationLevel);
				$child[$lastItem]->level_diff = ($child[$lastItem]->relationLevel - $relationLevel);
			}

			$lastItem = $i;
			self::setValues($child, count($lvlCounter), $dLevel);

			if ($useImageSprite && $child->level == 2)
			{
				$imagePath = JPATH_ROOT . '/' . $child->menu_image;

				if ($child->menu_image != '' && JFile::exists($imagePath))
				{
					$filename         = pathinfo($imagePath, PATHINFO_FILENAME);
					$fileExtension    = pathinfo($imagePath, PATHINFO_EXTENSION);
					$thumbFileName    = $filename . '_' . $imageWidth . 'x' . $imageHeight . '.' . $fileExtension;
					$imageThumbFolder = JPATH_ROOT . '/cache/mod_redmegamenu';
					$imageThumbPath   = $imageThumbFolder . '/thumbs/' . $thumbFileName;
					$thumb            = false;

					if (!JFile::exists($imageThumbPath))
					{
						$image  = new JImage($imagePath);
						$thumbs = $image->createThumbs(array($imageWidth . 'x' . $imageHeight), JImage::CROP_RESIZE, $imageThumbFolder);

						if (count($thumbs))
						{
							$thumb = $thumbs[0]->getPath();
						}
					}
					else
					{
						$thumb = array($imageThumbPath);
					}

					if ($thumb)
					{
						$images[$child->id] = $thumb;
					}
				}
			}

			$childs[] = $child;
			unset($items[$i]);
			prev($items);
		}

		if ($useImageSprite)
		{
			$parent->hasSprite = self::createSprite($images, $imageWidth, $imageHeight);
		}

		if (isset($childs[$lastItem]))
		{
			$childs[$lastItem]->deeper     = (1 > $childs[$lastItem]->relationLevel);
			$childs[$lastItem]->shallower  = (1 < $childs[$lastItem]->relationLevel);
			$childs[$lastItem]->level_diff = ($childs[$lastItem]->relationLevel - 1);
		}

		$parent->childs      = $childs;
		$parent->countChilds = $countChilds;
	}

	/**
	 * Set menu item values
	 *
	 * @param   object  &$item       Menu item
	 * @param   int     $lvlCounter  Menu different level counter
	 * @param   int     $dLevel      Deepest allowed level
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function setValues(&$item, $lvlCounter = 0, $dLevel = 1)
	{
		$app  = JFactory::getApplication();
		$menu = $app->getMenu();

		if ($lvlCounter < $dLevel)
		{
			$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
		}
		else
		{
			$item->parent = false;
		}

		$item->active = false;
		$item->flink  = $item->link;

		switch ($item->type)
		{
			case 'separator':
			case 'heading':
				// No further action needed.
				continue;

			case 'url':
				if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
				{
					// If this is an internal Joomla link, ensure the Itemid is set.
					$item->flink = $item->link . '&Itemid=' . $item->id;
				}
				break;

			case 'alias':
				$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
				break;

			default:
				$item->flink = 'index.php?Itemid=' . $item->id;
				break;
		}

		if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
		{
			$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
		}
		else
		{
			$item->flink = JRoute::_($item->flink);
		}

		// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
		// when the cause of that is found the argument should be removed
		$pattern            = '/\[modid=([0-9]+)\]/';
		$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
		$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
		$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
		$item->menu_image   = $item->params->get('menu_image', '') ?
			htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';

		if (preg_match($pattern, $item->title, $result))
		{
			$item->title = preg_replace($pattern, '', $item->title);
			$modules     = &RedMegaMenuModuleHelper::getAllModules();

			if (isset($modules[$result[1]]) && $modules[$result[1]]->module != 'mod_redmegamenu')
			{
				$item->type = 'module';
				$item->content = self::generateModuleById($result[1], 'xhtml');
			}
		}

		$result = explode("||", $item->title);

		if (isset($result[1]))
		{
			$item->desc = $result[1];
		}
		else
		{
			$item->desc = '';
		}

		$item->title = $result[0];
	}

	/**
	 * Render the module
	 *
	 * @param   int     $moduleId  The module ID to load
	 * @param   string  $style     Style for module
	 *
	 * @return string with HTML
	 *
	 * @since   1.0.0
	 */
	public static function generateModuleById($moduleId, $style = 'xhtml')
	{
		$attribs['style'] = $style;
		$modules          = &MegaMenuModuleHelper::getAllModules();

		// Get the title of the module to load
		$modTitle = $modules[$moduleId]->title;
		$modName  = $modules[$moduleId]->module;

		// Load the module
		if (JModuleHelper::isEnabled($modName))
		{
			$module = JModuleHelper::getModule($modName, $modTitle);

			return JModuleHelper::renderModule($module, $attribs);
		}

		return JText::sprintf('MOD_REDMEGAMENU_MODULE_NOT_FOUND', $moduleId);
	}

	/**
	 * Get active menu item.
	 *
	 * @return  object
	 *
	 * @since   1.0.0
	 */
	public static function getActive()
	{
		$menu = JFactory::getApplication()->getMenu();
		$lang = JFactory::getLanguage();

		// Look for the home menu
		if (JLanguageMultilang::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home  = $menu->getDefault();
		}

		return $menu->getActive() ? $menu->getActive() : $home;
	}

	/**
	 * Create Sprite for megamenu
	 *
	 * @param   array   $files   Array data images
	 * @param   int     $width   Width images
	 * @param   int     $height  Height images
	 * @param   string  $output  Name of class for sprite
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public static function createSprite($files = array(), $width = 65, $height = 65, $output = 'redmegamenu_sprite')
	{
		if (empty($files))
		{
			return false;
		}

		$md5        = md5(serialize(func_get_args()));
		$nameFile   = $output . '-' . $md5;
		$spritePath = JPATH_ROOT . '/cache/mod_redmegamenu/' . $nameFile;
		$urlPath    = JUri::root() . 'cache/mod_redmegamenu/' . $nameFile;

		if (!JFile::exists($spritePath . '.css') || !JFile::exists($spritePath . '.png'))
		{
			// The variable yy is the height of the sprite to be created, basically height * number of images
			$yy = $height * count($files);
			$im = imagecreatetruecolor($width, $yy);

			// Add alpha channel to image (transparency)
			imagesavealpha($im, true);
			$alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
			imagefill($im, 0, 0, $alpha);

			// Append images to sprite and generate CSS lines
			$i   = 0;
			$css = '.' . $output . '{width:' . $width . 'px;height:' . $height .
				'px;background:url(' . $nameFile . ".png) no-repeat 0px " . $height . "px #dfdfdf}\n";

			foreach ($files as $key => $path)
			{
				$css .= '.' . $output . '_' . $key . "{background-position: 0px -" . ($height * $i) . "px}\n";
				$info = getimagesize($path);

				switch ($info['mime'])
				{
					case 'image/gif':
						$im2 = imagecreatefromgif($path);
						break;
					case 'image/jpeg':
						$im2 = imagecreatefromjpeg($path);
						break;
					case 'image/png':
						$im2 = imagecreatefrompng($path);
						break;
					default:
						$i++;
						continue(2);
						break;
				}

				imagecopy($im, $im2, 0, ($height * $i), 0, 0, $width, $height);
				imagedestroy($im2);
				$i++;
			}

			JFile::write($spritePath . '.css', $css);
			imagepng($im, $spritePath . '.png');
			imagedestroy($im);
		}

		JFactory::getDocument()->addStyleSheet($urlPath . '.css');

		return true;
	}

	/**
	 * Display one redshopb level
	 *
	 * @param   array   &$items      Redshop list items
	 * @param   object  $parentItem  Joomla parent item
	 * @param   int     $level       Current level display
	 * @param   int     $parentId    Use parent id for filter redshop items
	 *
	 * @return  int
	 *
	 * @since   1.0.0
	 */
	public static function displayLevel(&$items, $parentItem, $level = 2, $parentId = 0)
	{
		if ($level > 1 && (!isset($parentItem->countChilds[$parentId]) || $parentItem->countChilds[$parentId] == 0))
		{
			return $parentItem->lastItem;
		}

		$countItems     = 0;
		$isLevel        = false;
		$countColumns   = $parentItem->pluginParams->get('countColumns', 4);
		$numberSpan     = round(12 / $countColumns, 0, PHP_ROUND_HALF_DOWN);
		$itemsInColumn  = 0;
		$itemsPerColumn = array();

		if ($level == 2)
		{
			$column = 0;

			for ($i = 0; $i < $parentItem->countChilds[$parentId]; $i++)
			{
				$column++;

				if (!isset($itemsPerColumn[$column]))
				{
					$itemsPerColumn[$column] = 0;
				}

				$itemsPerColumn[$column]++;

				if ($column >= $countColumns)
				{
					$column = 0;
				}
			}
		}

		$column = 1;

		for ($i = $parentItem->lastItem, $ci = count($items); $i < $ci; $i++)
		{
			$item = $items[$i];

			if ($level != 1 && $item->parent_id != $parentId)
			{
				continue;
			}

			if ($item->relationLevel != $level)
			{
				continue;
			}

			$parentItem->lastItem = $i;
			$key                  = $parentItem->id . '-' . $item->id;
			$class                = array('item-' . $key, 'level-item-' . $level);
			$attr                 = array('href' => $item->flink);
			$item->browserNav     = $parentItem->browserNav;

			if ($item->deeper)
			{
				$class[] = 'deeper';
			}

			if ($item->parent)
			{
				$class[] = 'parent';
			}

			if (in_array($item->id, $parentItem->activePath))
			{
				$class[] = 'active';

				if ($item->id == $parentItem->activeId)
				{
					$class[] = 'current';
				}
			}

			if ($level == 1)
			{
				$attr = self::setBrowserNav($item, $attr);
				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr)
					. '><span class="menuLinkTitle">' . $item->title . '</span></a>';
				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id);
				echo '</li>';
			}
			elseif ($level == 2)
			{
				if (!$isLevel)
				{
					echo '<ul class="nav-child unstyled megamenu"><li class="maxMegaMenuWidth"><div class="row-fluid">';
					$isLevel = true;
				}

				$countItems++;
				$itemsInColumn++;

				if ($itemsInColumn == 1)
				{
					echo '<div id="accordion' . $key . '" class="accordion span' . $numberSpan . ' ' . '">';
				}

				echo '<div class="accordion-group ' . implode(' ', $class) . '"><div class="accordion-heading">';
				$attr['class'] = 'categoryLink';

				if ($parentItem->hasSprite)
				{
					$imageUrl = '<div class="redmegamenu_sprite redmegamenu_sprite_' . $item->id . '"></div>';
				}
				else
				{
					$imageUrl = false;

					if (JFile::exists(JPATH_ROOT . '/' . $item->menu_image))
					{
						$imageUrl = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
					}

					if (!$imageUrl)
					{
						$imageUrl = '<div class="megaMenuEmptyBox"></div>';
					}
				}

				$linktype = '<div class="thumbnail">' . $imageUrl . '</div><span class="menuLinkTitle">' . $item->title . '</span>';

				$attr = self::setBrowserNav($item, $attr);

				echo '<a ' . self::getLinkAttributes($attr) . '>' . $linktype . '</a>';

				if ($item->parent)
				{
					$attr = array(
						'href' => '#collapseAnchor' . $key . '-' . $countItems,
						'data-parent' => '#accordion' . $key,
						'data-toggle' => 'collapse',
						'class' => 'accordion-toggle collapsed'
					);
					echo '<a ' . self::getLinkAttributes($attr) . '>' . $parentItem->pluginParams->get('indicatorSecondLevel', '+') . '</a>';
				}

				// Close <div class="accordion-heading">
				echo '</div>';

				echo '<div class="accordion-body collapse" id="collapseAnchor'
					. $key . '-' . $countItems . '"><div class="accordion-inner">';

				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id);

				// Close <div class="accordion-inner">
				echo '</div>';

				// Close <div class="accordion-body collapse">
				echo '</div>';

				// Close <div class="accordion-group">
				echo '</div>';

				if ($itemsInColumn == $itemsPerColumn[$column])
				{
					// Close previous <div id="accordion{$key}">
					echo '</div>';

					// Next item display in next column
					$column++;
					$itemsInColumn = 0;
				}
			}
			elseif ($parentId == $item->parent_id && $level >= 3)
			{
				if (!$isLevel)
				{
					if ($level == 3)
					{
						echo '<ul class="nav-child unstyled">';
					}
					else
					{
						echo '<ul class="nav-child unstyled dropdown">';
					}

					$isLevel = true;
				}

				$attr = self::setBrowserNav($item, $attr);

				echo '<li class="' . implode(' ', $class) . '"><a ' . self::getLinkAttributes($attr) . '>' . $item->title . '</a>';

				$i = self::displayLevel($items, $parentItem, $level + 1, $item->id);
				echo '</li>';
			}
		}

		if ($isLevel)
		{
			if ($level == 2)
			{
				// Close <div class="row-fluid">, and <li>, and <ul class="nav-child unstyled megamenu">
				echo '</div></li></ul>';
			}
			elseif ($level >= 3)
			{
				// Close <ul class="nav-child unstyled">
				echo '</ul>';
			}
		}

		return $parentItem->lastItem;
	}

	/**
	 * Build link attributes to string
	 *
	 * @param   array  $attr  Array link attributes
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getLinkAttributes($attr)
	{
		return implode(' ',
			array_map(
				function ($v, $k)
				{
					return sprintf('%s="%s"', $k, $v);
				},
				$attr,
				array_keys($attr)
			)
		);
	}

	/**
	 * Set browser navigation attributes
	 *
	 * @param   object  $item  Current menu item
	 * @param   array   $attr  Array item attributes
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public static function setBrowserNav($item, $attr = array())
	{
		switch ($item->browserNav)
		{
			case 1:
				// _blank
				$attr['target'] = '_blank';
				break;
			case 2:
				// Use JavaScript "window.open"
				$attr['onclick'] = "window.open(this.href,'targetWindow'"
					. ",'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;";
				break;
		}

		return $attr;
	}

	/**
	 * Ajax function for getting menu items for given menu type.
	 *
	 * @return  object  JSON response.
	 *
	 * @since   1.0.1
	 */
	public static function getMenuItemsAjax()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$type    = $input->getString('menutype', '');
		$items   = array();
		$options = array();

		if (!empty($menu))
		{
			$menu  = $app->getMenu();
			$items = $menu->getItems('menutype', $type);
		}

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		$return       = new stdClass;
		$return->html = implode('', $options);

		return $return;
	}
}
