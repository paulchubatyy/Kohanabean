<?php
/**
 * RedBean ORM wrapper/helper for Kohana Framework
 * Shortcuts for RedBean class
 *
 * Shortcut     | Returned Object
 * -------------|---------------
 * [`RB:i`](#group)   | [RedBean Facade]
 * [`RB::db()`](#group) | [RedBean Database Adapter]
 *
 * @category	Database
 * @package 	Kohanabean
 * @author		Paul Chubatyy <xobb@citylance.biz>
 * @license     http://kohanaphp.com/license
 */
class RB {
	public static function i($group = 'default')
	{
		return RedBean::redbean($group);
	}

	public static function db($group)
	{
		return RedBean::db($group);
	}
}
