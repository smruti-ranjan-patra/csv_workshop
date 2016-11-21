<?php
$start_time = microtime(true);

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
display($employee_num, $conn);

/**
 * Fetch the data from DB and display as CSV format
 *
 * @param  integer  $employee_num
 * @param  object  $conn
 * @return void
*/
function display($employee_num, $conn)
{
	$display_array = array();
	$select_query = "SELECT e.employee_id, e.first_name, e.last_name, GROUP_CONCAT(s.name) AS skills, 
					stk.stack_id, stk.nick_name, hr1.name AS created_by, hr2.name AS updated_by
					FROM employees e
					INNER JOIN employee_skill es ON e.id=es.emp_id
					INNER JOIN skills s	ON es.skill_id=s.id
					LEFT JOIN stackoverflow stk	ON e.id=stk.emp_id
					LEFT JOIN hr hr1 ON e.created_by=hr1.id
					LEFT JOIN hr hr2 ON e.updated_by=hr2.id
					GROUP BY e.id";
	$result = mysqli_query($conn, $select_query);
	$counter = 0;
	$skills_array = array();
	
	while($row = mysqli_fetch_assoc($result))
	{
		$display_array[$counter]['employee_id'] = $row['employee_id'];
		$display_array[$counter]['first_name'] = $row['first_name'];
		$display_array[$counter]['last_name'] = $row['last_name'];
		$skills_array = explode(",", $row['skills']);
		$display_array[$counter]['skill1'] = isset($skills_array[0]) ? $skills_array[0] : '';
		$display_array[$counter]['skill2'] = isset($skills_array[1]) ? $skills_array[1] : '';
		$display_array[$counter]['skill3'] = isset($skills_array[2]) ? $skills_array[2] : '';
		$display_array[$counter]['skill4'] = isset($skills_array[3]) ? $skills_array[3] : '';
		$display_array[$counter]['skill5'] = isset($skills_array[4]) ? $skills_array[4] : '';
		$display_array[$counter]['stack_id'] = $row['stack_id'];
		$display_array[$counter]['stack_nickname'] = $row['nick_name'];
		$display_array[$counter]['created_by'] = $row['created_by'];
		$display_array[$counter]['updated_by'] = $row['updated_by'];
		$counter++;
	}

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
						<th>Skill1</th>
						<th>Skill2</th>
						<th>Skill3</th>
						<th>Skill4</th>
						<th>Skill5</th>
						<th>StackID</th>
						<th>StackNickname</th>
						<th>CreatedBy</th>
						<th>UpdatedBy</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach($display_array as $values)
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
<?php
}

$end_time = microtime(true);
