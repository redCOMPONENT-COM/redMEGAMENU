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
				jQuery.ajax({
					url : \'index.php?option=com_ajax&module=redmegamenu&method=getMenuItems&format=json\',
					data : {
						menutype : menutype
					},
					dataType: \'json\'
				}).done(function(e) {
					if (e.success)
					{
						jQuery(\'#menuitems\').find(\'select\').html(e.data.html).trigger(\'liszt:updated\');
					}
				});
			}
		';
		$doc->addScriptDeclaration($script);

		return '<span id="menuitems">' . parent::getInput() . '</span>';
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
				$db->qn('m.id', 'value'),
				$db->qn('m.title', 'text'),
				$db->qn('mt.menutype', 'id'),
				$db->qn('mt.title', 'type')
			)
		)
			->from($db->qn('#__menu', 'm'))
			->innerJoin($db->qn('#__menu_types', 'mt') . ' ON ' . $db->qn('m.menutype') . ' = ' . $db->qn('mt.menutype'))
			->where($db->qn('published') . ' = 1');
		$items = array_merge($options, $db->setQuery($query)->loadObjectList());

		if (!empty($items))
		{
			$groups = array();

			foreach ($items as $item)
			{
				if (!isset($groups[$item->type]))
				{
					$groups[$item->id]        = new stdClass;
					$groups[$item->id]->title = $item->type;
					$groups[$item->id]->items = array($item);
				}
				else
				{
					$groups[$item->id]->items[] = $item;
				}
			}

			ksort($groups, SORT_ASC);

			foreach ($groups as $id => $group)
			{
				$options[] = JHtml::_(
					'select.optgroup',
					$group->title
				);

				foreach ($items as $item)
				{
					$options[] = JHtml::_('select.option', $item->value, $item->text);
				}

				$options[] = JHtml::_(
					'select.optgroup',
					$group
				);
			}
		}

		return $options;
	}
}
