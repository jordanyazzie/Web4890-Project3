
<?php include "templates/header.php"; ?>

<?php

require "../config.php";
require "../common.php";


/*Show previous problems*/
try {
    $connection = new PDO($dsn, $username, $password, $options);

    $sql = "SELECT
                `id`,`problem`
            FROM
                `calculator`
            ORDER BY
            id DESC";

    $statement = $connection->prepare($sql);
    $statement->execute();

    $result = $statement->fetchAll();

    } catch(PDOException $error) {
    echo $sql . "<br>" . $error->getMessage();
}

/*To CLEAR the table*/
if (isset($_POST["clear"])) {
    try {
        echo "<meta http-equiv='refresh' content='0'>";
        $connection = new PDO($dsn, $username, $password, $options);


        $sql3 = "TRUNCATE TABLE calculator";

        $statement3 = $connection->prepare($sql3);
        $statement3->execute();

    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
    }
}

/*Setting up the actual calculator*/
$buttons=[1,2,3,'+',4,5,6,'-',7,8,9,'*','C',0,'.','/','='];
$pressed='';
if(isset($_POST['pressed']) && in_array($_POST['pressed'],$buttons)){
    $pressed=$_POST['pressed'];
}
$stored='';
if(isset($_POST['stored']) && preg_match('~^(?:[\d.]+[*/+-]?)+$~',$_POST['stored'],$out)){
    $stored=$out[0];
}
$display=$stored.$pressed;
if($pressed=='C'){
    $display='';
}elseif($pressed=='=' && preg_match('~^\d*\.?\d+(?:[*/+-]\d*\.?\d+)*$~',$stored)){
    $display.=eval("return $stored;");

    /*Add items to the table when = is pressed*/
    $connection = new PDO($dsn, $username, $password, $options);
    try  {
        $new_calc = array(
            "problem"       => $display
    );

        $sql2 = sprintf(
            "INSERT INTO %s (%s) values (%s)",
            "calculator",
            implode(", ", array_keys($new_calc)),
            ":" . implode(", :", array_keys($new_calc))
        );

        $statement2 = $connection->prepare($sql2);
        $statement2->execute($new_calc);

    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
    }
}
/*The form for the calculator*/
echo "<div class='container'>";
echo "<div class='row'>";
echo "<div class='col-sm text-center'>";
echo "<form action=\"\" method=POST>";
echo "<table class='w-100'>";
echo "<tr>";
echo "<td colspan=\"4\" class=\"calcWindow\">$display</td>";
echo "</tr>";
foreach(array_chunk($buttons,4) as $chunk){
    echo "<tr>";
    foreach($chunk as $button){
        echo "<td",(sizeof($chunk)!=4?" colspan=\"4\"":""),"><button class=\"btn w-100 h3 text-white btn-active btn-submit\" name=\"pressed\" value=\"$button\">$button</button></td>";
    }
    echo "</tr>";
}
echo "</table>";
echo "<input type=\"hidden\" name=\"stored\" value=\"$display\">";
echo "</form>";
echo "</div>";
?>
<!--The rest of the application-->
<div class="col-sm">
    <form method="post">
        <input class="btn text-white btn-active  btn-submit mb-1 w-100" type="submit" name="clear" value="clear">
    </form>

    <table class="w-100">
        <thead>
        <tr>
            <th class="text-center">History</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($result as $row) : ?>
            <tr>
                <td class="number text-center"><?php echo escape($row["problem"]); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
</div>
<?php include "templates/footer.php"; ?>
