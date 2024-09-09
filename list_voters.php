<?php
require 'vendor/autoload.php';
session_start();
use PhpOffice\PhpSpreadsheet\IOFactory;

$filename = 'data.xlsx';
$combary = [];
if (file_exists($filename)) {
    $spreadsheet = IOFactory::load($filename);
    $worksheet = $spreadsheet->getActiveSheet();
    $data = $worksheet->toArray();
} else {
    die("No voters data found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voters List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            overflow: hidden;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        thead {
            background-color: #007bff;
            color: #fff;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .scrollable-table {
            max-height: 600px;
            overflow-y: auto;
            display: block;
        }

        .scrollable-table table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .scrollable-table th,
        .scrollable-table td {
            white-space: nowrap;
        }

        .scrollable-table td {
            border: 1px solid #ddd;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .hidden {
            display: none;
        }

        .filter-container {
            text-align: center;
            margin: 20px 0px;
            display: grid;
            gap: 5px;
            grid-template-columns: repeat(7, 1fr);
        }

        .filter-container input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 1px 0px 1px 0px rgba(0, 0, 0, 0.59);
            -webkit-box-shadow: 1px 0px 1px 0px rgba(0, 0, 0, 0.59);
            -moz-box-shadow: 1px 0px 1px 0px rgba(0, 0, 0, 0.59);
        }

        .create-button1 {
            margin-left: 50%;
            display: block;
            width: 200px;
            padding: 10px;
            text-align: center;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
        }

        .create-button2 {
            margin-left: 20px;
            display: block;
            width: 200px;
            padding: 10px;
            text-align: center;
            background-color: #ff8100;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
        }

        .create-button3 {
            margin-left: 20px;
            display: block;
            width: 200px;
            padding: 10px;
            text-align: center;
            background-color: #ef3a3a;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
        }

        .create-button:hover {
            background-color: #0056b3;
        }

        .top-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .delete_btn {
            color: #de4242;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }   
        .planlist-back {
            position: fixed;
            float: left;
            left: 20px;
            bottom: 30px;
            width: 50px;
            height: 50px;
        }
        .planlist-back img{
            width: 100%;
            cursor: pointer;
        }

     
    </style>
</head>

<body>
    <div class="container">
        <div class="top-head">
            <h1>Voters List</h1>
            <a href="create_voter.php" class="create-button1">Create Voter</a>
            <?php if($_SESSION['user_role'] === 'admin'){?>
                <a href="#" id = "import" class="create-button2">Import</a>
            <?php }?>
            <a href="data.xlsx" class="create-button3">Export</a>
            <div id = "fileuploaddiv" class = "popup">
                <div class="popup-content">
                <span class="close">&times;</span>
                    <form id="uploadForm" action="fileupload.php" method="post" enctype="multipart/form-data">
                        <label for="fileToUpload">Choose a file(only *.xlsx):</label>
                        <input type="file" id="fileToUpload" name="fileToUpload" accept=".xlsx" required>
                        <br><br>
                        <button type="submit">Upload</button>
                    </form>
                </div>
            </div>    
        </div>
        <div class="filter-container">
            <?php
            for ($i = 0; $i < count($data[0]); $i++) {
                echo "<input type='text' id='filter-$i' list = 'filter-$i-s' placeholder='Filter by " . htmlspecialchars($data[0][$i]) . "' oninput='filterTable()'>";
                // $combary['']
            }
            ?>
        </div>
        <div class="scrollable-table">
            <table id="votersTable">
                <thead>
                    <tr>
                        <?php
                        $numHeaders = count($data[0]);
                        for ($i = 0; $i < $numHeaders; $i++) {
                            echo "<th>" . htmlspecialchars($data[0][$i]) . "</th>";
                        }
                        ?>
                        <th>Edit</th>
                        <th>Delete</th> <!-- Add Delete Header -->
                    </tr>
                </thead>

                <tbody>
                    <?php
                    for ($i = 1; $i < count($data); $i++) {
                        echo "<tr>";
                        for ($j = 0; $j < count($data[$i]); $j++) {
                            // echo count($data[$i]);
                            // die();
                            if($j == 4)
                                $combary['province'][$data[$i][$j]] = $data[$i][$j];
                            if($j == 5)
                                $combary['city'][$data[$i][$j]] = $data[$i][$j];
                            if($j == 6)
                                $combary['barangay'][$data[$i][$j]] = $data[$i][$j];
                            if($j == 7)
                                $combary['precinct'][$data[$i][$j]] = $data[$i][$j];
                            echo "<td>" . htmlspecialchars($data[$i][$j]) . "</td>";
                        }
                        $id = $i + 1;
                        echo "<td><a href='edit_voter.php?row=$id'>Edit</a></td>";
                        echo "<td><a class='delete_btn' href='delete_voter.php?row=$id' onclick='return confirm(\"Are you sure you want to delete this voter?\");'>Delete</a></td>";
                        echo "</tr>";
                    }
                    // var_dump($combary);exit;
                    $combJson = json_encode($combary);
                    ?>
                </tbody>

            </table>
        </div>
    </div>
    <div class="planlist-back"><img src="https://svgrepo.com/show/67631/back-arrow.svg" onclick="window.history.back()"></img></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $('#import').click(function(event){
        // if (event.target.classList.contains('popup')) {
            document.getElementById("fileuploaddiv").style.display = 'block';
            document.getElementById("fileToUpload").innerHTML = "";
            // console.log("ddd");
        // }
    });

function filterTable() {
    // Get the table and its rows
    const table = document.getElementById('votersTable');
    const rows = table.querySelectorAll('tbody tr');

    // Get filter inputs excluding the last one (for "Edit" and "Delete" columns)
    const filters = [];
    const numColumns = table.querySelectorAll('thead th').length;
    
    // Check if filter inputs exist
    for (let i = 0; i < numColumns - 2; i++) { // Adjusted for "Edit" and "Delete" columns
        const filterInput = document.getElementById(`filter-${i}`);
        if (filterInput) {
            filters[i] = filterInput.value.toLowerCase();
        } else {
            console.error(`Filter input with ID filter-${i} not found.`);
            filters[i] = ''; // Ensure filters array is properly populated
        }
    }

    // Iterate through rows and apply filters
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let visible = true;

        cells.forEach((cell, index) => {
            if (index < numColumns - 2 && filters[index] && !cell.textContent.toLowerCase().includes(filters[index])) {
                visible = false;
            }
        });

        row.style.display = visible ? '' : 'none';
    });
}

