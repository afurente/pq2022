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
include "include/config.php";
global $ConfigMap;
$anno = 2021;
$webUsername = $_SERVER['REDIRECT_REMOTE_USER'];
$link = mysqli_connect($ConfigMap['database']['hostname'], $ConfigMap['database']['username'], $ConfigMap['database']['password']) or die('<h1>Could not connect: <h1>' . mysqli_error($link));
mysqli_select_db($link, $ConfigMap['database']['db_name']) or die('<h1>Could not select database</h1>');
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


$idconsegna = 0;
if (isset($_REQUEST['createConsegnat'])) {
    $nome = isset($_REQUEST['nomeConsegna']) ? $_REQUEST['nomeConsegna'] : "(vuota)";
    if ($nome == "")
        $nome = "(vuota)";
    $crea = "INSERT INTO CONSEGNE (NOME) VALUES ('$nome')";
    $result = mysqli_query($link, $crea) or die('<h1>Insert failed!</h1>' . mysqli_error($link));
    $idconsegna = mysqli_insert_id();
} else {
    $idconsegna = isset($_REQUEST['ID_CONSEGNA']) ? $_REQUEST['ID_CONSEGNA'] : 0;
}

$query = "SELECT * FROM CONSEGNE ORDER BY ID_CONSEGNA";
$result = mysqli_query($link, $query) or die('<H1>Query Ultimi inserimenti failed</H1> ' . mysqli_error($link));
$tabella_consegne = [];
$options_consegne = [];
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    if ($idconsegna == 0)
        $idconsegna = $line['ID_CONSEGNA'];
    $sel = ($line['ID_CONSEGNA'] == $idconsegna) ? "selected" : "";
    $options_consegne[] = "<option value=\"{$line['ID_CONSEGNA']}\" $sel>{$line['NOME']}</option>";
    $tabella_consegne[$line['ID_CONSEGNA']] = $line['NOME'];
}

if (isset($_REQUEST['delete'])) {

    // Performing SQL query
    $id = $_REQUEST['delete'];
    $query = "DELETE FROM DISTRIBUZIONE WHERE ID=$id";
    $result = mysqli_query($link, $query) or die('<h1>Insert failed!</h1>' . mysqli_error($link));
    echo "<script>window.location = 'distribuzione.php'</script>";
    exit;
}

if (isset($_REQUEST['insert'])) {

    // Performing SQL query
    $tipo = $_REQUEST['tipo'];
    $qta = $_REQUEST['qta'];
    $query = "INSERT INTO DISTRIBUZIONE (ID_CONSEGNA,TIPO,QUANTITA,UTENTE,ANNO) VALUES ($idconsegna,'$tipo',$qta,'$webUsername',$anno)";
    $result = mysqli_query($link, $query) or die('<h1>Insert failed!</h1>' . mysqli_error($link));
}
$query = "
            SELECT TIPO,SUM(QUANTITA) TOT_QTA
            FROM DISTRIBUZIONE
            WHERE ID_CONSEGNA = $idconsegna AND ANNO = $anno
            GROUP BY TIPO
        ";
$result = mysqli_query($link, $query) or die('<H1>Query failed</H1> ' . mysqli_error($link));

// Printing results in HTML
$tabella_dati = [];
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    //echo "<!--" . print_r($line,true) . "-->";
    $tabella_dati[$line['TIPO']] = str_replace('.000', '', $line['TOT_QTA']);
}

// COSTRUZIONE DELLE STATISTICHE DI CONSEGNA DEI GENERI RACCOLTI
$query = "
            SELECT TIPO,SUM(QUANTITA) TOT_QTA
            FROM RACCOLTA
            WHERE ANNO = $anno
            GROUP BY TIPO
        ";
$result = mysqli_query($link, $query) or die('<H1>Query failed</H1> ' . mysqli_error($link));

$tabella_raccolta = [];
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    //echo "<!--" . print_r($line,true) . "-->";
    $tabella_raccolta[$line['TIPO']] = str_replace('.000', '', $line['TOT_QTA']);
}

$query = "
            SELECT ID_CONSEGNA, TIPO, SUM(QUANTITA) TOT_QTA
            FROM DISTRIBUZIONE
            WHERE ANNO = $anno
            GROUP BY ID_CONSEGNA,TIPO
        ";
$result = mysqli_query($link, $query) or die('<H1>Query failed</H1> ' . mysqli_error($link));

$tabella_distro = [];
$tabella_giacenza = $tabella_raccolta;
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    //echo "<!--" . print_r($line,true) . "-->";
    if (!isset($tabella_distro[$line['ID_CONSEGNA']]))
        $tabella_distro[$line['ID_CONSEGNA']] = array();
    $tabella_distro[$line['ID_CONSEGNA']][$line['TIPO']] = str_replace('.000', '', $line['TOT_QTA']);
    $tabella_giacenza[$line['TIPO']] -= $line['TOT_QTA'];
}


