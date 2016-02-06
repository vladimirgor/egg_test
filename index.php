<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head lang="en">
    <meta charset="UTF-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet"
          href="css/bootstrap.min.css"
        >
    <!-- Optional theme -->
    <link rel="stylesheet"
          href="css/bootstrap-theme.min.css"
        >
    <!-- jquery -->
    <script language="javascript" src="/js/jquery-1.11.3.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script language="javascript" src="/js/bootstrap.min.js"></script>

    <script type="text/javascript">
        function checkFilter_choosing() {
            var valid = true;
            var checked = false;
            var radios = $('input[type=radio]');
            $.each(radios, function (i, e) {
                if ( radios[i].checked )
                {
                    checked = true;
                }
            });
            if (!checked) {
                $('#log').html('Please, choose \'Managers\' or \'Employees\'.');
                valid = false;
            }
            return valid;
        }
    </script>

    <title>EGG-test</title>
</head>

<body>
<?php
if ( !empty($_POST) ) {
    include_once(__DIR__ . '/config.php');
    $session = htmlspecialchars($_POST['session_id']);
    if ($session != session_id()) {
        echo '</br>'.'Something went wrong. Please, apply to system administrator.';
        error_log(date('Y-m-d H:i:s') . ' - Attempt of data transmission from the other host.'
            . PHP_EOL, 3, __DIR__ . '/'.LOG);
        die;
    } else {
        require_once(__DIR__ . '/functions.php');
        $pdo = dbConnection();
        if ( $_POST['filter'] == 'managers'){ $id_filter = 'id_e'; $id_out = 'id_m';
            $table_in = EMPLOYEES; $table_out = MANAGERS;}
        elseif ($_POST['filter'] == 'employees'){ $id_filter = 'id_m'; $id_out = 'id_e';
            $table_in = MANAGERS; $table_out = EMPLOYEES;}
        else {
            echo '</br>'.'Something went wrong. Please, apply to system administrator.';
            error_log( date('Y-m-d H:i:s'). ' - Filter receiving error.' .
                PHP_EOL,3,__DIR__ . '/'.LOG);
            die;
        }
        $name = htmlspecialchars(strip_tags($_POST['name']));
        try {
            $stmt = $pdo->prepare("
                  SELECT `id`
                  FROM `$table_in`
                  WHERE `name` = :name");
            $stmt->execute([':name' => $name]);
            $id = $stmt->fetchColumn();
            if (!$id) {
                $message = $name . ' doesn\'t exist in the ' . $table_in . ' list.';
            }
            else {
                $groups = GROUPS;
                try {
                    $stmt = $pdo->prepare("
                          SELECT *
                          FROM `$groups`
                          LEFT JOIN `$table_out`
                          ON $groups.$id_out = $table_out.id
                          WHERE $groups.$id_filter = :id");
                    $stmt->execute([':id' => $id]);
                    $id_out_DB = $stmt->fetchAll();
                    if ( !$id_out_DB ) {
                        $message = 'For '. $name . ' there are no  ' . $_POST['filter'].'.' ;
                    } else {
                        $message = $name .' has as '. $_POST['filter'] . ':  </br>';
                        foreach ($id_out_DB as $array) {
                            $message = $message . $array['name'] . '</br>';
                        }
                    }
                } catch (PDOException $e) {
                    echo '</br>'.'Something went wrong. Please, apply to system administrator.';
                    error_log( date('Y-m-d H:i:s'). ' - Id selection error: ' . $groups .'--'
                        . $e -> getMessage(). PHP_EOL,3,__DIR__ . '/'.LOG);
                    die;
                }
            }
        } catch (PDOException $e) {
            echo '</br>'.'Something went wrong. Please, apply to system administrator.';
            error_log( date('Y-m-d H:i:s'). ' - Id selection error: ' . $table_in .'--'
                . $e -> getMessage(). PHP_EOL,3,__DIR__ . '/'.LOG);
            die;
        }
    }
}
?>

<div class="container">
    <form class="form-horizontal" method="post"
          onsubmit="return checkFilter_choosing();">

        <div class="form-group"><b>For</b><br>
            <div class="col-sm-8">
                <input type="text" required class="form-control"
                       name ="name" pattern="^[A-ZА-Я][a-zа-я]+\s[A-ZА-Я][a-zа-я]+" placeholder="Name">
            </div>
        </div>

        <div class="form-group"><b>Find</b><br>
            <input type="radio" name="filter" value="managers">Managers<Br>
            <input type="radio" name="filter" value="employees">Employees<Br>
        </div>

        <input type = "hidden" name = "session_id"
               value="<?php echo session_id();?>">

        <div class="form-group">
            <div class="col-sm-8">
                <button type="reset" class="btn btn-danger">Clear</button>
            </div>
        </div>

        <div class="form-group">
            <div class=" col-sm-8">
                <button type="submit"  class="btn btn-success">Sent</button>
            </div>
        </div>
    </form>
    <h4 id = "log">
        <?php
        if ( isset($message) ) { echo $message;}
        ?>
    </h4>
</div>
</body>
</html>