<?php
include 'db.php';

$sql = "
    SELECT 
        w.id AS WriterID, 
        w.title, 
        w.content, 
        w.created_at AS writing_created_at
    FROM writings w
    JOIN writer wr ON w.WriterID = wr.WriterID
    ORDER BY w.created_at DESC
";

$result = $conn->query($sql);

$writings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $writings[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($writings);
?>
