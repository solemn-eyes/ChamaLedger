<?php
require_once 'api_client.php';
$api = new ApiClient();
$groupId = $_GET['group_id'] ?? null;
$memberId = $_GET['member_id'] ?? null;

if (!$groupId || !$memberId) {
    header("Location: index.php");
    exit;
}

// Fetch member details to show name
$members = $api->getGroupMembers($groupId);
$memberName = "Member";
foreach($members as $m) {
    if ($m['id'] == $memberId) {
        $memberName = $m['name'];
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_contribution'])) {
    $amount = $_POST['amount'];
    $desc = $_POST['description'];
    $api->addContribution($memberId, $groupId, $amount, $desc);
    header("Location: groups.php?id=$groupId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Contribution - ChamaLedger</title>
    <link rel="stylesheet" href="style.css">
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

        <div class="card">
            <h2>Record Contribution</h2>
            <p>For: <strong><?php echo htmlspecialchars($memberName); ?></strong></p>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Amount (KES)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 500">
                </div>
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <input type="text" name="description" value="Monthly Contribution">
                </div>
                <button type="submit" name="record_contribution" class="btn">Record Transaction</button>
            </form>
        </div>
    </div>
</body>
</html>
