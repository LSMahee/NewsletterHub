<?php

include 'db.php';

$selectedCategories = [];

if (isset($_POST['business'])) $selectedCategories[] = 'Business';
if (isset($_POST['sports'])) $selectedCategories[] = 'Sports';
if (isset($_POST['books'])) $selectedCategories[] = 'Books';
if (isset($_POST['quotes'])) $selectedCategories[] = 'Quotes';

$email = $_POST['email'];

$check_email = "SELECT UserID FROM user WHERE Email='$email'";
$result = $conn->query($check_email);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['UserID'];
    
    $sql = "INSERT INTO subscriber(UserID) VALUES ($user_id)";
    if ($conn->query($sql) === TRUE) {
        $subscriber_no = $conn->insert_id;

        foreach ($selectedCategories as $category) {
            $category_query = "SELECT CategoryID FROM category WHERE CategoryName = '$category'";
            $category_result = $conn->query($category_query);
            
            if ($category_result->num_rows > 0) {
                $category_row = $category_result->fetch_assoc();
                $category_id = $category_row['CategoryID'];
                
                $subscription = "INSERT INTO subscription (SubscriberNo, CategoryID, UserID) VALUES ('$subscriber_no', '$category_id', '$user_id')";
                $conn->query($subscription);
            }
        }
        echo "Your Subscription has been updated";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    $sql = "INSERT INTO user (Email) VALUES ('$email')";
    
    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;
        
        $sql = "INSERT INTO subscriber(UserID) VALUES ($user_id)";
        
        if ($conn->query($sql) === TRUE) {
            $subscriber_no = $conn->insert_id;

            foreach ($selectedCategories as $category) {
                $category_query = "SELECT CategoryID FROM category WHERE CategoryName = '$category'";
                $category_result = $conn->query($category_query);
                
                if ($category_result->num_rows > 0) {
                    $category_row = $category_result->fetch_assoc();
                    $category_id = $category_row['CategoryID'];
                    
                    $subscription = "INSERT INTO subscription (SubscriberNo, CategoryID, UserID) VALUES ('$subscriber_no', '$category_id', '$user_id')";
                    $conn->query($subscription);
                }
            }
            echo "Your Subscription has been updated";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