// Call the function after the page has fully loaded
window.onload = function () {
    filterTable(); // Initial filter application
    var combary = <?php echo $combJson; ?>;

    var provinceElement = document.getElementById('filter-4');
    const provinceAry = Object.entries(combary['province']);
    provinceElement.innerHTML = "";
    var datalist = document.createElement('datalist');
    datalist.id = "filter-4-s";
    provinceAry.forEach(function(value,index){
        const option = document.createElement('option');
        option.value = value[0];
        datalist.appendChild(option);
    });
    provinceElement.appendChild(datalist);       

    var cityElement = document.getElementById('filter-5');
    const cityAry = Object.entries(combary['city']);
    cityElement.innerHTML = "";
    var datalist = document.createElement('datalist');
    datalist.id = "filter-5-s";
    cityAry.forEach(function(value,index){
        const option = document.createElement('option');
        option.value = value[0];
        datalist.appendChild(option);
    });
    cityElement.appendChild(datalist);  

    var barangayElement = document.getElementById('filter-6');
    const barangayAry = Object.entries(combary['barangay']);
    barangayElement.innerHTML = "";
    var datalist = document.createElement('datalist');
    datalist.id = "filter-6-s";
    barangayAry.forEach(function(value,index){
        const option = document.createElement('option');
        option.value = value[0];
        datalist.appendChild(option);
    });
    barangayElement.appendChild(datalist);  

    var precinctElement = document.getElementById('filter-7');
    const precinctAry = Object.entries(combary['precinct']);
    precinctElement.innerHTML = "";
    var datalist = document.createElement('datalist');
    datalist.id = "filter-7-s";
    precinctAry.forEach(function(value,index){
        const option = document.createElement('option');
        option.value = value[0];
        datalist.appendChild(option);
    });
    precinctElement.appendChild(datalist);  

    document.querySelectorAll('.close').forEach(function (element) {
        element.addEventListener('click', function () {
            this.closest('.popup').style.display = 'none';
        });
    });

};

    </script>
</body>

</html>