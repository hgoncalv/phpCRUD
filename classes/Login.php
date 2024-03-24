<?php
require_once BASE_PATH . '/classes/Config.php';
require_once BASE_PATH . '/classes/Db.php';

class Login extends Database
{
	public $table = 'login';
	public $columns = ['username', 'password'];

	public $username;
	public $password;
}
