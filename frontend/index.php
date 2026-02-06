<?php
require_once 'api_client.php';
$api = new ApiClient();
$groups = $api->getGroups();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = $_POST['group_name'];
    $desc = $_POST['group_desc'];
    $api->createGroup($name, $desc);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChamaLedger - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">ChamaLedger</a></h1>
            <p>Community Savings Digital Ledger</p>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Create New Group</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" name="group_name" required placeholder="e.g., Weekly Savings Group">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="group_desc" placeholder="Optional description"></textarea>
                </div>
                <button type="submit" name="create_group" class="btn">Create Group</button>
            </form>
        </div>

        <div class="card">
            <h2>Your Savings Groups</h2>
            <?php if ($groups && count($groups) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Members</th>
                            <th>Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($group['name']); ?></strong><br><small><?php echo htmlspecialchars($group['description']); ?></small></td>
                            <td><?php echo $group['member_count']; ?></td>
                            <td><?php echo number_format($group['balance'], 2); ?></td>
                            <td><a href="groups.php?id=<?php echo $group['id']; ?>" class="btn btn-accent">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No groups found. Create one to get started!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
