<?php
session_start();

include('common/mysql-connect.php');
$conn = connect_to_database();
?>
<!DOCTYPE html> 
<html>
<head>
    <title>Teacher Portal</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f2f2;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .choice-container {
            width: 300px;
            padding: 16px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1); 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .header {
            text-align: center;
            margin-bottom: -10px;
            color: #5c6bc0;
            font-size: 24px;
            font-weight: bold;
        }
        .sub-header {
            text-align: center;
            margin-bottom: 5px;
            color: #5c6bc0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .button {
            font-size: 12px;
            background-color: #7885d1;
            color: white;
            border-radius: 10px;
            border: none;
            width: 80px;
            height: 40px;
            margin-bottom: 10PX;
        }
        .button:hover {
            background-color: #5c6bc0;
        }
        .button-container{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        select {
            font-size: 12px;
            padding: 10px;
            border: none;
            background-color: #f8f8f8;
            color: #444;
            margin: 10px 0;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <h1 class ="header">Teacher Portal</h1>
        <h3 class ="sub-header">Students View</h3>
        <select id="selection">
            <option value="section-1">section 1</option>
            <option value="section-2">section 2</option>
            <option value="section-3">section 3</option>
        </select>
        <table id="section-1">
            <thead>
                <tr>
                    <th>Name-1</th>
                    <th>Grade</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="name">inject</td>
                    <td id="class">data</td>
                    <td id="email">here</td>
                    
                </tr>
            </tbody>
        </table>
        <table id="section-2" style="display: none;">
            <thead>
                <tr>
                    <th>Name-2</th>
                    <th>Grade</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="name">inject</td>
                    <td id="class">data</td>
                    <td id="email">here</td>
                    
                </tr>
            </tbody>
        </table>
        <table id="section-3" style="display: none;">
            <thead>
                <tr>
                    <th>Name-3</th>
                    <th>Grade</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="name">inject</td>
                    <td id="class">data</td>
                    <td id="email">here</td>
                    
                </tr>
            </tbody>
        </table>
        <div class="button-container">
        <button class="button" id = "back">Back</button>
    </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById('selection').addEventListener('change', function() {
            document.getElementById('section-1').style.display = this.value === 'section-1' ? 'table' : 'none';
            document.getElementById('section-2').style.display = this.value === 'section-2' ? 'table' : 'none';
            document.getElementById('section-3').style.display = this.value === 'section-3' ? 'table' : 'none';
        });
            document.getElementById('back').addEventListener('click', function(event) {
                window.location.href = "teacher.php";
            });
        };
    </script>
</body>
</html>