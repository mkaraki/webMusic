<?php
$libraryInfo = DB::queryFirstRow('SELECT * FROM library WHERE id = %i', $this->id);
$acls = DB::query('SELECT a.id, u.username, a.permission FROM accessList a, user u WHERE libraryId = %i AND a.userid = u.id', $this->id);
$everyoneAcl = DB::queryFirstRow('SELECT * FROM accessList WHERE libraryId = %i AND userid IS NULL', $this->id);
if ($everyoneAcl !== null) {
    $everyoneAcl['username'] = "Everyone";
    $acls[] = $everyoneAcl;
}


$users = DB::query('SELECT id, username FROM user');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="/ui/assets/bootstrap.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management - webMusic Admin</title>
    <style>
        .tconform {
            display: inline-block;
        }

        h1 {
            margin-bottom: 0;
        }

        th {
            text-align: left;
        }

        td,
        th {
            padding-right: 40px;
        }
    </style>
</head>

<body class="m-3">
    <h1>Library: <?= htmlentities($libraryInfo['name']) ?> (<?= $this->id ?>)</h1>

    <small><?= htmlentities($libraryInfo['basepath']) ?></small>

    <hr>

    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Permission</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($acls as $acl) : ?>
                <tr>
                    <td><?= htmlentities($acl['username']) ?></td>
                    <td>
                        <?= ($acl['permission'] & 0b100) > 0 ? 'r' : '-' ?><?= ($acl['permission'] & 0b010) > 0 ? 'w' : '-' ?><?= ($acl['permission'] & 0b001) > 0 ? 'x' : '-' ?>
                    </td>
                    <td>
                        <form class="tconform" method="post" action="acl/<?= $acl['id'] ?>">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-danger btn-sm btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Add user</h3>
    <form method="POST" action="acl">
        <div class="mb-3">
            <label for="user" class="form-label">User</label>
            <select id="user" class="form-select" name="userid">
                <option value="NULL">Everyone</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?= $user['id'] ?>"><?= htmlentities($user['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="allow-read" name="read" value="1">
            <label class="form-check-label" for="allow-read">Allow read library</label>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="allow-write" name="write" value="1">
            <label class="form-check-label" for="allow-write">Allow write library</label>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="allow-play" name="play" value="1">
            <label class="form-check-label" for="allow-play">Allow play library</label>
        </div>
        <button type="submit" class="btn btn-primary">Add user</button>
    </form>

</body>

</html>