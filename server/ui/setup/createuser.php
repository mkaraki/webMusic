<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create admin user - webMusic Setup</title>
    <link rel="stylesheet" href="/ui/assets/bootstrap.min.css">
</head>

<body>
    <h1>Create admin user</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="uname" class="form-label">Username</label>
            <input type="text" class="form-control" id="uname" name="username">
        </div>
        <div class="mb-3">
            <label for="pwd" class="form-label">Password</label>
            <input type="password" class="form-control" id="pwd" name="password">
        </div>
        <button type="submit" class="btn btn-primary">Create user</button>
    </form>
</body>

</html>