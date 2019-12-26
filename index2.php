<?php
$host = "tcp:codingclass.database.windows.net,1433";
$user = "sudo"; //sudo
$passwd = "Garuda11#"; //Garuda11#
$dbname = "dbCodingClass";

try {
    $db = new PDO("sqlsrv:server = $host; Database = $dbname", "$user", "$passwd");
    // set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon"/>
    <title>Coding Class</title>
    <style media="screen">
      .form{
        width: 100px!important;
        margin-right: 10px;
        float:left;
      }

      table{
        border-collapse: collapse;
      }

      td, th{
        border: 1px solid #222;
        padding: 5px 10px;
      }
      .clear{
        clear: both;
      }
    </style>
    <script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    </script>
  </head>
  <body>
    <h1>Welcome to CodingClass</h1>

    <h2>Form Register</h2>
    <form action="" method="post">
      <div class="form">Full Name :</div>
      <input type="text" name="name" value="" required>
        <div class="clear"></div>
        <br>
      <div class="form">Email :</div>
      <input type="email" name="email" value="" required>
        <div class="clear"></div>
        <br>
      <div class="form">Class :</div>
      <select name="class" required>
        <option selected disabled>-- Select Class --</option>
        <option value="Javascript">Java</option>
        <option value="Laravel">Laravel</option>
        <option value="NodeJS">NodeJS</option>
        <option value="Python">Python</option>
      </select>
        <div class="clear"></div>
        <br>
      <input type="submit" name="register" value="REGISTER">
    </form>
    <br>

    <?php
    if (isset($_POST['register'])) {
      $name = trim($_POST['name']);
      $email = trim($_POST['email']);
      $class = trim($_POST['class']);
      try {
          $sql = "INSERT INTO dbo.tb_users (fullname, email, class)
          VALUES ('$name', '$email', '$class')";
          $db->exec($sql);
            echo '<font color="green">Registration successfully!</font>';
      }
      catch(PDOException $e){
            echo '<font color="red">Registration failed!</font><br>'.$sql . '<br>' . $e->getMessage();
      }
    }
    ?>

    <br><br><hr>

    <h2>Registered Users</h2>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Class</th>
        </tr>
      </thead>
      <tbody>
      <?php

      try {
        $sql_get = $db->prepare("SELECT * FROM dbo.tb_users");
        $sql_get->execute();
        $result = $sql_get->fetchAll();

        if (count($result) > 0) {
            $x = 1;
            foreach($result as $row) {
              echo "
                <tr>
                <td>".$x."</td>
                <td>".$row["fullname"]."</td>
                <td>".$row["email"]."</td>
                <td>".$row["class"]."</td>
                </tr>";
                $x++;
            }
        } else {
            echo "0 results";
        }
      }
      catch(PDOException $e) {
          echo "Error: " . $e->getMessage();
      }

      ?>
      </tbody>
    </table>

  </body>
</html>
