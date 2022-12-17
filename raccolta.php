<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="css/pq.css">

    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
    <script src="js/pq.js"></script>

    <title>Pane Quotidiano</title>
</head>
<body>
<?php
error_reporting(E_ALL);

include "include/config.php";

$anno = date("Y");
$webUsername = $_SERVER['REDIRECT_REMOTE_USER'];
$link = mysqli_connect($ConfigMap['database']['hostname'], $ConfigMap['database']['username'], $ConfigMap['database']['password'])
or die('<h1>Could not connect: <h1>' . mysqli_error());
mysqli_select_db($link, $ConfigMap['database']['db_name'])
or die('<h1>Could not select database</h1>');
$voci = ['Pasta', 'Pomodoro', 'Legumi', 'Olio', 'Latte', 'Zucchero', 'Farina', 'Caffe', 'Sale', 'Scatolame', 'Panettoni', 'Colazioni'];

$raccoltePrec = [
    '2015' => [
        'Pasta' => 5138.9,
        'Pomodoro' => 2447.26,
        'Legumi' => 946.74,
        'Olio' => 639.25,
        'Latte' => 826.5,
        'Zucchero' => 656,
        'Farina' => 370.5,
        'Caffe' => 17.75,
        'Sale' => 126,
        'Scatolame' => 57.88,
        'Panettoni' => 118,
        'Colazioni' => 2440
    ],
    '2014' => [
        'Pasta' => 6005.75,
        'Pomodoro' => 2252.4,
        'Legumi' => 1403.4,
        'Olio' => 827.5,
        'Latte' => 1116,
        'Zucchero' => 688,
        'Farina' => 342,
        'Caffe' => 45.5,
        'Sale' => 116,
        'Scatolame' => 80.9,
        'Panettoni' => 118,
        'Colazioni' => 1540
    ]
];

$prezzoUnitario = [
    'Pasta' => 1.40,
    'Pomodoro' => 1.20,
    'Legumi' => 1.10,
    'Olio' => 3.00,
    'Latte' => 1.20,
    'Zucchero' => 1.30,
    'Farina' => 0.70,
    'Caffe' => 8.00,
    'Sale' => 0.50,
    'Scatolame' => 7.00,
    'Panettoni' => 3.00,
    'Colazioni' => 2.50
];

if (isset($_REQUEST['delete'])) {

    // Performing SQL query
    $id = $_REQUEST['delete'];
    $query = "DELETE FROM RACCOLTA WHERE ID=$id";
    $result = mysqli_query($link, $query) or die('<h1>Insert failed!</h1>' . mysqli_error($link));
    echo "<script>window.location = 'raccolta.php'</script>";
    exit;
}
if (isset($_REQUEST['insert'])) {

    // Performing SQL query
    $tipo = $_REQUEST['tipo'];
    $qta = $_REQUEST['qta'];
    $query = "INSERT INTO RACCOLTA (TIPO,QUANTITA,UTENTE,ANNO) VALUES ('$tipo',$qta,'$webUsername',$anno)";
    $result = mysqli_query($query) or die('<h1>Insert failed!</h1>' . mysqli_error());
}
$query = "
            SELECT ANNO,TIPO,SUM(QUANTITA) TOT_QTA
            FROM RACCOLTA
            GROUP BY ANNO,TIPO
        ";
$result = mysqli_query($link, $query) or die('<H1>Query failed</H1> ' . mysqli_error($link));

// Printing results in HTML
$tabella_dati = $raccoltePrec;
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    //echo "<!--" . print_r($line,true) . "-->";
    if (!isset($tabella_dati[$line['ANNO']])) {
        $tabella_dati[$line['ANNO']] = [];
    }
    $tabella_dati[$line['ANNO']][$line['TIPO']] = str_replace('.000', '', $line['TOT_QTA']);
}
echo "<!--" . print_r($tabella_dati, true) . "-->";

$query = "SELECT * FROM RACCOLTA ORDER BY ID DESC LIMIT 25";
$result = mysqli_query($link, $query) or die('<H1>Query Ultimi inserimenti failed</H1> ' . mysqli_error($link));

// Printing results in HTML
$tabella_ultimi = [];
while ($line = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
    $tabella_ultimi[] = $line;
}
echo "<!-- " . print_r($tabella_ultimi, true) . "-->";

