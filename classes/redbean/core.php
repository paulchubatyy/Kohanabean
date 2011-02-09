<?php
/**
 * RedBean ORM wrapper/helper for Kohana Framework
 *
 * @category	Database
 * @package 	Kohanabean
 * @author		Paul Chubatyy <xobb@citylance.biz>
 * @license     http://kohanaphp.com/license
 */

class Redbean_Core {
	protected static $instances = array();
	public static function instance($group = 'default')
	{
		if ( ! isset(self::$instances[$group])) {
			$config = Kohana::config('redbean' . $group);
			if ( ! class_exists('RedBean_Setup')) {
				require_once Kohana::find_file('vendor', 'redbean/RedBean/redbean.inc.php');
			}

			self::$instances[$group] = RedBean_Setup::kickstartDev(
				$config->dsn, $config->user, $config->pass);
			self::$instances[$group]->freeze(Kohana::$environment == Kohana::PRODUCTION);
		}
		return self::$instances[$group];
	}

	public static function db($group = 'default')
	{
		return self::instance($group)->getDatabaseAdapter();
	}

	public static function redbean()
	{
		return self::instance($group)->getRedBean();
	}
}
