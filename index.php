<?php
session_start();

// Inizializza identificatore univoco
if (!isset($_COOKIE['giocatore_id'])) {
    $id = uniqid('giocatore_', true);
    setcookie('giocatore_id', $id, time() + (86400 * 30));
    $_COOKIE['giocatore_id'] = $id;
}

// Inizializza nome e monete
if (!isset($_COOKIE['nome']) && isset($_GET['nome'])) {
    setcookie('nome', $_GET['nome'], time() + (86400 * 30));
    setcookie('monete', 1000, time() + (86400 * 30));
    setcookie('inventario', json_encode([]), time() + (86400 * 30));
    setcookie('cronologia', json_encode([]), time() + (86400 * 30));
    $_COOKIE['nome'] = $_GET['nome'];
    $_COOKIE['monete'] = 1000;
    $_COOKIE['inventario'] = json_encode([]);
    $_COOKIE['cronologia'] = json_encode([]);
}

// Oggetti del negozio
$oggetti = [
    'mela' => ['nome' => 'Mela', 'prezzo' => 100, 'emoji' => '🍎'],
    'spugna' => ['nome' => 'Spugna', 'prezzo' => 150, 'emoji' => '🧽'],
    'palla' => ['nome' => 'Palla', 'prezzo' => 200, 'emoji' => '🏐'],
];

// Gestione acquisto
if (isset($_GET['acquista']) && isset($oggetti[$_GET['acquista']])) {
    $chiave = $_GET['acquista'];
    $oggetto = $oggetti[$chiave];
    $monete = (int)$_COOKIE['monete'];

    if ($monete >= $oggetto['prezzo']) {
        $monete -= $oggetto['prezzo'];
        setcookie('monete', $monete, time() + (86400 * 30));
        $_COOKIE['monete'] = $monete;

        // Aggiorna inventario
        $inventario = json_decode($_COOKIE['inventario'], true);
        $inventario[$chiave] = ($inventario[$chiave] ?? 0) + 1;
        setcookie('inventario', json_encode($inventario), time() + (86400 * 30));
        $_COOKIE['inventario'] = json_encode($inventario);

        // Aggiorna cronologia
        $cronologia = json_decode($_COOKIE['cronologia'], true);
        $cronologia[] = [
            'oggetto' => $oggetto['nome'],
            'emoji' => $oggetto['emoji'],
            'prezzo' => $oggetto['prezzo'],
            'data' => date('Y-m-d H:i:s')
        ];
        setcookie('cronologia', json_encode($cronologia), time() + (86400 * 30));
        $_COOKIE['cronologia'] = json_encode($cronologia);

        $messaggio = "Hai acquistato una {$oggetto['nome']} {$oggetto['emoji']}!";
    } else {
        $messaggio = "Non hai abbastanza monete per acquistare {$oggetto['nome']}.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gioco delle Monete</title>
</head>
<body>
    <h1>Benvenuto nel Gioco delle Monete!</h1>

    <?php if (!isset($_COOKIE['nome'])): ?>
        <form method="get">
            <label>Inserisci il tuo nome: <input type="text" name="nome" required></label>
            <button type="submit">Inizia</button>
        </form>
    <?php else: ?>
        <p>Ciao <strong><?= htmlspecialchars($_COOKIE['nome']) ?></strong>!</p>
        <p>Hai <strong><?= $_COOKIE['monete'] ?></strong> monete.</p>

        <?php if (isset($messaggio)) echo "<p><em>$messaggio</em></p>"; ?>

        <h2>Negozio</h2>
        <ul>
            <?php foreach ($oggetti as $chiave => $dati): ?>
                <li>
                    <?= $dati['emoji'] ?> <strong><?= $dati['nome'] ?></strong> - <?= $dati['prezzo'] ?> monete
                    <a href="?acquista=<?= $chiave ?>">[Acquista]</a>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Inventario</h2>
        <ul>
            <?php
            $inventario = json_decode($_COOKIE['inventario'], true);
            if (empty($inventario)) {
                echo "<li>Nessun oggetto acquistato.</li>";
            } else {
                foreach ($inventario as $chiave => $quantita) {
                    echo "<li>{$oggetti[$chiave]['emoji']} {$oggetti[$chiave]['nome']} x{$quantita}</li>";
                }
            }
            ?>
        </ul>

        <h2>Cronologia Acquisti</h2>
        <ul>
            <?php
            $cronologia = json_decode($_COOKIE['cronologia'], true);
            if (empty($cronologia)) {
                echo "<li>Nessun acquisto effettuato.</li>";
            } else {
                foreach (array_reverse($cronologia) as $entry) {
                    echo "<li>{$entry['emoji']} {$entry['oggetto']} - {$entry['prezzo']} monete il {$entry['data']}</li>";
                }
            }
            ?>
        </ul>
    <?php endif; ?>
</body>
</html>
