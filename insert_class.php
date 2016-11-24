<?php

echo "start time -> " . microtime(true);
require_once('config.php');

class InsertCSV
{
    public $conn = NULL;
    private $raw_data = array();
    private $employee_num = 0;
    public $transaction_error = FALSE;
    public $num_skills = 0;

    /**
     * Constructor to create a database connection
     *
     * @access private
     * 
     * @param  array $db_param
     * @return void
     */
    public function __construct($db_param)
    {
        $this->conn = mysqli_connect($db_param['hostname'], $db_param['username'], 
            $db_param['password'], $db_param['database']);

        if(mysqli_connect_errno($this->conn))
        {
            $err = "Failed to create database connection";
            echo $err;
            exit;
        }
    }

    /**
     * Parse the CSV file
     *
     * @param  void
     * @return void
    */
    public function parseCsv()
    {
        try
        {
            //Open csv file
            $file = fopen("sheet.csv","r");
            //Parse the csv file into an array
            $this->raw_data = array_map('str_getcsv', file('sheet.csv'));
            //close the file
            fclose($file);
            // Calculate the number of rows in the csv file
            $this->employee_num = count($this->raw_data);
            // Calculate the number of skills in heading
            $this->num_skills = substr_count(strtolower(implode($this->raw_data[0])), 'skill');
        }
        catch(Exception $e)
        {
            echo "An exception occured -> " . $e;
            exit;
        }
    }

    /**
     * Run insert query
     *
     * @param  string $query
     * @param  string $table_name
     * @return boolean
    */
    public function insertQuery($query, $table_name)
    {
        if (TRUE === mysqli_query($this->conn, $query))
        {
            echo "<br>New record inserted into " . $table_name . "table successfully";
        }
        else
        {
            $this->transaction_error = TRUE;
            echo "<br>Error: in skills table " . $query . "<br>";
        }
    }

    /**
     * Insert all skills into skills table
     *
     * @param  array  $raw_data
     * @param  integer  $employee_num
     * @param  object  $conn
     * @return void
    */
    public function skills()
    {
        $skills = array();

        for($i=1; $i<$this->employee_num; $i++)
        {
            for($j=3; $j<3+$this->num_skills; $j++)
            {
                $skill_name = isset($this->raw_data[$i][$j]) ? strtolower($this->raw_data[$i][$j]) : '';
                $skills[] = mysqli_real_escape_string($this->conn, $skill_name);
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
        $this->insertQuery($query, 'skills');
    }

    /**
     * Insert all hr details into hr table
     *
     * @param  array  $raw_data
     * @param  integer  $employee_num
     * @param  object  $conn
     * @return void
    */
    function hr()
    {
        $hr_list = array();

        for($i=1; $i<$this->employee_num; $i++)
        {
            $hr_name = isset($this->raw_data[$i][5+$this->num_skills]) ? strtoupper($this->raw_data[$i][5+$this->num_skills]) : '';
            $hr_list[] = mysqli_real_escape_string($this->conn, $hr_name);

            $hr_name = isset($this->raw_data[$i][6+$this->num_skills]) ? strtoupper($this->raw_data[$i][6+$this->num_skills]) : '';
            $hr_list[] = mysqli_real_escape_string($this->conn, $hr_name);
        }

        $hr_list = array_filter(array_unique($hr_list));
        $query = "INSERT INTO hr (name)
                    VALUES ";

        foreach($hr_list as $hr)
        {
            $query .= "('" . $hr . "'),";
        }

        $query = rtrim($query, ",");
        $this->insertQuery($query, 'hr');
    }

    /**
     * Insert all hr details into hr table
     *
     * @param  array  $raw_data
     * @param  integer  $employee_num
     * @param  object  $conn
     * @return void
    */
    function employees()
    {
        $query = "INSERT INTO employees (employee_id, first_name, last_name, created_by, updated_by)
                    VALUES";

        for($i=1; $i<$this->employee_num; $i++)
        {
            $employee_id = mysqli_real_escape_string($this->conn, $this->raw_data[$i][0]);
            $first_name = mysqli_real_escape_string($this->conn, $this->raw_data[$i][1]);
            $last_name = mysqli_real_escape_string($this->conn, $this->raw_data[$i][2]);
            $created_by = $this->fetchHrId($this->conn, $this->raw_data[$i][5+$this->num_skills]);
            $updated_by = $this->fetchHrId($this->conn, $this->raw_data[$i][6+$this->num_skills]);
            $query .= "('{$employee_id}', '{$first_name}', '{$last_name}', $created_by, $updated_by),";
        }

        $query = rtrim($query, ",");
        $this->insertQuery($query, 'employees');
    }

    /**
     * Insert all relationship data into employee_skill pivot table
     *
     * @param  array  $raw_data
     * @param  integer  $employee_num
     * @param  object  $conn
     * @return void
    */
    function employeeSkill()
    {
        $query = "INSERT INTO employee_skill (emp_id, skill_id)
                    VALUES";

        for($i=1; $i<$this->employee_num; $i++)
        {
            for($j=3; $j<3+$this->num_skills; $j++)
            {
                if($this->raw_data[$i][$j] != '')
                {
                    $emp_id = $this->fetchEmpId($this->conn, $this->raw_data[$i][0]);
                    $skill_id = $this->fetchSkillId($this->conn, $this->raw_data[$i][$j]);
                    $query .= "($emp_id, $skill_id),";
                }
            }
        }

        $query = rtrim($query, ",");
        $this->insertQuery($query, 'employee_skill');
    }

    /**
     * Insert all stackoverflow details into stackoverflow table
     *
     * @param  array  $raw_data
     * @param  integer  $employee_num
     * @param  object  $conn
     * @return void
    */
    function stackoverflow()
    {
        $query = "INSERT INTO stackoverflow (emp_id, stack_id, nick_name)
                    VALUES";

        for($i=1; $i<$this->employee_num; $i++)
        {
            $emp_id = $this->fetchEmpId($this->conn, $this->raw_data[$i][0]);
            $stack_id = $this->raw_data[$i][3+$this->num_skills];
            $nick_name = mysqli_real_escape_string($this->conn, $this->raw_data[$i][4+$this->num_skills]);
            $query .= "({$emp_id}, '{$stack_id}', '{$nick_name}'),";
        }

        $query = rtrim($query, ",");
        $this->insertQuery($query, 'stackoverflow');
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
        $result = mysqli_fetch_array($this->conn->query($select_query), MYSQLI_ASSOC);
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
        $result = mysqli_fetch_array($this->conn->query($select_query), MYSQLI_ASSOC);
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
        $result = mysqli_fetch_array($this->conn->query($select_query), MYSQLI_ASSOC);
        return $result['id'];
    }
}

$insert_obj = new InsertCSV($db_credentials);
$insert_obj->parseCsv();
mysqli_autocommit($insert_obj->conn, FALSE);
$insert_obj->skills();
$insert_obj->hr();
$insert_obj->employees();
$insert_obj->employeeSkill();
$insert_obj->stackoverflow();

if($insert_obj->transaction_error)
{
    mysqli_rollback($insert_obj->conn);
}
else
{
    mysqli_commit($insert_obj->conn);
}

mysqli_close($insert_obj->conn);
echo "<br>end time -> " . microtime(true);
?>