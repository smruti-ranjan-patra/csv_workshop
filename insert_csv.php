<?php
$start_time = microtime(true);
echo "start time -> " . $start_time;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file = fopen("sheet.csv","r");
$raw_data = array_map('str_getcsv', file('sheet.csv'));
fclose($file);

$employee_num = count($raw_data);

$servername = "localhost";
$username = "root";
$password = "mindfire";
$dbname = "csv_workshop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make the skills to lower case
for($i=1; $i<$employee_num; $i++)
{
	for($j=3; $j<=7; $j++)
	{
		$raw_data[$i][$j] = strtolower($raw_data[$i][$j]);
	}
}

skills($raw_data, $employee_num, $conn);
hr($raw_data, $employee_num, $conn);
employees($raw_data, $employee_num, $conn);
employeeSkill($raw_data, $employee_num, $conn);
stackoverflow($raw_data, $employee_num, $conn);

/**
 * Insert all skills into skills table
 *
 * @param  array  $raw_data
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function skills($raw_data, $employee_num, $conn)
{
	$skills = array();

	for($i=1; $i<$employee_num; $i++)
	{
		for($j=3; $j<=7; $j++)
		{
			$skills[] = mysqli_real_escape_string($conn, $raw_data[$i][$j]);
		}
	}

	$skills = array_filter(array_unique($skills));
	$query = "INSERT INTO skills (name)
				VALUES ";

	foreach($skills as $skill)
	{
		$query .= "('" . $skill . "'),";
	}

	$query = rtrim($query, ",");

	if ($conn->query($query) === TRUE)
	{
	    echo "\nNew record inserted into skills table successfully";
	}
	else
	{
	    echo "\nError: in skills table " . $query . "<br>" . $conn->error;
	}
}

/**
 * Insert all hr details into hr table
 *
 * @param  array  $raw_data
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function hr($raw_data, $employee_num, $conn)
{
	$hr_list = array();

	for($i=1; $i<$employee_num; $i++)
	{
		$hr_list[] = mysqli_real_escape_string($conn, $raw_data[$i][10]);
		$hr_list[] = mysqli_real_escape_string($conn, $raw_data[$i][11]);
	}

	$hr_list = array_filter(array_unique($hr_list));
	$query = "INSERT INTO hr (name)
				VALUES ";

	foreach($hr_list as $hr)
	{
		$query .= "('" . $hr . "'),";
	}

	$query = rtrim($query, ",");

	if ($conn->query($query) === TRUE)
	{
	    echo "\nNew record inserted into hr table successfully";
	}
	else
	{
	    echo "\nError: in hr table " . $query . "<br>" . $conn->error;
	}
}

/**
 * Insert all hr details into hr table
 *
 * @param  array  $raw_data
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function employees($raw_data, $employee_num, $conn)
{
	$query = "INSERT INTO employees (employee_id, first_name, last_name, created_by, updated_by)
				VALUES";

	for($i=1; $i<$employee_num; $i++)
	{
		$employee_id = mysqli_real_escape_string($conn, $raw_data[$i][0]);
		$first_name = mysqli_real_escape_string($conn, $raw_data[$i][1]);
		$last_name = mysqli_real_escape_string($conn, $raw_data[$i][2]);
		$created_by = fetchHrId($conn, $raw_data[$i][10]);
		$updated_by = fetchHrId($conn, $raw_data[$i][11]);
		$query .= "('{$employee_id}', '{$first_name}', '{$last_name}', $created_by, $updated_by),";
	}

	$query = rtrim($query, ",");

	if ($conn->query($query) === TRUE)
	{
	    echo "\nNew record inserted into employees table";
	}
	else
	{
	    echo "\nError: in employees table " . $query . "<br>" . $conn->error;
	}
}

/**
 * Insert all relationship data into employee_skill pivot table
 *
 * @param  array  $raw_data
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function employeeSkill($raw_data, $employee_num, $conn)
{
	$query = "INSERT INTO employee_skill (emp_id, skill_id)
				VALUES";

	for($i=1; $i<$employee_num; $i++)
	{
		for($j=3; $j<=7; $j++)
		{
			if($raw_data[$i][$j] != '')
			{
				$emp_id = fetchEmpId($conn, $raw_data[$i][0]);
				$skill_id = fetchSkillId($conn, $raw_data[$i][$j]);
				$query .= "($emp_id, $skill_id),";
			}
		}
	}

	$query = rtrim($query, ",");

	if ($conn->query($query) === TRUE)
	{
	    echo "\nNew record inserted into employee_skill pivot table";
	}
	else
	{
	    echo "\nError: in employee_skill table " . $query . "<br>" . $conn->error;
	}
}

/**
 * Insert all stackoverflow details into stackoverflow table
 *
 * @param  array  $raw_data
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function stackoverflow($raw_data, $employee_num, $conn)
{
	$query = "INSERT INTO stackoverflow (emp_id, stack_id, nick_name)
				VALUES";

	for($i=1; $i<$employee_num; $i++)
	{
		$emp_id = fetchEmpId($conn, $raw_data[$i][0]);
		$stack_id = $raw_data[$i][8];
		$nick_name = mysqli_real_escape_string($conn, $raw_data[$i][9]);
		$query .= "({$emp_id}, {$stack_id}, '{$nick_name}'),";
	}

	$query = rtrim($query, ",");

	if ($conn->query($query) === TRUE)
	{
	    echo "\nNew record inserted into stackoverflow table";
	}
	else
	{
	    echo "\nError: in stackoverflow table " . $query . "<br>" . $conn->error;
	}
}

/**
 * Fetch HR Id from hr table
 *
 * @param  object  $conn
 * @param  string  $name
 * @return integer
*/
function fetchHrId($conn, $name)
{
	$select_query = "SELECT id 
					FROM hr
					WHERE name='" . $name . "'";
	$result = mysqli_fetch_array($conn->query($select_query), MYSQLI_ASSOC);
	return $result['id'];
}

/**
 * Fetch Employee Id from employees table
 *
 * @param  object  $conn
 * @param  string  $employee_id
 * @return integer
*/
function fetchEmpId($conn, $employee_id)
{
	$select_query = "SELECT id 
					FROM employees
					WHERE employee_id='" . $employee_id . "'";
	$result = mysqli_fetch_array($conn->query($select_query), MYSQLI_ASSOC);
	return $result['id'];
}

/**
 * Fetch skill id from skills table
 *
 * @param  object  $conn
 * @param  string  $skill
 * @return integer
*/
function fetchSkillId($conn, $skill)
{
	$select_query = "SELECT id 
					FROM skills
					WHERE name='" . $skill . "'";
	$result = mysqli_fetch_array($conn->query($select_query), MYSQLI_ASSOC);
	return $result['id'];
}

$conn->close();
$end_time = microtime(true);
?>