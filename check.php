<?php
$conn = mysqli_connect("localhost", "root", "", "qrcode");
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$res = mysqli_query($conn, "SELECT * FROM borrowed_equipment");
if ($res) {
    echo "Bilang ng record sa borrowed_equipment: " . mysqli_num_rows($res) . "<br><hr>";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "ID: " . $row['id'] . " | Student ID: " . $row['student_id'] . " | Status: " . (isset($row['status']) ? $row['status'] : 'Wala') . "<br>";
    }
} else {
    echo "May error sa table: " . mysqli_error($conn);
}
?>