// Free resultset
mysqli_free_result($result);

mysqli_close($link);
?>

<div data-role="page" id="insertMenu">
    <div data-role="header">
        <h1>Pane Quotidiano <?php echo $anno; ?></h1>
    </div>

    <div data-role="main" class="ui-content">
        <?php
        foreach ($voci as $voce) {
            $quantita = isset($tabella_dati[$anno][$voce]) ? $tabella_dati[$anno][$voce] : 0;
            echo "<a href=\"#\" onclick=\"carica('$voce');\" class=\"ui-btn\">$voce ($quantita)</a>";
        }
        ?>
    </div>

    <div data-role="footer">
        <a href="#statsPage" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-r ui-btn-icon-right">Statistiche</a>
        <a href="#cronoPage" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-r ui-btn-icon-right">Cronologia</a>
    </div>
</div>

<div data-role="page" data-dialog="true" id="insertData">
    <div data-role="header">
        <h1>Salva dati</h1>
    </div>

    <div data-role="main" class="ui-content">
        <div class="center-wrapper">
            <form id="frmSalva" action="raccolta.php" method="POST" data-ajax="false">
                <input type="hidden" name="insert" value="Y"/>
                <input type="hidden" name="tipo" id="fldTipo"/>

                <div data-role="fieldcontain">
                    <label for="qta">
                        <span id="tipoAlimento">&nbsp;</span>
                    </label>
                    <input type="number" name="qta" id="fldId" value="" step="0.001"/>
                </div>
                <br/>

                <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b ui-icon-back ui-btn-icon-left"
                   data-rel="back">Annulla</a>
                <button type="submit" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-plus ui-btn-icon-left"
                        data-rel="back" value="Carica">Carica
                </button>
            </form>
        </div>
    </div>

</div>


<div data-role="page" id="statsPage">
    <div data-role="header">
        <h1>Pane Quotidiano <?php echo $anno; ?></h1>
    </div>

    <div data-role="main" class="ui-content">
        <table data-role="table" class="ui-responsive">
            <thead
            >
            <tr>
                <th>&nbsp;</th>
                <th>Qt&agrave;</th>
                <th>Valore (&euro;)</th>
                <th>Vs. <?php echo $anno - 1; ?></th>
                <th>Vs. <?php echo $anno - 2; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $valore_tot = 0;
            foreach ($voci as $voce) {
                $valore = $tabella_dati[$anno][$voce] * $prezzoUnitario[$voce];
                $valore_tot += $valore;
                $perc = [
                    $anno - 1 => sprintf("%d %%", round((($tabella_dati[$anno][$voce] / $tabella_dati[$anno - 1][$voce]) - 1) * 100)),
                    $anno - 2 => sprintf("%d %%", round((($tabella_dati[$anno][$voce] / $tabella_dati[$anno - 2][$voce]) - 1) * 100))
                ];
                echo "<tr><td>$voce</td><td>{$tabella_dati[$anno][$voce]}</td><td>$valore</td><td>{$perc[$anno-1]}</td><td>{$perc[$anno-2]}</td></tr>";
            }
            echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td><b>$valore_tot</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
            ?>
            </tbody>
        </table>
    </div>

    <div data-role="footer">
        <a href="#insertMenu" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-l ui-btn-icon-right">Inserimenti</a>
    </div>
</div>


<div data-role="page" id="cronoPage">
    <div data-role="header">
        <h1>Pane Quotidiano 2016</h1>
    </div>

    <div data-role="main" class="ui-content">
        <table data-role="table" class="ui-responsive">
            <thead
            >
            <tr>
                <th>Utente</th>
                <th>Prodotto</th>
                <th>Quantita</th>
                <th>Data/ora</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $valore_tot = 0;
            foreach ($tabella_ultimi as $riga) {
                echo "<tr><td>{$riga['UTENTE']}</td><td>{$riga['TIPO']}</td><td>{$riga['QUANTITA']}</td><td>{$riga['INSERITO']}</td>";
                echo "<td><a href=\"raccolta.php?delete={$riga['ID']}\" class=\"ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-delete ui-btn-icon-right\">Cancella</a></td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div data-role="footer">
        <a href="#insertMenu" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-l ui-btn-icon-right">Inserimenti</a>
    </div>
</div>


</body>
</html>
