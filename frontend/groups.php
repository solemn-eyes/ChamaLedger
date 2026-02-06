<?php
require_once 'api_client.php';
$api = new ApiClient();
$groupId = $_GET['id'] ?? null;

if (!$groupId) {
    header("Location: index.php");
    exit;
}

$groupBalance = $api->getGroupBalance($groupId);
$members = $api->getGroupMembers($groupId);
$transactions = $api->getGroupTransactions($groupId);

// Fetch group name (inefficient but works for now as we don't have getGroup endpoint separately or we iterate)
// Actually api->getGroups() returns all, we can filter. Or just trust the balance endpoint might have it if we updated it, but currently it just has ID and Balance.
// Let's just fetch all groups and find the name for display.
$allGroups = $api->getGroups();
$groupName = "Group " . $groupId;
foreach($allGroups as $g) {
    if ($g['id'] == $groupId) {
        $groupName = $g['name'];
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $name = $_POST['member_name'];
    $phone = $_POST['member_phone'];
    $api->addMember($name, $phone, $groupId);
    header("Location: groups.php?id=$groupId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($groupName); ?> - ChamaLedger</title>
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
            <a href="index.php">&larr; Back to Dashboard</a>
        </div>

        <div class="card">
            <h1><?php echo htmlspecialchars($groupName); ?></h1>
            <div class="balance-display">
                Total Balance: KES <?php echo number_format($groupBalance['balance'] ?? 0, 2); ?>
            </div>
        </div>

        <div class="card">
            <h2>Members</h2>
            <form method="POST" action="" style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <h3>Register New Member</h3>
                <div class="form-group">
                    <input type="text" name="member_name" required placeholder="Member Name">
                </div>
                <div class="form-group">
                    <input type="text" name="member_phone" required placeholder="Phone Number">
                </div>
                <button type="submit" name="add_member" class="btn">Add Member</button>
            </form>

            <?php if ($members && count($members) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Jioned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($member['joined_at'])); ?></td>
                            <td>
                                <a href="contributions.php?group_id=<?php echo $groupId; ?>&member_id=<?php echo $member['id']; ?>" class="btn">Record Cash</a>
                                <a href="mpesa_pay.php?group_id=<?php echo $groupId; ?>&member_id=<?php echo $member['id']; ?>" class="btn btn-accent">M-Pesa</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No members yet.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Transaction History</h2>
            <?php if ($transactions && count($transactions) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($t['date'])); ?></td>
                            <td><?php echo htmlspecialchars($t['member_name']); ?></td>
                            <td><?php echo htmlspecialchars($t['description']); ?></td>
                            <td style="color: <?php echo $t['amount'] >= 0 ? 'green' : 'red'; ?>;">
                                <?php echo number_format($t['amount'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No transactions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
