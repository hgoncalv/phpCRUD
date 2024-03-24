<?php
require_once BASE_PATH . '/classes/Config.php';
require_once BASE_PATH . '/classes/Db.php';

class Student extends Database
{
	public $table = 'student';
	public $columns = ['id', 'name', 'major', 'gpa'];

	public $id;
	public $name;
	public $major;
	public $gpa;
}
