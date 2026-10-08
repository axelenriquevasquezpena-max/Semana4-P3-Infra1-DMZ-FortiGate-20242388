<?php
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
$c = require '/etc/lab-app/database.php';
try {
    $db = new PDO(
        "mysql:host={$c['host']};dbname={$c['database']};charset=utf8mb4",
        $c['user'], $c['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
    $mensaje = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $cantidad = filter_var($_POST['existencias'] ?? '',
            FILTER_VALIDATE_INT, ['options' => ['min_range' => 0,
                                                'max_range' => 1000000]]);
        if ($nombre === '' || strlen($nombre) > 100 ||
            !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $precio) ||
            $cantidad === false) {
            $mensaje = 'Revisa el nombre, precio y existencias.';
        } else {
            $consulta = $db->prepare(
                'INSERT INTO productos (nombre,precio,existencias) VALUES (?,?,?)'
            );
            $consulta->execute([$nombre, $precio, $cantidad]);
            header('Location: /inventario.php?guardado=1');
            exit;
        }
    }
    $productos = $db->query(
        'SELECT id,nombre,precio,existencias FROM productos ORDER BY id'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Inventario: error DB '.$e->getCode());
    http_response_code(503);
    exit('Base de datos no disponible.');
}
?>
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sistema de Inventario</title>
<style>
body{font:16px Arial;background:#eef2f6;color:#172b40;margin:0}
main{max-width:950px;margin:40px auto;padding:28px;background:white;border-radius:12px}
h1{color:#17644c}form{display:flex;gap:12px;flex-wrap:wrap;margin:25px 0}
label{display:grid;gap:6px}input,button{padding:10px;font:inherit}
button{background:#17644c;color:white;border:0;border-radius:5px;cursor:pointer}
table{width:100%;border-collapse:collapse}th,td{padding:12px;text-align:left;border-bottom:1px solid #ddd}
th{background:#e5f2ec}.mensaje{font-weight:bold;color:#17644c}
</style>
<main>
<h1>Sistema de Inventario</h1>
<p>Servidor: WEB-INVENTARIO · Base de datos central: DB-P2</p>
<p class="mensaje"><?= h($mensaje) ?></p>
<?php if (isset($_GET['guardado'])): ?>
<p class="mensaje">Producto guardado.</p>
<?php endif; ?>
<h2>Agregar producto</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
<label>Nombre<input name="nombre" maxlength="100" required></label>
<label>Precio RD$<input name="precio" type="number" min="0"
max="99999999.99" step="0.01" required></label>
<label>Existencias<input name="existencias" type="number"
min="0" max="1000000" step="1" required></label>
<button>Guardar producto</button>
</form>
<h2>Productos disponibles</h2>
<table>
<tr><th>ID</th><th>Producto</th><th>Precio RD$</th><th>Existencias</th></tr>
<?php foreach ($productos as $p): ?>
<tr>
<td><?= h($p['id']) ?></td><td><?= h($p['nombre']) ?></td>
<td><?= h($p['precio']) ?></td><td><?= h($p['existencias']) ?></td>
</tr>
<?php endforeach; ?>
</table>
</main>
</html>
