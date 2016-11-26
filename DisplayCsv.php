<?php

require_once('config.php');

class DisplayCsv
{
    public $conn = NULL;
    private $raw_data = array();
    private $employee_num = 0;
    public $display_array = array();
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
     * Fetch the data from DB and display as CSV format
     *
     * @param  void
     * @return void
    */
    public function fetchData()
    {
        $display_array = array();
        $select_query = "SELECT e.employee_id, e.first_name, e.last_name, GROUP_CONCAT(s.name) AS skills, stk.stack_id, stk.nick_name, hr1.name AS created_by, hr2.name AS updated_by
                        FROM employees e
                        LEFT JOIN employee_skill es ON e.id=es.emp_id
                        LEFT JOIN skills s  ON es.skill_id=s.id
                        LEFT JOIN stackoverflow stk ON e.id=stk.emp_id
                        LEFT JOIN hr hr1 ON e.created_by=hr1.id
                        LEFT JOIN hr hr2 ON e.updated_by=hr2.id
                        GROUP BY e.id";
        $result = mysqli_query($this->conn, $select_query);
        $counter = 0;
        
        while($row = mysqli_fetch_assoc($result))
        {
            $this->display_array[$counter]['employee_id'] = $row['employee_id'];
            $this->display_array[$counter]['first_name'] = $row['first_name'];
            $this->display_array[$counter]['last_name'] = $row['last_name'];
            $skills_array = explode(",", $row['skills']);

            for($i=1; $i<=$this->num_skills; $i++)
            {
                $key = 'skill' . $i;
                $this->display_array[$counter][$key] = isset($skills_array[$i-1]) ? $skills_array[$i-1] : '';
            }

            $this->display_array[$counter]['stack_id'] = $row['stack_id'];
            $this->display_array[$counter]['stack_nickname'] = $row['nick_name'];
            $this->display_array[$counter]['created_by'] = $row['created_by'];
            $this->display_array[$counter]['updated_by'] = $row['updated_by'];
            $counter++;
        }
    }
}

$display_obj = new DisplayCsv($db_credentials);
$display_obj->parseCsv();
$display_obj->fetchData();
mysqli_close($display_obj->conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>CSV Workshop</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="text-center">
            <h2>Employee Table</h2>
        </div>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>EmpID</th>
                    <th>Name</th>
                    <th>Last</th>
                    <?php
                        for($i=1; $i<=$display_obj->num_skills; $i++)
                        {
                            echo "<th>Skill" . $i . "</th>";
                        }
                    ?>
                    <th>StackID</th>
                    <th>StackNickname</th>
                    <th>CreatedBy</th>
                    <th>UpdatedBy</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($display_obj->display_array as $values)
                    {
                        echo "<tr>";
                        foreach($values as $value)
                        {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</html>