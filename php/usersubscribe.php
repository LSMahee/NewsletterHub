<?php
session_start();
include 'db.php';

$UserID = $_SESSION['UserID'];
$Email = $_SESSION['Email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $action = $_POST['action'];
    
    $category_query = "SELECT CategoryID FROM category WHERE CategoryName = '$category'";
    $result = $conn->query($category_query);
    $category_row = $result->fetch_assoc();
    $category_id = $category_row['CategoryID'];
    
    if ($action === 'subscribe') {
        $sql = "INSERT INTO subscriber(UserID) VALUES ($UserID)";
        $conn->query($sql);
        $subscriber_no = $conn->insert_id;
        
        $subscription = "INSERT INTO subscription (SubscriberNo, CategoryID, UserID) 
                        VALUES ($subscriber_no, $category_id, $UserID)";
        $conn->query($subscription);
        
        echo json_encode(['success' => true, 'message' => "Successfully subscribed to $category"]);
    } 
    else if ($action === 'unsubscribe') {
        $subscriber_query = "SELECT SubscriberNo FROM subscription 
                           WHERE UserID = $UserID AND CategoryID = $category_id";
        $subscriber_result = $conn->query($subscriber_query);
        $subscriber_row = $subscriber_result->fetch_assoc();
        $subscriber_no = $subscriber_row['SubscriberNo'];
        
        $delete = "DELETE FROM subscription 
                  WHERE SubscriberNo = $subscriber_no AND CategoryID = $category_id";
        $conn->query($delete);
        
        $check = "SELECT COUNT(*) as count FROM subscription WHERE SubscriberNo = $subscriber_no";
        $count_result = $conn->query($check);
        $count = $count_result->fetch_assoc()['count'];
        
        if ($count == 0) {
            $conn->query("DELETE FROM subscriber WHERE SubscriberNo = $subscriber_no");
        }
        
        echo json_encode(['success' => true, 'message' => "Successfully unsubscribed from $category"]);
    }
    exit();
}

$subscriptions = [];
$query = "SELECT c.CategoryName 
          FROM subscription s 
          JOIN category c ON s.CategoryID = c.CategoryID 
          WHERE s.UserID = $UserID";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $subscriptions[] = $row['CategoryName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/usersubscribe.css">
</head>
<body>
    <div class="container">
        <h1>Newsletter Subscriptions</h1>
        <div id="message" class="message"></div>
        <div class="category-list">
            <?php
            $categories = ['Business', 'Sports', 'Books', 'Quotes'];
            foreach ($categories as $category) {
                $isSubscribed = in_array($category, $subscriptions);
                $buttonClass = $isSubscribed ? 'btn btn-unsubscribe' : 'btn btn-subscribe';
                $buttonText = $isSubscribed ? 'Unsubscribe' : 'Subscribe';
                $action = $isSubscribed ? 'unsubscribe' : 'subscribe';
                ?>
                <div class="category-item">
                    <span><?php echo $category; ?></span>
                    <button 
                        class="<?php echo $buttonClass; ?>"
                        onclick="handleSubscription('<?php echo $category; ?>', '<?php echo $action; ?>', this)"
                    >
                        <?php echo $buttonText; ?>
                    </button>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
    function handleSubscription(category, action, button) {
        const formData = new FormData();
        formData.append('category', category);
        formData.append('action', action);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = document.getElementById('message');
                message.textContent = data.message;
                message.className = 'message success';
                message.style.display = 'block';
                
                if (action === 'subscribe') {
                    button.textContent = 'Unsubscribe';
                    button.className = 'btn btn-unsubscribe';
                    button.onclick = () => handleSubscription(category, 'unsubscribe', button);
                } else {
                    button.textContent = 'Subscribe';
                    button.className = 'btn btn-subscribe';
                    button.onclick = () => handleSubscription(category, 'subscribe', button);
                }

                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>
