<?php
$libraries = DB::query('SELECT * FROM library');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libraries - webMusic Admin</title>
    <link rel="stylesheet" href="/ui/assets/bootstrap.min.css">
    <style>
        .tconform {
            display: inline-block;
        }

        th {
            text-align: left;
        }

        td {
            padding-right: 40px;
        }
    </style>
</head>

<body class="m-3">
    <h1>Library Manager</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Path</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($libraries as $library) : ?>
                <tr>
                    <td><?= htmlentities($library['id']) ?></td>
                    <td><?= htmlentities($library['name'] ?? 'Unnamed library') ?></td>
                    <td><?= htmlentities($library['basepath']) ?></td>
                    <td>
                        <button onclick="location.href='library/<?= intval($library['id']) ?>/'" class="btn btn-info btn-sm">Edit</button>
                        <form class="tconform" method="post" action="library/<?= intval($library['id']) ?>/">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Add new library</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label" for="name">Library Name</label>
            <input class="form-control" type="text" name="name" id="name">
        </div>
        <div class="mb-3">
            <label class="form-label" for="basepath">Path</label>
            <input class="form-control" type="text" name="basepath" id="basepath">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="allow-guest" name="allowguest" value="1">
            <label class="form-check-label" for="allow-guest">Allow read/play for all users</label>
        </div>
        <button type="submit" class="btn btn-primary">Add library</button>
    </form>


</body>

</html>