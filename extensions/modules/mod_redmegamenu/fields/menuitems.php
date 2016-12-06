<?php
/**
 * @package     RedMEGAMenu.Form
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

JLoader::import('joomla.form.formfield');
JFormHelper::loadFieldClass('list');

/**
 * Categories select list
 *
 * @package     RedMEGAMenu.Form
 * @subpackage  Fields.Menuitems
 *
 * @since       1.0.1
 */
class JFormFieldMenuitems extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.1
	 */
	protected $type = 'Menuitems';

	/**
	 * Method to get the field input markup for a modal select form.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0.1
	 */
	public function getInput()
	{
		$doc    = JFactory::getDocument();
		$script = '
			jQuery(document).ready(function() {
				reloadMenuItems();
				jQuery(\'#jform_params_menutype\').on(\'change\', reloadMenuItems);
			});
			function reloadMenuItems()
			{
				var menutype = jQuery(\'#jform_params_menutype\').val();
				jQuery(\'#menuitems\').find(\'optgroup\').attr(\'disabled\', \'disabled\');
				jQuery(\'#\' + menutype).removeAttr(\'disabled\');
				jQuery(\'#menuitems\').find(\'select\').trigger(\'liszt:updated\');
			}
		';
		$doc->addScriptDeclaration($script);

		$groups  = array();
		$options = $this->getOptions();

		foreach ($options as $option)
		{
			if (!empty($option->type_id) && !empty($option->type))
			{
				if (!isset($groups[$option->type_id]))
				{
					$groups[$option->type_id] = array('id' => $option->type_id, 'text' => $option->type);
					$groups[$option->type_id]['items'] = array($option);
				}
				else
				{
					$groups[$option->type_id]['items'][] = $option;
				}
			}
			elseif (!empty($option->value) && !empty($option->text))
			{
				if (!isset($groups['unsorted']))
				{
					$groups['unsorted'] = array('id' => '', 'text' => '');
					$groups['unsorted']['items'] = array(JHtml::_('select.option', $option->value, $option->text));
				}
				else
				{
					$groups['unsorted']['items'][] = JHtml::_('select.option', $option->value, $option->text);
				}
			}
		}

		foreach ($groups as $id => $group)
		{
			if ($id == 'unsorted')
			{
				continue;
			}

			$items     = $group['items'];
			$children  = array();
			$treeItems = array();

			foreach ($items as $item)
			{
				$item->value = $item->id;
				$item->text  = $item->title;
				$parent      = $item->parent_id;

				if (isset($children[$parent]))
				{
					$list = $children[$parent];
				}
				else
				{
					$list = array();
				}

				array_push($list, $item);
				$children[$parent] = $list;
			}

			// Add as options
			$list = JHtml::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);

			foreach ($list as $i)
			{
				$i->treename = JString::str_ireplace('&#160;', '-', $i->treename);
				$treeItems[] = JHtml::_('select.option', $i->id, $i->treename);
			}

			$groups[$id]['items'] = $treeItems;
		}

		// Compute the current selected values
		$selected = $this->value;

		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Initialize multiple value
		$attr .= $this->element['multiple'] ? ' multiple="true"' : '';

		return '<span id="menuitems">' . JHtml::_(
			'select.groupedlist', $groups, $this->name,
			array('id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected)
		) . '</span>';
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	public function getOptions()
	{
		$options = parent::getOptions();
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select(
			array(
				$db->qn('m.id', 'id'),
				$db->qn('m.title', 'title'),
				$db->qn('m.parent_id', 'parent_id'),
				$db->qn('m.lft', 'lft'),
				$db->qn('mt.menutype', 'type_id'),
				$db->qn('mt.title', 'type')
			)
		)
			->from($db->qn('#__menu', 'm'))
			->innerJoin($db->qn('#__menu_types', 'mt') . ' ON ' . $db->qn('m.menutype') . ' = ' . $db->qn('mt.menutype'))
			->where($db->qn('published') . ' = 1')
			->order($db->qn('m.lft') . ' ASC');
		$items = array_merge($options, $db->setQuery($query)->loadObjectList());

		return $items;
	}
}
