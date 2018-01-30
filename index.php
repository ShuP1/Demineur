<?php
ini_set('xdebug.max_nesting_level', 20000);

session_start();
if(!isset($_SESSION['config']) || (isset($_POST['sizeX']) && isset($_POST['sizeY']) && isset($_POST['bombs']))) $_SESSION['config'] = array(
    'sizeX' => isset($_POST['sizeX']) ? $_POST['sizeX'] : 10,
    'sizeY' => isset($_POST['sizeY']) ? $_POST['sizeY'] : 10,
    'bombs' => isset($_POST['bombs']) ? $_POST['bombs'] : 10
);

if(!isset($_SESSION['cases']) || isset($_POST['Reset'])){
    $_SESSION['cases'] = array_fill(0, $_SESSION['config']['sizeX'], array_fill(0, $_SESSION['config']['sizeY'], array('bomb' => false, 'visible' => false)));

    for ($i=0; $i < $_SESSION['config']['bombs']; $i++) {
        $x; $y;
        do{
            $x = rand(0, count($_SESSION['cases'])-1);
            $y = rand(0, count($_SESSION['cases'][$x])-1);
        }while($_SESSION['cases'][$x][$y]['bomb']);
        $_SESSION['cases'][$x][$y]['bomb'] = true;
    }
}

$alive = true;

if(isset($_POST['xPos']) && isset($_POST['yPos'])){
    if(!$_SESSION['cases'][$_POST['xPos']][$_POST['yPos']]['visible']){
        if($_SESSION['cases'][$_POST['xPos']][$_POST['yPos']]['bomb']){
            $alive = false;
            for ($x=0; $x < count($_SESSION['cases']); $x++){
                for ($y=0; $y < count($_SESSION['cases'][$x]); $y++){
                    $_SESSION['cases'][$x][$y]['visible'] = true;
                }
            }
        }else{
            set_visible_rec($_POST['xPos'], $_POST['yPos']);
        }
    }
}

$win = true;
for ($x=0; $x < count($_SESSION['cases']) && $win; $x++){
    for ($y=0; $y < count($_SESSION['cases'][$x]) && $win; $y++){
        if(!$_SESSION['cases'][$x][$y]['bomb'] && !$_SESSION['cases'][$x][$y]['visible']) $win = false;
    }
}

function get_bomb_count($x, $y){
    $count = 0;
    for ($i=-1; $i <= 1; $i++) { 
        for ($j=-1; $j <= 1; $j++) { 
            if(isset($_SESSION['cases'][$x+$i][$y+$j]) && $_SESSION['cases'][$x+$i][$y+$j]['bomb']) $count++;
        }
    }
    return $count;
}

function set_visible_rec($x, $y){
    if(!$_SESSION['cases'][$x][$y]['visible']){
        $_SESSION['cases'][$x][$y]['visible'] = true;
        if(get_bomb_count($x, $y) == 0){
            for ($i=-1; $i <= 1; $i++) { 
                for ($j=-1; $j <= 1; $j++) { 
                    if(isset($_SESSION['cases'][$x+$i][$y+$j])) set_visible_rec($x+$i, $y+$j);
                }
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Demineur</title>
</head>
<body>
    <h1>Demineur</h1>
    <nav>
        <form method="post">
            <input type="hidden" name="Reset">
            <button type="submit">
                <img id="reset" src="<?= $alive ? ($win ? 'happy' : 'ok') : 'sad' ?>.gif" />
            </button>
        </form>
    </nav>
    <table id="cases">
    <?php for ($x=0; $x < count($_SESSION['cases']); $x++): ?>
        <tr>
        <?php for ($y=0; $y < count($_SESSION['cases'][$x]); $y++): ?> 
            <td>
            <?php
            if($_SESSION['cases'][$x][$y]['visible']){
                if($_SESSION['cases'][$x][$y]['bomb']){
                    echo '*';
                }else{
                    $count = get_bomb_count($x, $y);
                    if($count > 0){
                        echo '<span style="color: ';
                        switch ($count) {
                            case 1:
                                echo 'blue';
                                break;

                            case 2:
                                echo 'green';
                                break;

                            case 3:
                                echo 'red';
                                break;

                            case 4:
                                echo 'darkblue';
                                break;
                            
                            case 5:
                                echo 'darkred';
                                break;

                            default:
                                echo 'black';
                                break;
                        }
                        echo '">'.$count.'</span>';
                    }
                }
            }else{
            ?>
                 <form method="post">
                    <input type="hidden" name="xPos" value="<?= $x ?>">
                    <input type="hidden" name="yPos" value="<?= $y ?>">
                    <input type="submit" value=" ">
                </form>
            <?php } ?>
            </td>
        <?php endfor ?>
        </tr>
    <?php endfor ?>
    </table>
    <div id="config">
        <form method="post">
            <p>
                <label for="bombs">Bombs: </label>
                <input type="number" name="bombs" id="bombs" value="10">
            </p>
            <p>
                <label for="sizeX">Size X: </label>
                <input type="number" name="sizeX" id="sizeX" value="<?= count($_SESSION['cases']) ?>">
            </p>
            <p>
                <label for="sizeY">Size Y: </label>
                <input type="number" name="sizeY" id="sizeY" value="<?= count($_SESSION['cases'][0]) ?>">
            </p>
            <input type="submit" value="Reset" name="Reset">
        </form>
    </div>
</body>
</html>