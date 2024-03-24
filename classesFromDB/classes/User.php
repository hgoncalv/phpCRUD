<?php
require_once BASE_PATH . '/classes/Config.php';
require_once BASE_PATH . '/classes/Db.php';

class User extends Database
{
	public $table = 'user';
	public $columns = ['id', 'name', 'email', 'phone', 'created'];

	public $id;
	public $name;
	public $email;
	public $phone;
	public $created;
}