// TABELLA ULTIME OPERAZIONI (PER CRONOLOGIA)
$query = "
            SELECT A.UTENTE,A.TIPO,A.QUANTITA,A.INSERITO,A.ID,B.NOME CONSEGNA
            FROM DISTRIBUZIONE A 
              INNER JOIN CONSEGNE B ON A.ID_CONSEGNA=B.ID_CONSEGNA
            ORDER BY ID DESC LIMIT 25";
$result = mysqli_query($link, $query) or die('<H1>Query Ultimi inserimenti failed</H1> ' . mysqli_error($link));

// Printing results in HTML
$tabella_ultimi = [];
while ($line = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $tabella_ultimi[] = $line;
}


// Free resultset
mysqli_free_result($result);

mysqli_close($link);
?>

<div data-role="page" id="insertMenu">
    <div data-role="header">
        <h1>Pane Quotidiano 2016</h1>
    </div>

    <div data-role="main" class="ui-content">
        <div class="ui-field-contain">
            Consegna:
            <a href="#createConsegna"
               class="ui-btn ui-shadow ui-corner-all ui-icon-plus ui-btn-icon-notext ui-btn-inline">Add</a>
            <select name="selectConsegna" id="selectConsegna">
                <?php
                foreach ($options_consegne as $opt) {
                    echo $opt;
                }
                ?>

            </select>
        </div>

        <?php
        foreach ($voci as $voce) {
            $quantita = isset($tabella_dati[$voce]) ? $tabella_dati[$voce] : 0;
            echo "<a href=\"#\" onclick=\"caricaConsegna('$voce');\" class=\"ui-btn\">$voce ($quantita)</a>";
        }
        ?>
    </div>

    <div data-role="footer">
        <a href="#statsPage" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-r ui-btn-icon-right">Statistiche</a>
        <a href="#cronoPage" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-arrow-r ui-btn-icon-right">Cronologia</a>
    </div>
</div>

<div data-role="page" data-dialog="true" id="createConsegna">
    <div data-role="header">
        <H1>Nuova consegna</H1>
    </div>

    <div data-role="main" class="ui-content">
        <div class="center-wrapper">
            <form id="frmSalva" action="distribuzione.php" method="POST" data-ajax="false">
                <input type="hidden" name="createConsegnat" value="Y"/>

                <div data-role="fieldcontain">
                    <input type="text" name="nomeConsegna" id="nomeConsegna" value=""/>
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
<div data-role="page" data-dialog="true" id="insertData">
    <div data-role="header">
        <H1><span id="idConsegna">&nbsp;</span></H1>
    </div>

    <div data-role="main" class="ui-content">
        <div class="center-wrapper">
            <form id="frmSalva" action="distribuzione.php" method="POST" data-ajax="false">
                <input type="hidden" name="insert" value="Y"/>
                <input type="hidden" name="tipo" id="fldTipo"/>
                <input type="hidden" name="ID_CONSEGNA" id="fldIDConsegna"/>

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
        <h1>Pane Quotidiano 2016</h1>
    </div>

    <div data-role="main" class="ui-content">
        <table data-role="table" class="ui-responsive">
            <thead
            >
            <tr>
                <th>&nbsp;</th>
                <th>Raccolta</th>
                <th>Giacenza</th>
                <?php
                foreach ($tabella_distro as $key => $details) {
                    echo "<th>" . $tabella_consegne[$key] . "</th>";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $valore_tot = 0;
            foreach ($voci as $voce) {
                echo "<tr><td>$voce</td><td>{$tabella_raccolta[$voce]}</td>";
                $perc_giac = round(($tabella_giacenza[$voce] / $tabella_raccolta[$voce]) * 100);
                echo "<td>{$tabella_giacenza[$voce]} ($perc_giac%)</td>";
                foreach ($tabella_distro as $details) {
                    $cella = (isset($details[$voce])) ? $details[$voce] : "&nbsp;";
                    echo "<td>$cella</td>";
                }
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
                <th>Consegna</th>
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
                echo "<tr><td>{$riga['UTENTE']}</td><td>{$riga['CONSEGNA']}</td><td>{$riga['TIPO']}</td><td>{$riga['QUANTITA']}</td><td>{$riga['INSERITO']}</td>";
                echo "<td><a href=\"distribuzione.php?delete={$riga['ID']}\" class=\"ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-delete ui-btn-icon-right\">Cancella</a></td>";
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
