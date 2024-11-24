<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $writingId = $_POST['writing_id'];
    $action = $_POST['action'];

    $sql = "SELECT title, content, CategoryName FROM writings WHERE WritingID = $writingId";
    $result = $conn->query($sql);
    $writing = $result->fetch_assoc();
    $CategoryName = $writing['CategoryName'];

    $CategoryIDQuery = "SELECT CategoryID FROM category WHERE CategoryName = '$CategoryName'";
    $CategoryResult = $conn->query($CategoryIDQuery);
    
    if ($CategoryResult->num_rows > 0) {
        $category = $CategoryResult->fetch_assoc();
        $CategoryID = $category['CategoryID'];
    } else {
        echo "Category '$CategoryName' does not exist.";
        exit;
    }

    if ($action === 'approve') {
        include 'send_mail_direct.php';
        
        $sql = "UPDATE writings SET approval_status = 'Approved' WHERE WritingID = $writingId";
        $conn->query($sql);
    } elseif ($action === 'deny') {
        $sql = "UPDATE writings SET approval_status = 'Denied' WHERE WritingID = $writingId";
        $conn->query($sql);
    }
}

$sql = "SELECT w.WritingID, w.title, w.content, w.CategoryName, u.UserName, w.created_at, w.approval_status 
        FROM writings AS w
        INNER JOIN writer AS wr ON w.WriterID = wr.WriterID
        INNER JOIN user AS u ON wr.UserID = u.UserID
        WHERE w.approval_status = 'Pending'
        ORDER BY w.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Review Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/writings.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-clipboard-check"></i>
            Content Review Dashboard
        </h1>
    </div>

    <div class="content-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="content-card" id="row-<?php echo htmlspecialchars($row['WritingID']); ?>">
                    <div class="card-header" onclick="openModal(<?php echo htmlspecialchars($row['WritingID']); ?>)">
                        <h2 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                        <div class="card-meta">
                            <span class="meta-item">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($row['UserName']); ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                            </span>
                        </div>
                        <span class="category-badge">
                            <?php echo htmlspecialchars($row['CategoryName']); ?>
                        </span>
                    </div>
                    <div class="card-content">
                        <?php echo htmlspecialchars($row['content']); ?>
                    </div>
                    <div class="card-actions">
                        <button onclick="openModal(<?php echo htmlspecialchars($row['WritingID']); ?>)" class="btn btn-view">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                    <div class="card-actions">
                        <button onclick="handleAction(<?php echo htmlspecialchars($row['WritingID']); ?>, 'approve')" class="btn btn-approve">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button onclick="handleAction(<?php echo htmlspecialchars($row['WritingID']); ?>, 'deny')" class="btn btn-deny">
                            <i class="fas fa-times"></i> Deny
                        </button>
                    </div>
                </div>

                <div id="modal-<?php echo htmlspecialchars($row['WritingID']); ?>" class="modal">
                    <div class="modal-content">
                        <span class="close-modal" onclick="closeModal(<?php echo htmlspecialchars($row['WritingID']); ?>)">
                            <i class="fas fa-times"></i>
                        </span>
                        <div class="modal-header">
                            <h2 class="modal-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                        </div>
                        <div class="modal-body">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>
                        <div class="modal-meta">
                            <span class="meta-item">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($row['UserName']); ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                            </span>
                        </div>
                        <div class="modal-actions">
                            <button onclick="handleAction(<?php echo htmlspecialchars($row['WritingID']); ?>, 'approve')" class="btn btn-approve">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button onclick="handleAction(<?php echo htmlspecialchars($row['WritingID']); ?>, 'deny')" class="btn btn-deny">
                                <i class="fas fa-times"></i> Deny
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-content">
                <i class="fas fa-folder-open"></i>
                <h3>No Pending Content</h3>
                <p>All content has been reviewed.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function openModal(id) {
            document.getElementById('modal-' + id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById('modal-' + id).style.display = 'none';
        }

        function handleAction(id, action) {
            if (confirm(`Are you sure you want to ${action} this writing?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const inputId = document.createElement('input');
                inputId.name = 'writing_id';
                inputId.value = id;

                const inputAction = document.createElement('input');
                inputAction.name = 'action';
                inputAction.value = action;

                form.appendChild(inputId);
                form.appendChild(inputAction);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
