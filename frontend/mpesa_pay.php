<?php
require_once 'api_client.php';
$api = new ApiClient();
$groupId = $_GET['group_id'] ?? null;
$memberId = $_GET['member_id'] ?? null;

if (!$groupId || !$memberId) {
    header("Location: index.php");
    exit;
}

// Fetch member details to show name - simplified by just carrying ID, in real app we'd fetch details
# For display purposes let's fetch members again
$members = $api->getGroupMembers($groupId);
$memberName = "Member";
$memberPhone = ""; 
foreach($members as $m) {
    if ($m['id'] == $memberId) {
        $memberName = $m['name'];
        $memberPhone = $m['phone'];
        break;
    }
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_mpesa'])) {
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];
    
    $response = $api->initiateMpesaPayment($memberId, $groupId, $phone, $amount);
    
    if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
        $message = "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 1rem;'>
            <strong>Success!</strong> Request sent to your phone. Please enter your PIN to complete the transaction.
        </div>";
        // Optionally redirect or wait for callback logic (but callback is async)
    } else {
        $error = $response['error'] ?? 'Unknown Error';
        $message = "<div style='color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 1rem;'>
            <strong>Error:</strong> Failed to initiate payment. ($error)
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with M-Pesa - ChamaLedger</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .mpesa-logo {
            max-width: 150px;
            margin: 0 auto 1rem;
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">ChamaLedger</a></h1>
        </div>
    </header>

    <div class="container">
        <div class="nav">
            <a href="groups.php?id=<?php echo $groupId; ?>">&larr; Back to Group</a>
        </div>

        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <!-- Simple Placeholder for M-Pesa Logo -->
            <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 1.5rem; color: #27ae60;">
                M-PESA
            </div>
            
            <h2 style="text-align: center;">Make Contribution</h2>
            <p style="text-align: center;">For: <strong><?php echo htmlspecialchars($memberName); ?></strong></p>
            
            <?php echo $message; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Amount (KES)</label>
                    <input type="number" step="1" name="amount" required placeholder="e.g. 500">
                </div>
                <div class="form-group">
                    <label>M-Pesa Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($memberPhone); ?>" required placeholder="2547XXXXXXXX">
                    <small>Format: 254712345678</small>
                </div>
                <button type="submit" name="pay_mpesa" class="btn btn-accent" style="width: 100%; font-weight: bold;">Pay Now</button>
            </form>
        </div>
    </div>
</body>
</html>
